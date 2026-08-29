<?php

namespace App\Services;

use App\Contracts\PaymentInterface;
use App\Integrations\ProviderDefinition;
use App\Integrations\ProviderRegistry;
use App\Models\Order;
use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Payments\PaymentContext;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Starts and settles payments.
 *
 * Two rules hold this together:
 *   1. An order is only ever marked paid from a verified gateway response — a webhook or
 *      a server-to-server verify. The browser return URL is never trusted.
 *   2. Settlement is idempotent. Gateways retry, so the same event will arrive twice.
 */
class PaymentService
{
    public function __construct(private readonly ProviderRegistry $registry) {}

    /**
     * Gateways that can take this order, keyed by slug.
     *
     * @return array<string, ProviderDefinition>
     */
    public function availableFor(string $currency, ?string $country = null): array
    {
        return $this->registry->forCheckout($currency, $country);
    }

    public function driver(string $provider): PaymentInterface
    {
        $driver = $this->registry->driver($provider);

        if (! $driver instanceof PaymentInterface) {
            throw new \RuntimeException("{$provider} is not a payment gateway.");
        }

        return $driver;
    }

    /**
     * Open a payment attempt for an order and hand back what the customer should do next.
     *
     * The return and cancel URLs are built here because they must carry the reference,
     * which is only generated once the attempt row exists.
     */
    public function begin(Order $order, string $provider): PaymentResult
    {
        $row     = $this->registry->rowOrStub($provider);
        $payment = Payment::create([
            'order_id'        => $order->id,
            'provider'        => $provider,
            'environment'     => $row->environment ?: 'sandbox',
            'status'          => PaymentStatus::Pending->value,
            'amount_minor'    => \App\Support\Currencies::toMinor(
                $order->presentment_total,
                $order->presentment_currency ?: $order->base_currency,
            ),
            'currency'        => $order->presentment_currency ?: $order->base_currency,
            'base_amount'     => $order->total,
            'base_currency'   => $order->base_currency,
            'exchange_rate'   => $order->exchange_rate,
            'idempotency_key' => $order->order_number.'-'.Str::lower(Str::random(12)),
        ]);

        $context = PaymentContext::forOrder(
            $order,
            $payment->idempotency_key,
            returnUrl: route('payment.return', ['reference' => $payment->idempotency_key]),
            cancelUrl: route('payment.cancel', ['reference' => $payment->idempotency_key]),
            webhookUrl: route('payments.webhook', ['provider' => $provider]),
        );

        try {
            $result = $this->driver($provider)->charge($context);
        } catch (\Throwable $e) {
            Log::error('Payment charge failed', ['provider' => $provider, 'order' => $order->order_number, 'error' => $e->getMessage()]);
            $result = PaymentResult::failed($e->getMessage());
        }

        $this->apply($payment, $result);

        // A form-post gateway cannot be followed directly; send the customer to our own
        // page, which replays the signed fields as a browser POST.
        if ($result->formPayload() !== null) {
            return PaymentResult::redirect(
                route('payment.form', ['reference' => $payment->idempotency_key]),
                $result->transactionId,
                $result->raw,
            );
        }

        return $result;
    }

    /**
     * Record a result against an attempt and move the order with it.
     *
     * Safe to call repeatedly: once an attempt is paid it is never re-settled, which is
     * what stops a replayed webhook from double-crediting an order.
     */
    public function apply(Payment $payment, PaymentResult $result): Payment
    {
        return DB::transaction(function () use ($payment, $result) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();

            if ($payment->isSettled()) {
                return $payment;
            }

            $payment->fill([
                'status'                 => $result->status->value,
                'gateway_transaction_id' => $result->transactionId ?? $payment->gateway_transaction_id,
                'error'                  => $result->error,
                'payload'                => $result->raw ?: $payment->payload,
            ]);

            if ($result->status->isSettled()) {
                $payment->paid_at = now();
            }

            $payment->save();

            $this->syncOrder($payment->order, $result->status, $payment);

            return $payment;
        });
    }

    /**
     * Process an inbound webhook. Returns true when the event was accepted.
     *
     * Signature verification happens first because webhooks arrive unauthenticated — the
     * signature is the only thing proving the request came from the gateway.
     */
    public function handleWebhook(string $provider, Request $request): bool
    {
        $driver = $this->driver($provider);

        if (! $driver->verifySignature($request)) {
            Log::warning('Rejected webhook with an invalid signature', ['provider' => $provider]);

            return false;
        }

        $event = $driver->parseWebhook($request);

        if ($event === null || $event->isIgnorable()) {
            return true; // understood, nothing to do
        }

        // Claiming the event row IS the replay guard: a duplicate delivery collides on the
        // (provider, event_id) unique index and is dropped before it can settle anything.
        try {
            $record = WebhookEvent::create([
                'provider'   => $provider,
                'event_id'   => $event->id,
                'event_type' => $event->type,
                'payload'    => $event->raw,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            return true;
        }

        $payment = Payment::where('idempotency_key', $event->reference)
            ->orWhere(fn ($q) => $event->transactionId ? $q->where('gateway_transaction_id', $event->transactionId) : $q->whereRaw('1 = 0'))
            ->first();

        if (! $payment) {
            $record->update(['error' => 'No matching payment attempt', 'processed_at' => now()]);

            return true;
        }

        $this->apply($payment, new PaymentResult(
            status: $event->status,
            transactionId: $event->transactionId,
            raw: $event->raw,
        ));

        $record->update(['processed_at' => now()]);

        return true;
    }

    /**
     * Ask the gateway directly what happened. Used when the customer returns from a
     * hosted page, where the redirect itself carries no proof of payment.
     */
    public function verify(Payment $payment): PaymentResult
    {
        $result = $this->driver($payment->provider)->verify($payment);
        $this->apply($payment, $result);

        return $result;
    }

    private function syncOrder(Order $order, PaymentStatus $status, Payment $payment): void
    {
        $target = $status->orderPaymentStatus();

        if ($order->payment_status === $target) {
            return;
        }

        // A later failed attempt must not un-pay an order that already settled.
        if ($order->payment_status === 'paid' && $target !== 'paid') {
            return;
        }

        $order->payment_status = $target;

        if ($target === 'paid' && ! $order->paid_at) {
            $order->paid_at = now();
        }

        $order->save();

        $order->logActivity(
            'payment_'.$target,
            'Payment '.$target,
            "{$payment->provider}: {$payment->formattedAmount()}",
            ['payment_id' => $payment->id, 'transaction_id' => $payment->gateway_transaction_id],
        );
    }
}

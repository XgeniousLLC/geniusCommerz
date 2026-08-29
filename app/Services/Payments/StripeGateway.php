<?php

namespace App\Services\Payments;

use App\Contracts\PaymentInterface;
use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use App\Services\ProviderDriver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Stripe via hosted Checkout Sessions.
 *
 * Uses the REST API over the Http facade rather than the SDK, matching every other driver
 * in this codebase and avoiding a dependency for what is three endpoints.
 *
 * Test vs live is decided by which secret key is loaded, which the environment-scoped
 * credential store already handles — hence no base-URL setting.
 */
class StripeGateway extends ProviderDriver implements PaymentInterface
{
    private const API = 'https://api.stripe.com/v1';

    /** Reject signatures older than this, so a captured request cannot be replayed later. */
    private const SIGNATURE_TOLERANCE = 300;

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->asForm()->post(self::API.'/checkout/sessions', [
            'mode'                 => 'payment',
            'success_url'          => $context->returnUrl,
            'cancel_url'           => $context->cancelUrl,
            'client_reference_id'  => $context->reference,
            'metadata'             => ['order_number' => $context->order->order_number],
            'line_items'           => [[
                'quantity'   => 1,
                'price_data' => [
                    'currency'     => strtolower($context->currency),
                    'unit_amount'  => $context->amountMinor,
                    'product_data' => ['name' => $context->description()],
                ],
            ]],
        ]);

        if ($response->failed()) {
            return PaymentResult::failed($this->errorFrom($response->json()), $response->json() ?? []);
        }

        return PaymentResult::redirect(
            $response->json('url'),
            $response->json('id'),
            $response->json(),
        );
    }

    public function verify(Payment $payment): PaymentResult
    {
        if (! $payment->gateway_transaction_id) {
            return PaymentResult::failed('No Stripe session recorded for this attempt.');
        }

        $response = $this->request()->get(self::API.'/checkout/sessions/'.$payment->gateway_transaction_id);

        if ($response->failed()) {
            return PaymentResult::failed($this->errorFrom($response->json()), $response->json() ?? []);
        }

        return $this->resultFromSession($response->json());
    }

    public function verifySignature(Request $request): bool
    {
        $secret = (string) $this->cred('webhook_secret');
        $header = (string) $request->header('Stripe-Signature');

        if ($secret === '' || $header === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $header) as $pair) {
            [$key, $value] = array_pad(explode('=', trim($pair), 2), 2, null);
            if ($key === 'v1') {
                $parts['v1'][] = $value;
            } elseif ($key !== null) {
                $parts[$key] = $value;
            }
        }

        $timestamp = (int) ($parts['t'] ?? 0);

        if ($timestamp <= 0 || abs(time() - $timestamp) > self::SIGNATURE_TOLERANCE) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);

        foreach ($parts['v1'] ?? [] as $candidate) {
            if (is_string($candidate) && hash_equals($expected, $candidate)) {
                return true;
            }
        }

        return false;
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $type    = (string) ($payload['type'] ?? '');
        $object  = $payload['data']['object'] ?? [];

        $status = match ($type) {
            'checkout.session.completed',
            'checkout.session.async_payment_succeeded' => ($object['payment_status'] ?? null) === 'paid'
                ? PaymentStatus::Paid
                : PaymentStatus::Pending,
            'checkout.session.async_payment_failed'    => PaymentStatus::Failed,
            'checkout.session.expired'                 => PaymentStatus::Cancelled,
            default                                    => null,
        };

        if ($status === null) {
            return null;
        }

        return new PaymentEvent(
            id: (string) ($payload['id'] ?? ''),
            type: $type,
            status: $status,
            reference: $object['client_reference_id'] ?? null,
            transactionId: $object['id'] ?? null,
            raw: $payload,
        );
    }

    public function refund(Payment $payment, ?int $amountMinor = null): PaymentResult
    {
        $intent = $payment->payload['payment_intent'] ?? null;

        if (! $intent) {
            // The session only carries the intent once paid, so re-read it.
            $session = $this->request()->get(self::API.'/checkout/sessions/'.$payment->gateway_transaction_id);
            $intent  = $session->json('payment_intent');
        }

        if (! $intent) {
            return PaymentResult::failed('No Stripe payment intent found to refund.');
        }

        $response = $this->request()->asForm()->post(self::API.'/refunds', array_filter([
            'payment_intent' => $intent,
            'amount'         => $amountMinor,
        ], fn ($v) => $v !== null));

        return $response->successful()
            ? PaymentResult::paid($response->json('id'), $response->json())
            : PaymentResult::failed($this->errorFrom($response->json()), $response->json() ?? []);
    }

    public function name(): string
    {
        return 'Stripe';
    }

    private function request()
    {
        return Http::withToken((string) $this->cred('secret_key'))->timeout(30);
    }

    private function resultFromSession(array $session): PaymentResult
    {
        return match ($session['payment_status'] ?? null) {
            'paid'      => PaymentResult::paid($session['id'] ?? null, $session),
            'unpaid'    => ($session['status'] ?? null) === 'expired'
                ? PaymentResult::cancelled($session)
                : PaymentResult::pending($session['id'] ?? null, $session),
            default     => PaymentResult::pending($session['id'] ?? null, $session),
        };
    }

    private function errorFrom(?array $body): string
    {
        return $body['error']['message'] ?? 'Stripe request failed.';
    }
}

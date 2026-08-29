<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** Cashfree (India) — cards, UPI, netbanking and wallets. */
class CashfreeGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive() ? 'https://api.cashfree.com/pg' : 'https://sandbox.cashfree.com/pg';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post($this->base().'/orders', [
            'order_id'       => $context->reference,
            'order_amount'   => (float) $context->amountDecimal(),
            'order_currency' => strtoupper($context->currency),
            'customer_details' => [
                'customer_id'    => (string) ($context->order->user_id ?? $context->order->id),
                'customer_email' => $context->order->customer_email,
                'customer_phone' => $context->order->customer_phone,
            ],
            'order_meta' => ['return_url' => $context->returnUrl, 'notify_url' => $context->webhookUrl],
        ]);

        // Guarded because a gateway returning an unexpected type here would otherwise
        // surface as a TypeError rather than a readable payment failure.
        $url = collect([$response->json('payment_link'), $response->json('payment_session_id')])
            ->first(fn ($value) => is_string($value) && $value !== '');

        return $url
            ? PaymentResult::redirect($url, $response->json('cf_order_id'), $response->json())
            : $this->fail($response, 'Cashfree could not create the payment.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get($this->base().'/orders/'.$payment->idempotency_key);

        if ($response->failed()) {
            return $this->fail($response, 'Cashfree verification failed.');
        }

        return $this->statusFrom($response->json());
    }

    public function verifySignature(Request $request): bool
    {
        return $this->hmacMatchesBody($request, 'x-webhook-signature', 'sha256', $this->cred('client_secret'));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $result  = $this->statusFrom($payload);

        return new PaymentEvent(
            id: 'cashfree:'.($payload['data']['order']['order_id'] ?? '').':'.($payload['data']['payment']['payment_status'] ?? ($payload['order_status'] ?? '')),
            type: (string) ($payload['data']['payment']['payment_status'] ?? ($payload['order_status'] ?? '')),
            status: $result->status,
            reference: $payload['data']['order']['order_id'] ?? null,
            transactionId: $payload['data']['payment']['cf_payment_id'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Cashfree';
    }

    private function statusFrom(array $body): PaymentResult
    {
        $status = strtoupper((string) ($body['order_status'] ?? ($body['data']['payment']['payment_status'] ?? '')));

        return match (true) {
            in_array($status, ['PAID', 'SUCCESS'], true)      => PaymentResult::paid($body['cf_order_id'] ?? null, $body),
            in_array($status, ['FAILED', 'USER_DROPPED'], true)    => PaymentResult::failed('Cashfree declined the payment.', $body),
            in_array($status, ['CANCELLED', 'EXPIRED'], true) => PaymentResult::cancelled($body),
            default                                => PaymentResult::pending($body['cf_order_id'] ?? null, $body),
        };
    }

    private function request()
    {
        return Http::withHeaders(['x-client-id' => (string) $this->cred('client_id'), 'x-client-secret' => (string) $this->cred('client_secret'), 'x-api-version' => '2023-08-01'])->timeout(30)->acceptJson();
    }
}

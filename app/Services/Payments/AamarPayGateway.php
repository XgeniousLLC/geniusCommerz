<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** aamarPay (Bangladesh) — cards, bKash, Nagad, Rocket and net banking. */
class AamarPayGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive() ? 'https://secure.aamarpay.com' : 'https://sandbox.aamarpay.com';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = Http::timeout(30)->post($this->base().'/jsonpost.php', [
            'store_id'     => $this->cred('store_id'),
            'signature_key' => $this->cred('signature_key'),
            'tran_id'      => $context->reference,
            'amount'       => $context->amountDecimal(),
            'currency'     => strtoupper($context->currency),
            'desc'         => $context->description(),
            'cus_name'     => $context->order->customer_name,
            'cus_email'    => $context->order->customer_email ?: 'customer@example.com',
            'cus_phone'    => $context->order->customer_phone,
            'success_url'  => $context->returnUrl,
            'fail_url'     => $context->cancelUrl,
            'cancel_url'   => $context->cancelUrl,
            'type'         => 'json',
        ]);

        $path = $response->json('payment_url');

        if (! $path) {
            return PaymentResult::failed(
                $response->json('result') ?: 'aamarPay could not create the payment.',
                $response->json() ?? [],
            );
        }

        // aamarPay returns a site-relative path.
        $url = str_starts_with($path, 'http') ? $path : $this->base().'/'.ltrim($path, '/');

        return PaymentResult::redirect($url, $context->reference, $response->json() ?? []);
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = Http::timeout(20)->get($this->base().'/api/v1/trxcheck/request.php', [
            'request_id' => $payment->idempotency_key,
            'store_id'   => $this->cred('store_id'),
            'signature_key' => $this->cred('signature_key'),
            'type'       => 'json',
        ]);

        return $this->statusFrom($response->json() ?? []);
    }

    /**
     * aamarPay echoes the signature key in its callback rather than signing the body,
     * so the check is a constant-time comparison of that shared secret.
     */
    public function verifySignature(Request $request): bool
    {
        $provided = (string) $request->input('signature_key');
        $expected = (string) $this->cred('signature_key');

        return $provided !== '' && $expected !== '' && hash_equals($expected, $provided);
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->all();
        $result  = $this->statusFrom($payload);

        return new PaymentEvent(
            id: 'aamarpay:'.($payload['mer_txnid'] ?? '').':'.($payload['pay_status'] ?? ''),
            type: (string) ($payload['pay_status'] ?? 'callback'),
            status: $result->status,
            reference: $payload['mer_txnid'] ?? null,
            transactionId: $payload['pg_txnid'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'aamarPay';
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match (strtolower((string) ($body['pay_status'] ?? ''))) {
            'successful' => PaymentResult::paid($body['pg_txnid'] ?? null, $body),
            'failed'     => PaymentResult::failed('aamarPay reported the payment as failed.', $body),
            'canceled', 'cancelled' => PaymentResult::cancelled($body),
            default      => PaymentResult::pending($body['pg_txnid'] ?? null, $body),
        };
    }
}

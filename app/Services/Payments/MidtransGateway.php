<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** Midtrans (Indonesia) — Snap hosted checkout. */
class MidtransGateway extends HostedGateway
{
    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->snap()->post($this->snapUrl().'/snap/v1/transactions', [
            'transaction_details' => [
                'order_id' => $context->reference,
                // Snap requires a whole-rupiah integer, and IDR has no minor unit in practice.
                'gross_amount' => (int) round((float) $context->amountDecimal()),
            ],
            'customer_details' => [
                'first_name' => $context->order->customer_name,
                'email'      => $context->order->customer_email,
                'phone'      => $context->order->customer_phone,
            ],
            'callbacks' => ['finish' => $context->returnUrl],
        ]);

        return $response->successful()
            ? PaymentResult::redirect($response->json('redirect_url'), $context->reference, $response->json())
            : $this->fail($response, 'Midtrans could not create the transaction.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->api()->get($this->apiUrl().'/v2/'.$payment->idempotency_key.'/status');

        return $response->failed()
            ? $this->fail($response, 'Midtrans verification failed.')
            : $this->statusFrom($response->json());
    }

    /**
     * Midtrans signs with SHA-512 over concatenated fields rather than the raw body.
     */
    public function verifySignature(Request $request): bool
    {
        $payload  = $request->json()->all();
        $provided = (string) ($payload['signature_key'] ?? '');
        $server   = (string) $this->cred('server_key');

        if ($provided === '' || $server === '') {
            return false;
        }

        $expected = hash('sha512',
            ($payload['order_id'] ?? '').($payload['status_code'] ?? '').($payload['gross_amount'] ?? '').$server);

        return hash_equals($expected, $provided);
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $result  = $this->statusFrom($payload);

        return new PaymentEvent(
            id: 'midtrans:'.($payload['transaction_id'] ?? $payload['order_id'] ?? '').':'.($payload['transaction_status'] ?? ''),
            type: (string) ($payload['transaction_status'] ?? 'status'),
            status: $result->status,
            reference: $payload['order_id'] ?? null,
            transactionId: $payload['transaction_id'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Midtrans';
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match ($body['transaction_status'] ?? null) {
            'capture', 'settlement' => PaymentResult::paid($body['transaction_id'] ?? null, $body),
            'deny', 'failure'       => PaymentResult::failed('Midtrans declined the transaction.', $body),
            'cancel', 'expire'      => PaymentResult::cancelled($body),
            default                 => PaymentResult::pending($body['transaction_id'] ?? null, $body),
        };
    }

    private function snapUrl(): string
    {
        return $this->isLive() ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
    }

    private function apiUrl(): string
    {
        return $this->isLive() ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';
    }

    private function snap()
    {
        return $this->api();
    }

    private function api()
    {
        // Server key as basic-auth username with an empty password.
        return Http::withBasicAuth((string) $this->cred('server_key'), '')->timeout(30)->acceptJson();
    }
}

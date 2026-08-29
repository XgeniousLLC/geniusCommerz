<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Nagad PGW (Bangladesh).
 *
 * Nagad is the most involved integration here: sensitive fields are RSA-encrypted with
 * Nagad's public key and signed with the merchant's private key, in two legs
 * (initialize, then complete). Both keys are PEM values entered in the credentials form.
 */
class NagadGateway extends HostedGateway
{
    private function base(): string
    {
        return rtrim((string) $this->cred(
            'base_url',
            $this->isLive() ? 'https://api.mynagad.com' : 'https://sandbox-ssl.mynagad.com',
        ), '/');
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $merchantId = (string) $this->cred('merchant_id');
        $orderId    = $context->reference;
        $timestamp  = now()->setTimezone('Asia/Dhaka')->format('YmdHis');

        $initialise = [
            'dateTime'    => $timestamp,
            'merchantId'  => $merchantId,
            'orderId'     => $orderId,
            'challenge'   => bin2hex(random_bytes(10)),
        ];

        $first = Http::withHeaders($this->headers())->timeout(30)->post(
            $this->base()."/api/dfs/check-out/initialize/{$merchantId}/{$orderId}",
            [
                'accountNumber' => $this->cred('merchant_number'),
                'dateTime'      => $timestamp,
                'sensitiveData' => $this->encrypt($initialise),
                'signature'     => $this->sign($initialise),
            ],
        );

        $decrypted = $this->decrypt($first->json('sensitiveData'));

        if (! isset($decrypted['paymentReferenceId'], $decrypted['challenge'])) {
            return PaymentResult::failed(
                $first->json('message') ?: 'Nagad could not initialise the payment.',
                $first->json() ?? [],
            );
        }

        $complete = [
            'merchantId'   => $merchantId,
            'orderId'      => $orderId,
            'currencyCode' => '050',            // Nagad's numeric code for BDT
            'amount'       => $context->amountDecimal(),
            'challenge'    => $decrypted['challenge'],
        ];

        $second = Http::withHeaders($this->headers())->timeout(30)->post(
            $this->base().'/api/dfs/check-out/complete/'.$decrypted['paymentReferenceId'],
            [
                'sensitiveData'   => $this->encrypt($complete),
                'signature'       => $this->sign($complete),
                'merchantCallbackURL' => $context->returnUrl,
                'additionalMerchantInfo' => (object) ['reference' => $context->reference],
            ],
        );

        return $second->json('callBackUrl')
            ? PaymentResult::redirect($second->json('callBackUrl'), $decrypted['paymentReferenceId'], $second->json())
            : PaymentResult::failed(
                $second->json('message') ?: 'Nagad did not return a checkout URL.',
                $second->json() ?? [],
            );
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = Http::withHeaders($this->headers())->timeout(20)
            ->get($this->base().'/api/dfs/verify/payment/'.$payment->idempotency_key);

        return $this->statusFrom($response->json() ?? []);
    }

    /** Nagad has no signed webhook; the outcome is confirmed through the verify API. */
    public function verifySignature(Request $request): bool
    {
        return $request->filled('order_id') || $request->filled('payment_ref_id');
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $orderId = (string) $request->input('order_id', $request->input('merchant_order_id'));

        if ($orderId === '') {
            return null;
        }

        $response = Http::withHeaders($this->headers())->timeout(20)
            ->get($this->base().'/api/dfs/verify/payment/'.$orderId);

        $body   = $response->json() ?? [];
        $result = $this->statusFrom($body);

        return new PaymentEvent(
            id: 'nagad:'.$orderId.':'.($body['status'] ?? ''),
            type: (string) ($body['status'] ?? 'verify'),
            status: $result->status,
            reference: $body['orderId'] ?? $orderId,
            transactionId: $body['paymentRefId'] ?? ($body['issuerPaymentRefNo'] ?? null),
            raw: $body,
        );
    }

    public function name(): string
    {
        return 'Nagad';
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match (strtolower((string) ($body['status'] ?? ''))) {
            'success'   => PaymentResult::paid($body['issuerPaymentRefNo'] ?? ($body['paymentRefId'] ?? null), $body),
            'aborted', 'cancelled' => PaymentResult::cancelled($body),
            'failed'    => PaymentResult::failed($body['message'] ?? 'Nagad declined the payment.', $body),
            default     => PaymentResult::pending($body['paymentRefId'] ?? null, $body),
        };
    }

    private function headers(): array
    {
        return [
            'X-KM-Api-Version' => 'v-0.2.0',
            'X-KM-IP-V4'       => request()->ip() ?: '127.0.0.1',
            'X-KM-Client-Type' => 'PC_WEB',
            'Content-Type'     => 'application/json',
        ];
    }

    private function encrypt(array $data): string
    {
        $key = openssl_pkey_get_public($this->pem($this->cred('public_key'), 'PUBLIC'));

        if (! $key || ! openssl_public_encrypt(json_encode($data), $encrypted, $key)) {
            throw new \RuntimeException('Nagad encryption failed — check the public key.');
        }

        return base64_encode($encrypted);
    }

    private function decrypt(?string $payload): array
    {
        if (! $payload) {
            return [];
        }

        $key = openssl_pkey_get_private($this->pem($this->cred('private_key'), 'PRIVATE'));

        if (! $key || ! openssl_private_decrypt(base64_decode($payload), $plain, $key)) {
            return [];
        }

        return json_decode($plain, true) ?: [];
    }

    private function sign(array $data): string
    {
        $key = openssl_pkey_get_private($this->pem($this->cred('private_key'), 'PRIVATE'));

        if (! $key || ! openssl_sign(json_encode($data), $signature, $key, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Nagad signing failed — check the private key.');
        }

        return base64_encode($signature);
    }

    /** Accepts a bare base64 key or a full PEM block, so paste format does not matter. */
    private function pem(?string $key, string $type): string
    {
        $key = trim((string) $key);

        if (str_contains($key, 'BEGIN')) {
            return $key;
        }

        return "-----BEGIN {$type} KEY-----\n".chunk_split($key, 64, "\n")."-----END {$type} KEY-----\n";
    }
}

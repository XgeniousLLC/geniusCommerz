<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * PhonePe (India) — UPI, cards and wallet.
 *
 * The request body is base64-encoded and signed with an X-VERIFY checksum of
 * sha256(payload + path + salt) + "###" + saltIndex.
 */
class PhonePeGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive()
            ? 'https://api.phonepe.com/apis/hermes'
            : 'https://api-preprod.phonepe.com/apis/pg-sandbox';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $path    = '/pg/v1/pay';
        $payload = base64_encode(json_encode([
            'merchantId'            => $this->cred('merchant_id'),
            'merchantTransactionId' => Str::limit($context->reference, 34, ''),
            'merchantUserId'        => 'U'.($context->order->user_id ?? $context->order->id),
            // PhonePe amounts are in paise.
            'amount'                => $context->amountMinor,
            'redirectUrl'           => $context->returnUrl,
            'redirectMode'          => 'REDIRECT',
            'callbackUrl'           => $context->webhookUrl,
            'mobileNumber'          => $context->order->customer_phone,
            'paymentInstrument'     => ['type' => 'PAY_PAGE'],
        ]));

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-VERIFY'     => $this->checksum($payload.$path),
        ])->timeout(30)->post($this->base().$path, ['request' => $payload]);

        $url = $response->json('data.instrumentResponse.redirectInfo.url');

        return $url
            ? PaymentResult::redirect($url, $response->json('data.merchantTransactionId'), $response->json())
            : PaymentResult::failed(
                $response->json('message') ?: 'PhonePe could not create the payment.',
                $response->json() ?? [],
            );
    }

    public function verify(Payment $payment): PaymentResult
    {
        $merchant = (string) $this->cred('merchant_id');
        $txn      = Str::limit($payment->idempotency_key, 34, '');
        $path     = "/pg/v1/status/{$merchant}/{$txn}";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-VERIFY'     => $this->checksum($path),
            'X-MERCHANT-ID' => $merchant,
        ])->timeout(20)->get($this->base().$path);

        return $this->statusFrom($response->json() ?? []);
    }

    /** The callback carries the same checksum over its base64 response body. */
    public function verifySignature(Request $request): bool
    {
        $provided = (string) $request->header('X-VERIFY');
        $response = (string) $request->input('response');

        if ($provided === '' || $response === '') {
            return false;
        }

        return hash_equals($this->checksum($response), $provided);
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $decoded = json_decode(base64_decode((string) $request->input('response')), true) ?: [];
        $result  = $this->statusFrom($decoded);
        $data    = $decoded['data'] ?? [];

        return new PaymentEvent(
            id: 'phonepe:'.($data['transactionId'] ?? '').':'.($decoded['code'] ?? ''),
            type: (string) ($decoded['code'] ?? 'callback'),
            status: $result->status,
            reference: $data['merchantTransactionId'] ?? null,
            transactionId: $data['transactionId'] ?? null,
            raw: $decoded,
        );
    }

    public function name(): string
    {
        return 'PhonePe';
    }

    private function statusFrom(array $body): PaymentResult
    {
        $data = $body['data'] ?? [];

        return match ($body['code'] ?? ($data['state'] ?? '')) {
            'PAYMENT_SUCCESS', 'COMPLETED' => PaymentResult::paid($data['transactionId'] ?? null, $body),
            'PAYMENT_ERROR', 'PAYMENT_DECLINED', 'FAILED' => PaymentResult::failed(
                $body['message'] ?? 'PhonePe declined the payment.', $body),
            'PAYMENT_CANCELLED' => PaymentResult::cancelled($body),
            default => PaymentResult::pending($data['transactionId'] ?? null, $body),
        };
    }

    private function checksum(string $payload): string
    {
        $index = (string) $this->cred('salt_index', '1');

        return hash('sha256', $payload.$this->cred('salt_key')).'###'.$index;
    }
}

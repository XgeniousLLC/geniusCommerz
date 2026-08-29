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
 * Paytm (India) — hosted payment page.
 *
 * Paytm's checksum is a salted SHA-256 encrypted with AES-128-CBC under a fixed IV, and
 * the payment page itself takes a browser POST, so this returns a form payload.
 */
class PaytmGateway extends HostedGateway
{
    /** Paytm's published fixed IV for checksum encryption. */
    private const IV = '@@@@&&&&####$$$$';

    private function base(): string
    {
        return $this->isLive() ? 'https://securegw.paytm.in' : 'https://securegw-stage.paytm.in';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $mid     = (string) $this->cred('merchant_id');
        $orderId = Str::limit($context->reference, 48, '');

        $body = [
            'requestType' => 'Payment',
            'mid'         => $mid,
            'websiteName' => (string) $this->cred('website', 'DEFAULT'),
            'orderId'     => $orderId,
            'callbackUrl' => $context->returnUrl,
            'txnAmount'   => ['value' => $context->amountDecimal(), 'currency' => strtoupper($context->currency)],
            'userInfo'    => ['custId' => (string) ($context->order->user_id ?? $context->order->id)],
        ];

        $response = Http::timeout(30)->post(
            $this->base()."/theia/api/v1/initiateTransaction?mid={$mid}&orderId={$orderId}",
            ['body' => $body, 'head' => ['signature' => $this->signature($body)]],
        );

        $token = $response->json('body.txnToken');

        if (! $token) {
            return PaymentResult::failed(
                $response->json('body.resultInfo.resultMsg') ?: 'Paytm could not initiate the transaction.',
                $response->json() ?? [],
            );
        }

        return PaymentResult::formPost(
            $this->base()."/theia/api/v1/showPaymentPage?mid={$mid}&orderId={$orderId}",
            ['mid' => $mid, 'orderId' => $orderId, 'txnToken' => $token],
            $orderId,
        );
    }

    public function verify(Payment $payment): PaymentResult
    {
        $mid     = (string) $this->cred('merchant_id');
        $orderId = Str::limit($payment->idempotency_key, 48, '');
        $body    = ['mid' => $mid, 'orderId' => $orderId];

        $response = Http::timeout(20)->post($this->base().'/v3/order/status', [
            'body' => $body,
            'head' => ['signature' => $this->signature($body)],
        ]);

        return $this->statusFrom($response->json('body') ?? []);
    }

    /** The callback carries a CHECKSUMHASH over its own posted fields. */
    public function verifySignature(Request $request): bool
    {
        $payload  = $request->all();
        $provided = (string) ($payload['CHECKSUMHASH'] ?? '');

        if ($provided === '') {
            return false;
        }

        unset($payload['CHECKSUMHASH']);
        ksort($payload);

        return $this->checksumMatches(implode('|', array_map('strval', $payload)), $provided);
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->all();
        $result  = $this->statusFrom([
            'resultInfo' => ['resultStatus' => $payload['STATUS'] ?? '', 'resultMsg' => $payload['RESPMSG'] ?? ''],
            'txnId'      => $payload['TXNID'] ?? null,
        ]);

        return new PaymentEvent(
            id: 'paytm:'.($payload['TXNID'] ?? '').':'.($payload['STATUS'] ?? ''),
            type: (string) ($payload['STATUS'] ?? 'callback'),
            status: $result->status,
            reference: $payload['ORDERID'] ?? null,
            transactionId: $payload['TXNID'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Paytm';
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match ($body['resultInfo']['resultStatus'] ?? null) {
            'TXN_SUCCESS' => PaymentResult::paid($body['txnId'] ?? null, $body),
            'TXN_FAILURE' => PaymentResult::failed($body['resultInfo']['resultMsg'] ?? 'Paytm declined the payment.', $body),
            default       => PaymentResult::pending($body['txnId'] ?? null, $body),
        };
    }

    /** Salted SHA-256 of the JSON body, AES-128-CBC encrypted under the merchant key. */
    private function signature(array $body): string
    {
        $salt = substr(bin2hex(random_bytes(4)), 0, 4);
        $hash = hash('sha256', json_encode($body).'|'.$salt).$salt;

        return base64_encode(openssl_encrypt(
            $hash,
            'AES-128-CBC',
            (string) $this->cred('merchant_key'),
            OPENSSL_RAW_DATA,
            self::IV,
        ));
    }

    private function checksumMatches(string $params, string $provided): bool
    {
        $decrypted = openssl_decrypt(
            base64_decode($provided),
            'AES-128-CBC',
            (string) $this->cred('merchant_key'),
            OPENSSL_RAW_DATA,
            self::IV,
        );

        if (! $decrypted || strlen($decrypted) < 4) {
            return false;
        }

        $salt = substr($decrypted, -4);

        return hash_equals(hash('sha256', $params.'|'.$salt).$salt, $decrypted);
    }
}

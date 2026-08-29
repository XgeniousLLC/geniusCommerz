<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Adyen — hosted Pay by Link.
 *
 * Adyen's notifications are batched and HMAC-signed per item over a pipe-joined payload
 * with escaped separators, so the signature is checked per notification item rather than
 * over the raw body.
 */
class AdyenGateway extends HostedGateway
{
    private function base(): string
    {
        // The live endpoint is prefixed per merchant account, which Adyen issues.
        $prefix = trim((string) $this->cred('live_url_prefix'));

        return $this->isLive() && $prefix !== ''
            ? "https://{$prefix}-checkout-live.adyenpayments.com/checkout/v71"
            : 'https://checkout-test.adyen.com/v71';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post($this->base().'/paymentLinks', [
            'merchantAccount' => $this->cred('merchant_account'),
            'reference'       => $context->reference,
            'amount'          => ['currency' => strtoupper($context->currency), 'value' => $context->amountMinor],
            'returnUrl'       => $context->returnUrl,
            'description'     => $context->description(),
            'shopperEmail'    => $context->order->customer_email,
            'countryCode'     => $context->order->shipping_address['country'] ?? null,
        ]);

        return $response->json('url')
            ? PaymentResult::redirect($response->json('url'), $response->json('id'), $response->json())
            : $this->fail($response, 'Adyen could not create the payment link.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get($this->base().'/paymentLinks/'.$payment->gateway_transaction_id);

        return $response->failed()
            ? $this->fail($response, 'Adyen verification failed.')
            : $this->statusFrom($response->json());
    }

    public function verifySignature(Request $request): bool
    {
        $key = (string) $this->cred('hmac_key');

        if ($key === '') {
            return false;
        }

        foreach ($request->input('notificationItems', []) as $wrapper) {
            $item = $wrapper['NotificationRequestItem'] ?? [];
            $sig  = $item['additionalData']['hmacSignature'] ?? null;

            if (! $sig || ! hash_equals($this->itemSignature($item, $key), $sig)) {
                return false;
            }
        }

        return $request->has('notificationItems');
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $item = $request->input('notificationItems.0.NotificationRequestItem', []);

        if (! $item) {
            return null;
        }

        $success = filter_var($item['success'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

        $status = match ($item['eventCode'] ?? '') {
            'AUTHORISATION' => $success ? PaymentStatus::Paid : PaymentStatus::Failed,
            'CANCELLATION', 'EXPIRE' => PaymentStatus::Cancelled,
            default         => null,
        };

        if ($status === null) {
            return null;
        }

        return new PaymentEvent(
            id: 'adyen:'.($item['pspReference'] ?? '').':'.($item['eventCode'] ?? ''),
            type: (string) ($item['eventCode'] ?? ''),
            status: $status,
            reference: $item['merchantReference'] ?? null,
            transactionId: $item['pspReference'] ?? null,
            raw: $request->all(),
        );
    }

    public function name(): string
    {
        return 'Adyen';
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match ($body['status'] ?? null) {
            'completed' => PaymentResult::paid($body['id'] ?? null, $body),
            'expired'   => PaymentResult::cancelled($body),
            default     => PaymentResult::pending($body['id'] ?? null, $body),
        };
    }

    /**
     * Adyen's HMAC covers a fixed field order joined by ':', with ':' and '\' escaped
     * inside values, hashed with the binary form of the hex key.
     */
    private function itemSignature(array $item, string $hexKey): string
    {
        $escape = fn (?string $v) => str_replace(['\\', ':'], ['\\\\', '\\:'], (string) $v);

        $payload = implode(':', array_map($escape, [
            $item['pspReference'] ?? '',
            $item['originalReference'] ?? '',
            $item['merchantAccountCode'] ?? '',
            $item['merchantReference'] ?? '',
            (string) ($item['amount']['value'] ?? ''),
            $item['amount']['currency'] ?? '',
            $item['eventCode'] ?? '',
            $item['success'] ?? '',
        ]));

        return base64_encode(hash_hmac('sha256', $payload, hex2bin($hexKey), true));
    }

    private function request()
    {
        return Http::withHeaders(['X-API-Key' => (string) $this->cred('api_key')])
            ->timeout(30)->acceptJson();
    }
}

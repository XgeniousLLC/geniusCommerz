<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** MercadoPago — Brazil, Argentina, Mexico and wider LatAm. */
class MercadoPagoGateway extends HostedGateway
{
    private const API = 'https://api.mercadopago.com';

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post(self::API.'/checkout/preferences', [
            'items' => [[
                'title'       => $context->description(),
                'quantity'    => 1,
                'unit_price'  => (float) $context->amountDecimal(),
                'currency_id' => strtoupper($context->currency),
            ]],
            'external_reference' => $context->reference,
            'notification_url'   => $context->webhookUrl,
            'back_urls'          => [
                'success' => $context->returnUrl,
                'pending' => $context->returnUrl,
                'failure' => $context->cancelUrl,
            ],
            'auto_return' => 'approved',
        ]);

        if ($response->failed()) {
            return $this->fail($response, 'MercadoPago could not create the preference.');
        }

        $url = $this->isLive() ? $response->json('init_point') : $response->json('sandbox_init_point');

        return PaymentResult::redirect($url ?: $response->json('init_point'), $response->json('id'), $response->json());
    }

    public function verify(Payment $payment): PaymentResult
    {
        // Search by our own reference — the preference id is not the payment id.
        $response = $this->request()->get(self::API.'/v1/payments/search', [
            'external_reference' => $payment->idempotency_key,
        ]);

        if ($response->failed()) {
            return $this->fail($response, 'MercadoPago verification failed.');
        }

        $found = $response->json('results.0');

        return $found ? $this->statusFrom($found) : PaymentResult::pending($payment->gateway_transaction_id);
    }

    /**
     * MercadoPago signs a manifest of id, request id and timestamp rather than the body.
     */
    public function verifySignature(Request $request): bool
    {
        $secret = (string) $this->cred('webhook_secret');
        $header = (string) $request->header('x-signature');

        if ($secret === '' || $header === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $header) as $pair) {
            [$k, $v] = array_pad(explode('=', trim($pair), 2), 2, null);
            if ($k !== null) {
                $parts[trim($k)] = trim((string) $v);
            }
        }

        $ts = $parts['ts'] ?? null;
        $v1 = $parts['v1'] ?? null;

        if (! $ts || ! $v1) {
            return false;
        }

        $manifest = sprintf(
            'id:%s;request-id:%s;ts:%s;',
            $request->query('data.id') ?? $request->input('data.id', ''),
            $request->header('x-request-id', ''),
            $ts,
        );

        return hash_equals(hash_hmac('sha256', $manifest, $secret), $v1);
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $paymentId = $request->input('data.id') ?? $request->query('data.id');

        if (! $paymentId) {
            return null;
        }

        // The notification carries only an id; the outcome comes from the API.
        $response = $this->request()->get(self::API.'/v1/payments/'.$paymentId);

        if ($response->failed()) {
            return null;
        }

        $body   = $response->json();
        $result = $this->statusFrom($body);

        return new PaymentEvent(
            id: 'mp:'.$paymentId.':'.($body['status'] ?? ''),
            type: (string) ($body['status'] ?? 'payment'),
            status: $result->status,
            reference: $body['external_reference'] ?? null,
            transactionId: (string) $paymentId,
            raw: $body,
        );
    }

    public function name(): string
    {
        return 'MercadoPago';
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match ($body['status'] ?? null) {
            'approved'  => PaymentResult::paid((string) ($body['id'] ?? ''), $body),
            'rejected'  => PaymentResult::failed('MercadoPago rejected the payment.', $body),
            'cancelled' => PaymentResult::cancelled($body),
            default     => PaymentResult::pending((string) ($body['id'] ?? ''), $body),
        };
    }

    private function request()
    {
        return Http::withToken((string) $this->cred('access_token'))->timeout(30)->acceptJson();
    }
}

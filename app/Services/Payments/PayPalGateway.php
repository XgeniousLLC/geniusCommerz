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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * PayPal via Orders v2.
 *
 * PayPal's intent=CAPTURE flow is two-legged: the order is created and approved by the
 * customer, then captured server-side. Capture happens in verify(), so an approved-but-
 * uncaptured order is never treated as paid.
 */
class PayPalGateway extends ProviderDriver implements PaymentInterface
{
    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post($this->api('/v2/checkout/orders'), [
            'intent'         => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $context->reference,
                'custom_id'    => $context->reference,
                'description'  => $context->description(),
                'amount'       => [
                    'currency_code' => strtoupper($context->currency),
                    'value'         => $context->amountDecimal(),
                ],
            ]],
            'application_context' => [
                'return_url' => $context->returnUrl,
                'cancel_url' => $context->cancelUrl,
                'user_action' => 'PAY_NOW',
            ],
        ]);

        if ($response->failed()) {
            return PaymentResult::failed($this->errorFrom($response->json()), $response->json() ?? []);
        }

        $approve = collect($response->json('links', []))->firstWhere('rel', 'payer-action')
            ?? collect($response->json('links', []))->firstWhere('rel', 'approve');

        if (! $approve) {
            return PaymentResult::failed('PayPal did not return an approval link.', $response->json() ?? []);
        }

        return PaymentResult::redirect($approve['href'], $response->json('id'), $response->json());
    }

    public function verify(Payment $payment): PaymentResult
    {
        if (! $payment->gateway_transaction_id) {
            return PaymentResult::failed('No PayPal order recorded for this attempt.');
        }

        $order = $this->request()->get($this->api('/v2/checkout/orders/'.$payment->gateway_transaction_id));

        if ($order->failed()) {
            return PaymentResult::failed($this->errorFrom($order->json()), $order->json() ?? []);
        }

        return match ($order->json('status')) {
            'COMPLETED' => PaymentResult::paid($payment->gateway_transaction_id, $order->json()),
            'APPROVED'  => $this->capture($payment),
            'VOIDED'    => PaymentResult::cancelled($order->json()),
            default     => PaymentResult::pending($payment->gateway_transaction_id, $order->json()),
        };
    }

    public function verifySignature(Request $request): bool
    {
        $webhookId = (string) $this->cred('webhook_id');

        if ($webhookId === '') {
            return false;
        }

        // PayPal signatures are verified by calling PayPal, not locally.
        $response = $this->request()->post($this->api('/v1/notifications/verify-webhook-signature'), [
            'auth_algo'         => $request->header('PAYPAL-AUTH-ALGO'),
            'cert_url'          => $request->header('PAYPAL-CERT-URL'),
            'transmission_id'   => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_sig'  => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
            'webhook_id'        => $webhookId,
            'webhook_event'     => $request->json()->all(),
        ]);

        return $response->successful() && $response->json('verification_status') === 'SUCCESS';
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload  = $request->json()->all();
        $type     = (string) ($payload['event_type'] ?? '');
        $resource = $payload['resource'] ?? [];

        $status = match ($type) {
            'PAYMENT.CAPTURE.COMPLETED' => PaymentStatus::Paid,
            'PAYMENT.CAPTURE.DENIED',
            'PAYMENT.CAPTURE.DECLINED'  => PaymentStatus::Failed,
            'CHECKOUT.ORDER.APPROVED'   => PaymentStatus::Pending,
            default                     => null,
        };

        if ($status === null) {
            return null;
        }

        // custom_id survives onto the capture resource; on order events it sits in the unit.
        $reference = $resource['custom_id']
            ?? ($resource['purchase_units'][0]['custom_id'] ?? null)
            ?? ($resource['purchase_units'][0]['reference_id'] ?? null);

        return new PaymentEvent(
            id: (string) ($payload['id'] ?? ''),
            type: $type,
            status: $status,
            reference: $reference,
            transactionId: $resource['supplementary_data']['related_ids']['order_id'] ?? ($resource['id'] ?? null),
            raw: $payload,
        );
    }

    public function refund(Payment $payment, ?int $amountMinor = null): PaymentResult
    {
        $captureId = $payment->payload['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;

        if (! $captureId) {
            return PaymentResult::failed('No PayPal capture found to refund.');
        }

        $body = $amountMinor === null ? [] : ['amount' => [
            'currency_code' => strtoupper($payment->currency),
            'value'         => number_format(
                \App\Support\Currencies::fromMinor($amountMinor, $payment->currency),
                \App\Support\Currencies::exponent($payment->currency),
                '.',
                ''
            ),
        ]];

        $response = $this->request()->post($this->api("/v2/payments/captures/{$captureId}/refund"), $body);

        return $response->successful()
            ? PaymentResult::paid($response->json('id'), $response->json())
            : PaymentResult::failed($this->errorFrom($response->json()), $response->json() ?? []);
    }

    public function name(): string
    {
        return 'PayPal';
    }

    private function capture(Payment $payment): PaymentResult
    {
        $response = $this->request()
            ->post($this->api('/v2/checkout/orders/'.$payment->gateway_transaction_id.'/capture'));

        if ($response->failed()) {
            return PaymentResult::failed($this->errorFrom($response->json()), $response->json() ?? []);
        }

        return $response->json('status') === 'COMPLETED'
            ? PaymentResult::paid($payment->gateway_transaction_id, $response->json())
            : PaymentResult::pending($payment->gateway_transaction_id, $response->json());
    }

    private function api(string $path): string
    {
        $host = $this->isLive() ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        return $host.$path;
    }

    private function request()
    {
        return Http::withToken($this->accessToken())->timeout(30);
    }

    private function accessToken(): string
    {
        $key = 'paypal_token_'.($this->integration->environment ?: 'sandbox');

        return Cache::remember($key, 3000, function () {
            $response = Http::asForm()
                ->withBasicAuth((string) $this->cred('client_id'), (string) $this->cred('client_secret'))
                ->post($this->api('/v1/oauth2/token'), ['grant_type' => 'client_credentials']);

            if ($response->failed()) {
                throw new \RuntimeException('PayPal authentication failed: '.$this->errorFrom($response->json()));
            }

            return (string) $response->json('access_token');
        });
    }

    private function errorFrom(?array $body): string
    {
        return $body['message']
            ?? $body['error_description']
            ?? ($body['details'][0]['description'] ?? 'PayPal request failed.');
    }
}

<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** Peach Payments (South Africa and wider Africa) — hosted Checkout v2. */
class PeachPaymentsGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive() ? 'https://secure.peachpayments.com' : 'https://testsecure.peachpayments.com';
    }

    private function authBase(): string
    {
        return $this->isLive() ? 'https://dashboard.peachpayments.com' : 'https://sandbox-dashboard.peachpayments.com';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = Http::withToken($this->accessToken())->timeout(30)->post($this->base().'/v2/checkout', [
            'authentication' => ['entityId' => $this->cred('entity_id')],
            'amount'         => $context->amountDecimal(),
            'currency'       => strtoupper($context->currency),
            'shopperResultUrl' => $context->returnUrl,
            'merchantTransactionId' => $context->reference,
            'nonce'          => $context->reference,
            'defaultPaymentMethod' => 'CARD',
        ]);

        $checkoutId = $response->json('checkoutId');

        return $checkoutId
            ? PaymentResult::redirect($this->base()."/checkout/{$checkoutId}", $checkoutId, $response->json())
            : $this->fail($response, 'Peach Payments could not create the checkout.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = Http::withToken($this->accessToken())->timeout(20)
            ->get($this->base().'/v2/checkout/'.$payment->gateway_transaction_id.'/payment', [
                'entityId' => $this->cred('entity_id'),
            ]);

        return $this->statusFrom($response->json() ?? []);
    }

    public function verifySignature(Request $request): bool
    {
        return $this->tokenMatches($request, 'x-webhook-token', $this->cred('webhook_token'));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $result  = $this->statusFrom($payload);

        return new PaymentEvent(
            id: 'peach:'.($payload['id'] ?? '').':'.($payload['result']['code'] ?? ''),
            type: (string) ($payload['result']['code'] ?? 'notification'),
            status: $result->status,
            reference: $payload['merchantTransactionId'] ?? null,
            transactionId: $payload['id'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Peach Payments';
    }

    /**
     * Peach encodes the outcome in a result code: 000.000.* and 000.100.1* are success,
     * 000.200.* means still pending.
     */
    private function statusFrom(array $body): PaymentResult
    {
        $code = (string) ($body['result']['code'] ?? '');

        return match (true) {
            (bool) preg_match('/^(000\.000\.|000\.100\.1|000\.[36])/', $code) => PaymentResult::paid($body['id'] ?? null, $body),
            (bool) preg_match('/^(000\.200)/', $code) => PaymentResult::pending($body['id'] ?? null, $body),
            $code === '' => PaymentResult::pending($body['id'] ?? null, $body),
            default      => PaymentResult::failed($body['result']['description'] ?? 'Peach Payments declined the payment.', $body),
        };
    }

    private function accessToken(): string
    {
        return \Illuminate\Support\Facades\Cache::remember(
            'peach_token_'.($this->integration->environment ?: 'sandbox'),
            2400,
            function () {
                $response = Http::timeout(20)->post($this->authBase().'/api/oauth/token', [
                    'clientId'     => $this->cred('client_id'),
                    'clientSecret' => $this->cred('client_secret'),
                    'merchantId'   => $this->cred('merchant_id'),
                ]);

                if (! $response->json('access_token')) {
                    throw new \RuntimeException('Peach Payments authentication failed.');
                }

                return (string) $response->json('access_token');
            },
        );
    }
}

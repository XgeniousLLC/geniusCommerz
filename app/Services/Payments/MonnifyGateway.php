<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** Monnify (Nigeria) — cards, transfer and USSD. */
class MonnifyGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive() ? 'https://api.monnify.com' : 'https://sandbox.monnify.com';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post($this->base().'/api/v1/merchant/transactions/init-transaction', [
            'amount'            => (float) $context->amountDecimal(),
            'customerName'      => $context->order->customer_name,
            'customerEmail'     => $context->order->customer_email,
            'paymentReference'  => $context->reference,
            'paymentDescription' => $context->description(),
            'currencyCode'      => strtoupper($context->currency),
            'contractCode'      => $this->cred('contract_code'),
            'redirectUrl'       => $context->returnUrl,
        ]);

        $url = $response->json('responseBody.checkoutUrl');

        return $url
            ? PaymentResult::redirect($url, $response->json('responseBody.transactionReference'), $response->json())
            : $this->fail($response, 'Monnify could not create the payment.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get($this->base().'/api/v2/transactions/'.$payment->gateway_transaction_id);

        if ($response->failed()) {
            return $this->fail($response, 'Monnify verification failed.');
        }

        return $this->statusFrom($response->json());
    }

    public function verifySignature(Request $request): bool
    {
        return $this->hmacMatchesBody($request, 'monnify-signature', 'sha512', $this->cred('secret_key'));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $result  = $this->statusFrom($payload);

        return new PaymentEvent(
            id: 'monnify:'.($payload['eventData']['transactionReference'] ?? '').':'.($payload['eventType'] ?? ''),
            type: (string) ($payload['eventType'] ?? ''),
            status: $result->status,
            reference: $payload['eventData']['paymentReference'] ?? null,
            transactionId: $payload['eventData']['transactionReference'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Monnify';
    }

    private function statusFrom(array $body): PaymentResult
    {
        $status = strtoupper((string) ($body['eventType'] ?? ($body['responseBody']['paymentStatus'] ?? '')));

        return match (true) {
            in_array($status, ['SUCCESSFUL_TRANSACTION', 'PAID'], true)      => PaymentResult::paid($body['responseBody']['transactionReference'] ?? null, $body),
            in_array($status, ['FAILED_TRANSACTION', 'FAILED'], true)    => PaymentResult::failed('Monnify declined the payment.', $body),
            in_array($status, ['CANCELLED'], true) => PaymentResult::cancelled($body),
            default                                => PaymentResult::pending($body['responseBody']['transactionReference'] ?? null, $body),
        };
    }

    private function accessToken(): string
    {
        $key = 'monnify_token_'.($this->integration->environment ?: 'sandbox');

        return \Illuminate\Support\Facades\Cache::remember($key, 3000, function () {
            $response = Http::withBasicAuth((string) $this->cred('api_key'), (string) $this->cred('secret_key'))
                ->post($this->base().'/api/v1/auth/login');

            $token = $response->json('responseBody.accessToken');

            if (! $token) {
                throw new \RuntimeException('Monnify authentication failed.');
            }

            return (string) $token;
        });
    }

    private function request()
    {
        return Http::withToken($this->accessToken())->timeout(30)->acceptJson();
    }
}

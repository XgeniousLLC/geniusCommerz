<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** Moyasar — Saudi cards, mada, Apple Pay and STC Pay. */
class MoyasarGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive() ? 'https://api.moyasar.com/v1' : 'https://api.moyasar.com/v1';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post($this->base().'/invoices', [
            'amount'      => $context->amountMinor,
            'currency'    => strtoupper($context->currency),
            'description' => $context->description(),
            'callback_url' => $context->returnUrl,
            'metadata'    => ['reference' => $context->reference],
        ]);

        $url = $response->json('url');

        return $url
            ? PaymentResult::redirect($url, $response->json('id'), $response->json())
            : $this->fail($response, 'Moyasar could not create the payment.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get($this->base().'/invoices/'.$payment->gateway_transaction_id);

        if ($response->failed()) {
            return $this->fail($response, 'Moyasar verification failed.');
        }

        return $this->statusFrom($response->json());
    }

    public function verifySignature(Request $request): bool
    {
        return $this->tokenMatches($request, 'x-webhook-token', $this->cred('webhook_secret'));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $result  = $this->statusFrom($payload);

        return new PaymentEvent(
            id: 'moyasar:'.($payload['data']['id'] ?? '').':'.($payload['data']['status'] ?? ''),
            type: (string) ($payload['data']['status'] ?? ''),
            status: $result->status,
            reference: $payload['data']['metadata']['reference'] ?? null,
            transactionId: $payload['data']['id'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Moyasar';
    }

    private function statusFrom(array $body): PaymentResult
    {
        $status = strtoupper((string) ($body['status'] ?? ($body['data']['status'] ?? '')));

        return match (true) {
            in_array($status, ['PAID'], true)      => PaymentResult::paid($body['id'] ?? null, $body),
            in_array($status, ['FAILED'], true)    => PaymentResult::failed('Moyasar declined the payment.', $body),
            in_array($status, ['CANCELED', 'EXPIRED'], true) => PaymentResult::cancelled($body),
            default                                => PaymentResult::pending($body['id'] ?? null, $body),
        };
    }

    private function request()
    {
        return Http::withBasicAuth((string) $this->cred('secret_key'), '')->timeout(30)->acceptJson();
    }
}

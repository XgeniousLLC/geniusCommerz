<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** Tap Payments — Gulf cards, KNET, mada, Benefit and Apple Pay. */
class TapGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive() ? 'https://api.tap.company/v2' : 'https://api.tap.company/v2';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post($this->base().'/charges', [
            'amount'   => (float) $context->amountDecimal(),
            'currency' => strtoupper($context->currency),
            'reference' => ['transaction' => $context->reference, 'order' => $context->order->order_number],
            'customer' => [
                'first_name' => $context->order->customer_name,
                'email'      => $context->order->customer_email,
            ],
            'source'   => ['id' => 'src_all'],
            'post'     => ['url' => $context->webhookUrl],
            'redirect' => ['url' => $context->returnUrl],
        ]);

        $url = $response->json('transaction.url');

        return $url
            ? PaymentResult::redirect($url, $response->json('id'), $response->json())
            : $this->fail($response, 'Tap Payments could not create the payment.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get($this->base().'/charges/'.$payment->gateway_transaction_id);

        if ($response->failed()) {
            return $this->fail($response, 'Tap Payments verification failed.');
        }

        return $this->statusFrom($response->json());
    }

    public function verifySignature(Request $request): bool
    {
        return $this->hmacMatchesBody($request, 'hashstring', 'sha256', $this->cred('secret_key'));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $result  = $this->statusFrom($payload);

        return new PaymentEvent(
            id: 'tap:'.($payload['id'] ?? '').':'.($payload['status'] ?? ''),
            type: (string) ($payload['status'] ?? ''),
            status: $result->status,
            reference: $payload['reference']['transaction'] ?? null,
            transactionId: $payload['id'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Tap Payments';
    }

    private function statusFrom(array $body): PaymentResult
    {
        $status = strtoupper((string) ($body['status'] ?? ''));

        return match (true) {
            in_array($status, ['CAPTURED'], true)      => PaymentResult::paid($body['id'] ?? null, $body),
            in_array($status, ['DECLINED', 'FAILED'], true)    => PaymentResult::failed('Tap Payments declined the payment.', $body),
            in_array($status, ['CANCELLED', 'VOID'], true) => PaymentResult::cancelled($body),
            default                                => PaymentResult::pending($body['id'] ?? null, $body),
        };
    }

    private function request()
    {
        return Http::withToken((string) $this->cred('secret_key'))->timeout(30)->acceptJson();
    }
}

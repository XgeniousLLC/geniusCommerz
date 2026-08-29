<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** Xendit — Indonesia and the Philippines, hosted invoices. */
class XenditGateway extends HostedGateway
{
    private const API = 'https://api.xendit.co';

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post(self::API.'/v2/invoices', [
            'external_id'           => $context->reference,
            'amount'                => (float) $context->amountDecimal(),
            'currency'              => strtoupper($context->currency),
            'description'           => $context->description(),
            'payer_email'           => $context->order->customer_email,
            'success_redirect_url'  => $context->returnUrl,
            'failure_redirect_url'  => $context->cancelUrl,
        ]);

        return $response->successful()
            ? PaymentResult::redirect($response->json('invoice_url'), $response->json('id'), $response->json())
            : $this->fail($response, 'Xendit could not create the invoice.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get(self::API.'/v2/invoices/'.$payment->gateway_transaction_id);

        return $response->failed()
            ? $this->fail($response, 'Xendit verification failed.')
            : $this->statusFrom($response->json());
    }

    public function verifySignature(Request $request): bool
    {
        return $this->tokenMatches($request, 'x-callback-token', $this->cred('callback_token'));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $result  = $this->statusFrom($payload);

        return new PaymentEvent(
            id: 'xendit:'.($payload['id'] ?? '').':'.($payload['status'] ?? ''),
            type: (string) ($payload['status'] ?? 'invoice'),
            status: $result->status,
            reference: $payload['external_id'] ?? null,
            transactionId: $payload['id'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Xendit';
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match ($body['status'] ?? null) {
            'PAID', 'SETTLED' => PaymentResult::paid($body['id'] ?? null, $body),
            'EXPIRED'         => PaymentResult::cancelled($body),
            default           => PaymentResult::pending($body['id'] ?? null, $body),
        };
    }

    private function request()
    {
        return Http::withBasicAuth((string) $this->cred('secret_key'), '')->timeout(30)->acceptJson();
    }
}

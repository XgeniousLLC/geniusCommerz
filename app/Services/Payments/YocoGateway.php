<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** Yoco (South Africa) — hosted checkout. */
class YocoGateway extends HostedGateway
{
    private const API = 'https://payments.yoco.com/api';

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post(self::API.'/checkouts', [
            'amount'     => $context->amountMinor,
            'currency'   => strtoupper($context->currency),
            'successUrl' => $context->returnUrl,
            'cancelUrl'  => $context->cancelUrl,
            'failureUrl' => $context->cancelUrl,
            'metadata'   => ['reference' => $context->reference],
        ]);

        return $response->json('redirectUrl')
            ? PaymentResult::redirect($response->json('redirectUrl'), $response->json('id'), $response->json())
            : $this->fail($response, 'Yoco could not create the checkout.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get(self::API.'/checkouts/'.$payment->gateway_transaction_id);

        return $response->failed()
            ? $this->fail($response, 'Yoco verification failed.')
            : $this->statusFrom($response->json());
    }

    /** Yoco uses Standard Webhooks: HMAC over "id.timestamp.body" with a base64 secret. */
    public function verifySignature(Request $request): bool
    {
        $secret = (string) $this->cred('webhook_secret');
        $id     = (string) $request->header('webhook-id');
        $ts     = (string) $request->header('webhook-timestamp');
        $header = (string) $request->header('webhook-signature');

        if ($secret === '' || $id === '' || $ts === '' || $header === '') {
            return false;
        }

        if (abs(time() - (int) $ts) > 300) {
            return false;
        }

        $key      = base64_decode(str_replace('whsec_', '', $secret));
        $expected = base64_encode(hash_hmac('sha256', "{$id}.{$ts}.".$request->getContent(), $key, true));

        // The header carries space-separated "v1,<sig>" entries.
        foreach (explode(' ', $header) as $entry) {
            $parts = explode(',', $entry, 2);

            if (count($parts) === 2 && hash_equals($expected, $parts[1])) {
                return true;
            }
        }

        return false;
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $data    = $payload['payload'] ?? [];

        $status = match ($payload['type'] ?? '') {
            'payment.succeeded' => PaymentStatus::Paid,
            'payment.failed'    => PaymentStatus::Failed,
            default             => null,
        };

        if ($status === null) {
            return null;
        }

        return new PaymentEvent(
            id: 'yoco:'.($payload['id'] ?? ''),
            type: (string) ($payload['type'] ?? ''),
            status: $status,
            reference: $data['metadata']['reference'] ?? null,
            transactionId: $data['checkoutId'] ?? ($data['id'] ?? null),
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Yoco';
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match ($body['status'] ?? null) {
            'completed' => PaymentResult::paid($body['id'] ?? null, $body),
            'failed'    => PaymentResult::failed('Yoco declined the payment.', $body),
            default     => PaymentResult::pending($body['id'] ?? null, $body),
        };
    }

    private function request()
    {
        return Http::withToken((string) $this->cred('secret_key'))->timeout(30)->acceptJson();
    }
}

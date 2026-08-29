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
 * Paddle Billing — merchant of record.
 *
 * Paddle becomes the seller, so it collects and remits EU VAT and US sales tax itself.
 * Orders taken through Paddle therefore should not also have local tax applied.
 */
class PaddleGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive() ? 'https://api.paddle.com' : 'https://sandbox-api.paddle.com';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post($this->base().'/transactions', [
            'items' => [[
                'quantity' => 1,
                'price'    => [
                    'description'  => $context->description(),
                    'name'         => $context->description(),
                    'unit_price'   => ['amount' => (string) $context->amountMinor, 'currency_code' => strtoupper($context->currency)],
                    'product'      => ['name' => $context->description(), 'tax_category' => 'standard'],
                    'quantity'     => ['minimum' => 1, 'maximum' => 1],
                ],
            ]],
            'custom_data'   => ['reference' => $context->reference],
            'currency_code' => strtoupper($context->currency),
            'checkout'      => ['url' => $context->returnUrl],
            'collection_mode' => 'automatic',
        ]);

        return $response->json('data.checkout.url')
            ? PaymentResult::redirect($response->json('data.checkout.url'), $response->json('data.id'), $response->json())
            : $this->fail($response, 'Paddle could not create the transaction.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get($this->base().'/transactions/'.$payment->gateway_transaction_id);

        return $response->failed()
            ? $this->fail($response, 'Paddle verification failed.')
            : $this->statusFrom($response->json('data') ?? []);
    }

    /** Paddle signs "ts:body" and sends both in one header. */
    public function verifySignature(Request $request): bool
    {
        $secret = (string) $this->cred('webhook_secret');
        $header = (string) $request->header('Paddle-Signature');

        if ($secret === '' || $header === '') {
            return false;
        }

        $parts = [];
        foreach (explode(';', $header) as $pair) {
            [$k, $v] = array_pad(explode('=', trim($pair), 2), 2, null);
            $parts[trim((string) $k)] = trim((string) $v);
        }

        $ts = $parts['ts'] ?? null;
        $h1 = $parts['h1'] ?? null;

        if (! $ts || ! $h1 || abs(time() - (int) $ts) > 300) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $ts.':'.$request->getContent(), $secret), $h1);
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $data    = $payload['data'] ?? [];

        $status = match ($payload['event_type'] ?? '') {
            'transaction.completed', 'transaction.paid' => PaymentStatus::Paid,
            'transaction.canceled'                      => PaymentStatus::Cancelled,
            'transaction.payment_failed'                => PaymentStatus::Failed,
            default                                     => null,
        };

        if ($status === null) {
            return null;
        }

        return new PaymentEvent(
            id: 'paddle:'.($payload['event_id'] ?? ''),
            type: (string) ($payload['event_type'] ?? ''),
            status: $status,
            reference: $data['custom_data']['reference'] ?? null,
            transactionId: $data['id'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Paddle';
    }

    private function statusFrom(array $data): PaymentResult
    {
        return match ($data['status'] ?? null) {
            'completed', 'paid' => PaymentResult::paid($data['id'] ?? null, $data),
            'canceled'          => PaymentResult::cancelled($data),
            default             => PaymentResult::pending($data['id'] ?? null, $data),
        };
    }

    private function request()
    {
        return Http::withToken((string) $this->cred('api_key'))->timeout(30)->acceptJson();
    }
}

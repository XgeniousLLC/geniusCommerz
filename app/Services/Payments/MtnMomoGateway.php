<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * MTN Mobile Money Collections — Ghana, Uganda, Cameroon, Côte d'Ivoire and more.
 *
 * Like M-Pesa this is a push-to-handset flow, not a redirect: the customer approves on
 * their phone, so charge() returns Pending and settlement follows.
 */
class MtnMomoGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive()
            ? 'https://proxy.momoapi.mtn.com'
            : 'https://sandbox.momodeveloper.mtn.com';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $referenceId = (string) Str::uuid();
        $msisdn = ltrim((string) PhoneNumber::toE164(
            $context->order->customer_phone,
            $context->order->shipping_address['country'] ?? null,
        ), '+');

        $response = $this->request()
            ->withHeaders([
                'X-Reference-Id'    => $referenceId,
                'X-Target-Environment' => $this->targetEnvironment(),
                'X-Callback-Url'    => $context->webhookUrl,
            ])
            ->post($this->base().'/collection/v1_0/requesttopay', [
                'amount'       => $context->amountDecimal(),
                'currency'     => strtoupper($context->currency),
                'externalId'   => $context->reference,
                'payer'        => ['partyIdType' => 'MSISDN', 'partyId' => $msisdn],
                'payerMessage' => $context->description(),
                'payeeNote'    => $context->order->order_number,
            ]);

        if (! $response->successful()) {
            return $this->fail($response, 'MTN MoMo could not request the payment.');
        }

        // The reference id is ours and is how the payment is polled and matched later.
        return PaymentResult::pending($referenceId, ['reference_id' => $referenceId]);
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()
            ->withHeaders(['X-Target-Environment' => $this->targetEnvironment()])
            ->get($this->base().'/collection/v1_0/requesttopay/'.$payment->gateway_transaction_id);

        return $response->failed()
            ? $this->fail($response, 'MTN MoMo verification failed.')
            : $this->statusFrom($response->json());
    }

    /** MoMo callbacks are unsigned, so the endpoint carries an unguessable token. */
    public function verifySignature(Request $request): bool
    {
        $expected = (string) $this->cred('callback_token');

        return $expected !== ''
            && hash_equals($expected, (string) $request->query('token', $request->header('x-callback-token', '')));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $result  = $this->statusFrom($payload);

        return new PaymentEvent(
            id: 'momo:'.($payload['financialTransactionId'] ?? $payload['externalId'] ?? '').':'.($payload['status'] ?? ''),
            type: (string) ($payload['status'] ?? 'requesttopay'),
            status: $result->status,
            reference: $payload['externalId'] ?? null,
            transactionId: $payload['financialTransactionId'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'MTN MoMo';
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match ($body['status'] ?? null) {
            'SUCCESSFUL' => PaymentResult::paid($body['financialTransactionId'] ?? null, $body),
            'FAILED'     => PaymentResult::failed($body['reason'] ?? 'MTN MoMo declined the payment.', $body),
            'REJECTED', 'TIMEOUT' => PaymentResult::cancelled($body),
            default      => PaymentResult::pending($body['financialTransactionId'] ?? null, $body),
        };
    }

    private function targetEnvironment(): string
    {
        return $this->isLive() ? (string) $this->cred('target_environment', 'mtnghana') : 'sandbox';
    }

    private function request()
    {
        return Http::withToken($this->accessToken())
            ->withHeaders(['Ocp-Apim-Subscription-Key' => (string) $this->cred('subscription_key')])
            ->timeout(30)->acceptJson();
    }

    private function accessToken(): string
    {
        $key = 'momo_token_'.($this->integration->environment ?: 'sandbox');

        return Cache::remember($key, 3000, function () {
            $response = Http::withBasicAuth((string) $this->cred('api_user'), (string) $this->cred('api_key'))
                ->withHeaders(['Ocp-Apim-Subscription-Key' => (string) $this->cred('subscription_key')])
                ->post($this->base().'/collection/token/');

            if (! $response->json('access_token')) {
                throw new \RuntimeException('MTN MoMo authentication failed.');
            }

            return (string) $response->json('access_token');
        });
    }
}

<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Vipps MobilePay — Norway, Denmark, Finland and Sweden.
 *
 * One driver covers both brands: Vipps and MobilePay merged onto the same ePayment API,
 * and which wallet the customer sees follows the currency and their market.
 */
class VippsMobilePayGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive() ? 'https://api.vipps.no' : 'https://apitest.vipps.no';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()
            ->withHeaders(['Idempotency-Key' => (string) Str::uuid()])
            ->post($this->base().'/epayment/v1/payments', [
                'amount'        => ['currency' => strtoupper($context->currency), 'value' => $context->amountMinor],
                'paymentMethod' => ['type' => 'WALLET'],
                'reference'     => Str::limit($context->reference, 50, ''),
                'returnUrl'     => $context->returnUrl,
                'userFlow'      => 'WEB_REDIRECT',
                'paymentDescription' => $context->description(),
            ]);

        return $response->json('redirectUrl')
            ? PaymentResult::redirect($response->json('redirectUrl'), $response->json('reference'), $response->json())
            : $this->fail($response, 'Vipps MobilePay could not create the payment.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get($this->base().'/epayment/v1/payments/'.$payment->gateway_transaction_id);

        if ($response->failed()) {
            return $this->fail($response, 'Vipps MobilePay verification failed.');
        }

        $body  = $response->json();
        $state = $body['state'] ?? null;

        // AUTHORIZED still needs capturing before the money moves.
        if ($state === 'AUTHORIZED') {
            return $this->capture($payment, $body);
        }

        return $this->statusFrom($body);
    }

    public function verifySignature(Request $request): bool
    {
        return $this->tokenMatches($request, 'x-callback-token', $this->cred('callback_token'));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();

        $status = match ($payload['name'] ?? ($payload['state'] ?? '')) {
            'AUTHORIZED', 'CAPTURED' => PaymentStatus::Paid,
            'CANCELLED', 'ABORTED', 'EXPIRED' => PaymentStatus::Cancelled,
            'TERMINATED' => PaymentStatus::Failed,
            default      => null,
        };

        if ($status === null) {
            return null;
        }

        return new PaymentEvent(
            id: 'vipps:'.($payload['reference'] ?? '').':'.($payload['name'] ?? $payload['state'] ?? ''),
            type: (string) ($payload['name'] ?? 'event'),
            status: $status,
            reference: $payload['reference'] ?? null,
            transactionId: $payload['reference'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Vipps MobilePay';
    }

    private function capture(Payment $payment, array $body): PaymentResult
    {
        $response = $this->request()
            ->withHeaders(['Idempotency-Key' => (string) Str::uuid()])
            ->post($this->base().'/epayment/v1/payments/'.$payment->gateway_transaction_id.'/capture', [
                'modificationAmount' => [
                    'currency' => strtoupper($payment->currency),
                    'value'    => $payment->amount_minor,
                ],
            ]);

        return $response->successful()
            ? PaymentResult::paid($payment->gateway_transaction_id, $response->json() ?? $body)
            : $this->fail($response, 'Vipps MobilePay capture failed.');
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match ($body['state'] ?? null) {
            'CAPTURED'  => PaymentResult::paid($body['reference'] ?? null, $body),
            'ABORTED', 'EXPIRED', 'TERMINATED' => PaymentResult::cancelled($body),
            default     => PaymentResult::pending($body['reference'] ?? null, $body),
        };
    }

    private function request()
    {
        return Http::withToken($this->accessToken())
            ->withHeaders([
                'Ocp-Apim-Subscription-Key' => (string) $this->cred('subscription_key'),
                'Merchant-Serial-Number'    => (string) $this->cred('merchant_serial_number'),
                'Vipps-System-Name'         => 'geniuscommerz',
                'Vipps-System-Version'      => '1.0',
            ])->timeout(30)->acceptJson();
    }

    private function accessToken(): string
    {
        return Cache::remember('vipps_token_'.($this->integration->environment ?: 'sandbox'), 3000, function () {
            $response = Http::withHeaders([
                'client_id'                 => (string) $this->cred('client_id'),
                'client_secret'             => (string) $this->cred('client_secret'),
                'Ocp-Apim-Subscription-Key' => (string) $this->cred('subscription_key'),
                'Merchant-Serial-Number'    => (string) $this->cred('merchant_serial_number'),
            ])->post($this->base().'/accesstoken/get');

            if (! $response->json('access_token')) {
                throw new \RuntimeException('Vipps MobilePay authentication failed.');
            }

            return (string) $response->json('access_token');
        });
    }
}

<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * bKash Tokenized Checkout (Bangladesh).
 *
 * Three-legged: grant a token, create the payment, then execute it when the customer
 * returns. bKash publishes no webhook, so the callback is the only signal and execute()
 * is what actually captures — an unexecuted payment is never treated as paid.
 */
class BkashGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive()
            ? 'https://tokenized.pay.bka.sh/v1.2.0-beta'
            : 'https://tokenized.sandbox.bka.sh/v1.2.0-beta';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post($this->base().'/tokenized/checkout/create', [
            'mode'                  => '0011',   // checkout with callback
            'payerReference'        => $context->order->customer_phone ?: $context->order->order_number,
            'callbackURL'           => $context->returnUrl,
            'amount'                => $context->amountDecimal(),
            'currency'              => strtoupper($context->currency),
            'intent'                => 'sale',
            'merchantInvoiceNumber' => $context->reference,
        ]);

        return $response->json('bkashURL')
            ? PaymentResult::redirect($response->json('bkashURL'), $response->json('paymentID'), $response->json())
            : PaymentResult::failed(
                $response->json('statusMessage') ?: 'bKash could not create the payment.',
                $response->json() ?? [],
            );
    }

    public function verify(Payment $payment): PaymentResult
    {
        // Execute captures the funds; it is idempotent enough that a second call on an
        // already-completed payment returns the completed state rather than charging twice.
        $execute = $this->request()->post($this->base().'/tokenized/checkout/execute', [
            'paymentID' => $payment->gateway_transaction_id,
        ]);

        $body = $execute->json() ?? [];

        if (($body['transactionStatus'] ?? null) === 'Completed') {
            return PaymentResult::paid($body['trxID'] ?? $payment->gateway_transaction_id, $body);
        }

        // Fall back to querying, which is what tells us about an abandoned payment.
        $query = $this->request()->post($this->base().'/tokenized/checkout/payment/status', [
            'paymentID' => $payment->gateway_transaction_id,
        ]);

        return $this->statusFrom($query->json() ?? $body);
    }

    /** bKash has no webhook; the customer's return is the only signal. */
    public function verifySignature(Request $request): bool
    {
        return false;
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        return null;
    }

    public function refund(Payment $payment, ?int $amountMinor = null): PaymentResult
    {
        $response = $this->request()->post($this->base().'/tokenized/checkout/payment/refund', [
            'paymentID' => $payment->gateway_transaction_id,
            'amount'    => number_format(
                $amountMinor === null ? $payment->amount() : \App\Support\Currencies::fromMinor($amountMinor, $payment->currency),
                2, '.', ''
            ),
            'trxID'     => $payment->payload['trxID'] ?? '',
            'sku'       => $payment->order->order_number,
            'reason'    => 'Merchant refund',
        ]);

        return $response->json('refundTrxID')
            ? PaymentResult::paid($response->json('refundTrxID'), $response->json())
            : $this->fail($response, 'bKash refund failed.');
    }

    public function name(): string
    {
        return 'bKash';
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match ($body['transactionStatus'] ?? null) {
            'Completed'   => PaymentResult::paid($body['trxID'] ?? null, $body),
            'Cancelled'   => PaymentResult::cancelled($body),
            'Failed'      => PaymentResult::failed($body['statusMessage'] ?? 'bKash declined the payment.', $body),
            default       => PaymentResult::pending($body['paymentID'] ?? null, $body),
        };
    }

    private function request()
    {
        return Http::withHeaders([
            'Authorization' => $this->token(),
            'X-APP-Key'     => (string) $this->cred('app_key'),
        ])->timeout(30)->acceptJson();
    }

    private function token(): string
    {
        $key = 'bkash_token_'.($this->integration->environment ?: 'sandbox');

        return Cache::remember($key, 3000, function () {
            $response = Http::withHeaders([
                'username' => (string) $this->cred('username'),
                'password' => (string) $this->cred('password'),
            ])->post($this->base().'/tokenized/checkout/token/grant', [
                'app_key'    => $this->cred('app_key'),
                'app_secret' => $this->cred('app_secret'),
            ]);

            if (! $response->json('id_token')) {
                throw new \RuntimeException('bKash authentication failed: '.($response->json('statusMessage') ?? 'no token'));
            }

            return (string) $response->json('id_token');
        });
    }
}

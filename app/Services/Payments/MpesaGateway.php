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

/**
 * M-Pesa (Kenya) via Safaricom Daraja STK Push.
 *
 * Not a hosted redirect: the customer is prompted on their handset and approves with a
 * PIN, so charge() returns Pending and the order settles from the callback. That is why
 * the payment machinery models Pending separately from Redirect.
 */
class MpesaGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive() ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        // Daraja wants a 2547XXXXXXXX msisdn with no plus.
        $msisdn = ltrim((string) PhoneNumber::toE164($context->order->customer_phone, 'KE'), '+');
        $timestamp = now()->format('YmdHis');
        $shortcode = (string) $this->cred('shortcode');

        $response = Http::withToken($this->accessToken())->timeout(30)->post(
            $this->base().'/mpesa/stkpush/v1/processrequest',
            [
                'BusinessShortCode' => $shortcode,
                'Password'          => base64_encode($shortcode.$this->cred('passkey').$timestamp),
                'Timestamp'         => $timestamp,
                'TransactionType'   => 'CustomerPayBillOnline',
                // M-Pesa settles whole shillings only.
                'Amount'            => (int) round((float) $context->amountDecimal()),
                'PartyA'            => $msisdn,
                'PartyB'            => $shortcode,
                'PhoneNumber'       => $msisdn,
                'CallBackURL'       => $context->webhookUrl,
                'AccountReference'  => substr($context->reference, 0, 12),
                'TransactionDesc'   => $context->description(),
            ],
        );

        if ($response->json('ResponseCode') !== '0') {
            return PaymentResult::failed(
                $response->json('errorMessage') ?? $response->json('ResponseDescription') ?? 'M-Pesa push failed.',
                $response->json() ?? [],
            );
        }

        // The customer approves on their phone; the outcome arrives by callback.
        return PaymentResult::pending($response->json('CheckoutRequestID'), $response->json());
    }

    public function verify(Payment $payment): PaymentResult
    {
        $timestamp = now()->format('YmdHis');
        $shortcode = (string) $this->cred('shortcode');

        $response = Http::withToken($this->accessToken())->timeout(20)->post(
            $this->base().'/mpesa/stkpushquery/v1/query',
            [
                'BusinessShortCode' => $shortcode,
                'Password'          => base64_encode($shortcode.$this->cred('passkey').$timestamp),
                'Timestamp'         => $timestamp,
                'CheckoutRequestID' => $payment->gateway_transaction_id,
            ],
        );

        return match ((string) $response->json('ResultCode')) {
            '0'     => PaymentResult::paid($payment->gateway_transaction_id, $response->json()),
            '1032'  => PaymentResult::cancelled($response->json()),
            ''      => PaymentResult::pending($payment->gateway_transaction_id, $response->json()),
            default => PaymentResult::failed($response->json('ResultDesc') ?? 'M-Pesa declined the payment.', $response->json() ?? []),
        };
    }

    /**
     * Daraja callbacks are unsigned, so the endpoint is protected by an unguessable
     * token appended to the callback URL and compared here.
     */
    public function verifySignature(Request $request): bool
    {
        $expected = (string) $this->cred('callback_token');

        if ($expected === '') {
            return false;
        }

        return hash_equals($expected, (string) $request->query('token', $request->header('x-callback-token', '')));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $callback = $request->input('Body.stkCallback', []);

        if (! $callback) {
            return null;
        }

        $code   = (string) ($callback['ResultCode'] ?? '');
        $status = match ($code) {
            '0'     => PaymentStatus::Paid,
            '1032'  => PaymentStatus::Cancelled,
            default => PaymentStatus::Failed,
        };

        $receipt = null;
        foreach ($callback['CallbackMetadata']['Item'] ?? [] as $item) {
            if (($item['Name'] ?? '') === 'MpesaReceiptNumber') {
                $receipt = $item['Value'] ?? null;
            }
        }

        return new PaymentEvent(
            id: 'mpesa:'.($callback['CheckoutRequestID'] ?? '').':'.$code,
            type: 'stkCallback',
            status: $status,
            // Daraja does not echo our reference, so the attempt is matched on the
            // CheckoutRequestID recorded when the push was created.
            reference: null,
            transactionId: $callback['CheckoutRequestID'] ?? null,
            raw: $request->all() + ['mpesa_receipt' => $receipt],
        );
    }

    public function name(): string
    {
        return 'M-Pesa';
    }

    private function accessToken(): string
    {
        $key = 'mpesa_token_'.($this->integration->environment ?: 'sandbox');

        return Cache::remember($key, 3000, function () {
            $response = Http::withBasicAuth((string) $this->cred('consumer_key'), (string) $this->cred('consumer_secret'))
                ->get($this->base().'/oauth/v1/generate?grant_type=client_credentials');

            if (! $response->json('access_token')) {
                throw new \RuntimeException('M-Pesa authentication failed.');
            }

            return (string) $response->json('access_token');
        });
    }
}

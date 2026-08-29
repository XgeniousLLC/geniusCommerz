<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Easypaisa (Pakistan) — mobile-account wallet charge.
 *
 * Uses the Mobile Account REST flow rather than the hosted page, because the hosted flow
 * requires an AES-encrypted browser form POST that does not fit a server-side redirect.
 * The customer approves on their handset, so this returns Pending and settles from the
 * callback — the same shape as M-Pesa and MTN MoMo.
 */
class EasypaisaGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive()
            ? 'https://easypay.easypaisa.com.pk/easypay-service/rest/v4'
            : 'https://easypaystg.easypaisa.com.pk/easypay-service/rest/v4';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        // Easypaisa expects a local 03XXXXXXXXX mobile account number.
        $account = PhoneNumber::national($context->order->customer_phone, 'PK')
            ?? $context->order->customer_phone;

        $response = Http::withHeaders([
            'Credentials' => base64_encode($this->cred('username').':'.$this->cred('password')),
        ])->timeout(30)->post($this->base().'/initiate-ma-transaction', [
            'orderId'            => $context->reference,
            'storeId'            => (string) $this->cred('store_id'),
            'transactionAmount'  => $context->amountDecimal(),
            'transactionType'    => 'MA',
            'mobileAccountNo'    => $account,
            'emailAddress'       => $context->order->customer_email ?: 'customer@example.com',
            'msisdn'             => $account,
        ]);

        $code = (string) $response->json('responseCode');

        // 0000 accepted, 0001 already in progress — both leave the customer to approve.
        if (! in_array($code, ['0000', '0001'], true)) {
            return PaymentResult::failed(
                $response->json('responseDesc') ?: 'Easypaisa could not start the transaction.',
                $response->json() ?? [],
            );
        }

        return PaymentResult::pending($response->json('transactionId') ?: $context->reference, $response->json() ?? []);
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = Http::withHeaders([
            'Credentials' => base64_encode($this->cred('username').':'.$this->cred('password')),
        ])->timeout(20)->post($this->base().'/inquire-transaction', [
            'orderId'   => $payment->idempotency_key,
            'storeId'   => (string) $this->cred('store_id'),
            'accountNum' => '',
        ]);

        return $this->statusFrom($response->json() ?? []);
    }

    /**
     * Easypaisa callbacks are unsigned, so the endpoint is protected by an unguessable
     * token appended to the callback URL — the same approach used for M-Pesa.
     */
    public function verifySignature(Request $request): bool
    {
        $expected = (string) $this->cred('callback_token');

        return $expected !== ''
            && hash_equals($expected, (string) $request->query('token', $request->header('x-callback-token', '')));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->all();
        $result  = $this->statusFrom($payload);

        return new PaymentEvent(
            id: 'easypaisa:'.($payload['orderId'] ?? '').':'.($payload['responseCode'] ?? $payload['transactionStatus'] ?? ''),
            type: (string) ($payload['transactionStatus'] ?? 'callback'),
            status: $result->status,
            reference: $payload['orderId'] ?? null,
            transactionId: $payload['transactionId'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Easypaisa';
    }

    private function statusFrom(array $body): PaymentResult
    {
        $status = strtoupper((string) ($body['transactionStatus'] ?? ''));
        $code   = (string) ($body['responseCode'] ?? '');

        return match (true) {
            $status === 'PAID' || $code === '0000' => PaymentResult::paid($body['transactionId'] ?? null, $body),
            $status === 'EXPIRED', $code === '0005' => PaymentResult::cancelled($body),
            $status === 'FAILED' => PaymentResult::failed($body['responseDesc'] ?? 'Easypaisa declined the payment.', $body),
            default => PaymentResult::pending($body['transactionId'] ?? null, $body),
        };
    }
}

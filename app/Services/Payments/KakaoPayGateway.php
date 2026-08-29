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
 * KakaoPay (South Korea) — hosted redirect.
 *
 * Chosen over Toss Payments for the Korean market because Toss is widget-first: its flow
 * needs a client-side SDK to produce a payment key, which cannot be driven from a
 * server-side redirect. KakaoPay's ready → approve flow maps cleanly onto this
 * architecture.
 */
class KakaoPayGateway extends HostedGateway
{
    private const API = 'https://open-api.kakaopay.com';

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post(self::API.'/online/v1/payment/ready', [
            'cid'          => $this->cred('cid', 'TC0ONETIME'),
            'partner_order_id' => $context->reference,
            'partner_user_id'  => (string) ($context->order->user_id ?? $context->order->id),
            'item_name'    => $context->description(),
            'quantity'     => 1,
            // KRW has no minor unit, so the integer amount is the amount.
            'total_amount' => $context->amountMinor,
            'tax_free_amount' => 0,
            'approval_url' => $context->returnUrl,
            'cancel_url'   => $context->cancelUrl,
            'fail_url'     => $context->cancelUrl,
        ]);

        if (! $response->json('next_redirect_pc_url')) {
            return $this->fail($response, 'KakaoPay could not prepare the payment.');
        }

        // The tid is needed to approve later, so keep it with the attempt.
        Cache::put('kakaopay_tid_'.$context->reference, $response->json('tid'), now()->addHour());

        return PaymentResult::redirect(
            $response->json('next_redirect_pc_url'),
            $response->json('tid'),
            $response->json(),
        );
    }

    public function verify(Payment $payment): PaymentResult
    {
        // KakaoPay returns a pg_token on the approval URL; without it the payment cannot
        // be approved, so an attempt that never came back stays pending.
        $pgToken = request()->query('pg_token');

        if (! $pgToken) {
            return PaymentResult::pending($payment->gateway_transaction_id);
        }

        $response = $this->request()->post(self::API.'/online/v1/payment/approve', [
            'cid'              => $this->cred('cid', 'TC0ONETIME'),
            'tid'              => $payment->gateway_transaction_id,
            'partner_order_id' => $payment->idempotency_key,
            'partner_user_id'  => (string) ($payment->order->user_id ?? $payment->order_id),
            'pg_token'         => $pgToken,
        ]);

        return $response->successful()
            ? PaymentResult::paid($response->json('aid') ?? $payment->gateway_transaction_id, $response->json())
            : $this->fail($response, 'KakaoPay approval failed.');
    }

    /** KakaoPay has no webhook; approval happens on the customer's return. */
    public function verifySignature(Request $request): bool
    {
        return false;
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        return null;
    }

    public function name(): string
    {
        return 'KakaoPay';
    }

    private function request()
    {
        return Http::withHeaders([
            'Authorization' => 'SECRET_KEY '.$this->cred('secret_key'),
            'Content-Type'  => 'application/json',
        ])->timeout(30);
    }
}

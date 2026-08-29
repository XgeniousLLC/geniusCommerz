<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** ShurjoPay (Bangladesh) — cards, mobile financial services and net banking. */
class ShurjoPayGateway extends HostedGateway
{
    private function base(): string
    {
        return rtrim((string) $this->cred(
            'base_url',
            $this->isLive() ? 'https://engine.shurjopayment.com' : 'https://sandbox.shurjopayment.com',
        ), '/');
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $token = $this->token();
        $shipping = $context->order->shipping_address ?? [];

        $response = Http::timeout(30)->post($this->base().'/api/secret-pay', [
            'token'          => $token['token'],
            'store_id'       => $token['store_id'],
            'prefix'         => $this->cred('prefix', 'SP'),
            'amount'         => (float) $context->amountDecimal(),
            'order_id'       => $context->reference,
            'currency'       => strtoupper($context->currency),
            'customer_name'  => $context->order->customer_name,
            'customer_phone' => $context->order->customer_phone,
            'customer_email' => $context->order->customer_email,
            'customer_address' => $shipping['address'] ?? 'N/A',
            'customer_city'  => $shipping['city'] ?? 'N/A',
            'client_ip'      => request()->ip(),
            'return_url'     => $context->returnUrl,
            'cancel_url'     => $context->cancelUrl,
        ]);

        return $response->json('checkout_url')
            ? PaymentResult::redirect($response->json('checkout_url'), $response->json('sp_order_id'), $response->json())
            : $this->fail($response, 'ShurjoPay could not create the payment.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $token = $this->token();

        $response = Http::withToken($token['token'])->timeout(20)
            ->post($this->base().'/api/verification', ['order_id' => $payment->gateway_transaction_id]);

        $body = $response->json('0') ?? $response->json();

        return $this->statusFrom(is_array($body) ? $body : []);
    }

    /**
     * ShurjoPay has no webhook signature — the callback carries only an order id, so the
     * outcome is confirmed through the authenticated verification API instead.
     */
    public function verifySignature(Request $request): bool
    {
        return $request->filled('order_id');
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $orderId = (string) $request->input('order_id');
        $token   = $this->token();

        $response = Http::withToken($token['token'])->timeout(20)
            ->post($this->base().'/api/verification', ['order_id' => $orderId]);

        $body = $response->json('0') ?? $response->json();

        if (! is_array($body)) {
            return null;
        }

        $result = $this->statusFrom($body);

        return new PaymentEvent(
            id: 'shurjopay:'.$orderId.':'.($body['sp_code'] ?? ''),
            type: (string) ($body['sp_message'] ?? 'verification'),
            status: $result->status,
            reference: $body['customer_order_id'] ?? null,
            transactionId: $orderId,
            raw: $body,
        );
    }

    public function name(): string
    {
        return 'ShurjoPay';
    }

    private function statusFrom(array $body): PaymentResult
    {
        // sp_code 1000 is success; anything else is a documented failure reason.
        return match ((string) ($body['sp_code'] ?? '')) {
            '1000'  => PaymentResult::paid($body['order_id'] ?? null, $body),
            '1002', '1005' => PaymentResult::cancelled($body),
            ''      => PaymentResult::pending($body['order_id'] ?? null, $body),
            default => PaymentResult::failed($body['sp_message'] ?? 'ShurjoPay declined the payment.', $body),
        };
    }

    /** @return array{token: string, store_id: string} */
    private function token(): array
    {
        $response = Http::timeout(20)->post($this->base().'/api/get_token', [
            'username' => $this->cred('username'),
            'password' => $this->cred('password'),
        ]);

        if (! $response->json('token')) {
            throw new \RuntimeException('ShurjoPay authentication failed.');
        }

        return ['token' => (string) $response->json('token'), 'store_id' => (string) $response->json('store_id')];
    }
}

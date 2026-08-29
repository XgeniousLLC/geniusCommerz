<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * SSLCOMMERZ (Bangladesh) — cards, mobile banking and net banking.
 *
 * The IPN is verified by hashing the posted fields with the store password, then
 * confirmed server-side against the validation API — the post alone is not trusted.
 */
class SslCommerzGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive() ? 'https://securepay.sslcommerz.com' : 'https://sandbox.sslcommerz.com';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $shipping = $context->order->shipping_address ?? [];

        $response = Http::asForm()->timeout(30)->post($this->base().'/gwprocess/v4/api.php', [
            'store_id'       => $this->cred('store_id'),
            'store_passwd'   => $this->cred('store_password'),
            'total_amount'   => $context->amountDecimal(),
            'currency'       => strtoupper($context->currency),
            'tran_id'        => $context->reference,
            'success_url'    => $context->returnUrl,
            'fail_url'       => $context->cancelUrl,
            'cancel_url'     => $context->cancelUrl,
            'ipn_url'        => $context->webhookUrl,
            'cus_name'       => $context->order->customer_name,
            'cus_email'      => $context->order->customer_email ?: 'customer@example.com',
            'cus_phone'      => $context->order->customer_phone,
            'cus_add1'       => $shipping['address'] ?? 'N/A',
            'cus_city'       => $shipping['city'] ?? 'N/A',
            'cus_country'    => $shipping['country'] ?? 'BD',
            'shipping_method' => 'NO',
            'product_name'   => $context->description(),
            'product_category' => 'general',
            'product_profile'  => 'general',
        ]);

        return strtoupper((string) $response->json('status')) === 'SUCCESS'
            ? PaymentResult::redirect($response->json('GatewayPageURL'), $response->json('sessionkey'), $response->json())
            : PaymentResult::failed($response->json('failedreason') ?? 'SSLCOMMERZ could not start the session.', $response->json() ?? []);
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = Http::timeout(20)->get($this->base().'/validator/api/merchantTransIDvalidationAPI.php', [
            'tran_id'      => $payment->idempotency_key,
            'store_id'     => $this->cred('store_id'),
            'store_passwd' => $this->cred('store_password'),
            'format'       => 'json',
        ]);

        $element = $response->json('element.0') ?? $response->json();

        return $this->statusFrom(is_array($element) ? $element : []);
    }

    /**
     * SSLCOMMERZ signs the IPN with an MD5 of the posted fields plus a hashed store
     * password, in a documented key order.
     */
    public function verifySignature(Request $request): bool
    {
        $verifySign = (string) $request->input('verify_sign');
        $verifyKey  = (string) $request->input('verify_key');
        $password   = (string) $this->cred('store_password');

        if ($verifySign === '' || $verifyKey === '' || $password === '') {
            return false;
        }

        $parts = ['store_passwd' => md5($password)];

        foreach (explode(',', $verifyKey) as $key) {
            $parts[$key] = (string) $request->input($key);
        }

        ksort($parts);

        $hash = [];
        foreach ($parts as $key => $value) {
            $hash[] = $key.'='.$value;
        }

        return hash_equals(md5(implode('&', $hash)), $verifySign);
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->all();
        $result  = $this->statusFrom($payload);

        return new PaymentEvent(
            id: 'sslcommerz:'.($payload['tran_id'] ?? '').':'.($payload['status'] ?? ''),
            type: (string) ($payload['status'] ?? 'ipn'),
            status: $result->status,
            reference: $payload['tran_id'] ?? null,
            transactionId: $payload['bank_tran_id'] ?? ($payload['val_id'] ?? null),
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'SSLCOMMERZ';
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match (strtoupper((string) ($body['status'] ?? ''))) {
            'VALID', 'VALIDATED' => PaymentResult::paid($body['bank_tran_id'] ?? null, $body),
            'FAILED'             => PaymentResult::failed('SSLCOMMERZ reported the payment as failed.', $body),
            'CANCELLED'          => PaymentResult::cancelled($body),
            default              => PaymentResult::pending($body['bank_tran_id'] ?? null, $body),
        };
    }
}

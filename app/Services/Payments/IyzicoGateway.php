<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/** iyzico (Turkey) — hosted Checkout Form, using the v2 HMAC auth scheme. */
class IyzicoGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive() ? 'https://api.iyzipay.com' : 'https://sandbox-api.iyzipay.com';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $path    = '/payment/iyzipos/checkoutform/initialize/auth/ecom';
        $shipping = $context->order->shipping_address ?? [];
        $price   = $context->amountDecimal();

        $body = [
            'locale'         => 'en',
            'conversationId' => $context->reference,
            'price'          => $price,
            'paidPrice'      => $price,
            'currency'       => strtoupper($context->currency),
            'basketId'       => $context->order->order_number,
            'paymentGroup'   => 'PRODUCT',
            'callbackUrl'    => $context->returnUrl,
            'buyer' => [
                'id'                  => (string) ($context->order->user_id ?? $context->order->id),
                'name'                => $context->order->customer_name,
                'surname'             => $context->order->customer_name,
                'email'               => $context->order->customer_email ?: 'buyer@example.com',
                'identityNumber'      => '11111111111',
                'registrationAddress' => $shipping['address'] ?? 'N/A',
                'city'                => $shipping['city'] ?? 'N/A',
                'country'             => $shipping['country'] ?? 'TR',
                'ip'                  => request()->ip() ?: '127.0.0.1',
            ],
            'shippingAddress' => $this->address($context, $shipping),
            'billingAddress'  => $this->address($context, $shipping),
            'basketItems' => [[
                'id'        => (string) $context->order->id,
                'name'      => $context->description(),
                'category1' => 'General',
                'itemType'  => 'PHYSICAL',
                'price'     => $price,
            ]],
        ];

        $response = $this->request($path, $body)->post($this->base().$path, $body);

        return $response->json('paymentPageUrl')
            ? PaymentResult::redirect($response->json('paymentPageUrl'), $response->json('token'), $response->json())
            : PaymentResult::failed(
                $response->json('errorMessage') ?: 'iyzico could not create the checkout form.',
                $response->json() ?? [],
            );
    }

    public function verify(Payment $payment): PaymentResult
    {
        $path = '/payment/iyzipos/checkoutform/auth/ecom/detail';
        $body = ['locale' => 'en', 'token' => $payment->gateway_transaction_id];

        $response = $this->request($path, $body)->post($this->base().$path, $body);

        return $this->statusFrom($response->json() ?? []);
    }

    /** iyzico posts the token back; the outcome comes from the authenticated detail call. */
    public function verifySignature(Request $request): bool
    {
        return $request->filled('token');
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $path = '/payment/iyzipos/checkoutform/auth/ecom/detail';
        $body = ['locale' => 'en', 'token' => (string) $request->input('token')];

        $response = $this->request($path, $body)->post($this->base().$path, $body);
        $detail   = $response->json() ?? [];

        if (! $detail) {
            return null;
        }

        $result = $this->statusFrom($detail);

        return new PaymentEvent(
            id: 'iyzico:'.($detail['paymentId'] ?? $body['token']).':'.($detail['paymentStatus'] ?? ''),
            type: (string) ($detail['paymentStatus'] ?? 'detail'),
            status: $result->status,
            reference: $detail['conversationId'] ?? null,
            transactionId: $detail['token'] ?? $body['token'],
            raw: $detail,
        );
    }

    public function name(): string
    {
        return 'iyzico';
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match ($body['paymentStatus'] ?? null) {
            'SUCCESS'  => PaymentResult::paid($body['paymentId'] ?? null, $body),
            'FAILURE'  => PaymentResult::failed($body['errorMessage'] ?? 'iyzico declined the payment.', $body),
            default    => PaymentResult::pending($body['paymentId'] ?? null, $body),
        };
    }

    private function address(PaymentContext $context, array $shipping): array
    {
        return [
            'contactName' => $context->order->customer_name,
            'city'        => $shipping['city'] ?? 'N/A',
            'country'     => $shipping['country'] ?? 'TR',
            'address'     => $shipping['address'] ?? 'N/A',
        ];
    }

    /** IYZWSv2: HMAC-SHA256 over randomKey + uriPath + body, hex, base64-wrapped. */
    private function request(string $path, array $body)
    {
        $apiKey    = (string) $this->cred('api_key');
        $secret    = (string) $this->cred('secret_key');
        $randomKey = (string) now()->getTimestampMs().Str::random(8);

        $signature = hash_hmac('sha256', $randomKey.$path.json_encode($body), $secret);

        $authorization = 'IYZWSv2 '.base64_encode(
            "apiKey:{$apiKey}&randomKey:{$randomKey}&signature:{$signature}"
        );

        return Http::withHeaders([
            'Authorization' => $authorization,
            'x-iyzi-rnd'    => $randomKey,
            'Content-Type'  => 'application/json',
        ])->timeout(30);
    }
}

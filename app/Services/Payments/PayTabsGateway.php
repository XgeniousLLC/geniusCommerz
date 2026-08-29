<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** PayTabs — Gulf and wider MENA. */
class PayTabsGateway extends HostedGateway
{
    public function charge(PaymentContext $context): PaymentResult
    {
        $shipping = $context->order->shipping_address ?? [];

        $response = $this->request()->post($this->base().'/payment/request', [
            'profile_id'    => $this->cred('profile_id'),
            'tran_type'     => 'sale',
            'tran_class'    => 'ecom',
            'cart_id'       => $context->reference,
            'cart_currency' => strtoupper($context->currency),
            'cart_amount'   => (float) $context->amountDecimal(),
            'cart_description' => $context->description(),
            'paypage_lang'  => 'en',
            'return'        => $context->returnUrl,
            'callback'      => $context->webhookUrl,
            'customer_details' => array_filter([
                'name'    => $context->order->customer_name,
                'email'   => $context->order->customer_email,
                'phone'   => $context->order->customer_phone,
                'street1' => $shipping['address'] ?? null,
                'city'    => $shipping['city'] ?? null,
                'state'   => $shipping['state'] ?? null,
                'country' => $shipping['country'] ?? null,
                'zip'     => $shipping['postal_code'] ?? null,
            ]),
        ]);

        return $response->successful() && $response->json('redirect_url')
            ? PaymentResult::redirect($response->json('redirect_url'), $response->json('tran_ref'), $response->json())
            : $this->fail($response, 'PayTabs could not create the payment page.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->post($this->base().'/payment/query', [
            'profile_id' => $this->cred('profile_id'),
            'tran_ref'   => $payment->gateway_transaction_id,
        ]);

        return $response->failed()
            ? $this->fail($response, 'PayTabs verification failed.')
            : $this->statusFrom($response->json());
    }

    public function verifySignature(Request $request): bool
    {
        return $this->hmacMatchesBody($request, 'signature', 'sha256', $this->cred('server_key'));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $result  = $this->statusFrom($payload);

        return new PaymentEvent(
            id: 'paytabs:'.($payload['tran_ref'] ?? '').':'.($payload['payment_result']['response_status'] ?? ''),
            type: (string) ($payload['payment_result']['response_status'] ?? 'status'),
            status: $result->status,
            reference: $payload['cart_id'] ?? null,
            transactionId: $payload['tran_ref'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'PayTabs';
    }

    private function statusFrom(array $body): PaymentResult
    {
        // A = authorised/captured, D = declined, C = cancelled, P = pending.
        return match ($body['payment_result']['response_status'] ?? null) {
            'A'     => PaymentResult::paid($body['tran_ref'] ?? null, $body),
            'D', 'E' => PaymentResult::failed(
                $body['payment_result']['response_message'] ?? 'PayTabs declined the payment.', $body),
            'C'     => PaymentResult::cancelled($body),
            default => PaymentResult::pending($body['tran_ref'] ?? null, $body),
        };
    }

    private function base(): string
    {
        // PayTabs is region-sharded; the endpoint is part of the merchant's account setup.
        return rtrim((string) $this->cred('base_url', 'https://secure.paytabs.com'), '/');
    }

    private function request()
    {
        return Http::withHeaders(['authorization' => (string) $this->cred('server_key')])
            ->timeout(30)->acceptJson();
    }
}

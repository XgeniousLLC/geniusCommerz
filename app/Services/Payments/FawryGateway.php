<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** Fawry (Egypt) — hosted checkout, cards and cash at Fawry outlets. */
class FawryGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive() ? 'https://www.atfawry.com' : 'https://atfawry.fawrystaging.com';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $merchant = (string) $this->cred('merchant_code');
        $amount   = $context->amountDecimal();
        $customer = (string) ($context->order->user_id ?? $context->order->id);

        // Fawry's signature is a SHA-256 over a fixed concatenation ending in the secure key.
        $signature = hash('sha256', $merchant.$context->reference.$customer.$context->returnUrl.$this->cred('security_key'));

        $response = Http::timeout(30)->post($this->base().'/ECommerceWeb/Fawry/payments/init', [
            'merchantCode'      => $merchant,
            'merchantRefNum'    => $context->reference,
            'customerProfileId' => $customer,
            'customerName'      => $context->order->customer_name,
            'customerMobile'    => $context->order->customer_phone,
            'customerEmail'     => $context->order->customer_email,
            'amount'            => $amount,
            'currencyCode'      => strtoupper($context->currency),
            'language'          => 'en-gb',
            'chargeItems'       => [[
                'itemId'   => (string) $context->order->id,
                'description' => $context->description(),
                'price'    => $amount,
                'quantity' => 1,
            ]],
            'returnUrl'  => $context->returnUrl,
            'signature'  => $signature,
        ]);

        $url = trim((string) $response->body(), '"');

        return str_starts_with($url, 'http')
            ? PaymentResult::redirect($url, $context->reference, ['checkout_url' => $url])
            : PaymentResult::failed('Fawry did not return a checkout URL.', ['body' => $url]);
    }

    public function verify(Payment $payment): PaymentResult
    {
        $merchant = (string) $this->cred('merchant_code');

        $response = Http::timeout(20)->get($this->base().'/ECommerceWeb/Fawry/payments/status/v2', [
            'merchantCode'   => $merchant,
            'merchantRefNumber' => $payment->idempotency_key,
            'signature'      => hash('sha256', $merchant.$payment->idempotency_key.$this->cred('security_key')),
        ]);

        return $this->statusFrom($response->json() ?? []);
    }

    public function verifySignature(Request $request): bool
    {
        $payload  = $request->json()->all();
        $provided = (string) ($payload['messageSignature'] ?? '');

        if ($provided === '') {
            return false;
        }

        $expected = hash('sha256',
            ($payload['fawryRefNumber'] ?? '').
            ($payload['merchantRefNumber'] ?? '').
            number_format((float) ($payload['paymentAmount'] ?? 0), 2, '.', '').
            number_format((float) ($payload['orderAmount'] ?? 0), 2, '.', '').
            ($payload['orderStatus'] ?? '').
            ($payload['paymentMethod'] ?? '').
            ($payload['paymentRefrenceNumber'] ?? '').
            $this->cred('security_key')
        );

        return hash_equals($expected, $provided);
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $result  = $this->statusFrom($payload);

        return new PaymentEvent(
            id: 'fawry:'.($payload['fawryRefNumber'] ?? '').':'.($payload['orderStatus'] ?? ''),
            type: (string) ($payload['orderStatus'] ?? 'notification'),
            status: $result->status,
            reference: $payload['merchantRefNumber'] ?? null,
            transactionId: $payload['fawryRefNumber'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Fawry';
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match (strtoupper((string) ($body['orderStatus'] ?? ''))) {
            'PAID'      => PaymentResult::paid($body['fawryRefNumber'] ?? null, $body),
            'CANCELED', 'EXPIRED' => PaymentResult::cancelled($body),
            'FAILED'    => PaymentResult::failed('Fawry declined the payment.', $body),
            // Cash-at-outlet orders sit UNPAID until the customer pays in person.
            default     => PaymentResult::pending($body['fawryRefNumber'] ?? null, $body),
        };
    }
}

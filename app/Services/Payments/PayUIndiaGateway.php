<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** PayU India — cards, UPI and netbanking. */
class PayUIndiaGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive() ? 'https://info.payu.in' : 'https://test.payu.in';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post($this->base().'/merchant/postservice.php?form=2', [
            'key'       => $this->cred('merchant_key'),
            'txnid'     => $context->reference,
            'amount'    => $context->amountDecimal(),
            'productinfo' => $context->description(),
            'firstname' => $context->order->customer_name,
            'email'     => $context->order->customer_email,
            'phone'     => $context->order->customer_phone,
            'surl'      => $context->returnUrl,
            'furl'      => $context->cancelUrl,
            'hash'      => $this->requestHash($context),
        ]);

        // Guarded: an unexpected type here should read as a payment failure, not a TypeError.
        $link = $response->json('payment_link');
        $url  = is_string($link) && $link !== ''
            ? $link
            : $this->base().'/_payment?txnid='.urlencode($context->reference);

        return $url
            ? PaymentResult::redirect($url, $context->reference, $response->json())
            : $this->fail($response, 'PayU India could not create the payment.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get($this->base().'/merchant/postservice.php?form=2&txnid='.$payment->idempotency_key);

        if ($response->failed()) {
            return $this->fail($response, 'PayU India verification failed.');
        }

        return $this->statusFrom($response->json());
    }

    public function verifySignature(Request $request): bool
    {
        return $this->payuHashMatches($request);
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $result  = $this->statusFrom($payload);

        return new PaymentEvent(
            id: 'payu_india:'.($payload['txnid'] ?? '').':'.($payload['status'] ?? ''),
            type: (string) ($payload['status'] ?? ''),
            status: $result->status,
            reference: $payload['txnid'] ?? null,
            transactionId: $payload['mihpayid'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'PayU India';
    }

    private function statusFrom(array $body): PaymentResult
    {
        $status = strtoupper((string) ($body['status'] ?? ''));

        return match (true) {
            in_array($status, ['SUCCESS'], true)      => PaymentResult::paid($body['mihpayid'] ?? null, $body),
            in_array($status, ['FAILURE'], true)    => PaymentResult::failed('PayU India declined the payment.', $body),
            in_array($status, ['CANCEL'], true) => PaymentResult::cancelled($body),
            default                                => PaymentResult::pending($body['mihpayid'] ?? null, $body),
        };
    }

    /** PayU hashes a fixed pipe-delimited field order; the layout is not negotiable. */
    private function requestHash(PaymentContext $context): string
    {
        return hash('sha512', implode('|', [
            $this->cred('merchant_key'),
            $context->reference,
            $context->amountDecimal(),
            $context->description(),
            $context->order->customer_name,
            $context->order->customer_email,
            '', '', '', '', '', '', '', '', '', '',
            $this->cred('salt'),
        ]));
    }

    /** The response hash is the request hash reversed, with status inserted. */
    private function payuHashMatches(Request $request): bool
    {
        $provided = (string) $request->input('hash');
        $salt     = (string) $this->cred('salt');

        if ($provided === '' || $salt === '') {
            return false;
        }

        $expected = hash('sha512', implode('|', [
            $salt,
            (string) $request->input('status'),
            '', '', '', '', '', '', '', '', '', '',
            (string) $request->input('email'),
            (string) $request->input('firstname'),
            (string) $request->input('productinfo'),
            (string) $request->input('amount'),
            (string) $request->input('txnid'),
            $this->cred('merchant_key'),
        ]));

        return hash_equals($expected, $provided);
    }

    private function request()
    {
        return Http::asForm()->timeout(30)->acceptJson();
    }
}

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

/** Square — hosted Payment Links. US, Canada, UK, Australia, Japan and Ireland. */
class SquareGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive() ? 'https://connect.squareup.com/v2' : 'https://connect.squareupsandbox.com/v2';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post($this->base().'/online-checkout/payment-links', [
            'idempotency_key' => Str::limit($context->reference, 45, ''),
            'quick_pay' => [
                'name'        => $context->description(),
                'price_money' => ['amount' => $context->amountMinor, 'currency' => strtoupper($context->currency)],
                'location_id' => $this->cred('location_id'),
            ],
            'checkout_options' => ['redirect_url' => $context->returnUrl],
            'payment_note'     => $context->reference,
        ]);

        return $response->json('payment_link.url')
            ? PaymentResult::redirect(
                $response->json('payment_link.url'),
                $response->json('payment_link.order_id'),
                $response->json(),
            )
            : $this->fail($response, 'Square could not create the payment link.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get($this->base().'/orders/'.$payment->gateway_transaction_id);

        return $response->failed()
            ? $this->fail($response, 'Square verification failed.')
            : $this->statusFrom($response->json('order') ?? []);
    }

    /**
     * Square signs the notification URL concatenated with the raw body, so the URL the
     * merchant registered must match exactly.
     */
    public function verifySignature(Request $request): bool
    {
        $key      = (string) $this->cred('webhook_signature_key');
        $provided = (string) $request->header('x-square-hmacsha256-signature');

        if ($key === '' || $provided === '') {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $request->fullUrl().$request->getContent(), $key, true));

        return hash_equals($expected, $provided);
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $object  = $payload['data']['object'] ?? [];
        $payment = $object['payment'] ?? [];

        $status = match ($payment['status'] ?? null) {
            'COMPLETED' => PaymentStatus::Paid,
            'FAILED'    => PaymentStatus::Failed,
            'CANCELED'  => PaymentStatus::Cancelled,
            default     => null,
        };

        if ($status === null) {
            return null;
        }

        return new PaymentEvent(
            id: 'square:'.($payload['event_id'] ?? ''),
            type: (string) ($payload['type'] ?? 'payment'),
            status: $status,
            reference: $payment['note'] ?? null,
            transactionId: $payment['order_id'] ?? ($payment['id'] ?? null),
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Square';
    }

    private function statusFrom(array $order): PaymentResult
    {
        return match ($order['state'] ?? null) {
            'COMPLETED' => PaymentResult::paid($order['id'] ?? null, $order),
            'CANCELED'  => PaymentResult::cancelled($order),
            default     => PaymentResult::pending($order['id'] ?? null, $order),
        };
    }

    private function request()
    {
        return Http::withToken((string) $this->cred('access_token'))
            ->withHeaders(['Square-Version' => '2024-10-17'])
            ->timeout(30)->acceptJson();
    }
}

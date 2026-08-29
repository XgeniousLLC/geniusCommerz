<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** Razorpay (India) — hosted Payment Links. */
class RazorpayGateway extends HostedGateway
{
    private const API = 'https://api.razorpay.com/v1';

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post(self::API.'/payment_links', [
            'amount'          => $context->amountMinor,
            'currency'        => strtoupper($context->currency),
            'description'     => $context->description(),
            'reference_id'    => $context->reference,
            'callback_url'    => $context->returnUrl,
            'callback_method' => 'get',
            'notify'          => ['sms' => false, 'email' => false],
        ]);

        return $response->successful()
            ? PaymentResult::redirect($response->json('short_url'), $response->json('id'), $response->json())
            : $this->fail($response, 'Razorpay could not create a payment link.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get(self::API.'/payment_links/'.$payment->gateway_transaction_id);

        if ($response->failed()) {
            return $this->fail($response, 'Razorpay verification failed.');
        }

        return match ($response->json('status')) {
            'paid'      => PaymentResult::paid($payment->gateway_transaction_id, $response->json()),
            'cancelled', 'expired' => PaymentResult::cancelled($response->json()),
            default     => PaymentResult::pending($payment->gateway_transaction_id, $response->json()),
        };
    }

    public function verifySignature(Request $request): bool
    {
        return $this->hmacMatchesBody($request, 'X-Razorpay-Signature', 'sha256', $this->cred('webhook_secret'));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $event   = (string) ($payload['event'] ?? '');
        $link    = $payload['payload']['payment_link']['entity'] ?? [];

        $status = match ($event) {
            'payment_link.paid' => PaymentStatus::Paid,
            'payment_link.cancelled', 'payment_link.expired' => PaymentStatus::Cancelled,
            default => null,
        };

        if ($status === null) {
            return null;
        }

        return new PaymentEvent(
            // Razorpay does not send an event id header on all plans; the link id plus the
            // event name is stable enough to deduplicate on.
            id: ($link['id'] ?? 'rzp').':'.$event,
            type: $event,
            status: $status,
            reference: $link['reference_id'] ?? null,
            transactionId: $link['id'] ?? null,
            raw: $payload,
        );
    }

    public function refund(Payment $payment, ?int $amountMinor = null): PaymentResult
    {
        $paymentId = $payment->payload['payments'][0]['payment_id'] ?? null;

        if (! $paymentId) {
            return PaymentResult::failed('No captured Razorpay payment found to refund.');
        }

        $response = $this->request()->post(self::API."/payments/{$paymentId}/refund",
            array_filter(['amount' => $amountMinor]));

        return $response->successful()
            ? PaymentResult::paid($response->json('id'), $response->json())
            : $this->fail($response, 'Razorpay refund failed.');
    }

    public function name(): string
    {
        return 'Razorpay';
    }

    private function request()
    {
        return Http::withBasicAuth((string) $this->cred('key_id'), (string) $this->cred('key_secret'))
            ->timeout(30)->acceptJson();
    }
}

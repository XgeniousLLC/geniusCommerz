<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Support\Currencies;

/**
 * Mollie (Europe) — iDEAL, Bancontact, SEPA, cards.
 *
 * Mollie webhooks carry no signature: the body is just a payment id. That is by design —
 * the id is not a credential, so the outcome must be read back from the API over an
 * authenticated request. parseWebhook therefore re-fetches rather than trusting the post.
 */
class MollieGateway extends HostedGateway
{
    private const API = 'https://api.mollie.com/v2';

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post(self::API.'/payments', [
            'amount' => [
                'currency' => strtoupper($context->currency),
                'value'    => $context->amountDecimal(),
            ],
            'description' => $context->description(),
            'redirectUrl' => $context->returnUrl,
            'webhookUrl'  => $context->webhookUrl,
            'metadata'    => ['reference' => $context->reference],
        ]);

        return $response->successful()
            ? PaymentResult::redirect($response->json('_links.checkout.href'), $response->json('id'), $response->json())
            : $this->fail($response, 'Mollie could not create the payment.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get(self::API.'/payments/'.$payment->gateway_transaction_id);

        return $response->failed()
            ? $this->fail($response, 'Mollie verification failed.')
            : $this->statusFrom($response->json());
    }

    /** Nothing to verify locally — see the class docblock. */
    public function verifySignature(Request $request): bool
    {
        return $request->filled('id');
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $id       = (string) $request->input('id');
        $response = $this->request()->get(self::API.'/payments/'.$id);

        if ($response->failed()) {
            return null;
        }

        $body   = $response->json();
        $result = $this->statusFrom($body);

        return new PaymentEvent(
            // Mollie re-notifies on every state change, so the id alone would collapse
            // distinct transitions into one; pair it with the status.
            id: $id.':'.($body['status'] ?? 'unknown'),
            type: 'payment.'.($body['status'] ?? 'unknown'),
            status: $result->status,
            reference: $body['metadata']['reference'] ?? null,
            transactionId: $id,
            raw: $body,
        );
    }

    public function refund(Payment $payment, ?int $amountMinor = null): PaymentResult
    {
        $amount = $amountMinor === null ? $payment->amount() : Currencies::fromMinor($amountMinor, $payment->currency);

        $response = $this->request()->post(self::API.'/payments/'.$payment->gateway_transaction_id.'/refunds', [
            'amount' => [
                'currency' => strtoupper($payment->currency),
                'value'    => number_format($amount, Currencies::exponent($payment->currency), '.', ''),
            ],
        ]);

        return $response->successful()
            ? PaymentResult::paid($response->json('id'), $response->json())
            : $this->fail($response, 'Mollie refund failed.');
    }

    public function name(): string
    {
        return 'Mollie';
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match ($body['status'] ?? null) {
            'paid'      => PaymentResult::paid($body['id'] ?? null, $body),
            'failed'    => PaymentResult::failed('Mollie reported the payment as failed.', $body),
            'canceled', 'expired' => PaymentResult::cancelled($body),
            default     => PaymentResult::pending($body['id'] ?? null, $body),
        };
    }

    private function request()
    {
        return Http::withToken((string) $this->cred('api_key'))->timeout(30)->acceptJson();
    }
}

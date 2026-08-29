<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** Paystack — Nigeria, Ghana, South Africa, Kenya. */
class PaystackGateway extends HostedGateway
{
    private const API = 'https://api.paystack.co';

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post(self::API.'/transaction/initialize', [
            'email'        => $context->order->customer_email ?: 'customer@example.com',
            'amount'       => $context->amountMinor,
            'currency'     => strtoupper($context->currency),
            'reference'    => $context->reference,
            'callback_url' => $context->returnUrl,
        ]);

        return $response->successful() && $response->json('status')
            ? PaymentResult::redirect(
                $response->json('data.authorization_url'),
                $response->json('data.reference'),
                $response->json(),
            )
            : $this->fail($response, 'Paystack could not initialise the transaction.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get(self::API.'/transaction/verify/'.$payment->idempotency_key);

        if ($response->failed()) {
            return $this->fail($response, 'Paystack verification failed.');
        }

        return match ($response->json('data.status')) {
            'success'  => PaymentResult::paid($response->json('data.reference'), $response->json()),
            'failed'   => PaymentResult::failed('Paystack reported the transaction as failed.', $response->json()),
            'abandoned' => PaymentResult::cancelled($response->json()),
            default    => PaymentResult::pending($response->json('data.reference'), $response->json()),
        };
    }

    public function verifySignature(Request $request): bool
    {
        // Paystack signs with the secret key, not a separate webhook secret.
        return $this->hmacMatchesBody($request, 'x-paystack-signature', 'sha512', $this->cred('secret_key'));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $event   = (string) ($payload['event'] ?? '');
        $data    = $payload['data'] ?? [];

        $status = match ($event) {
            'charge.success' => PaymentStatus::Paid,
            'charge.failed'  => PaymentStatus::Failed,
            default          => null,
        };

        if ($status === null) {
            return null;
        }

        return new PaymentEvent(
            id: $event.':'.($data['id'] ?? $data['reference'] ?? ''),
            type: $event,
            status: $status,
            reference: $data['reference'] ?? null,
            transactionId: (string) ($data['reference'] ?? ''),
            raw: $payload,
        );
    }

    public function refund(Payment $payment, ?int $amountMinor = null): PaymentResult
    {
        $response = $this->request()->post(self::API.'/refund', array_filter([
            'transaction' => $payment->idempotency_key,
            'amount'      => $amountMinor,
        ]));

        return $response->successful()
            ? PaymentResult::paid((string) $response->json('data.id'), $response->json())
            : $this->fail($response, 'Paystack refund failed.');
    }

    public function name(): string
    {
        return 'Paystack';
    }

    private function request()
    {
        return Http::withToken((string) $this->cred('secret_key'))->timeout(30)->acceptJson();
    }
}

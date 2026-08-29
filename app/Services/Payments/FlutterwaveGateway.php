<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/** Flutterwave — pan-African coverage. */
class FlutterwaveGateway extends HostedGateway
{
    private const API = 'https://api.flutterwave.com/v3';

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post(self::API.'/payments', [
            'tx_ref'       => $context->reference,
            'amount'       => $context->amountDecimal(),
            'currency'     => strtoupper($context->currency),
            'redirect_url' => $context->returnUrl,
            'customer'     => [
                'email'       => $context->order->customer_email ?: 'customer@example.com',
                'phonenumber' => $context->order->customer_phone,
                'name'        => $context->order->customer_name,
            ],
            'customizations' => ['title' => $context->description()],
        ]);

        return $response->successful() && $response->json('status') === 'success'
            ? PaymentResult::redirect($response->json('data.link'), $context->reference, $response->json())
            : $this->fail($response, 'Flutterwave could not create the payment.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get(self::API.'/transactions/verify_by_reference', [
            'tx_ref' => $payment->idempotency_key,
        ]);

        if ($response->failed()) {
            return $this->fail($response, 'Flutterwave verification failed.');
        }

        return match ($response->json('data.status')) {
            'successful' => PaymentResult::paid((string) $response->json('data.id'), $response->json()),
            'failed'     => PaymentResult::failed('Flutterwave reported the transaction as failed.', $response->json()),
            'cancelled'  => PaymentResult::cancelled($response->json()),
            default      => PaymentResult::pending((string) $response->json('data.id'), $response->json()),
        };
    }

    public function verifySignature(Request $request): bool
    {
        // Flutterwave echoes a secret hash you configure, rather than signing the body.
        return $this->tokenMatches($request, 'verif-hash', $this->cred('secret_hash'));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $data    = $payload['data'] ?? $payload;

        $status = match ($data['status'] ?? null) {
            'successful' => PaymentStatus::Paid,
            'failed'     => PaymentStatus::Failed,
            'cancelled'  => PaymentStatus::Cancelled,
            default      => null,
        };

        if ($status === null) {
            return null;
        }

        return new PaymentEvent(
            id: 'flw:'.($data['id'] ?? $data['tx_ref'] ?? ''),
            type: (string) ($payload['event'] ?? 'charge'),
            status: $status,
            reference: $data['tx_ref'] ?? null,
            transactionId: (string) ($data['id'] ?? ''),
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Flutterwave';
    }

    private function request()
    {
        return Http::withToken((string) $this->cred('secret_key'))->timeout(30)->acceptJson();
    }
}

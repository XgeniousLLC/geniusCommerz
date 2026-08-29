<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Pagar.me (Brazil) — cards, boleto and Pix.
 *
 * Pix is Brazil's instant mobile rail and is the default here, since it settles in
 * seconds and carries far lower fees than cards.
 */
class PagarMeGateway extends HostedGateway
{
    private const API = 'https://api.pagar.me/core/v5';

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = $this->request()->post(self::API.'/orders', [
            'code'  => $context->reference,
            'items' => [[
                'amount'      => $context->amountMinor,
                'description' => $context->description(),
                'quantity'    => 1,
            ]],
            'customer' => [
                'name'  => $context->order->customer_name,
                'email' => $context->order->customer_email ?: 'cliente@example.com',
                'type'  => 'individual',
            ],
            'payments' => [[
                'payment_method' => 'checkout',
                'checkout' => [
                    'expires_in'         => 3600,
                    'default_payment_method' => 'pix',
                    'accepted_payment_methods' => ['credit_card', 'pix', 'boleto'],
                    'success_url'        => $context->returnUrl,
                    'customer_editable'  => true,
                ],
            ]],
        ]);

        $url = $response->json('checkouts.0.payment_url');

        return $url
            ? PaymentResult::redirect($url, $response->json('id'), $response->json())
            : $this->fail($response, 'Pagar.me could not create the order.');
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = $this->request()->get(self::API.'/orders/'.$payment->gateway_transaction_id);

        return $response->failed()
            ? $this->fail($response, 'Pagar.me verification failed.')
            : $this->statusFrom($response->json());
    }

    public function verifySignature(Request $request): bool
    {
        return $this->hmacMatchesBody($request, 'x-hub-signature', 'sha1', $this->cred('webhook_secret'));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $order   = $payload['data'] ?? [];
        $result  = $this->statusFrom($order);

        return new PaymentEvent(
            id: 'pagarme:'.($payload['id'] ?? ''),
            type: (string) ($payload['type'] ?? 'order'),
            status: $result->status,
            reference: $order['code'] ?? null,
            transactionId: $order['id'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Pagar.me';
    }

    private function statusFrom(array $body): PaymentResult
    {
        return match ($body['status'] ?? null) {
            'paid'     => PaymentResult::paid($body['id'] ?? null, $body),
            'failed'   => PaymentResult::failed('Pagar.me declined the order.', $body),
            'canceled' => PaymentResult::cancelled($body),
            default    => PaymentResult::pending($body['id'] ?? null, $body),
        };
    }

    private function request()
    {
        return Http::withBasicAuth((string) $this->cred('secret_key'), '')->timeout(30)->acceptJson();
    }
}

<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Authorize.Net — Accept Hosted.
 *
 * A token is fetched server-side and the browser POSTs it to the hosted page, so this
 * returns a form payload rather than a URL.
 */
class AuthorizeNetGateway extends HostedGateway
{
    private function api(): string
    {
        return $this->isLive() ? 'https://api.authorize.net/xml/v1/request.api' : 'https://apitest.authorize.net/xml/v1/request.api';
    }

    private function hostedPage(): string
    {
        return $this->isLive() ? 'https://accept.authorize.net/payment/payment' : 'https://test.authorize.net/payment/payment';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $response = Http::timeout(30)->post($this->api(), [
            'getHostedPaymentPageRequest' => [
                'merchantAuthentication' => $this->auth(),
                'transactionRequest' => [
                    'transactionType' => 'authCaptureTransaction',
                    'amount'          => $context->amountDecimal(),
                    'order'           => ['invoiceNumber' => substr($context->reference, 0, 20)],
                ],
                'hostedPaymentSettings' => ['setting' => [
                    ['settingName' => 'hostedPaymentReturnOptions',
                     'settingValue' => json_encode(['url' => $context->returnUrl, 'cancelUrl' => $context->cancelUrl, 'showReceipt' => false])],
                    ['settingName' => 'hostedPaymentButtonOptions', 'settingValue' => json_encode(['text' => 'Pay'])],
                ]],
            ],
        ]);

        // Authorize.Net returns JSON with a UTF-8 BOM that breaks strict decoding.
        $body  = json_decode(preg_replace('/^[\x00-\x20]+/', '', $response->body()), true) ?: [];
        $token = $body['token'] ?? null;

        if (! $token) {
            return PaymentResult::failed(
                $body['messages']['message'][0]['text'] ?? 'Authorize.Net did not return a hosted page token.',
                $body,
            );
        }

        return PaymentResult::formPost($this->hostedPage(), ['token' => $token], $context->reference);
    }

    public function verify(Payment $payment): PaymentResult
    {
        $response = Http::timeout(20)->post($this->api(), [
            'getTransactionListRequest' => [
                'merchantAuthentication' => $this->auth(),
                'refId' => substr($payment->idempotency_key, 0, 20),
            ],
        ]);

        $body = json_decode(preg_replace('/^[\x00-\x20]+/', '', $response->body()), true) ?: [];

        $transaction = $body['transactions'][0] ?? null;

        return $transaction
            ? $this->statusFrom($transaction)
            : PaymentResult::pending($payment->gateway_transaction_id, $body);
    }

    public function verifySignature(Request $request): bool
    {
        return $this->hmacMatchesBody($request, 'x-anet-signature', 'sha512', $this->cred('signature_key'))
            // Authorize.Net prefixes the header value with "sha512=".
            || $this->prefixedHmacMatches($request);
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->json()->all();
        $payload_ = $payload['payload'] ?? [];

        $status = match ($payload['eventType'] ?? '') {
            'net.authorize.payment.authcapture.created' => PaymentStatus::Paid,
            'net.authorize.payment.void.created'        => PaymentStatus::Cancelled,
            default                                     => null,
        };

        if ($status === null) {
            return null;
        }

        return new PaymentEvent(
            id: 'authnet:'.($payload['notificationId'] ?? ''),
            type: (string) ($payload['eventType'] ?? ''),
            status: $status,
            reference: $payload_['invoiceNumber'] ?? null,
            transactionId: (string) ($payload_['id'] ?? ''),
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'Authorize.Net';
    }

    private function statusFrom(array $transaction): PaymentResult
    {
        return match ($transaction['transactionStatus'] ?? null) {
            'settledSuccessfully', 'capturedPendingSettlement' => PaymentResult::paid($transaction['transId'] ?? null, $transaction),
            'voided'  => PaymentResult::cancelled($transaction),
            'declined', 'failedReview' => PaymentResult::failed('Authorize.Net declined the transaction.', $transaction),
            default   => PaymentResult::pending($transaction['transId'] ?? null, $transaction),
        };
    }

    private function prefixedHmacMatches(Request $request): bool
    {
        $header = (string) $request->header('x-anet-signature');
        $key    = (string) $this->cred('signature_key');

        if ($key === '' || ! str_contains($header, '=')) {
            return false;
        }

        [, $provided] = explode('=', $header, 2);

        return hash_equals(strtoupper(hash_hmac('sha512', $request->getContent(), $key)), strtoupper($provided));
    }

    private function auth(): array
    {
        return [
            'name'           => (string) $this->cred('login_id'),
            'transactionKey' => (string) $this->cred('transaction_key'),
        ];
    }
}

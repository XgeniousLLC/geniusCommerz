<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Payments\PaymentStatus;
use Illuminate\Http\Request;

/**
 * 2Checkout / Verifone — merchant of record, like Paddle.
 *
 * ConvertPlus takes a signed browser POST, and its signature is an HMAC over each value
 * prefixed by its byte length, which is what stops two adjacent fields from being
 * rearranged without changing the hash.
 */
class TwoCheckoutGateway extends HostedGateway
{
    private const CHECKOUT = 'https://secure.2checkout.com/checkout/buy';

    public function charge(PaymentContext $context): PaymentResult
    {
        $fields = [
            'merchant'      => (string) $this->cred('merchant_code'),
            'dynamic'       => '1',
            'currency'      => strtoupper($context->currency),
            'prod'          => $context->description(),
            'price'         => $context->amountDecimal(),
            'qty'           => '1',
            'type'          => 'digital',
            'return-url'    => $context->returnUrl,
            'return-type'   => 'redirect',
            'merchant-order-id' => $context->reference,
            'order-ext-ref' => $context->reference,
            'email'         => (string) $context->order->customer_email,
        ];

        $fields['signature'] = $this->lengthPrefixedHmac($fields);

        return PaymentResult::formPost(self::CHECKOUT, $fields, $context->reference);
    }

    public function verify(Payment $payment): PaymentResult
    {
        // 2Checkout's order API needs separate API credentials; the IPN is authoritative,
        // so an unconfirmed attempt simply stays pending rather than guessing.
        return PaymentResult::pending($payment->gateway_transaction_id);
    }

    public function verifySignature(Request $request): bool
    {
        $payload  = $request->all();
        $provided = (string) ($payload['md5_hash'] ?? '');

        if ($provided === '') {
            return false;
        }

        // IPN hash: sale id, merchant code, invoice id, then the secret word.
        $expected = strtoupper(md5(
            ($payload['sale_id'] ?? '').
            $this->cred('merchant_code').
            ($payload['invoice_id'] ?? '').
            $this->cred('secret_word')
        ));

        return hash_equals($expected, strtoupper($provided));
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->all();

        $status = match (strtoupper((string) ($payload['message_type'] ?? ''))) {
            'ORDER_CREATED', 'FRAUD_STATUS_CHANGED' => ($payload['fraud_status'] ?? 'pass') === 'fail'
                ? PaymentStatus::Failed
                : PaymentStatus::Paid,
            'REFUND_ISSUED' => PaymentStatus::Cancelled,
            default         => null,
        };

        if ($status === null) {
            return null;
        }

        return new PaymentEvent(
            id: '2co:'.($payload['sale_id'] ?? '').':'.($payload['message_type'] ?? ''),
            type: (string) ($payload['message_type'] ?? 'ipn'),
            status: $status,
            reference: $payload['vendor_order_id'] ?? ($payload['merchant_order_id'] ?? null),
            transactionId: $payload['sale_id'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return '2Checkout';
    }

    private function lengthPrefixedHmac(array $fields): string
    {
        ksort($fields);

        $payload = '';
        foreach ($fields as $value) {
            $value = (string) $value;
            $payload .= strlen($value).$value;
        }

        return hash_hmac('sha256', $payload, (string) $this->cred('secret_key'));
    }
}

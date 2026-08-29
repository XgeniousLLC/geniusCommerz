<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * JazzCash (Pakistan) — hosted checkout.
 *
 * The customer's browser must POST a signed field set, so this returns a form payload
 * rather than a URL. The secure hash is an HMAC-SHA256 over the non-empty pp_ fields in
 * key order, joined with the integrity salt.
 */
class JazzCashGateway extends HostedGateway
{
    private function base(): string
    {
        return $this->isLive()
            ? 'https://payments.jazzcash.com.pk'
            : 'https://sandbox.jazzcash.com.pk';
    }

    public function charge(PaymentContext $context): PaymentResult
    {
        $now = now();

        $fields = [
            'pp_Version'        => '1.1',
            'pp_TxnType'        => 'MWALLET',
            'pp_Language'       => 'EN',
            'pp_MerchantID'     => (string) $this->cred('merchant_id'),
            'pp_Password'       => (string) $this->cred('password'),
            'pp_TxnRefNo'       => 'T'.$now->format('YmdHis'),
            // JazzCash amounts are in the minor unit with no separator.
            'pp_Amount'         => (string) $context->amountMinor,
            'pp_TxnCurrency'    => strtoupper($context->currency),
            'pp_TxnDateTime'    => $now->format('YmdHis'),
            'pp_BillReference'  => $context->reference,
            'pp_Description'    => $context->description(),
            'pp_TxnExpiryDateTime' => $now->copy()->addHour()->format('YmdHis'),
            'pp_ReturnURL'      => $context->returnUrl,
            'ppmpf_1'           => $context->reference,
        ];

        $fields['pp_SecureHash'] = $this->secureHash($fields);

        return PaymentResult::formPost(
            $this->base().'/CustomerPortal/transactionmanagement/merchantform',
            $fields,
            $fields['pp_TxnRefNo'],
        );
    }

    public function verify(Payment $payment): PaymentResult
    {
        $fields = [
            'pp_MerchantID' => (string) $this->cred('merchant_id'),
            'pp_Password'   => (string) $this->cred('password'),
            'pp_TxnRefNo'   => (string) $payment->gateway_transaction_id,
        ];
        $fields['pp_SecureHash'] = $this->secureHash($fields);

        $response = Http::timeout(20)
            ->post($this->base().'/ApplicationAPI/API/PaymentInquiry/Inquire', $fields);

        return $this->statusFrom($response->json() ?? []);
    }

    /** The IPN carries the same secure hash, recomputed over its own fields. */
    public function verifySignature(Request $request): bool
    {
        $payload  = $request->all();
        $provided = (string) ($payload['pp_SecureHash'] ?? '');

        if ($provided === '') {
            return false;
        }

        unset($payload['pp_SecureHash']);

        return hash_equals($this->secureHash($payload), $provided);
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        $payload = $request->all();
        $result  = $this->statusFrom($payload);

        return new PaymentEvent(
            id: 'jazzcash:'.($payload['pp_TxnRefNo'] ?? '').':'.($payload['pp_ResponseCode'] ?? ''),
            type: (string) ($payload['pp_ResponseCode'] ?? 'ipn'),
            status: $result->status,
            // ppmpf_1 carries our own reference back.
            reference: $payload['ppmpf_1'] ?? ($payload['pp_BillReference'] ?? null),
            transactionId: $payload['pp_TxnRefNo'] ?? null,
            raw: $payload,
        );
    }

    public function name(): string
    {
        return 'JazzCash';
    }

    private function statusFrom(array $body): PaymentResult
    {
        // 000 succeeded, 121 pending; everything else is a documented failure code.
        return match ((string) ($body['pp_ResponseCode'] ?? '')) {
            '000', '121' => PaymentResult::paid($body['pp_TxnRefNo'] ?? null, $body),
            '124'        => PaymentResult::cancelled($body),
            ''           => PaymentResult::pending($body['pp_TxnRefNo'] ?? null, $body),
            default      => PaymentResult::failed($body['pp_ResponseMessage'] ?? 'JazzCash declined the payment.', $body),
        };
    }

    /** HMAC-SHA256 over non-empty pp_/ppmpf_ fields in key order, salt-prefixed. */
    private function secureHash(array $fields): string
    {
        $relevant = array_filter(
            $fields,
            fn ($value, $key) => str_starts_with($key, 'pp_') || str_starts_with($key, 'ppmpf_'),
            ARRAY_FILTER_USE_BOTH,
        );

        ksort($relevant);

        $values = array_filter($relevant, fn ($v) => $v !== null && $v !== '');
        $salt   = (string) $this->cred('integrity_salt');

        return strtoupper(hash_hmac('sha256', $salt.'&'.implode('&', $values), $salt));
    }
}

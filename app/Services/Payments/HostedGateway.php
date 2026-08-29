<?php

namespace App\Services\Payments;

use App\Contracts\PaymentInterface;
use App\Models\Payment;
use App\Payments\PaymentResult;
use App\Services\ProviderDriver;
use Illuminate\Http\Request;

/**
 * Shared skeleton for hosted-redirect gateways.
 *
 * They all create a payment server-side, send the customer to a URL the gateway returns,
 * and learn the outcome from a webhook. What genuinely differs is the request body, the
 * response field names and the webhook authentication scheme — those stay in each driver.
 */
abstract class HostedGateway extends ProviderDriver implements PaymentInterface
{
    /** Most gateways here support refunds; those that do not inherit this. */
    public function refund(Payment $payment, ?int $amountMinor = null): PaymentResult
    {
        return PaymentResult::failed($this->name().' refunds are not wired up — refund in the gateway dashboard.');
    }

    /**
     * Constant-time HMAC check over the raw request body.
     *
     * The raw body must be used rather than a re-encoded array: re-encoding changes key
     * order and whitespace, which breaks every signature.
     */
    protected function hmacMatchesBody(Request $request, string $header, string $algo, mixed $secret): bool
    {
        $provided = (string) $request->header($header);
        $secret   = (string) $secret;

        if ($provided === '' || $secret === '') {
            return false;
        }

        return hash_equals(hash_hmac($algo, $request->getContent(), $secret), $provided);
    }

    /** Shared-token schemes, where the gateway echoes a value you configured. */
    protected function tokenMatches(Request $request, string $header, mixed $expected): bool
    {
        $provided = (string) $request->header($header);
        $expected = (string) $expected;

        return $provided !== '' && $expected !== '' && hash_equals($expected, $provided);
    }

    protected function errorFrom($response, string $fallback): string
    {
        $body = is_array($response) ? $response : (is_object($response) && method_exists($response, 'json') ? $response->json() : null);

        if (! is_array($body)) {
            return $fallback;
        }

        return $body['message']
            ?? $body['error']['message']
            ?? $body['error_description']
            ?? $body['detail']
            ?? ($body['errors'][0]['description'] ?? $fallback);
    }

    protected function fail($response, string $fallback): PaymentResult
    {
        $body = is_object($response) && method_exists($response, 'json') ? ($response->json() ?? []) : (array) $response;

        return PaymentResult::failed($this->errorFrom($response, $fallback), $body);
    }
}

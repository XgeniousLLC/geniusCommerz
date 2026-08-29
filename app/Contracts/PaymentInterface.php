<?php

namespace App\Contracts;

use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use Illuminate\Http\Request;

interface PaymentInterface
{
    /**
     * Begin a charge. Returns a redirect for hosted flows, or a terminal result for
     * gateways that settle synchronously.
     */
    public function charge(PaymentContext $context): PaymentResult;

    /**
     * Ask the gateway, server-to-server, what actually happened. This is the only
     * trustworthy source when the customer returns from a hosted page — the browser
     * return URL proves nothing.
     */
    public function verify(Payment $payment): PaymentResult;

    /**
     * Confirm the request genuinely came from the gateway. Webhooks arrive
     * unauthenticated, so this is the authentication step.
     */
    public function verifySignature(Request $request): bool;

    /** Normalise a webhook body, or null when the event is not one we act on. */
    public function parseWebhook(Request $request): ?PaymentEvent;

    public function refund(Payment $payment, ?int $amountMinor = null): PaymentResult;

    public function name(): string;
}

<?php

namespace App\Services\Payments;

use App\Contracts\PaymentInterface;
use App\Models\Payment;
use App\Payments\PaymentContext;
use App\Payments\PaymentEvent;
use App\Payments\PaymentResult;
use App\Services\ProviderDriver;
use Illuminate\Http\Request;

/**
 * Cash on delivery.
 *
 * A real driver rather than a special case in the controller: the order proceeds with no
 * online payment, so it stays unpaid until someone marks it collected. Modelling it as a
 * gateway means checkout has exactly one code path.
 */
class CodGateway extends ProviderDriver implements PaymentInterface
{
    public function charge(PaymentContext $context): PaymentResult
    {
        return PaymentResult::deferred(['method' => 'cod']);
    }

    public function verify(Payment $payment): PaymentResult
    {
        return PaymentResult::deferred();
    }

    public function verifySignature(Request $request): bool
    {
        return false; // COD has no webhooks
    }

    public function parseWebhook(Request $request): ?PaymentEvent
    {
        return null;
    }

    public function refund(Payment $payment, ?int $amountMinor = null): PaymentResult
    {
        return PaymentResult::failed('Cash on delivery refunds are handled outside the system.');
    }

    public function name(): string
    {
        return 'Cash on Delivery';
    }
}

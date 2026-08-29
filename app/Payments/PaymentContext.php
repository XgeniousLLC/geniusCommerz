<?php

namespace App\Payments;

use App\Models\Order;
use App\Support\Currencies;

/**
 * Everything a driver needs to start a charge.
 *
 * The amount is carried in integer minor units in the customer's presentment currency —
 * converting once here keeps every driver from re-deriving it and getting JPY or KWD wrong.
 */
class PaymentContext
{
    public function __construct(
        public readonly Order $order,
        public readonly int $amountMinor,
        public readonly string $currency,
        /** Our own reference, echoed back by the gateway so a webhook can be matched. */
        public readonly string $reference,
        public readonly string $returnUrl,
        public readonly string $cancelUrl,
        public readonly string $webhookUrl,
    ) {}

    public static function forOrder(Order $order, string $reference, string $returnUrl, string $cancelUrl, string $webhookUrl): self
    {
        $currency = $order->presentment_currency ?: $order->base_currency;

        return new self(
            order: $order,
            amountMinor: Currencies::toMinor($order->presentment_total, $currency),
            currency: $currency,
            reference: $reference,
            returnUrl: $returnUrl,
            cancelUrl: $cancelUrl,
            webhookUrl: $webhookUrl,
        );
    }

    /** Decimal amount, for gateways that want a formatted string rather than minor units. */
    public function amountDecimal(): string
    {
        return number_format(
            Currencies::fromMinor($this->amountMinor, $this->currency),
            Currencies::exponent($this->currency),
            '.',
            ''
        );
    }

    public function description(): string
    {
        return 'Order '.$this->order->order_number;
    }
}

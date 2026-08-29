<?php

namespace App\Shipping;

/** One rate offered by a carrier for a shipment. */
class ShippingQuote
{
    public function __construct(
        public readonly string $carrier,
        public readonly string $service,
        public readonly float $amount,
        public readonly string $currency,
        public readonly ?int $estimatedDays = null,
        /** Carrier-side id, needed to buy the label for this exact rate. */
        public readonly ?string $rateId = null,
        public readonly array $raw = [],
    ) {}

    public function label(): string
    {
        return trim($this->carrier.' '.$this->service);
    }
}

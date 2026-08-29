<?php

namespace App\Shipping;

/**
 * A parcel going from one address to another.
 *
 * Deliberately separate from CourierInterface, which is shaped around Pathao's
 * city → zone → area tree and only makes sense inside Bangladesh. Global carriers rate
 * on country, postal code, weight and dimensions instead.
 */
class ShipmentRequest
{
    /**
     * @param  array{name?: string, street1?: string, street2?: string, city?: string, state?: string, zip?: string, country?: string, phone?: string, email?: string}  $to
     * @param  array{name?: string, street1?: string, street2?: string, city?: string, state?: string, zip?: string, country?: string, phone?: string}  $from
     */
    public function __construct(
        public readonly array $to,
        public readonly array $from,
        /** kilograms */
        public readonly float $weight,
        /** centimetres; null when the merchant records no dimensions */
        public readonly ?float $length = null,
        public readonly ?float $width = null,
        public readonly ?float $height = null,
        public readonly ?float $declaredValue = null,
        public readonly ?string $currency = null,
        public readonly ?string $reference = null,
    ) {}

    public function weightInGrams(): int
    {
        return (int) round($this->weight * 1000);
    }

    public function weightInOunces(): float
    {
        return round($this->weight * 35.274, 2);
    }

    public function isInternational(): bool
    {
        return strtoupper((string) ($this->to['country'] ?? '')) !== strtoupper((string) ($this->from['country'] ?? ''));
    }
}

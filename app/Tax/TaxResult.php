<?php

namespace App\Tax;

class TaxResult
{
    /**
     * @param  list<array{name: string, rate: float, amount: float}>  $breakdown
     * @param  array<int, float>  $lineTax  tax per line, keyed by the line's index
     */
    public function __construct(
        public readonly float $total = 0.0,
        public readonly array $breakdown = [],
        public readonly array $lineTax = [],
        public readonly bool $inclusive = false,
        public readonly ?string $zoneName = null,
    ) {}

    public static function none(bool $inclusive = false): self
    {
        return new self(inclusive: $inclusive);
    }

    /**
     * How much tax adds to the order total.
     *
     * Zero when prices already include tax: the customer is not charged again, the tax
     * is simply the portion of what they already pay that is owed to the tax authority.
     */
    public function addedToTotal(): float
    {
        return $this->inclusive ? 0.0 : $this->total;
    }

    public function isZero(): bool
    {
        return $this->total <= 0.0;
    }
}

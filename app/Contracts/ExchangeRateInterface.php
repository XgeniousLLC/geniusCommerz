<?php

namespace App\Contracts;

interface ExchangeRateInterface
{
    /**
     * Rates expressed as target units per one unit of $base — the same direction as
     * currencies.rate, so a value can be stored without inverting it.
     *
     * @return array<string, float>
     */
    public function rates(string $base): array;

    public function name(): string;
}

<?php

namespace App\Tax;

use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\TaxRate;
use App\Models\TaxZone;
use App\Support\Currencies;

/**
 * Destination-based tax.
 *
 * Nothing computed tax before this: orders.tax was always zero while the printed invoice
 * recomputed a figure of its own, so the invoice and the amount charged could disagree.
 * This resolves a zone from the shipping address, applies the rates for each line's tax
 * class, and returns a breakdown that is stored on the order and read back by the invoice.
 */
class TaxCalculator
{
    public function enabled(): bool
    {
        return SiteSetting::bool('tax.enabled');
    }

    /** Whether catalogue prices are gross (tax already inside) rather than net. */
    public function pricesIncludeTax(): bool
    {
        return SiteSetting::bool('accounting.prices_include_tax');
    }

    /**
     * The most specific active zone covering an address.
     *
     * Specificity beats priority so a postal-level county rate wins over a country-wide
     * one; priority only breaks ties between equally specific zones.
     */
    public function zoneFor(array $address): ?TaxZone
    {
        $country = $address['country'] ?? null;

        if (! $country) {
            return null;
        }

        return TaxZone::matching($address)->load('rates')->first();
    }

    /**
     * @param  list<array{product_id?: int|string, total: float|int}>  $lines
     * @param  array{country?: string, state?: string, postal_code?: string}  $address
     * @param  float  $discount  order-level discount, spread across lines in proportion
     */
    public function calculate(array $lines, array $address, float $shipping = 0.0, float $discount = 0.0, ?string $currency = null): TaxResult
    {
        $inclusive = $this->pricesIncludeTax();

        if (! $this->enabled() || $lines === []) {
            return TaxResult::none($inclusive);
        }

        $zone = $this->zoneFor($address);

        if (! $zone || $zone->rates->isEmpty()) {
            return TaxResult::none($inclusive);
        }

        $currency  = $currency ?: SiteSetting::get('general.currency', 'BDT');
        $precision = Currencies::exponent($currency);
        $classes   = $this->taxClasses($lines);

        $lineTotal = array_sum(array_map(fn ($l) => (float) $l['total'], $lines));
        $totals    = [];   // rate id => accumulated tax
        $lineTax   = [];

        foreach ($lines as $index => $line) {
            // Spread the order-level discount so tax is charged on what is actually paid.
            $share   = $lineTotal > 0 ? ((float) $line['total'] / $lineTotal) : 0.0;
            $taxable = max(0.0, (float) $line['total'] - ($discount * $share));

            $rates = $this->ratesFor($zone, $classes[$line['product_id'] ?? null] ?? 'standard');
            $split = $this->apply($taxable, $rates, $inclusive);

            foreach ($split as $rateId => $amount) {
                $totals[$rateId] = ($totals[$rateId] ?? 0.0) + $amount;
            }

            $lineTax[$index] = round(array_sum($split), $precision);
        }

        if ($shipping > 0) {
            $shippingRates = $this->ratesFor($zone, 'standard')->filter->applies_to_shipping;

            foreach ($this->apply($shipping, $shippingRates, $inclusive) as $rateId => $amount) {
                $totals[$rateId] = ($totals[$rateId] ?? 0.0) + $amount;
            }
        }

        $breakdown = [];
        $total     = 0.0;

        foreach ($zone->rates->sortBy('priority') as $rate) {
            $amount = round($totals[$rate->id] ?? 0.0, $precision);

            if ($amount <= 0) {
                continue;
            }

            $breakdown[] = ['name' => $rate->name, 'rate' => (float) $rate->rate, 'amount' => $amount];
            $total += $amount;
        }

        return new TaxResult(
            total: round($total, $precision),
            breakdown: $breakdown,
            lineTax: $lineTax,
            inclusive: $inclusive,
            zoneName: $zone->name,
        );
    }

    /**
     * Split an amount across the applicable rates.
     *
     * Rates are additive rather than compound, which is what US state+county and Canadian
     * GST+PST both need. For gross prices the combined rate is extracted once and then
     * apportioned, so the parts always sum back to the whole.
     *
     * @return array<int, float> rate id => tax amount
     */
    private function apply(float $amount, $rates, bool $inclusive): array
    {
        $combined = 0.0;
        foreach ($rates as $rate) {
            $combined += $rate->fraction();
        }

        if ($combined <= 0 || $amount <= 0) {
            return [];
        }

        $totalTax = $inclusive
            ? $amount - ($amount / (1 + $combined))
            : $amount * $combined;

        $split = [];
        foreach ($rates as $rate) {
            $split[$rate->id] = $totalTax * ($rate->fraction() / $combined);
        }

        return $split;
    }

    private function ratesFor(TaxZone $zone, string $class)
    {
        $rates = $zone->rates->where('tax_class', $class);

        // A zone that says nothing about a class does not tax it — that is what makes
        // 'zero' zero, rather than silently falling back to the standard rate.
        return $rates;
    }

    /**
     * @param  list<array{product_id?: int|string}>  $lines
     * @return array<int|string, string>
     */
    private function taxClasses(array $lines): array
    {
        $ids = array_filter(array_column($lines, 'product_id'));

        return $ids
            ? Product::whereIn('id', $ids)->pluck('tax_class', 'id')->all()
            : [];
    }
}

<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\SiteSetting;
use App\Shipping\ShipmentRequest;

/**
 * Shipping cost for a cart.
 *
 * Exists because the quote shown at checkout and the amount actually charged were
 * computed in two different places and disagreed: the storefront displayed a courier
 * zone rate while the order was written with the flat rate. Both paths now come through
 * here, with weight derived server-side so a client cannot understate it.
 */
class ShippingCalculator
{
    /** Assumed per-unit weight (kg) when a product has none recorded. */
    private const DEFAULT_UNIT_WEIGHT = 0.5;

    public function __construct(
        private readonly CourierService $courier,
        private readonly CarrierService $carrier,
    ) {}

    /**
     * Total cart weight in kg, from real product and variant weights.
     *
     * @param  array<int, array{product_id?: int|string, variant_id?: int|string|null, quantity?: int|string}>  $items
     */
    public function weight(array $items): float
    {
        $productIds = array_filter(array_column($items, 'product_id'));
        $variantIds = array_filter(array_column($items, 'variant_id'));

        $products = $productIds ? Product::whereIn('id', $productIds)->pluck('weight', 'id') : collect();
        $variants = $variantIds ? ProductVariant::whereIn('id', $variantIds)->pluck('weight', 'id') : collect();

        $total = 0.0;

        foreach ($items as $item) {
            $quantity = max(1, (int) ($item['quantity'] ?? 1));

            $unit = ($item['variant_id'] ?? null) ? (float) ($variants[$item['variant_id']] ?? 0) : 0.0;

            if ($unit <= 0) {
                $unit = (float) ($products[$item['product_id'] ?? null] ?? 0);
            }

            $total += ($unit > 0 ? $unit : self::DEFAULT_UNIT_WEIGHT) * $quantity;
        }

        return round(max($total, self::DEFAULT_UNIT_WEIGHT), 3);
    }

    /**
     * Courier zone-based charge, or null when zone pricing does not apply — the courier
     * is unconfigured, the feature is off, or no destination zone was chosen.
     */
    public function courierCharge(array $location, array $items): ?float
    {
        if (! $this->courierZonePricingEnabled()) {
            return null;
        }

        $cityId = $location['city_id'] ?? null;
        $zoneId = $location['zone_id'] ?? null;

        if (! $cityId || ! $zoneId) {
            return null;
        }

        try {
            $charge = $this->courier->driver()->calculateCharge([
                'city_id'     => (int) $cityId,
                'zone_id'     => (int) $zoneId,
                'area_id'     => $location['area_id'] ?? null,
                'item_weight' => $this->weight($items),
            ]);
        } catch (\Throwable) {
            return null;
        }

        return $charge === null ? null : (float) $charge;
    }

    /**
     * The best matching zone rate for a destination, or null when no zone covers it.
     *
     * The most specific zone wins (postal over state over country); within it, the
     * lowest-priority applicable rate is used so the merchant controls which of several
     * bands applies rather than it depending on row order.
     */
    public function zoneRate(array $address, array $items, float $subtotal): ?ShippingRate
    {
        $weight = $this->weight($items);

        foreach (ShippingZone::matching($address) as $zone) {
            $rate = $zone->rates()
                ->where('is_active', true)
                ->orderBy('priority')
                ->orderBy('price')
                ->get()
                ->first(fn (ShippingRate $r) => $r->covers($weight, $subtotal));

            if ($rate) {
                return $rate;
            }
        }

        return null;
    }

    /**
     * What to charge for shipping, and what to call it on the order.
     *
     * Precedence: a live courier zone quote (Bangladesh) beats a configured shipping
     * zone, which beats the global flat rate. Products flagged shipping_included always
     * ship free.
     *
     * @return array{cost: float, method: string|null}
     */
    public function quote(array $address, array $items, float $subtotal, array $courierLocation = []): array
    {
        $productIds = array_filter(array_column($items, 'product_id'));

        if ($productIds && Product::whereIn('id', $productIds)->where('shipping_included', false)->doesntExist()) {
            return ['cost' => 0.0, 'method' => 'Included'];
        }

        $courierCharge = $this->courierCharge($courierLocation, $items);

        if ($courierCharge !== null) {
            return ['cost' => $courierCharge, 'method' => 'Courier'];
        }

        // A live carrier quote, when one is configured and can rate this destination.
        // Rating failures fall through rather than blocking checkout.
        if ($quote = $this->carrierQuote($address, $items)) {
            return ['cost' => round($quote->amount, 2), 'method' => $quote->label()];
        }

        if ($rate = $this->zoneRate($address, $items, $subtotal)) {
            return [
                'cost'   => $rate->costFor($this->weight($items), $subtotal),
                'method' => $rate->zone->name.' — '.$rate->name,
            ];
        }

        return ['cost' => $this->flatRate($subtotal, $productIds), 'method' => null];
    }

    private function carrierQuote(array $address, array $items): ?\App\Shipping\ShippingQuote
    {
        if (! $this->carrier->hasDefault() || ! CarrierService::hasOrigin()) {
            return null;
        }

        $dimensions = $this->dimensions($items);

        return $this->carrier->cheapestRate(new ShipmentRequest(
            to: [
                'street1' => $address['address'] ?? null,
                'street2' => $address['address_line_2'] ?? null,
                'city'    => $address['city'] ?? null,
                'state'   => $address['state'] ?? null,
                'zip'     => $address['postal_code'] ?? null,
                'country' => $address['country'] ?? null,
            ],
            from: CarrierService::originAddress(),
            weight: $this->weight($items),
            length: $dimensions['length'],
            width: $dimensions['width'],
            height: $dimensions['height'],
        ));
    }

    /**
     * Largest recorded dimensions across the cart — a crude single-parcel approximation,
     * but better than sending none, since carriers price bulky-but-light parcels on volume.
     *
     * @return array{length: float|null, width: float|null, height: float|null}
     */
    private function dimensions(array $items): array
    {
        $ids = array_filter(array_column($items, 'product_id'));

        if (! $ids) {
            return ['length' => null, 'width' => null, 'height' => null];
        }

        $products = Product::whereIn('id', $ids)->get(['length', 'width', 'height']);

        return [
            'length' => ($v = (float) $products->max('length')) > 0 ? $v : null,
            'width'  => ($v = (float) $products->max('width')) > 0 ? $v : null,
            'height' => ($v = (float) $products->max('height')) > 0 ? $v : null,
        ];
    }

    public function courierZonePricingEnabled(): bool
    {
        return $this->courier->hasDefault()
            && (bool) SiteSetting::get('shipping.courier_location_charges', false);
    }

    /**
     * The flat/threshold rate, used when zone pricing does not apply.
     *
     * @param  list<int>  $productIds
     */
    public function flatRate(float $subtotal, array $productIds): float
    {
        $allIncluded = Product::whereIn('id', $productIds)->where('shipping_included', false)->doesntExist();

        if ($allIncluded) {
            return 0.0;
        }

        $freeAbove = (float) SiteSetting::get('shipping.free_above', 0);

        if ($freeAbove > 0 && $subtotal >= $freeAbove) {
            return 0.0;
        }

        return (float) SiteSetting::get('shipping.flat_rate', 60);
    }
}

<?php

namespace App\Contracts;

use App\Shipping\ShipmentRequest;
use App\Shipping\ShippingQuote;

/**
 * A carrier that rates by destination, weight and dimensions.
 *
 * Kept separate from CourierInterface rather than widening it: that interface's
 * getCities/getZones/getAreas(int $cityId) is Pathao's Bangladeshi location tree, and
 * forcing global carriers through it would distort both.
 */
interface ShippingRateInterface
{
    /** @return list<ShippingQuote> available services, cheapest first */
    public function rates(ShipmentRequest $shipment): array;

    /**
     * Purchase a label for a previously returned rate.
     *
     * @return array{tracking_code: string, label_url: ?string, carrier: ?string, raw: array}
     */
    public function buyLabel(string $rateId): array;

    /** @return array{status: string, raw: array} */
    public function track(string $trackingCode): array;

    public function name(): string;
}

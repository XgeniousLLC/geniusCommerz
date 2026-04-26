<?php

namespace App\Contracts;

use App\Models\Order;

interface CourierInterface
{
    /**
     * Create a shipment for the given order.
     * Returns ['consignment_id' => '...', 'tracking_code' => '...', 'raw' => [...]]
     * or throws \RuntimeException on failure.
     */
    public function createOrder(Order $order, array $extra = []): array;

    /**
     * Fetch current delivery status for a consignment.
     * Returns ['status' => '...', 'raw' => [...]]
     */
    public function getStatus(string $consignmentId): array;

    /**
     * Return list of cities/districts as [['id' => ..., 'name' => ...], ...]
     */
    public function getCities(): array;

    /**
     * Return zones for a city as [['id' => ..., 'name' => ...], ...]
     */
    public function getZones(int $cityId): array;

    /**
     * Return areas for a zone as [['id' => ..., 'name' => ...], ...]
     * May return [] if the courier doesn't support area-level granularity.
     */
    public function getAreas(int $zoneId): array;

    /**
     * Calculate delivery charge.
     * $params depends on courier (e.g. ['city_id' => 1, 'zone_id' => 2, 'item_weight' => 0.5])
     * Returns float charge or null if not supported.
     */
    public function calculateCharge(array $params): ?float;

    /**
     * Human-readable provider name.
     */
    public function name(): string;
}

<?php

namespace App\Services;

use App\Contracts\ShippingRateInterface;
use App\Models\SiteSetting;
use App\Shipping\ShipmentRequest;

class CarrierService extends ProviderManager
{
    protected function group(): string
    {
        return 'carrier';
    }

    protected function contract(): string
    {
        return ShippingRateInterface::class;
    }

    protected function missingDefaultMessage(): string
    {
        return 'No active default carrier is configured. Go to Admin → Integrations to set one.';
    }

    public function driver(?string $provider = null): ShippingRateInterface
    {
        return parent::driver($provider);
    }

    /**
     * Cheapest live rate for a shipment, or null when no carrier is configured or the
     * carrier cannot rate it. Rating failures must not block checkout, so they fall
     * through to the configured zone rates instead of throwing.
     */
    public function cheapestRate(ShipmentRequest $shipment): ?\App\Shipping\ShippingQuote
    {
        if (! $this->hasDefault()) {
            return null;
        }

        try {
            return $this->driver()->rates($shipment)[0] ?? null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Carrier rating failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * The address parcels ship from. Carriers cannot rate without it.
     *
     * @return array<string, string|null>
     */
    public static function originAddress(): array
    {
        return [
            'name'    => SiteSetting::get('shipping.origin_name') ?: SiteSetting::get('general.site_name'),
            'street1' => SiteSetting::get('shipping.origin_street'),
            'city'    => SiteSetting::get('shipping.origin_city'),
            'state'   => SiteSetting::get('shipping.origin_state'),
            'zip'     => SiteSetting::get('shipping.origin_postal'),
            'country' => SiteSetting::get('shipping.origin_country')
                ?: SiteSetting::get('general.store_country', 'BD'),
            'phone'   => SiteSetting::get('general.phone'),
        ];
    }

    public static function hasOrigin(): bool
    {
        $origin = self::originAddress();

        return ! empty($origin['country']) && ! empty($origin['city']);
    }
}

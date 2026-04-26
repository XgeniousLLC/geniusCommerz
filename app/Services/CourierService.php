<?php

namespace App\Services;

use App\Contracts\CourierInterface;
use App\Models\Integration;

class CourierService
{
    public function driver(?string $provider = null): CourierInterface
    {
        if ($provider === null) {
            $integration = Integration::defaultCourier();
            $provider    = $integration?->provider;
        }

        return match ($provider) {
            'pathao'    => app(Couriers\PathaoService::class),
            'redx'      => app(Couriers\RedxService::class),
            'steadfast' => app(Couriers\SteadfastService::class),
            default     => throw new \RuntimeException("No active default courier is configured. Go to Admin → Integrations to set one."),
        };
    }

    public function hasDefault(): bool
    {
        return Integration::defaultCourier() !== null;
    }
}

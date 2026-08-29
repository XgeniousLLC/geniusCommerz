<?php

namespace App\Services;

use App\Contracts\FraudInterface;

/**
 * Resolves the merchant's chosen fraud checker.
 *
 * FraudController used to hardcode "BDCourier wins if configured", which meant
 * Integration::defaultFraud() existed but was never consulted — picking a different
 * default in the admin had no effect.
 */
class FraudService extends ProviderManager
{
    protected function group(): string
    {
        return 'fraud';
    }

    protected function contract(): string
    {
        return FraudInterface::class;
    }

    protected function missingDefaultMessage(): string
    {
        return 'No active default fraud checker is configured. Go to Admin → Integrations to set one.';
    }

    public function driver(?string $provider = null): FraudInterface
    {
        return parent::driver($provider);
    }

    /**
     * The default checker, or null when none is configured — callers decide what to do.
     *
     * Pass a destination country to also skip a checker that does not serve it: the
     * Bangladeshi courier-history services are meaningless for a US order, and sending
     * that customer's phone to them would be both useless and a privacy problem.
     */
    public function active(?string $country = null): ?FraudInterface
    {
        try {
            $driver = $this->driver();
        } catch (\RuntimeException) {
            return null;
        }

        if (! $driver->isConfigured()) {
            return null;
        }

        if ($country !== null && ! $this->servesCountry($country)) {
            return null;
        }

        return $driver;
    }

    /** Whether the default checker covers a destination country. */
    public function servesCountry(string $country): bool
    {
        $definition = app(\App\Integrations\ProviderRegistry::class)
            ->find(\App\Models\Integration::defaultFor('fraud')?->provider);

        return $definition === null || $definition->supportsCountry($country);
    }
}

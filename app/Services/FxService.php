<?php

namespace App\Services;

use App\Contracts\ExchangeRateInterface;
use App\Integrations\ProviderRegistry;

class FxService extends ProviderManager
{
    protected function group(): string
    {
        return 'fx';
    }

    protected function contract(): string
    {
        return ExchangeRateInterface::class;
    }

    protected function missingDefaultMessage(): string
    {
        return 'No active default exchange-rate source is configured. Go to Admin → Integrations to set one.';
    }

    public function driver(?string $provider = null): ExchangeRateInterface
    {
        return parent::driver($provider);
    }

    /**
     * The rate source to use.
     *
     * Falls back to the keyless free provider so scheduled refresh works without the
     * merchant having to configure anything first.
     */
    public function source(): ExchangeRateInterface
    {
        if ($this->hasDefault()) {
            return $this->driver();
        }

        return app(ProviderRegistry::class)->driver('open_er_api');
    }
}

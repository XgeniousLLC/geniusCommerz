<?php

namespace App\Services;

use App\Contracts\CourierInterface;

class CourierService extends ProviderManager
{
    protected function group(): string
    {
        return 'courier';
    }

    protected function contract(): string
    {
        return CourierInterface::class;
    }

    protected function missingDefaultMessage(): string
    {
        return 'No active default courier is configured. Go to Admin → Integrations to set one.';
    }

    public function driver(?string $provider = null): CourierInterface
    {
        return parent::driver($provider);
    }
}

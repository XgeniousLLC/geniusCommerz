<?php

namespace App\Services;

use App\Contracts\AiInterface;

class AiService extends ProviderManager
{
    protected function group(): string
    {
        return 'ai';
    }

    protected function contract(): string
    {
        return AiInterface::class;
    }

    protected function missingDefaultMessage(): string
    {
        return 'No active default AI provider configured. Go to Admin → Integrations to set one.';
    }

    public function driver(?string $provider = null): AiInterface
    {
        return parent::driver($provider);
    }

    public function complete(string $prompt, array $options = []): string
    {
        return $this->driver()->complete($prompt, $options);
    }
}

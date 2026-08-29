<?php

namespace App\Services;

use App\Integrations\ProviderRegistry;
use App\Models\Integration;

/**
 * Shared resolution for the provider-backed services (courier, SMS, AI, …).
 *
 * Replaces the per-service match statements: resolution is now a registry lookup, so it
 * costs the same at five providers as at fifty. Subclasses keep their existing public
 * API and narrow the return type.
 */
abstract class ProviderManager
{
    /** Provider group this service resolves, e.g. 'courier'. */
    abstract protected function group(): string;

    /** Interface a resolved driver must implement. */
    abstract protected function contract(): string;

    /** Message shown when the merchant has not configured a default yet. */
    abstract protected function missingDefaultMessage(): string;

    public function driver(?string $provider = null): object
    {
        $provider ??= Integration::defaultFor($this->group())?->provider;

        $registry   = app(ProviderRegistry::class);
        $definition = $registry->find($provider);

        if (! $definition || $definition->group !== $this->group() || ! $definition->isImplemented()) {
            throw new \RuntimeException($this->missingDefaultMessage());
        }

        $driver = $registry->driver($definition->slug);

        if (! $driver instanceof ($this->contract())) {
            throw new \RuntimeException("{$definition->label} does not implement {$this->contract()}.");
        }

        return $driver;
    }

    public function hasDefault(): bool
    {
        return Integration::defaultFor($this->group()) !== null;
    }
}

<?php

namespace App\Services;

use App\Integrations\ProviderRegistry;
use App\Models\Integration;

/**
 * Base for provider drivers.
 *
 * The Integration row is injected rather than looked up inside the constructor, so a
 * driver can be unit-tested without seeding a row. The null fallback keeps plain
 * container resolution (app(SomeDriver::class)) working exactly as before.
 */
abstract class ProviderDriver
{
    protected Integration $integration;

    public function __construct(?Integration $integration = null)
    {
        $this->integration = $integration ?? app(ProviderRegistry::class)->rowFor(static::class);
    }

    protected function cred(string $key, mixed $default = null): mixed
    {
        return $this->integration->getCredential($key, $default);
    }

    protected function isLive(): bool
    {
        return $this->integration->environment === 'live';
    }
}

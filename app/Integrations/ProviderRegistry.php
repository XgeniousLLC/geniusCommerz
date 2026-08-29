<?php

namespace App\Integrations;

use App\Models\Integration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * Discovers provider definitions and resolves their drivers.
 *
 * Adding a provider means adding one definition class (and a driver, when it is actually
 * implemented) — the admin cards, credential form, seeding and resolution all derive from
 * here rather than from constants and hardcoded arrays scattered across models and blades.
 */
class ProviderRegistry
{
    private const CACHE_KEY = 'integrations.manifest';

    /** @var array<string, ProviderDefinition>|null */
    private ?array $definitions = null;

    /** @var array<string, ProviderDefinition> */
    public function all(): array
    {
        return $this->definitions ??= $this->discover();
    }

    public function find(?string $slug): ?ProviderDefinition
    {
        return $slug === null ? null : ($this->all()[$slug] ?? null);
    }

    public function has(string $slug): bool
    {
        return isset($this->all()[$slug]);
    }

    /**
     * @return array<string, ProviderDefinition> definitions in one group, ordered by
     *                                           sort then label
     */
    public function group(string $group): array
    {
        $matches = array_filter($this->all(), fn (ProviderDefinition $d) => $d->group === $group);

        uasort($matches, fn ($a, $b) => [$a->sort, $a->label] <=> [$b->sort, $b->label]);

        return $matches;
    }

    /** @return string[] slugs in a group — the replacement for the old class constants. */
    public function slugs(string $group): array
    {
        return array_keys($this->group($group));
    }

    /** Which group a provider belongs to, or null if it is not a known provider. */
    public function groupOf(?string $slug): ?string
    {
        return $this->find($slug)?->group;
    }

    /** Reverse lookup so a driver can find its own slug without duplicating it. */
    public function slugForDriver(string $driverClass): ?string
    {
        foreach ($this->all() as $slug => $definition) {
            if ($definition->driver === $driverClass) {
                return $slug;
            }
        }

        return null;
    }

    /**
     * The Integration row backing a driver class, or an unsaved stub when the merchant
     * has not configured it. The stub keeps drivers constructible (and therefore
     * container-resolvable) before any row exists.
     */
    public function rowFor(string $driverClass): Integration
    {
        return $this->rowOrStub($this->slugForDriver($driverClass) ?? '');
    }

    /**
     * The saved row, or an unsaved stub carrying the definition's own environment
     * default (fraud providers, for instance, are live-only).
     */
    public function rowOrStub(string $slug): Integration
    {
        if ($row = Integration::forProvider($slug)) {
            return $row;
        }

        $definition = $this->find($slug);

        return new Integration([
            'provider'    => $slug,
            'group'       => $definition?->group ?? 'other',
            'credentials' => [],
            'environment' => $definition && ! $definition->supportsEnvironments()
                ? ($definition->environments[0] ?? 'live')
                : 'sandbox',
        ]);
    }

    /**
     * Resolve a provider's driver. Replaces the per-service match statements — this is a
     * hash lookup, so it costs the same at 5 providers as at 50.
     */
    public function driver(string $slug, ?Integration $row = null): object
    {
        $definition = $this->find($slug)
            ?? throw new \RuntimeException("Unknown provider [{$slug}].");

        if (! $definition->isImplemented()) {
            throw new \RuntimeException("{$definition->label} is not available yet.");
        }

        $row ??= $this->rowOrStub($slug);

        return app()->makeWith($definition->driver, ['integration' => $row]);
    }

    /**
     * Payment providers that can actually take this order — implemented, enabled by the
     * merchant, and able to charge the currency and serve the country.
     *
     * @return array<string, ProviderDefinition>
     */
    public function forCheckout(string $currency, ?string $country = null): array
    {
        $enabled = Integration::where('group', 'payment')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('provider');

        $available = [];

        foreach ($enabled as $slug => $row) {
            $definition = $this->find($slug);

            if (! $definition || ! $definition->isImplemented()) {
                continue;
            }

            // A row may narrow the definition, never widen it.
            $currencies = $row->currencies ?: $definition->currencies;
            $countries  = $row->countries ?: $definition->countries;

            $currencyOk = $currencies === ['*'] || in_array(strtoupper($currency), $currencies, true);
            $countryOk  = $country === null
                || $countries === ['*']
                || in_array(strtoupper($country), $countries, true);

            if ($currencyOk && $countryOk) {
                $available[$slug] = $definition;
            }
        }

        return $available;
    }

    public function flush(): void
    {
        $this->definitions = null;
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, ProviderDefinition>
     */
    private function discover(): array
    {
        $classes = app()->isProduction()
            ? Cache::rememberForever(self::CACHE_KEY, fn () => $this->definitionClasses())
            : $this->definitionClasses();

        $definitions = [];

        foreach ($classes as $class) {
            $definition = $class::definition();
            $definitions[$definition->slug] = $definition;
        }

        return $definitions;
    }

    /** @return list<class-string<ProvidesDefinition>> */
    private function definitionClasses(): array
    {
        $base = app_path('Integrations/Definitions');

        if (! File::isDirectory($base)) {
            return [];
        }

        $classes = [];

        foreach (File::allFiles($base) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relative = str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
            $class    = 'App\\Integrations\\Definitions\\'.$relative;

            if (class_exists($class) && is_subclass_of($class, ProvidesDefinition::class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }
}

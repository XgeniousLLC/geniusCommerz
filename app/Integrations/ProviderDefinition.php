<?php

namespace App\Integrations;

/**
 * The catalog entry for one provider — the single source of truth that drives the admin
 * cards, the credential form, driver resolution and checkout availability.
 *
 * `driver` is nullable on purpose: a definition with no driver is a catalog entry for a
 * provider that has not been built yet, which is how a large gateway catalog can ship
 * ahead of its implementations.
 */
class ProviderDefinition
{
    public const GROUPS = ['payment', 'courier', 'carrier', 'sms', 'ai', 'fraud', 'fx'];

    public function __construct(
        public readonly string $slug,
        public readonly string $group,
        public readonly string $label,
        /** @var class-string|null */
        public readonly ?string $driver = null,
        /** @var CredentialField[] */
        public readonly array $fields = [],
        /** @var string[] */
        public readonly array $environments = ['sandbox', 'live'],
        /** @var Capability[] */
        public readonly array $capabilities = [],
        /**
         * ISO 4217 codes this provider can actually transact in; ['*'] for no restriction.
         * Load-bearing: it stops a customer being offered a gateway that will hard-fail
         * at the API because it cannot charge their currency.
         *
         * @var string[]
         */
        public readonly array $currencies = ['*'],
        /**
         * ISO 3166-1 alpha-2 customer countries this should be offered to; ['*'] for all.
         * Filters the storefront only — never the admin list.
         *
         * @var string[]
         */
        public readonly array $countries = ['*'],
        /** stable|beta|planned */
        public readonly string $status = 'stable',
        public readonly ?string $docsUrl = null,
        public readonly ?string $hint = null,
        public readonly int $sort = 0,
    ) {}

    /** A definition with no driver class is catalog-only and cannot be resolved. */
    public function isImplemented(): bool
    {
        return $this->driver !== null;
    }

    public function supportsEnvironments(): bool
    {
        return count($this->environments) > 1;
    }

    public function has(Capability $capability): bool
    {
        return in_array($capability, $this->capabilities, true);
    }

    public function supportsCurrency(string $code): bool
    {
        return $this->currencies === ['*'] || in_array(strtoupper($code), $this->currencies, true);
    }

    public function supportsCountry(string $code): bool
    {
        return $this->countries === ['*'] || in_array(strtoupper($code), $this->countries, true);
    }

    /** Convention over configuration — no per-provider logo path to maintain. */
    public function logoPath(): string
    {
        return "images/providers/{$this->slug}.svg";
    }

    /** @return CredentialField[] keyed by field key */
    public function fieldsByKey(): array
    {
        $keyed = [];
        foreach ($this->fields as $field) {
            $keyed[$field->key] = $field;
        }

        return $keyed;
    }
}

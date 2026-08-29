<?php

namespace App\Models;

use App\Integrations\ProviderDefinition;
use App\Integrations\ProviderRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * A merchant's saved configuration for one provider.
 *
 * The catalog of providers lives in App\Integrations\Definitions — this model holds only
 * the configured state, and rows are created lazily when credentials are first saved.
 */
class Integration extends Model
{
    /** Credentials that are not environment-specific live under this key. */
    private const SHARED = 'shared';

    protected $fillable = [
        'provider',
        'group',
        'label',
        'credentials',
        'is_active',
        'is_default',
        'sort_order',
        'environment',
        'countries',
        'currencies',
        'notes',
        'settings',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
        'countries'  => 'array',
        'currencies' => 'array',
        'settings'   => 'array',
    ];

    protected $hidden = ['credentials'];

    public function getCredentialsAttribute(): array
    {
        $raw = $this->attributes['credentials'] ?? null;
        if ($raw === null) {
            return [];
        }
        try {
            return json_decode(Crypt::decryptString($raw), true) ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    public function setCredentialsAttribute(mixed $value): void
    {
        $this->attributes['credentials'] = $value !== null
            ? Crypt::encryptString(json_encode((array) $value))
            : null;
    }

    /**
     * Read a credential for the row's current environment.
     *
     * Credentials are stored as {shared: {...}, sandbox: {...}, live: {...}} so a
     * provider's test and live keys coexist — switching to sandbox to debug must not
     * destroy the live keys. The final fallback reads the flat pre-environment shape,
     * so rows saved before this change keep working with no data migration.
     */
    public function getCredential(string $key, mixed $default = null): mixed
    {
        $credentials = $this->credentials;
        $environment = $this->environment ?: 'sandbox';

        return $credentials[$environment][$key]
            ?? $credentials[self::SHARED][$key]
            ?? $credentials[$key]
            ?? $default;
    }

    /**
     * Merge new values in without clobbering the other environment, and without a blank
     * field wiping a stored secret.
     *
     * @param  array<string, mixed>  $values  keyed by credential key
     * @param  array<string, bool>   $scoped  key => whether it is environment-specific
     */
    public function mergeCredentials(array $values, array $scoped, string $environment): void
    {
        $credentials = $this->credentials;

        foreach ($values as $key => $value) {
            if ($value === null || $value === '') {
                continue; // blank means "leave what is stored alone"
            }

            if ($scoped[$key] ?? false) {
                $credentials[$environment][$key] = $value;
            } else {
                $credentials[self::SHARED][$key] = $value;
            }

            // Drop any legacy flat copy so there is only one home for the value.
            unset($credentials[$key]);
        }

        $this->credentials = $credentials;
    }

    /** How many credentials are actually stored, across every scope. */
    public function credentialCount(): int
    {
        $count = 0;

        foreach ($this->credentials as $key => $value) {
            $count += is_array($value) ? count($value) : 1;
        }

        return $count;
    }

    public function definition(): ?ProviderDefinition
    {
        return app(ProviderRegistry::class)->find($this->provider);
    }

    public static function forProvider(string $provider): ?self
    {
        return static::where('provider', $provider)->first();
    }

    public static function activeFor(string $provider): ?self
    {
        return static::where('provider', $provider)->where('is_active', true)->first();
    }

    /**
     * Find or build the row for a provider. Providers are no longer seeded, so the row
     * only exists once the merchant has saved something.
     */
    public static function forSlug(string $provider): self
    {
        $definition = app(ProviderRegistry::class)->find($provider);

        return static::firstOrNew(
            ['provider' => $provider],
            [
                'group'       => $definition?->group ?? 'other',
                'label'       => $definition?->label ?? $provider,
                'credentials' => [],
                'is_active'   => false,
                'environment' => $definition && ! $definition->supportsEnvironments()
                    ? ($definition->environments[0] ?? 'live')
                    : 'sandbox',
            ]
        );
    }

    /** The active default for a provider group, e.g. 'courier'. */
    public static function defaultFor(string $group): ?self
    {
        return static::where('group', $group)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    public static function defaultCourier(): ?self
    {
        return static::defaultFor('courier');
    }

    public static function defaultSms(): ?self
    {
        return static::defaultFor('sms');
    }

    public static function defaultAi(): ?self
    {
        return static::defaultFor('ai');
    }

    public static function defaultFraud(): ?self
    {
        return static::defaultFor('fraud');
    }

    /**
     * Make this the default for its group, clearing the others first.
     *
     * Payments are excluded: several gateways are active simultaneously and are ordered
     * by sort_order instead, so "default" is meaningless there.
     */
    public function setAsDefault(): void
    {
        if (! $this->group || ! $this->supportsDefault()) {
            return;
        }

        static::where('group', $this->group)->update(['is_default' => false]);
        $this->update(['is_default' => true, 'is_active' => true]);
    }

    public function supportsDefault(): bool
    {
        return in_array($this->group, ['courier', 'sms', 'ai', 'fraud', 'fx'], true);
    }
}

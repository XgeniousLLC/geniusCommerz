<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Integration extends Model
{
    public const COURIER_PROVIDERS = ['pathao', 'redx', 'steadfast'];
    public const SMS_PROVIDERS     = ['bulksmsbd', 'smsbd', 'mram', 'twilio'];
    public const AI_PROVIDERS      = ['openai', 'gemini', 'claude', 'deepseek'];
    public const FRAUD_PROVIDERS   = ['fraudbd', 'bdcourier'];

    protected $fillable = [
        'provider',
        'label',
        'credentials',
        'is_active',
        'is_default',
        'environment',
        'notes',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
    ];

    public function getCredentialsAttribute(): array
    {
        $raw = $this->attributes['credentials'] ?? null;
        if ($raw === null) return [];
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

    protected $hidden = ['credentials'];

    public function getCredential(string $key, mixed $default = null): mixed
    {
        return $this->credentials[$key] ?? $default;
    }

    public static function forProvider(string $provider): ?self
    {
        return static::where('provider', $provider)->first();
    }

    public static function activeFor(string $provider): ?self
    {
        return static::where('provider', $provider)->where('is_active', true)->first();
    }

    public static function defaultCourier(): ?self
    {
        return static::whereIn('provider', self::COURIER_PROVIDERS)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    public static function defaultSms(): ?self
    {
        return static::whereIn('provider', self::SMS_PROVIDERS)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    public static function defaultAi(): ?self
    {
        return static::whereIn('provider', self::AI_PROVIDERS)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    public static function defaultFraud(): ?self
    {
        return static::whereIn('provider', self::FRAUD_PROVIDERS)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Set this integration as the default for its group (courier or sms),
     * unsetting all others in the same group first.
     */
    public function setAsDefault(): void
    {
        $group = $this->getGroup();
        if (! $group) return;

        static::whereIn('provider', $group)->update(['is_default' => false]);
        $this->update(['is_default' => true, 'is_active' => true]);
    }

    private function getGroup(): ?array
    {
        if (in_array($this->provider, self::COURIER_PROVIDERS)) {
            return self::COURIER_PROVIDERS;
        }
        if (in_array($this->provider, self::SMS_PROVIDERS)) {
            return self::SMS_PROVIDERS;
        }
        if (in_array($this->provider, self::AI_PROVIDERS)) {
            return self::AI_PROVIDERS;
        }
        if (in_array($this->provider, self::FRAUD_PROVIDERS)) {
            return self::FRAUD_PROVIDERS;
        }
        return null;
    }
}

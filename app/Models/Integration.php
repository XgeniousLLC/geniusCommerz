<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    public const COURIER_PROVIDERS = ['pathao', 'redx', 'steadfast'];
    public const SMS_PROVIDERS     = ['bulksmsbd', 'smsbd', 'twilio'];

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
        'credentials' => 'encrypted:array',
        'is_active'   => 'boolean',
        'is_default'  => 'boolean',
    ];

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
        return null;
    }
}

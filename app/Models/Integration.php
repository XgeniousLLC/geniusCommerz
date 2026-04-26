<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    protected $fillable = [
        'provider',
        'label',
        'credentials',
        'is_active',
        'environment',
        'notes',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'is_active' => 'boolean',
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
}

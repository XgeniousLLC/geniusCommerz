<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltySetting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    public function getValueAttribute($value): mixed
    {
        return match ($this->type) {
            'json'    => json_decode($value, true),
            'boolean' => (bool) $value,
            'decimal' => (float) $value,
            'number'  => (int) $value,
            default   => $value,
        };
    }

    public function setValueAttribute(mixed $value): void
    {
        $type = $this->attributes['type'] ?? $this->type ?? 'text';
        $this->attributes['value'] = match ($type) {
            'json'    => is_array($value) ? json_encode($value) : (string) $value,
            'boolean' => $value ? '1' : '0',
            'decimal' => (string) $value,
            'number'  => (string) $value,
            default   => is_array($value) ? json_encode($value) : (string) $value,
        };
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, mixed $value, string $type = 'text'): static
    {
        $raw = match ($type) {
            'json'    => json_encode($value),
            'boolean' => $value ? '1' : '0',
            'decimal' => (string) $value,
            'number'  => (string) $value,
            default   => (string) $value,
        };

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $raw, 'type' => $type]
        );
    }
}

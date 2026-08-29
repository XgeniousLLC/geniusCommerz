<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    public function getValueAttribute($value)
    {
        return match ($this->type) {
            'json' => json_decode($value, true),
            'boolean' => (bool) $value,
            'number' => (int) $value,
            default => $value,
        };
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = match ($this->type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            'number' => (string) $value,
            default => (string) $value,
        };
    }

    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Read a setting as a boolean.
     *
     * Needed because the same flag can come back as a real bool or as the string '1'
     * depending on the row's `type` — the settings form writes every field as 'text',
     * while code that creates rows directly tends to use 'boolean'. Comparing with
     * `=== '1'` silently fails for one of those shapes.
     */
    public static function bool($key, bool $default = false): bool
    {
        $value = static::get($key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function set($key, $value, $type = 'text', $group = 'general', $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
            ]
        );
    }

    public function scopeGroup($query, $group)
    {
        return $query->where('group', $group);
    }
}

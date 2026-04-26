<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    protected $fillable = ['name', 'code', 'is_default', 'is_active'];

    protected $casts = ['is_default' => 'boolean', 'is_active' => 'boolean'];

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

    public static function default(): ?self
    {
        return static::where('is_default', true)->where('is_active', true)->first();
    }

    public function setAsDefault(): void
    {
        static::query()->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }

    public function translationsMap(): array
    {
        return $this->translations()->pluck('value', 'key')->all();
    }
}

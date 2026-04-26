<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['code', 'symbol', 'name', 'rate', 'is_default', 'is_active'];

    protected $casts = [
        'rate'       => 'float',
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public static function default(): ?self
    {
        return static::where('is_default', true)->where('is_active', true)->first();
    }

    public function setAsDefault(): void
    {
        static::query()->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }
}

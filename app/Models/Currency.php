<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code', 'symbol', 'name', 'rate', 'is_default', 'is_active',
        'exponent', 'rate_source', 'rate_updated_at', 'rate_locked', 'rate_markup_percent',
    ];

    protected $casts = [
        'rate'            => 'float',
        'is_default'      => 'boolean',
        'is_active'       => 'boolean',
        'rate_updated_at' => 'datetime',
        'rate_locked'     => 'boolean',
        'exponent'        => 'integer',
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

    /** Rates older than a day are worth flagging rather than silently trusting. */
    public function isRateStale(): bool
    {
        return $this->rate_source === 'api'
            && ! $this->rate_locked
            && (! $this->rate_updated_at || $this->rate_updated_at->lt(now()->subDay()));
    }
}

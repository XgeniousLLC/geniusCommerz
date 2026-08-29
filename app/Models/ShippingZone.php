<?php

namespace App\Models;

use App\Models\Concerns\MatchesAddress;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    use MatchesAddress;

    protected $fillable = ['name', 'country', 'state', 'postal_pattern', 'priority', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'priority'  => 'integer',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }
}

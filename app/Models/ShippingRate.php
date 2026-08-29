<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    protected $fillable = [
        'shipping_zone_id', 'name', 'price', 'per_kg',
        'min_weight', 'max_weight', 'min_subtotal', 'max_subtotal',
        'free_above', 'delivery_estimate', 'priority', 'is_active',
    ];

    protected $casts = [
        'price'        => 'decimal:4',
        'per_kg'       => 'decimal:4',
        'min_weight'   => 'decimal:3',
        'max_weight'   => 'decimal:3',
        'min_subtotal' => 'decimal:4',
        'max_subtotal' => 'decimal:4',
        'free_above'   => 'decimal:4',
        'priority'     => 'integer',
        'is_active'    => 'boolean',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'shipping_zone_id');
    }

    /** Whether this rate's weight and subtotal bands cover the cart. Null bounds are open. */
    public function covers(float $weight, float $subtotal): bool
    {
        return ! (
            ($this->min_weight !== null && $weight < (float) $this->min_weight)
            || ($this->max_weight !== null && $weight > (float) $this->max_weight)
            || ($this->min_subtotal !== null && $subtotal < (float) $this->min_subtotal)
            || ($this->max_subtotal !== null && $subtotal > (float) $this->max_subtotal)
        );
    }

    /** Cost for this cart: base price plus any per-kg charge above the band's floor. */
    public function costFor(float $weight, float $subtotal): float
    {
        if ($this->free_above !== null && $subtotal >= (float) $this->free_above) {
            return 0.0;
        }

        $billableWeight = max(0.0, $weight - (float) ($this->min_weight ?? 0));

        return round((float) $this->price + ($billableWeight * (float) $this->per_kg), 2);
    }

    public function describeBand(): string
    {
        $parts = [];

        if ($this->min_weight !== null || $this->max_weight !== null) {
            $parts[] = trim(($this->min_weight !== null ? rtrim(rtrim((string) $this->min_weight, '0'), '.').'kg' : '0')
                .'–'.($this->max_weight !== null ? rtrim(rtrim((string) $this->max_weight, '0'), '.').'kg' : '∞'));
        }

        if ($this->min_subtotal !== null || $this->max_subtotal !== null) {
            $parts[] = 'order '.($this->min_subtotal !== null ? (string) (float) $this->min_subtotal : '0')
                .'–'.($this->max_subtotal !== null ? (string) (float) $this->max_subtotal : '∞');
        }

        return $parts ? implode(' · ', $parts) : 'any cart';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SourcingItem extends Model
{
    protected $fillable = [
        'product_id', 'product_name', 'supplier',
        'cost_price', 'sell_price', 'moq', 'lead_days', 'status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function margin(): ?float
    {
        if (! $this->sell_price || $this->sell_price == 0) {
            return null;
        }
        return round((($this->sell_price - $this->cost_price) / $this->sell_price) * 100, 1);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = ['reference', 'supplier_name', 'order_date', 'status', 'notes'];

    protected $casts = ['order_date' => 'date'];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(PurchaseShipment::class);
    }

    public function totalCost(): float
    {
        return (float) $this->items->sum(fn ($i) => $i->unit_cost * $i->quantity);
    }

    public function totalShipment(): float
    {
        return (float) $this->shipments->sum('amount');
    }

    public function landedCost(): float
    {
        return $this->totalCost() + $this->totalShipment();
    }

    public static function nextReference(): string
    {
        $year  = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;
        return sprintf('PO-%d-%03d', $year, $count);
    }
}

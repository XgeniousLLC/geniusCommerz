<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseShipment extends Model
{
    protected $fillable = ['purchase_order_id', 'description', 'amount', 'shipment_date', 'notes'];

    protected $casts = ['shipment_date' => 'date', 'amount' => 'decimal:2'];

    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
}

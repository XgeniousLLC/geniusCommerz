<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    public const STATUSES = ['pending', 'approved', 'rejected', 'processed'];

    public const REASONS = [
        'wrong_item'     => 'Received wrong item',
        'damaged'        => 'Item arrived damaged',
        'not_received'   => 'Item not received',
        'changed_mind'   => 'Changed my mind',
        'defective'      => 'Item is defective',
        'other'          => 'Other',
    ];

    protected $fillable = [
        'order_id', 'user_id', 'reason', 'details',
        'amount', 'status', 'admin_note', 'resolved_at',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'approved'  => 'bg-green-100 text-green-800',
            'processed' => 'bg-blue-100 text-blue-800',
            'rejected'  => 'bg-red-100 text-red-700',
            default     => 'bg-yellow-100 text-yellow-800',
        };
    }
}

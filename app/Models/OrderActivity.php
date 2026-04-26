<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderActivity extends Model
{
    protected $fillable = [
        'order_id', 'type', 'title', 'description', 'metadata', 'admin_id',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function iconClass(): string
    {
        return match($this->type) {
            'created'         => 'bg-green-100 text-green-600',
            'status_changed'  => 'bg-blue-100 text-blue-600',
            'payment_updated' => 'bg-purple-100 text-purple-600',
            'tracking_added'  => 'bg-indigo-100 text-indigo-600',
            'note_added'      => 'bg-yellow-100 text-yellow-600',
            default           => 'bg-gray-100 text-gray-500',
        };
    }
}

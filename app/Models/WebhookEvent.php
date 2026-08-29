<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A received gateway webhook. The (provider, event_id) unique index is the replay guard:
 * inserting is what claims an event, so the same delivery cannot settle an order twice.
 */
class WebhookEvent extends Model
{
    protected $fillable = ['provider', 'event_id', 'event_type', 'payload', 'processed_at', 'error'];

    protected $casts = [
        'payload'      => 'array',
        'processed_at' => 'datetime',
    ];
}

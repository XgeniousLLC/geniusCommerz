<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdSpend extends Model
{
    protected $fillable = ['platform', 'campaign_name', 'amount', 'spend_date', 'product_id', 'notes'];

    protected $casts = ['spend_date' => 'date', 'amount' => 'decimal:2'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }

    public static array $platforms = ['meta' => 'Meta (Facebook/Instagram)', 'google' => 'Google Ads', 'tiktok' => 'TikTok Ads', 'youtube' => 'YouTube Ads', 'other' => 'Other'];
}

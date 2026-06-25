<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FraudCheckCache extends Model
{
    protected $table = 'fraud_check_cache';

    protected $fillable = [
        'phone',
        'provider',
        'risk_level',
        'risk_score',
        'fraud_report_count',
        'summary',
        'couriers',
        'reports',
        'expires_at',
    ];

    protected $casts = [
        'summary'    => 'array',
        'couriers'   => 'array',
        'reports'    => 'array',
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}

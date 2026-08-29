<?php

namespace App\Models;

use App\Payments\PaymentStatus;
use App\Support\Currencies;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One payment attempt. An order can have several — a declined card, a retry, a success —
 * and reconciliation needs the whole history, not just the last outcome.
 */
class Payment extends Model
{
    protected $fillable = [
        'order_id', 'provider', 'environment', 'status',
        'amount_minor', 'currency', 'base_amount', 'base_currency', 'exchange_rate',
        'gateway_transaction_id', 'idempotency_key', 'payload', 'error', 'paid_at',
    ];

    protected $casts = [
        'amount_minor'  => 'integer',
        'base_amount'   => 'decimal:4',
        'exchange_rate' => 'decimal:8',
        'payload'       => 'array',
        'paid_at'       => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function status(): PaymentStatus
    {
        return PaymentStatus::tryFrom($this->status) ?? PaymentStatus::Pending;
    }

    public function isSettled(): bool
    {
        return $this->status === PaymentStatus::Paid->value;
    }

    /** Decimal amount in the currency the customer was charged. */
    public function amount(): float
    {
        return Currencies::fromMinor($this->amount_minor, $this->currency);
    }

    public function formattedAmount(): string
    {
        return Currencies::format($this->amount(), $this->currency);
    }
}

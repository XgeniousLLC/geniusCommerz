<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    public const SOURCES = [
        'website'   => 'Website',
        'whatsapp'  => 'WhatsApp',
        'phone'     => 'Phone Call',
        'facebook'  => 'Facebook',
        'instagram' => 'Instagram',
        'walk_in'   => 'Walk-in',
        'other'     => 'Other',
    ];

    protected $fillable = [
        'order_number','source','user_id','customer_name','customer_email','customer_phone',
        'billing_address','shipping_address',
        'coupon_id','coupon_code','discount_amount',
        'subtotal','shipping_cost','tax','total',
        'status','payment_status','payment_method','tracking_number',
        'courier_provider','consignment_id','courier_status','courier_data',
        'is_preorder',
        'notes','admin_note','paid_at',
    ];

    protected $casts = [
        'billing_address'  => 'array',
        'shipping_address' => 'array',
        'subtotal'         => 'decimal:2',
        'shipping_cost'    => 'decimal:2',
        'tax'              => 'decimal:2',
        'total'            => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'paid_at'          => 'datetime',
        'courier_data'     => 'array',
        'is_preorder'      => 'boolean',
    ];

    public const STATUSES         = ['pending','processing','shipped','delivered','cancelled','refunded'];
    public const PAYMENT_STATUSES = ['unpaid','paid','partially_refunded','refunded'];

    public static function generateOrderNumber(): string
    {
        $format      = SiteSetting::get('order.number_format', '{year}-{month}-{day}-{serial}');
        $digits      = max(1, (int) SiteSetting::get('order.serial_digits', 4));
        $resetPeriod = SiteSetting::get('order.serial_reset', 'daily');
        $prefix      = SiteSetting::get('general.order_prefix', '');
        $now         = now();

        // Determine scope key for reset tracking
        $scope = match($resetPeriod) {
            'daily'   => $now->format('Y-m-d'),
            'monthly' => $now->format('Y-m'),
            'yearly'  => $now->format('Y'),
            default   => 'never',
        };

        // Atomically bump the serial inside a transaction
        $serial = DB::transaction(function () use ($scope) {
            $lastScope  = SiteSetting::get('order.last_serial_scope', '');
            $lastSerial = (int) SiteSetting::get('order.last_serial', 0);

            $next = ($scope !== 'never' && $scope !== $lastScope) ? 1 : $lastSerial + 1;

            SiteSetting::set('order.last_serial',       $next,  'number', 'orders');
            SiteSetting::set('order.last_serial_scope', $scope, 'text',   'orders');

            return $next;
        });

        $tokens = [
            '{year}'   => $now->format('Y'),
            '{month}'  => $now->format('m'),
            '{day}'    => $now->format('d'),
            '{prefix}' => $prefix,
            '{serial}' => str_pad($serial, $digits, '0', STR_PAD_LEFT),
        ];

        $number = str_replace(array_keys($tokens), array_values($tokens), $format);

        // Safety: if a collision somehow exists, keep bumping
        while (static::where('order_number', $number)->exists()) {
            $serial++;
            $tokens['{serial}'] = str_pad($serial, $digits, '0', STR_PAD_LEFT);
            $number = str_replace(array_keys($tokens), array_values($tokens), $format);
            SiteSetting::set('order.last_serial', $serial, 'number', 'orders');
        }

        return $number;
    }

    public function sourceBadgeClass(): string
    {
        return match($this->source) {
            'whatsapp'  => 'bg-green-100 text-green-800',
            'phone'     => 'bg-blue-100 text-blue-800',
            'facebook'  => 'bg-indigo-100 text-indigo-800',
            'instagram' => 'bg-pink-100 text-pink-800',
            'walk_in'   => 'bg-orange-100 text-orange-800',
            default     => 'bg-gray-100 text-gray-600',
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(OrderActivity::class)->orderBy('created_at');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function logActivity(string $type, string $title, ?string $description = null, array $metadata = [], ?int $adminId = null): void
    {
        $this->activities()->create([
            'type'        => $type,
            'title'       => $title,
            'description' => $description,
            'metadata'    => $metadata ?: null,
            'admin_id'    => $adminId,
        ]);
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'pending'    => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'shipped'    => 'bg-indigo-100 text-indigo-800',
            'delivered'  => 'bg-green-100 text-green-800',
            'cancelled'  => 'bg-red-100 text-red-700',
            'refunded'   => 'bg-gray-100 text-gray-600',
            default      => 'bg-gray-100 text-gray-600',
        };
    }

    public function paymentBadgeClass(): string
    {
        return match($this->payment_status) {
            'paid'               => 'bg-green-100 text-green-800',
            'partially_refunded' => 'bg-orange-100 text-orange-700',
            'refunded'           => 'bg-gray-100 text-gray-600',
            default              => 'bg-red-100 text-red-700',
        };
    }
}

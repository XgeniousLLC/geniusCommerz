<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code','description','type','value',
        'minimum_order','maximum_discount','applies_to',
        'usage_limit','per_customer_limit','usage_count',
        'is_active','is_auto_apply',
        'starts_at','expires_at',
    ];

    protected $casts = [
        'value'             => 'decimal:2',
        'minimum_order'     => 'decimal:2',
        'maximum_discount'  => 'decimal:2',
        'is_active'         => 'boolean',
        'is_auto_apply'     => 'boolean',
        'starts_at'         => 'datetime',
        'expires_at'        => 'datetime',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_products');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'coupon_categories');
    }

    public function isValid(float $subtotal = 0, ?int $userId = null): bool
    {
        if (! $this->is_active) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) return false;
        if ($this->minimum_order && $subtotal < (float) $this->minimum_order) return false;

        if ($this->per_customer_limit && $userId) {
            $used = Order::where('user_id', $userId)
                ->where('coupon_id', $this->id)
                ->count();
            if ($used >= $this->per_customer_limit) return false;
        }

        return true;
    }

    public function appliesToProducts(array $productIds): bool
    {
        if ($this->applies_to === 'all') return true;

        if ($this->applies_to === 'specific_products') {
            return $this->products()->whereIn('products.id', $productIds)->exists();
        }

        if ($this->applies_to === 'specific_categories') {
            $categoryIds = \DB::table('category_product')
                ->whereIn('product_id', $productIds)
                ->pluck('category_id')
                ->unique()
                ->toArray();
            return $this->categories()->whereIn('categories.id', $categoryIds)->exists();
        }

        return false;
    }

    public function computeDiscount(float $subtotal): float
    {
        $discount = $this->type === 'percent'
            ? $subtotal * ($this->value / 100)
            : (float) $this->value;

        if ($this->maximum_discount) {
            $discount = min($discount, (float) $this->maximum_discount);
        }

        return round(min($discount, $subtotal), 2);
    }

    public static function findAutoApply(float $subtotal, array $productIds = [], ?int $userId = null): ?self
    {
        return static::where('is_auto_apply', true)
            ->where('is_active', true)
            ->get()
            ->filter(fn($c) => $c->isValid($subtotal, $userId) && $c->appliesToProducts($productIds))
            ->sortByDesc(fn($c) => $c->computeDiscount($subtotal))
            ->first();
    }
}

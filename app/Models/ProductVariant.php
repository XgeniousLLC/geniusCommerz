<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'product_id', 'sku', 'price', 'compare_at_price', 'cost_price',
        'weight', 'image', 'is_active', 'stock_qty', 'sort_order',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return HasMany<ProductVariantValue, $this> */
    public function variantValues(): HasMany
    {
        return $this->hasMany(ProductVariantValue::class, 'variant_id');
    }

    public function label(): string
    {
        return $this->variantValues->map(fn ($v) => $v->attributeValue->value)->join(' / ');
    }
}

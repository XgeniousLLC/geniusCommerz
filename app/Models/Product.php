<?php

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Product extends Model
{
    use Auditable;

    /** @var list<string> */
    protected $fillable = [
        'name', 'slug', 'short_description', 'description', 'brand_id',
        'status', 'is_featured', 'has_variants', 'sku', 'price',
        'compare_at_price', 'cost_price', 'weight', 'shipping_included', 'warranty', 'return_policy',
        'specifications', 'stock_qty', 'sort_order', 'created_by', 'updated_by',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'is_featured'        => 'boolean',
        'has_variants'       => 'boolean',
        'shipping_included'  => 'boolean',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:3',
        'specifications' => 'array',
    ];

    /** @return BelongsTo<Brand, $this> */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /** @return BelongsToMany<Category, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /** @return HasMany<ProductVariant, $this> */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    /** @return BelongsToMany<Media, $this> */
    public function images(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'product_media')
            ->wherePivot('type', 'image')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    /** @return BelongsToMany<Media, $this> */
    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'product_media')
            ->wherePivot('type', 'video')
            ->withPivot('sort_order');
    }

    /** @return HasMany<ProductReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('is_approved', true)->orderByDesc('created_at');
    }

    /** @return MorphOne<MetaInformation, $this> */
    public function meta(): MorphOne
    {
        return $this->morphOne(MetaInformation::class, 'metable');
    }

    /** @return BelongsTo<Admin, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function displayPrice(): string
    {
        if ($this->has_variants) {
            $min = $this->variants->min('price');

            return $min !== null ? number_format((float) $min, 2) : '—';
        }

        return $this->price !== null ? number_format((float) $this->price, 2) : '—';
    }
}

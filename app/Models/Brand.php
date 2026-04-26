<?php

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use Auditable;

    /** @var list<string> */
    protected $fillable = ['name', 'slug', 'description', 'logo_media_id', 'logo', 'website', 'is_active'];

    /** @var array<string, string> */
    protected $casts = ['is_active' => 'boolean'];

    /** @return BelongsTo<Media, $this> */
    public function logoMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_media_id');
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Blog extends Model
{
    protected $appends = ['cover_url', 'category_name', 'author_display_name', 'read_time'];

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'enable_toc', 'faqs',
        'cover_image', 'cover_media_id', 'category', 'blog_category_id',
        'author_name', 'author_admin_id',
        'status', 'published_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'enable_toc'   => 'boolean',
            'faqs'         => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function blogCategory()
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function getCategoryNameAttribute(): ?string
    {
        return $this->blogCategory?->name ?? $this->category;
    }

    public function coverMedia()
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_media_id) {
            return $this->coverMedia?->getUrl();
        }
        return $this->cover_image ?: null;
    }

    public function metaInformation()
    {
        return $this->morphOne(MetaInformation::class, 'metable');
    }

    public function contentTranslations(): MorphMany
    {
        return $this->morphMany(ContentTranslation::class, 'translatable');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function authorAdmin()
    {
        return $this->belongsTo(Admin::class, 'author_admin_id');
    }

    public function getAuthorDisplayNameAttribute(): string
    {
        return $this->authorAdmin?->name ?? $this->author_name ?? 'Admin';
    }

    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = $value;
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    public function getReadTimeAttribute(): string
    {
        $words = str_word_count(strip_tags($this->content ?? ''));
        $minutes = max(1, (int) ceil($words / 200));
        return $minutes . ' min read';
    }
}

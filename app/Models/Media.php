<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $fillable = [
        'uuid',
        'folder_id',
        'type',
        'model_type',
        'model_id',
        'collection_name',
        'disk',
        'path',
        'filename',
        'original_filename',
        'alt',
        'title',
        'caption',
        'width',
        'height',
        'mime_type',
        'size',
        'conversions',
        'custom_properties',
        'order_column',
        'created_by',
    ];

    protected $casts = [
        'size'              => 'integer',
        'conversions'       => 'array',
        'custom_properties' => 'array',
        'order_column'      => 'integer',
        'width'             => 'integer',
        'height'            => 'integer',
    ];

    /** @return MorphTo<\Illuminate\Database\Eloquent\Model, \Illuminate\Database\Eloquent\Model> */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function getUrl(string $conversion = ''): string
    {
        if ($conversion && isset($this->conversions[$conversion])) {
            return Storage::disk($this->disk)->url($this->conversions[$conversion]);
        }

        return Storage::disk($this->disk)->url($this->path);
    }

    public function getTemporaryUrl(int $minutes = 15, string $conversion = ''): string
    {
        $path = ($conversion && isset($this->conversions[$conversion]))
            ? $this->conversions[$conversion]
            : $this->path;

        return Storage::disk($this->disk)->temporaryUrl($path, now()->addMinutes($minutes));
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function isDocument(): bool
    {
        return $this->type === 'document';
    }

    public function typeFromMime(): string
    {
        if (str_starts_with($this->mime_type, 'image/')) return 'image';
        if (str_starts_with($this->mime_type, 'video/')) return 'video';
        return 'document';
    }

    public function humanSize(): string
    {
        $bytes = $this->size;
        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024) {
                return round($bytes, 1).' '.$unit;
            }
            $bytes /= 1024;
        }

        return round($bytes, 1).' TB';
    }
}

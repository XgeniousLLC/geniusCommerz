<?php

namespace App\Concerns;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMedia
{
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model')->orderBy('order_column');
    }

    public function getMedia(string $collection = 'default'): \Illuminate\Database\Eloquent\Collection
    {
        return $this->media()->where('collection_name', $collection)->get();
    }

    public function getFirstMedia(string $collection = 'default'): ?Media
    {
        return $this->media()->where('collection_name', $collection)->first();
    }

    public function getFirstMediaUrl(string $collection = 'default', string $conversion = ''): string
    {
        return $this->getFirstMedia($collection)?->getUrl($conversion) ?? '';
    }
}

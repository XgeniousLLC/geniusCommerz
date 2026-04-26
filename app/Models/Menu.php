<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    public const LOCATIONS = [
        'main_nav'         => 'Main Navigation',
        'footer_primary'   => 'Footer Primary',
        'footer_secondary' => 'Footer Secondary',
    ];

    protected $fillable = ['name', 'location'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }
}

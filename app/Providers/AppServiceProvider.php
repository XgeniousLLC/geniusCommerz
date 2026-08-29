<?php

namespace App\Providers;

use App\Models\SiteSetting;
use App\Services\CloudStorageService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->applyStoreTimezone();

        CloudStorageService::configure();

        if (request()->is('admin/*') || request()->is('admin')) {
            Paginator::defaultView('vendor.pagination.cobalt');
            Paginator::defaultSimpleView('vendor.pagination.cobalt');
        }
    }

    /**
     * Apply the merchant's timezone setting.
     *
     * general.timezone was editable in settings but nothing ever read it, so dates were
     * always rendered in config('app.timezone') regardless of what the merchant chose.
     * Cached because this runs on every request, and guarded so a missing database (during
     * install or migrations) cannot break boot.
     */
    private function applyStoreTimezone(): void
    {
        try {
            $timezone = Cache::remember(
                'store_timezone',
                now()->addHour(),
                fn () => SiteSetting::get('general.timezone') ?: config('app.timezone'),
            );
        } catch (\Throwable) {
            return;
        }

        if ($timezone && in_array($timezone, timezone_identifiers_list(), true)) {
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }
    }
}

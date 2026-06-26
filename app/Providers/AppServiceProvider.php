<?php

namespace App\Providers;

use App\Services\CloudStorageService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        CloudStorageService::configure();

        if (request()->is('admin/*') || request()->is('admin')) {
            Paginator::defaultView('vendor.pagination.cobalt');
            Paginator::defaultSimpleView('vendor.pagination.cobalt');
        }
    }
}

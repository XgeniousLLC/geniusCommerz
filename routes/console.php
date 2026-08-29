<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Hourly is ample for retail pricing and costs nothing — the job only touches currencies
 * explicitly set to refresh automatically.
 *
 * Requires the usual scheduler cron in deployment:
 *   * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
 */
Schedule::command('currency:refresh-rates')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();

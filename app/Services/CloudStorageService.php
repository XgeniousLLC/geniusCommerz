<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class CloudStorageService
{
    public static function configure(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        $driver = Cache::remember('storage.driver', 300, fn () => SiteSetting::get('storage.driver', 'public'));

        if ($driver === 's3') {
            $cfg = Cache::remember('storage.s3_config', 300, fn () => [
                'key'    => SiteSetting::get('storage.s3_key', ''),
                'secret' => SiteSetting::get('storage.s3_secret', ''),
                'region' => SiteSetting::get('storage.s3_region', 'us-east-1'),
                'bucket' => SiteSetting::get('storage.s3_bucket', ''),
                'url'    => SiteSetting::get('storage.s3_url', ''),
            ]);

            config(['filesystems.disks.s3' => [
                'driver'     => 's3',
                'key'        => $cfg['key'],
                'secret'     => $cfg['secret'],
                'region'     => $cfg['region'],
                'bucket'     => $cfg['bucket'],
                'url'        => $cfg['url'] ?: null,
                'visibility' => 'public',
                'throw'      => false,
            ]]);
        }

        if ($driver === 'r2') {
            $cfg = Cache::remember('storage.r2_config', 300, fn () => [
                'key'      => SiteSetting::get('storage.r2_key', ''),
                'secret'   => SiteSetting::get('storage.r2_secret', ''),
                'bucket'   => SiteSetting::get('storage.r2_bucket', ''),
                'endpoint' => SiteSetting::get('storage.r2_endpoint', ''),
                'url'      => SiteSetting::get('storage.r2_url', ''),
            ]);

            config(['filesystems.disks.r2' => [
                'driver'                  => 's3',
                'key'                     => $cfg['key'],
                'secret'                  => $cfg['secret'],
                'region'                  => 'auto',
                'bucket'                  => $cfg['bucket'],
                'endpoint'                => $cfg['endpoint'],
                'url'                     => $cfg['url'] ?: null,
                'use_path_style_endpoint' => false,
                'visibility'              => 'public',
                'throw'                   => false,
            ]]);
        }
    }

    public static function activeDisk(): string
    {
        if (!Schema::hasTable('site_settings')) {
            return 'public';
        }

        $driver = Cache::remember('storage.driver', 300, fn () => SiteSetting::get('storage.driver', 'public'));

        return match ($driver) {
            's3'    => 's3',
            'r2'    => 'r2',
            default => 'public',
        };
    }

    public static function flushCache(): void
    {
        Cache::forget('storage.driver');
        Cache::forget('storage.s3_config');
        Cache::forget('storage.r2_config');
    }
}

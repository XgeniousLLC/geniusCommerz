<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PixelLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PixelEventLogController extends Controller
{
    public function index(Request $request): View
    {
        $all      = PixelLogger::read(1000);
        $platform = $request->query('platform');
        $status   = $request->query('status');

        $filtered = array_filter($all, function (array $e) use ($platform, $status) {
            if ($platform && ($e['platform'] ?? '') !== $platform) return false;
            if ($status === 'failed'  && ($e['success'] ?? true))  return false;
            if ($status === 'success' && ! ($e['success'] ?? false)) return false;
            return true;
        });

        $logs = array_slice(array_values($filtered), 0, 200);

        $stats = [
            'total'   => count($all),
            'failed'  => count(array_filter($all, fn ($e) => ! ($e['success'] ?? true))),
            'meta'    => count(array_filter($all, fn ($e) => ($e['platform'] ?? '') === 'meta')),
            'tiktok'  => count(array_filter($all, fn ($e) => ($e['platform'] ?? '') === 'tiktok')),
            'ga4'     => count(array_filter($all, fn ($e) => ($e['platform'] ?? '') === 'ga4')),
        ];

        return view('admin.pixel-logs.index', compact('logs', 'stats'));
    }

    public function destroy(): RedirectResponse
    {
        $path = storage_path('logs/pixel_events.log');

        if (file_exists($path)) {
            file_put_contents($path, '');
        }

        return back()->with('success', 'Pixel event log cleared.');
    }
}

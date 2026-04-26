<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FailedJobController extends Controller
{
    public function index(Request $request): View
    {
        $queue = $request->input('queue');
        $search = $request->input('search');

        $query = DB::table('failed_jobs')->orderByDesc('failed_at');

        if ($queue) {
            $query->where('queue', $queue);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('payload', 'like', "%{$search}%")
                    ->orWhere('exception', 'like', "%{$search}%");
            });
        }

        $jobs = $query->paginate(25)->withQueryString();
        $queues = DB::table('failed_jobs')->distinct()->pluck('queue')->sort()->values();

        return view('admin.failed-jobs.index', compact('jobs', 'queues', 'queue', 'search'));
    }

    public function show(string $uuid): View
    {
        $job = DB::table('failed_jobs')->where('uuid', $uuid)->firstOrFail();

        return view('admin.failed-jobs.show', compact('job'));
    }

    public function retry(string $uuid): RedirectResponse
    {
        Artisan::call('queue:retry', ['id' => [$uuid]]);

        return back()->with('success', "Job {$uuid} queued for retry.");
    }

    public function retryAll(): RedirectResponse
    {
        Artisan::call('queue:retry', ['id' => ['all']]);

        return back()->with('success', 'All failed jobs queued for retry.');
    }

    public function destroy(string $uuid): RedirectResponse
    {
        Artisan::call('queue:forget', ['id' => $uuid]);

        return back()->with('success', "Job {$uuid} discarded.");
    }

    public function destroyAll(): RedirectResponse
    {
        Artisan::call('queue:flush');

        return back()->with('success', 'All failed jobs cleared.');
    }
}

@extends('admin.layouts.admin')

@section('title', 'Failed Jobs')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Failed Jobs</li>
    </ol>
@endsection

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Failed Jobs</h1>
            <p class="text-gray-600 mt-1">{{ $jobs->total() }} failed job(s)</p>
        </div>
        @if($jobs->total() > 0)
        <div class="flex items-center space-x-3">
            <form method="POST" action="{{ route('admin.failed-jobs.retry-all') }}"
                  onsubmit="return confirm('Retry all {{ $jobs->total() }} failed jobs?')">
                @csrf
                <x-admin.button type="submit" variant="secondary">Retry All</x-admin.button>
            </form>
            <form method="POST" action="{{ route('admin.failed-jobs.destroy-all') }}"
                  onsubmit="return confirm('Permanently delete all failed jobs? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <x-admin.button type="submit" variant="danger">Clear All</x-admin.button>
            </form>
        </div>
        @endif
    </div>
@endsection

@section('content')
{{-- Filters --}}
<x-admin.card class="mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search payload / exception</label>
            <x-admin.input type="text" name="search" value="{{ $search }}" placeholder="class name, message…" />
        </div>
        <div class="w-48">
            <label class="block text-sm font-medium text-gray-700 mb-1">Queue</label>
            <x-admin.select name="queue">
                <option value="">All queues</option>
                @foreach($queues as $q)
                    <option value="{{ $q }}" {{ $queue === $q ? 'selected' : '' }}>{{ $q }}</option>
                @endforeach
            </x-admin.select>
        </div>
        <x-admin.button type="submit">Filter</x-admin.button>
        @if($search || $queue)
            <a href="{{ route('admin.failed-jobs.index') }}" class="text-sm text-gray-500 hover:text-gray-700 self-center">Clear</a>
        @endif
    </form>
</x-admin.card>

{{-- Table --}}
@if($jobs->isEmpty())
    <x-admin.card>
        <p class="text-center text-gray-500 py-12">No failed jobs. Great job!</p>
    </x-admin.card>
@else
<x-admin.card>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Job Class</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Queue</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Error</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Failed At</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($jobs as $job)
                    @php
                        $payload   = json_decode($job->payload, true);
                        $jobClass  = class_basename($payload['displayName'] ?? $payload['job'] ?? 'Unknown');
                        $firstLine = Str::limit(explode("\n", $job->exception)[0], 120);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $jobClass }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                {{ $job->queue }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 font-mono text-xs max-w-xs truncate" title="{{ $firstLine }}">
                            {{ $firstLine }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($job->failed_at)->format('d M Y, H:i') }}
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap space-x-2">
                            <a href="{{ route('admin.failed-jobs.show', $job->uuid) }}"
                               class="text-blue-600 hover:text-blue-800 text-xs font-medium">View</a>

                            <form method="POST" action="{{ route('admin.failed-jobs.retry', $job->uuid) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium">Retry</button>
                            </form>

                            <form method="POST" action="{{ route('admin.failed-jobs.destroy', $job->uuid) }}" class="inline"
                                  onsubmit="return confirm('Discard this job?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Discard</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($jobs->hasPages())
        <div class="mt-4 px-4">
            {{ $jobs->links() }}
        </div>
    @endif
</x-admin.card>
@endif
@endsection

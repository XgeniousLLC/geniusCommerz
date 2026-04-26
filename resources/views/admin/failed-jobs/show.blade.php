@extends('admin.layouts.admin')

@section('title', 'Failed Job Detail')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li><a href="{{ route('admin.failed-jobs.index') }}" class="hover:text-gray-700">Failed Jobs</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Detail</li>
    </ol>
@endsection

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Failed Job</h1>
            <p class="text-gray-500 text-sm font-mono mt-1">{{ $job->uuid }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <form method="POST" action="{{ route('admin.failed-jobs.retry', $job->uuid) }}">
                @csrf
                <x-admin.button type="submit" variant="secondary">Retry Job</x-admin.button>
            </form>
            <form method="POST" action="{{ route('admin.failed-jobs.destroy', $job->uuid) }}"
                  onsubmit="return confirm('Discard this job permanently?')">
                @csrf
                @method('DELETE')
                <x-admin.button type="submit" variant="danger">Discard</x-admin.button>
            </form>
        </div>
    </div>
@endsection

@section('content')
@php
    $payload = json_decode($job->payload, true);
@endphp

<div class="space-y-6">
    {{-- Meta --}}
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-4">Job Info</h3>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <dt class="font-medium text-gray-500">Job Class</dt>
                <dd class="mt-1 text-gray-900">{{ $payload['displayName'] ?? $payload['job'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500">Queue</dt>
                <dd class="mt-1 text-gray-900">{{ $job->queue }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500">Connection</dt>
                <dd class="mt-1 text-gray-900">{{ $job->connection }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500">Failed At</dt>
                <dd class="mt-1 text-gray-900">{{ \Carbon\Carbon::parse($job->failed_at)->format('d M Y, H:i:s') }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500">Attempts</dt>
                <dd class="mt-1 text-gray-900">{{ $payload['attempts'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-500">Max Tries</dt>
                <dd class="mt-1 text-gray-900">{{ $payload['maxTries'] ?? 'unlimited' }}</dd>
            </div>
        </dl>
    </x-admin.card>

    {{-- Exception --}}
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-4">Exception</h3>
        <pre class="bg-red-50 text-red-800 text-xs font-mono rounded p-4 overflow-x-auto whitespace-pre-wrap break-words">{{ $job->exception }}</pre>
    </x-admin.card>

    {{-- Payload --}}
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-4">Payload</h3>
        <pre class="bg-gray-50 text-gray-800 text-xs font-mono rounded p-4 overflow-x-auto whitespace-pre-wrap break-words">{{ json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </x-admin.card>
</div>
@endsection

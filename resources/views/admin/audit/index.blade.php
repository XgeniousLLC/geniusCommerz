@extends('admin.layouts.admin')

@section('title', 'Audit Log')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Audit Log</li>
    </ol>
@endsection

@section('page-header')
    <h1 class="text-2xl font-bold text-gray-900">Audit Log</h1>
    <p class="text-gray-600 mt-1">All create / update / delete events across audited models</p>
@endsection

@section('content')
{{-- Filters --}}
<x-admin.card class="mb-6">
    <form method="GET" action="{{ route('admin.audit.index') }}" class="flex flex-wrap gap-4 items-end">
        <div class="w-40">
            <label class="block text-xs font-medium text-gray-500 mb-1">Event</label>
            <x-admin.select name="event">
                <option value="">All Events</option>
                <option value="created"  {{ request('event') === 'created'  ? 'selected' : '' }}>Created</option>
                <option value="updated"  {{ request('event') === 'updated'  ? 'selected' : '' }}>Updated</option>
                <option value="deleted"  {{ request('event') === 'deleted'  ? 'selected' : '' }}>Deleted</option>
            </x-admin.select>
        </div>

        <div class="w-44">
            <label class="block text-xs font-medium text-gray-500 mb-1">Model</label>
            <x-admin.select name="model">
                <option value="">All Models</option>
                @foreach($modelTypes as $type)
                    <option value="{{ $type }}" {{ request('model') === $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </x-admin.select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
            <x-admin.input type="date" name="from" value="{{ request('from') }}" />
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
            <x-admin.input type="date" name="to" value="{{ request('to') }}" />
        </div>

        <div class="flex gap-2">
            <x-admin.button type="submit">Filter</x-admin.button>
            <x-admin.button variant="secondary" type="button"
                onclick="window.location='{{ route('admin.audit.index') }}'">
                Clear
            </x-admin.button>
        </div>
    </form>
</x-admin.card>

{{-- Log table --}}
<x-admin.card>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">When</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">By</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Changes</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50" x-data="{ open: false }">
                        <td class="px-4 py-3 whitespace-nowrap text-gray-500 text-xs">
                            {{ $log->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $badge = match($log->event) {
                                    'created' => 'bg-green-100 text-green-800',
                                    'updated' => 'bg-yellow-100 text-yellow-800',
                                    'deleted' => 'bg-red-100 text-red-800',
                                    default   => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $badge }}">
                                {{ ucfirst($log->event) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="font-medium text-gray-900">{{ class_basename($log->auditable_type) }}</span>
                            <span class="text-gray-400">#{{ $log->auditable_id }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                            {{ $log->user?->name ?? 'System' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($log->event === 'updated' && $log->old_values)
                                <button @click="open = !open"
                                    class="text-blue-500 hover:text-blue-700 text-xs underline">
                                    <span x-text="open ? 'Hide diff' : 'Show diff'"></span>
                                </button>
                                <div x-show="open" x-cloak class="mt-2 space-y-1">
                                    @foreach(array_keys($log->new_values ?? []) as $field)
                                        @if(isset($log->old_values[$field]) && $log->old_values[$field] !== ($log->new_values[$field] ?? null))
                                            <div class="text-xs">
                                                <span class="font-medium text-gray-700">{{ $field }}:</span>
                                                <span class="line-through text-red-500 ml-1">{{ Str::limit((string) $log->old_values[$field], 40) }}</span>
                                                <span class="text-green-600 ml-1">→ {{ Str::limit((string) ($log->new_values[$field] ?? ''), 40) }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @elseif($log->event === 'created')
                                <span class="text-xs text-gray-400">New record</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400">No audit events found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    @endif
</x-admin.card>
@endsection

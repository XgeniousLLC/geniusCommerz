@extends('admin.layouts.admin')

@section('title', 'Orders Report')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><span class="text-gray-400">Reports</span></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Orders</li>
</ol>
@endsection

@section('page-header')
<div class="flex items-center justify-between flex-wrap gap-4">
    <h1 class="text-2xl font-bold text-gray-900">Orders Report</h1>
    <div class="flex gap-2">
        @foreach([
            ['href' => route('admin.reports.sales'),      'label' => 'Sales'],
            ['href' => route('admin.reports.inventory'),  'label' => 'Inventory'],
            ['href' => route('admin.reports.customers'),  'label' => 'Customers'],
        ] as $tab)
        <a href="{{ $tab['href'] }}" class="text-sm px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:border-blue-300 hover:text-blue-600 transition-colors">{{ $tab['label'] }}</a>
        @endforeach
    </div>
</div>
@endsection

@section('content')

{{-- Date filter --}}
<x-admin.card class="mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Period</label>
            <select name="period" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
                @foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year','custom'=>'Custom Range'] as $val => $lbl)
                    <option value="{{ $val }}" @selected(request('period', 'month') === $val)>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        @if(request('period') === 'custom' || request('start_date'))
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
            <x-admin.input type="date" name="start_date" value="{{ request('start_date', $startDate->toDateString()) }}" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
            <x-admin.input type="date" name="end_date" value="{{ request('end_date', $endDate->toDateString()) }}" />
        </div>
        <x-admin.button type="submit" variant="secondary">Apply</x-admin.button>
        @endif
        <a href="{{ route('admin.reports.export', array_merge(request()->all(), ['type' => 'orders'])) }}"
           class="ml-auto text-sm flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:border-green-400 hover:text-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
    </form>
</x-admin.card>

{{-- Summary --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <x-admin.card class="text-center">
        <div class="text-3xl font-extrabold text-blue-600">{{ number_format($totalOrders) }}</div>
        <div class="text-sm text-gray-500 mt-1">Total Orders</div>
    </x-admin.card>
    <x-admin.card class="text-center">
        <div class="text-3xl font-extrabold text-red-500">{{ number_format($cancelledOrders) }}</div>
        <div class="text-sm text-gray-500 mt-1">Cancelled Orders</div>
    </x-admin.card>
    <x-admin.card class="text-center">
        <div class="text-3xl font-extrabold {{ $cancellationRate > 20 ? 'text-red-600' : ($cancellationRate > 10 ? 'text-yellow-600' : 'text-green-600') }}">{{ $cancellationRate }}%</div>
        <div class="text-sm text-gray-500 mt-1">Cancellation Rate</div>
    </x-admin.card>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- By status --}}
    <x-admin.card>
        <h2 class="text-base font-semibold text-gray-900 mb-4">Orders by Status</h2>
        @php
            $statusColors = ['pending'=>'#D97706','processing'=>'#2563EB','shipped'=>'#7C3AED','delivered'=>'#16A34A','cancelled'=>'#DC2626','refunded'=>'#6B7280'];
        @endphp
        @if($byStatus->isEmpty())
            <p class="text-center text-gray-400 py-4 text-sm">No data.</p>
        @else
            <div class="space-y-3">
                @foreach($byStatus as $row)
                @php $color = $statusColors[$row->status] ?? '#6B7280'; @endphp
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $color }}"></span>
                        <span class="text-sm capitalize text-gray-700">{{ $row->status }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold text-gray-900">{{ number_format($row->count) }}</span>
                        <span class="text-xs text-gray-400 ml-2">৳{{ number_format($row->revenue) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </x-admin.card>

    {{-- By payment method --}}
    <x-admin.card>
        <h2 class="text-base font-semibold text-gray-900 mb-4">Orders by Payment Method</h2>
        @if($byPaymentMethod->isEmpty())
            <p class="text-center text-gray-400 py-4 text-sm">No data.</p>
        @else
            @php $maxCount = $byPaymentMethod->max('count') ?: 1; @endphp
            <div class="space-y-3">
                @foreach($byPaymentMethod as $row)
                @php $pct = round(($row->count / $maxCount) * 100); @endphp
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="capitalize text-gray-700 font-medium">{{ str_replace('_', ' ', $row->payment_method) }}</span>
                        <span class="text-gray-900 font-bold">{{ number_format($row->count) }} orders</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">৳{{ number_format($row->revenue) }}</p>
                </div>
                @endforeach
            </div>
        @endif
    </x-admin.card>
</div>
@endsection

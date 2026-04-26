@extends('admin.layouts.admin')
@section('title', 'Demand Trends')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.reports.sales') }}" class="hover:text-gray-700">Reports</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Demand Trends</li>
</ol>
@endsection

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Demand Trends</h1>
    <p class="text-sm text-gray-500 mt-1">Monthly, hourly, and day-of-week sales patterns to predict demand.</p>
</div>
@endsection

@section('content')
@php
    $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $dayNames   = ['','Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    $maxMonthly = $monthly->max('revenue') ?: 1;
    $maxHourly  = $hourly->max('orders') ?: 1;
    $maxDow     = $dayOfWeek->max('orders') ?: 1;
@endphp

{{-- Year selector --}}
<form method="GET" class="flex items-end gap-3 mb-6">
    <div>
        <label class="text-xs text-gray-500 block mb-1">Year</label>
        <select name="year" onchange="this.form.submit()" class="border border-gray-200 rounded px-3 py-2 text-sm">
            @foreach(array_reverse($yearRange) as $y)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </div>
</form>

<div class="grid lg:grid-cols-2 gap-6 mb-6">
    {{-- Monthly Revenue Bar Chart --}}
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-4">Monthly Revenue — {{ $year }}</h3>
        <div class="flex items-end gap-1 h-40">
            @for($m = 1; $m <= 12; $m++)
            @php $row = $monthly->get($m); $pct = $row ? round(($row->revenue / $maxMonthly) * 100) : 0; @endphp
            <div class="flex-1 flex flex-col items-center gap-1">
                <div class="w-full flex flex-col justify-end" style="height:128px">
                    <div class="w-full rounded-t bg-blue-400 hover:bg-blue-500 transition-all" style="height:{{ max(2,$pct) }}%" title="{{ $row ? '৳'.number_format($row->revenue,0) : '৳0' }}"></div>
                </div>
                <span class="text-[9px] text-gray-400">{{ $monthNames[$m-1] }}</span>
            </div>
            @endfor
        </div>
        <div class="overflow-x-auto mt-4">
            <table class="w-full text-xs">
                <thead><tr class="text-gray-400 border-b"><th class="pb-1 text-left">Month</th><th class="pb-1 text-right">Orders</th><th class="pb-1 text-right">Revenue</th><th class="pb-1 text-right">AOV</th></tr></thead>
                <tbody class="divide-y divide-gray-50">
                    @for($m = 1; $m <= 12; $m++)
                    @php $row = $monthly->get($m); @endphp
                    <tr>
                        <td class="py-1">{{ $monthNames[$m-1] }}</td>
                        <td class="py-1 text-right">{{ $row ? number_format($row->orders) : '—' }}</td>
                        <td class="py-1 text-right font-medium">{{ $row ? '৳'.number_format($row->revenue,0) : '—' }}</td>
                        <td class="py-1 text-right text-gray-500">{{ $row ? '৳'.number_format($row->avg_order,0) : '—' }}</td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </x-admin.card>

    {{-- Day of Week --}}
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-4">Sales by Day of Week — {{ $year }}</h3>
        <div class="space-y-2">
            @foreach($dayOfWeek as $row)
            @php $pct = $maxDow > 0 ? round(($row->orders / $maxDow) * 100) : 0; @endphp
            <div class="flex items-center gap-3">
                <span class="w-8 text-xs text-gray-500 font-medium">{{ $dayNames[$row->dow] ?? $row->dow }}</span>
                <div class="flex-1 h-5 bg-gray-100 rounded overflow-hidden">
                    <div class="h-5 bg-indigo-400 rounded transition-all" style="width:{{ max(2,$pct) }}%"></div>
                </div>
                <span class="text-xs text-gray-600 w-24 text-right">{{ number_format($row->orders) }} orders</span>
                <span class="text-xs text-gray-500 w-28 text-right">৳{{ number_format($row->revenue,0) }}</span>
            </div>
            @endforeach
        </div>
    </x-admin.card>
</div>

{{-- Hourly Heatmap --}}
<x-admin.card>
    <h3 class="font-semibold text-gray-800 mb-4">Hourly Order Distribution — {{ $year }}</h3>
    @php $maxH = $hourly->max('orders') ?: 1; @endphp
    <div class="flex items-end gap-0.5 h-28">
        @for($h = 0; $h <= 23; $h++)
        @php $row = $hourly->get($h); $pct = $row ? round(($row->orders/$maxH)*100) : 0; @endphp
        <div class="flex-1 flex flex-col items-center">
            <div class="w-full flex flex-col justify-end" style="height:80px">
                <div class="w-full rounded-t {{ $pct > 70 ? 'bg-orange-500' : ($pct > 40 ? 'bg-orange-300' : 'bg-orange-100') }} hover:opacity-80" style="height:{{ max(2,$pct) }}%" title="{{ $h }}:00 — {{ $row ? $row->orders.' orders' : '0' }}"></div>
            </div>
            <span class="text-[8px] text-gray-400 mt-0.5">{{ str_pad($h,2,'0',STR_PAD_LEFT) }}</span>
        </div>
        @endfor
    </div>
    <p class="text-xs text-gray-400 mt-2">Hours 0–23 (server time). Darker = more orders. Useful for scheduling campaigns and flash sales.</p>
</x-admin.card>

{{-- Year-over-Year --}}
@if($yoy->isNotEmpty())
<x-admin.card class="mt-6">
    <h3 class="font-semibold text-gray-800 mb-4">Year-over-Year Comparison</h3>
    <div class="overflow-x-auto">
        @php
            $yoyByYear = $yoy->groupBy('year');
            $years = $yoyByYear->keys()->sort()->values();
        @endphp
        <table class="w-full text-sm">
            <thead><tr class="border-b text-xs text-gray-400 uppercase">
                <th class="pb-2 text-left">Month</th>
                @foreach($years as $y)<th class="pb-2 text-right">{{ $y }}</th>@endforeach
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @for($m = 1; $m <= 12; $m++)
                <tr>
                    <td class="py-1.5 font-medium text-gray-700">{{ $monthNames[$m-1] }}</td>
                    @foreach($years as $y)
                    @php $entry = $yoyByYear->get($y)?->firstWhere('month', $m); @endphp
                    <td class="py-1.5 text-right">{{ $entry ? '৳'.number_format($entry->revenue,0) : '—' }}</td>
                    @endforeach
                </tr>
                @endfor
            </tbody>
        </table>
    </div>
</x-admin.card>
@endif
@endsection

@extends('admin.layouts.admin')
@section('title', 'Customer LTV Report')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.reports.sales') }}" class="hover:text-gray-700">Reports</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Customer LTV</li>
</ol>
@endsection

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Customer Lifetime Value</h1>
    <p class="text-sm text-gray-500 mt-1">Repeat purchase rate, cohort analysis, and top customers by total spend.</p>
</div>
@endsection

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <x-admin.card class="p-5"><p class="text-xs text-gray-500 uppercase tracking-wide">Total Buyers</p><p class="text-2xl font-bold mt-1">{{ number_format($stats['total_buyers']) }}</p></x-admin.card>
    <x-admin.card class="p-5"><p class="text-xs text-gray-500 uppercase tracking-wide">Repeat Buyers</p><p class="text-2xl font-bold mt-1 text-blue-600">{{ number_format($stats['repeat_buyers']) }}</p></x-admin.card>
    <x-admin.card class="p-5"><p class="text-xs text-gray-500 uppercase tracking-wide">Repeat Rate</p><p class="text-2xl font-bold mt-1 text-green-600">{{ $stats['repeat_rate'] }}%</p></x-admin.card>
    <x-admin.card class="p-5"><p class="text-xs text-gray-500 uppercase tracking-wide">Avg LTV</p><p class="text-2xl font-bold mt-1">{{ money($stats['avg_ltv'], 0) }}</p></x-admin.card>
    <x-admin.card class="p-5"><p class="text-xs text-gray-500 uppercase tracking-wide">Avg Orders / Customer</p><p class="text-2xl font-bold mt-1">{{ number_format($stats['avg_order_count'],1) }}</p></x-admin.card>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-6">
    {{-- LTV Segments --}}
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-4">Customer Segments by LTV</h3>
        @php
            $segments = [
                ['label' => 'Champions (' . currency_symbol() . '10k+)',    'min' => 10000, 'max' => PHP_INT_MAX, 'color' => 'bg-green-500'],
                ['label' => 'Loyal (' . currency_symbol() . '5k–10k)',      'min' => 5000,  'max' => 10000,       'color' => 'bg-blue-400'],
                ['label' => 'Regular (' . currency_symbol() . '1k–5k)',     'min' => 1000,  'max' => 5000,        'color' => 'bg-indigo-400'],
                ['label' => 'New/Small (<' . currency_symbol() . '1k)',      'min' => 0,     'max' => 1000,        'color' => 'bg-gray-300'],
            ];
            $totalTop = $topLtv->count() ?: 1;
        @endphp
        <div class="space-y-3">
            @foreach($segments as $seg)
            @php $cnt = $topLtv->filter(fn($c) => $c->ltv >= $seg['min'] && $c->ltv < $seg['max'])->count(); $pct = round(($cnt/$totalTop)*100); @endphp
            <div>
                <div class="flex justify-between text-sm mb-0.5">
                    <span class="text-gray-700">{{ $seg['label'] }}</span>
                    <span class="font-semibold">{{ $cnt }} <span class="text-gray-400 font-normal text-xs">({{ $pct }}%)</span></span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded overflow-hidden">
                    <div class="h-2 {{ $seg['color'] }} rounded" style="width:{{ $pct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </x-admin.card>

    {{-- Cohort --}}
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-4">Monthly Cohorts (last 12 months)</h3>
        @if($cohorts->isEmpty())
            <p class="text-sm text-gray-400 py-6 text-center">No cohort data.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead><tr class="border-b text-gray-400 uppercase">
                    <th class="pb-1 text-left">Cohort</th>
                    <th class="pb-1 text-right">Signups</th>
                    <th class="pb-1 text-right">Buyers</th>
                    <th class="pb-1 text-right">Conv.</th>
                    <th class="pb-1 text-right">Revenue</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($cohorts as $row)
                    @php $conv = $row->new_customers > 0 ? round(($row->buyers/$row->new_customers)*100,0) : 0; @endphp
                    <tr>
                        <td class="py-1.5 font-medium">{{ $row->cohort }}</td>
                        <td class="py-1.5 text-right">{{ number_format($row->new_customers) }}</td>
                        <td class="py-1.5 text-right">{{ number_format($row->buyers) }}</td>
                        <td class="py-1.5 text-right {{ $conv >= 50 ? 'text-green-600' : ($conv >= 20 ? 'text-yellow-600' : 'text-red-500') }} font-semibold">{{ $conv }}%</td>
                        <td class="py-1.5 text-right">{{ money($row->revenue, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-admin.card>
</div>

{{-- Top LTV customers --}}
<x-admin.card>
    <h3 class="font-semibold text-gray-800 mb-4">Top 50 Customers by Lifetime Value</h3>
    @if($topLtv->isEmpty())
        <p class="text-sm text-gray-400 py-8 text-center">No customer purchase data yet.</p>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b text-xs text-gray-400 uppercase">
                <th class="pb-2 text-left">#</th>
                <th class="pb-2 text-left">Customer</th>
                <th class="pb-2 text-right">Orders</th>
                <th class="pb-2 text-right">LTV</th>
                <th class="pb-2 text-right">Avg Order</th>
                <th class="pb-2 text-right">First Order</th>
                <th class="pb-2 text-right">Last Order</th>
                <th class="pb-2 text-right">Days Active</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($topLtv as $i => $c)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 text-gray-400 text-xs">{{ $i+1 }}</td>
                    <td class="py-2">
                        <a href="{{ route('admin.users.show', $c->id) }}" class="font-medium text-blue-600 hover:underline">{{ $c->name }}</a>
                        <div class="text-xs text-gray-400">{{ $c->email }}</div>
                    </td>
                    <td class="py-2 text-right font-semibold">{{ $c->order_count }}</td>
                    <td class="py-2 text-right font-bold text-green-700">{{ money($c->ltv, 0) }}</td>
                    <td class="py-2 text-right text-gray-600">{{ money($c->avg_order, 0) }}</td>
                    <td class="py-2 text-right text-xs text-gray-400">{{ \Carbon\Carbon::parse($c->first_order)->format('d M y') }}</td>
                    <td class="py-2 text-right text-xs text-gray-400">{{ \Carbon\Carbon::parse($c->last_order)->format('d M y') }}</td>
                    <td class="py-2 text-right text-xs text-gray-500">{{ number_format($c->days_active) }}d</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-admin.card>
@endsection

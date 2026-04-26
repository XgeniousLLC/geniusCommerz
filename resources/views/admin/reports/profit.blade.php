@extends('admin.layouts.admin')
@section('title', 'Profit Analysis')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.reports.sales') }}" class="hover:text-gray-700">Reports</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Profit Analysis</li>
</ol>
@endsection

@section('page-header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Profit Analysis</h1>
        <p class="text-sm text-gray-500 mt-1">Revenue, COGS, gross profit and margin breakdown.</p>
    </div>
</div>
@endsection

@section('content')
{{-- Filters --}}
<form method="GET" class="flex flex-wrap items-end gap-3 mb-6">
    <div>
        <label class="text-xs text-gray-500 block mb-1">Period</label>
        <select name="period" onchange="this.form.submit()" class="border border-gray-200 rounded px-3 py-2 text-sm">
            @foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year'] as $val => $lbl)
                <option value="{{ $val }}" {{ $request->input('period','month') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
            <option value="custom" {{ $request->input('period') === 'custom' ? 'selected' : '' }}>Custom</option>
        </select>
    </div>
    <div>
        <label class="text-xs text-gray-500 block mb-1">From</label>
        <input type="date" name="start_date" value="{{ $request->input('start_date', $startDate->format('Y-m-d')) }}" class="border border-gray-200 rounded px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-xs text-gray-500 block mb-1">To</label>
        <input type="date" name="end_date" value="{{ $request->input('end_date', $endDate->format('Y-m-d')) }}" class="border border-gray-200 rounded px-3 py-2 text-sm">
    </div>
    <button type="submit" class="kb-btn kb-btn-primary text-sm px-4 py-2">Apply</button>
</form>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $cards = [
            ['label' => 'Total Revenue',   'value' => '৳'.number_format($revenue,0),      'color' => 'blue'],
            ['label' => 'COGS',            'value' => '৳'.number_format($cogs,0),          'color' => 'red'],
            ['label' => 'Gross Profit',    'value' => '৳'.number_format($grossProfit,0),   'color' => 'green'],
            ['label' => 'Gross Margin',    'value' => $margin.'%',                          'color' => 'purple'],
        ];
    @endphp
    @foreach($cards as $card)
    <x-admin.card class="p-5">
        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">{{ $card['label'] }}</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $card['value'] }}</p>
    </x-admin.card>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-6">
    {{-- Monthly breakdown --}}
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-4">Monthly Revenue vs Discounts</h3>
        @if($byMonth->isEmpty())
            <p class="text-sm text-gray-400 py-6 text-center">No data for this period.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-100 text-xs text-gray-500 uppercase">
                    <th class="pb-2 text-left">Month</th>
                    <th class="pb-2 text-right">Revenue</th>
                    <th class="pb-2 text-right">Discount</th>
                    <th class="pb-2 text-right">Shipping</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($byMonth as $row)
                    <tr>
                        <td class="py-2">{{ $row->month }}</td>
                        <td class="py-2 text-right font-medium">৳{{ number_format($row->revenue,0) }}</td>
                        <td class="py-2 text-right text-red-500">-৳{{ number_format($row->discount,0) }}</td>
                        <td class="py-2 text-right text-blue-500">+৳{{ number_format($row->shipping,0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-admin.card>

    {{-- Summary box --}}
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-4">P&L Summary</h3>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-gray-600">Gross Revenue</span><span class="font-semibold">৳{{ number_format($revenue,0) }}</span></div>
            <div class="flex justify-between text-red-600"><span>Cost of Goods (COGS)</span><span>-৳{{ number_format($cogs,0) }}</span></div>
            <div class="flex justify-between font-bold border-t pt-3"><span>Gross Profit</span><span class="text-green-700">৳{{ number_format($grossProfit,0) }}</span></div>
            <div class="flex justify-between text-red-600"><span>Discounts Given</span><span>-৳{{ number_format($discount,0) }}</span></div>
            <div class="flex justify-between text-blue-600"><span>Shipping Collected</span><span>+৳{{ number_format($shipping,0) }}</span></div>
            <div class="flex justify-between font-bold border-t pt-3 text-lg"><span>Est. Net Profit</span><span class="{{ $netProfit >= 0 ? 'text-green-700' : 'text-red-600' }}">৳{{ number_format($netProfit,0) }}</span></div>
            <p class="text-xs text-gray-400 mt-2">* COGS only calculated for products with cost price set. Net profit is an estimate.</p>
        </div>
    </x-admin.card>
</div>

{{-- Top margin products --}}
<x-admin.card>
    <h3 class="font-semibold text-gray-800 mb-4">Most Profitable Products (by gross profit)</h3>
    @if($topMargin->isEmpty())
        <p class="text-sm text-gray-400 py-6 text-center">No products with cost price set for this period.</p>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-gray-100 text-xs text-gray-500 uppercase">
                <th class="pb-2 text-left">Product</th>
                <th class="pb-2 text-right">Revenue</th>
                <th class="pb-2 text-right">Cost</th>
                <th class="pb-2 text-right">Profit</th>
                <th class="pb-2 text-right">Margin %</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($topMargin as $p)
                <tr>
                    <td class="py-2 max-w-xs truncate">{{ $p->product_name }}</td>
                    <td class="py-2 text-right">৳{{ number_format($p->revenue,0) }}</td>
                    <td class="py-2 text-right text-red-500">৳{{ number_format($p->cost,0) }}</td>
                    <td class="py-2 text-right font-semibold text-green-700">৳{{ number_format($p->profit,0) }}</td>
                    <td class="py-2 text-right">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $p->margin_pct >= 30 ? 'bg-green-100 text-green-700' : ($p->margin_pct >= 10 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                            {{ number_format($p->margin_pct,1) }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-admin.card>
@endsection

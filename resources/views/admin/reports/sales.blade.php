@extends('admin.layouts.admin')

@section('title', 'Sales Report')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><span class="text-gray-400">Reports</span></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Sales</li>
</ol>
@endsection

@section('page-header')
<div class="flex items-center justify-between flex-wrap gap-4">
    <h1 class="text-2xl font-bold text-gray-900">Sales Report</h1>
    <div class="flex gap-2">
        @foreach([
            ['href' => route('admin.reports.inventory'),  'label' => 'Inventory'],
            ['href' => route('admin.reports.customers'),  'label' => 'Customers'],
            ['href' => route('admin.reports.orders'),     'label' => 'Orders'],
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
        <a href="{{ route('admin.reports.export', array_merge(request()->all(), ['type' => 'sales'])) }}"
           class="ml-auto text-sm flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:border-green-400 hover:text-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
    </form>
</x-admin.card>

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['label' => 'Total Revenue',     'value' => '৳' . number_format($stats['total_revenue'], 0),  'color' => 'text-green-600'],
        ['label' => 'Total Orders',      'value' => number_format($stats['total_orders']),             'color' => 'text-blue-600'],
        ['label' => 'Avg Order Value',   'value' => '৳' . number_format($stats['avg_order_value'], 0), 'color' => 'text-indigo-600'],
        ['label' => 'Items Sold',        'value' => number_format($stats['total_items_sold']),          'color' => 'text-purple-600'],
    ] as $s)
    <x-admin.card class="text-center">
        <div class="text-2xl font-extrabold {{ $s['color'] }}">{{ $s['value'] }}</div>
        <div class="text-xs text-gray-500 mt-1">{{ $s['label'] }}</div>
    </x-admin.card>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Chart placeholder + data table --}}
    <div class="lg:col-span-2 space-y-6">
        <x-admin.card>
            <h2 class="text-base font-semibold text-gray-900 mb-4">Revenue Over Time</h2>
            @if(empty($chartData))
                <p class="text-center text-gray-400 py-8 text-sm">No data for this period.</p>
            @else
                {{-- Simple bar chart using CSS --}}
                @php $maxRev = max(array_column($chartData, 'revenue')) ?: 1; @endphp
                <div class="flex items-end gap-1 h-32 overflow-x-auto pb-2">
                    @foreach($chartData as $row)
                    <div class="flex flex-col items-center gap-1 min-w-[28px] flex-1 group relative">
                        <div class="w-full bg-blue-500 rounded-t hover:bg-blue-600 transition-colors cursor-default"
                             style="height: {{ max(2, round(($row['revenue'] / $maxRev) * 100)) }}%"
                             title="{{ $row['date'] }}: ৳{{ number_format($row['revenue']) }} ({{ $row['orders'] }} orders)">
                        </div>
                        <span class="text-[9px] text-gray-400 truncate w-full text-center">{{ substr($row['date'], -5) }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-xs text-gray-500 font-semibold uppercase tracking-wide">
                                <th class="pb-2 pr-4">Period</th>
                                <th class="pb-2 pr-4">Orders</th>
                                <th class="pb-2">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach(array_slice($chartData, -10) as $row)
                            <tr>
                                <td class="py-2 pr-4 text-gray-700 font-mono text-xs">{{ $row['date'] }}</td>
                                <td class="py-2 pr-4 text-gray-700">{{ $row['orders'] }}</td>
                                <td class="py-2 font-semibold text-gray-900">৳{{ number_format($row['revenue']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin.card>

        {{-- Top products --}}
        <x-admin.card>
            <h2 class="text-base font-semibold text-gray-900 mb-4">Top Products by Revenue</h2>
            @if($topProducts->isEmpty())
                <p class="text-center text-gray-400 py-4 text-sm">No data.</p>
            @else
                <div class="space-y-3">
                    @foreach($topProducts as $i => $p)
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-gray-400 w-5">{{ $i + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $p->product_name }}</p>
                            <p class="text-xs text-gray-500">{{ $p->qty_sold }} units sold</p>
                        </div>
                        <span class="font-bold text-gray-900 shrink-0">৳{{ number_format($p->revenue) }}</span>
                    </div>
                    @endforeach
                </div>
            @endif
        </x-admin.card>
    </div>

    {{-- Right: Payment method breakdown --}}
    <div>
        <x-admin.card>
            <h2 class="text-base font-semibold text-gray-900 mb-4">Revenue by Payment Method</h2>
            @if($revenueByPaymentMethod->isEmpty())
                <p class="text-center text-gray-400 py-4 text-sm">No data.</p>
            @else
                @php $totalRev = $revenueByPaymentMethod->sum('revenue') ?: 1; @endphp
                <div class="space-y-3">
                    @foreach($revenueByPaymentMethod as $row)
                    @php $pct = round(($row->revenue / $totalRev) * 100); @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="capitalize text-gray-700 font-medium">{{ str_replace('_', ' ', $row->payment_method) }}</span>
                            <span class="font-bold text-gray-900">৳{{ number_format($row->revenue) }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ $row->orders }} orders · {{ $pct }}%</div>
                    </div>
                    @endforeach
                </div>
            @endif
        </x-admin.card>
    </div>
</div>
@endsection

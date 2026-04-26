@extends('admin.layouts.admin')
@section('title', 'Payment Trends')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.reports.sales') }}" class="hover:text-gray-700">Reports</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Payment Trends</li>
</ol>
@endsection

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Payment Trends</h1>
    <p class="text-sm text-gray-500 mt-1">Payment method usage, payment status, and revenue by payment channel.</p>
</div>
@endsection

@section('content')
<form method="GET" class="flex flex-wrap items-end gap-3 mb-6">
    <div>
        <label class="text-xs text-gray-500 block mb-1">Period</label>
        <select name="period" onchange="this.form.submit()" class="border border-gray-200 rounded px-3 py-2 text-sm">
            @foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year'] as $val=>$lbl)
                <option value="{{ $val }}" {{ $request->input('period','month')===$val?'selected':'' }}>{{ $lbl }}</option>
            @endforeach
            <option value="custom" {{ $request->input('period')==='custom'?'selected':'' }}>Custom</option>
        </select>
    </div>
    <div><label class="text-xs text-gray-500 block mb-1">From</label><input type="date" name="start_date" value="{{ $request->input('start_date',$startDate->format('Y-m-d')) }}" class="border border-gray-200 rounded px-3 py-2 text-sm"></div>
    <div><label class="text-xs text-gray-500 block mb-1">To</label><input type="date" name="end_date" value="{{ $request->input('end_date',$endDate->format('Y-m-d')) }}" class="border border-gray-200 rounded px-3 py-2 text-sm"></div>
    <button type="submit" class="kb-btn kb-btn-primary text-sm px-4 py-2">Apply</button>
</form>

@php $totalOrders = $byMethod->sum('orders') ?: 1; $totalRev = $byMethod->sum('revenue') ?: 1; @endphp

<div class="grid lg:grid-cols-2 gap-6 mb-6">
    {{-- Payment method breakdown --}}
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-4">Revenue by Payment Method</h3>
        @if($byMethod->isEmpty())
            <p class="text-sm text-gray-400 py-8 text-center">No orders in this period.</p>
        @else
        <div class="space-y-3">
            @foreach($byMethod as $row)
            @php $pct = round(($row->revenue/$totalRev)*100,1); @endphp
            <div>
                <div class="flex justify-between text-sm mb-0.5">
                    <span class="font-medium text-gray-700 capitalize">{{ str_replace('_',' ',$row->method) }}</span>
                    <span>৳{{ number_format($row->revenue,0) }} <span class="text-xs text-gray-400">({{ $pct }}%)</span></span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded overflow-hidden">
                    <div class="h-2 bg-blue-400 rounded" style="width:{{ $pct }}%"></div>
                </div>
                <div class="flex gap-4 text-xs text-gray-400 mt-0.5">
                    <span>{{ number_format($row->orders) }} orders</span>
                    <span>AOV: ৳{{ number_format($row->avg_order,0) }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </x-admin.card>

    {{-- Payment status --}}
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-4">Payment Status Breakdown</h3>
        @php
            $statusColors = ['unpaid'=>'red','paid'=>'green','partially_refunded'=>'yellow','refunded'=>'blue'];
            $statusTotal = $paymentStatus->sum('count') ?: 1;
        @endphp
        <div class="space-y-3">
            @foreach($paymentStatus as $row)
            @php $pct = round(($row->count/$statusTotal)*100,1); @endphp
            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full {{ $row->payment_status==='paid'?'bg-green-400':($row->payment_status==='unpaid'?'bg-red-400':($row->payment_status==='refunded'?'bg-blue-400':'bg-yellow-400')) }}"></span>
                    <span class="text-sm font-medium capitalize text-gray-700">{{ str_replace('_',' ',$row->payment_status) }}</span>
                </div>
                <div class="text-right">
                    <span class="text-sm font-bold">{{ number_format($row->count) }}</span>
                    <span class="text-xs text-gray-400 ml-1">({{ $pct }}%)</span>
                    <div class="text-xs text-gray-400">৳{{ number_format($row->revenue,0) }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </x-admin.card>
</div>

{{-- Payment method table --}}
<x-admin.card>
    <h3 class="font-semibold text-gray-800 mb-4">Detailed Payment Method Stats</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b text-xs text-gray-400 uppercase">
                <th class="pb-2 text-left">Method</th>
                <th class="pb-2 text-right">Orders</th>
                <th class="pb-2 text-right">% of Orders</th>
                <th class="pb-2 text-right">Revenue</th>
                <th class="pb-2 text-right">% of Revenue</th>
                <th class="pb-2 text-right">Avg Order Value</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($byMethod as $row)
                @php
                    $orderPct = round(($row->orders/$totalOrders)*100,1);
                    $revPct = round(($row->revenue/$totalRev)*100,1);
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="py-2 font-medium capitalize">{{ str_replace('_',' ',$row->method) }}</td>
                    <td class="py-2 text-right">{{ number_format($row->orders) }}</td>
                    <td class="py-2 text-right text-gray-500">{{ $orderPct }}%</td>
                    <td class="py-2 text-right font-semibold">৳{{ number_format($row->revenue,0) }}</td>
                    <td class="py-2 text-right text-gray-500">{{ $revPct }}%</td>
                    <td class="py-2 text-right text-gray-600">৳{{ number_format($row->avg_order,0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin.card>
@endsection

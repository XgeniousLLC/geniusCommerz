@extends('admin.layouts.admin')
@section('title', 'Geographic Sales')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.reports.sales') }}" class="hover:text-gray-700">Reports</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Geographic Sales</li>
</ol>
@endsection

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Geographic Sales Distribution</h1>
    <p class="text-sm text-gray-500 mt-1">Revenue and order counts by city and state/division.</p>
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

<div class="grid lg:grid-cols-2 gap-6">
    {{-- By City --}}
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-4">Top Cities by Revenue (top 30)</h3>
        @if($byCity->isEmpty())
            <p class="text-sm text-gray-400 py-8 text-center">No geographic data available. Ensure shipping addresses include city.</p>
        @else
        @php $maxCity = $byCity->max('revenue') ?: 1; @endphp
        <div class="space-y-2 max-h-96 overflow-y-auto">
            @foreach($byCity as $i => $row)
            @php $pct = $totalRevenue > 0 ? round(($row->revenue/$totalRevenue)*100,1) : 0; @endphp
            <div class="flex items-center gap-2">
                <span class="w-5 text-xs text-gray-400 font-mono">{{ $i+1 }}</span>
                <div class="flex-1">
                    <div class="flex justify-between text-sm mb-0.5">
                        <span class="font-medium text-gray-700">{{ $row->city }}</span>
                        <span class="font-semibold">৳{{ number_format($row->revenue,0) }} <span class="text-xs text-gray-400">({{ $pct }}%)</span></span>
                    </div>
                    <div class="w-full h-1.5 bg-gray-100 rounded overflow-hidden">
                        <div class="h-1.5 bg-blue-400 rounded" style="width:{{ min(100, round(($row->revenue/$maxCity)*100)) }}%"></div>
                    </div>
                    <span class="text-xs text-gray-400">{{ number_format($row->orders) }} orders • AOV ৳{{ number_format($row->avg_order,0) }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </x-admin.card>

    {{-- By State/Division --}}
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-4">Revenue by State / Division</h3>
        @if($byState->isEmpty())
            <p class="text-sm text-gray-400 py-8 text-center">No state data available.</p>
        @else
        @php $maxState = $byState->max('revenue') ?: 1; $stateTotalRev = $byState->sum('revenue') ?: 1; @endphp
        <div class="space-y-2">
            @foreach($byState as $row)
            @php $pct = round(($row->revenue/$stateTotalRev)*100,1); @endphp
            <div>
                <div class="flex justify-between text-sm mb-0.5">
                    <span class="font-medium text-gray-700">{{ $row->state }}</span>
                    <span class="font-semibold">৳{{ number_format($row->revenue,0) }} <span class="text-xs text-gray-400">({{ $pct }}%)</span></span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded overflow-hidden">
                    <div class="h-2 bg-indigo-400 rounded" style="width:{{ $pct }}%"></div>
                </div>
                <span class="text-xs text-gray-400">{{ number_format($row->orders) }} orders</span>
            </div>
            @endforeach
        </div>
        @endif
    </x-admin.card>
</div>

@if($byCity->isNotEmpty())
<x-admin.card class="mt-6">
    <h3 class="font-semibold text-gray-800 mb-4">City Details Table</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b text-xs text-gray-400 uppercase">
                <th class="pb-2 text-left">#</th>
                <th class="pb-2 text-left">City</th>
                <th class="pb-2 text-right">Orders</th>
                <th class="pb-2 text-right">Revenue</th>
                <th class="pb-2 text-right">Avg Order</th>
                <th class="pb-2 text-right">% of Revenue</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($byCity as $i => $row)
                @php $pct = $totalRevenue > 0 ? round(($row->revenue/$totalRevenue)*100,2) : 0; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="py-2 text-gray-400 text-xs">{{ $i+1 }}</td>
                    <td class="py-2 font-medium">{{ $row->city }}</td>
                    <td class="py-2 text-right">{{ number_format($row->orders) }}</td>
                    <td class="py-2 text-right font-semibold">৳{{ number_format($row->revenue,0) }}</td>
                    <td class="py-2 text-right text-gray-500">৳{{ number_format($row->avg_order,0) }}</td>
                    <td class="py-2 text-right text-gray-500">{{ $pct }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin.card>
@endif
@endsection

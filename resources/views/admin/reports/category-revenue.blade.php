@extends('admin.layouts.admin')
@section('title', 'Category & Brand Revenue')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.reports.sales') }}" class="hover:text-gray-700">Reports</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Category & Brand Revenue</li>
</ol>
@endsection

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Category & Brand Revenue</h1>
    <p class="text-sm text-gray-500 mt-1">Revenue breakdown by product category and brand.</p>
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

<div class="grid lg:grid-cols-2 gap-6">
    {{-- By Category --}}
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-4">Revenue by Category</h3>
        @if($byCategory->isEmpty())
            <p class="text-sm text-gray-400 py-8 text-center">No category-linked orders in this period.</p>
        @else
        @php $maxCat = $byCategory->max('revenue') ?: 1; @endphp
        <div class="space-y-3">
            @foreach($byCategory as $row)
            @php $pct = $totalRevenue > 0 ? round(($row->revenue/$totalRevenue)*100,1) : 0; @endphp
            <div>
                <div class="flex justify-between text-sm mb-0.5">
                    <span class="font-medium text-gray-700">{{ $row->category }}</span>
                    <span class="font-semibold">৳{{ number_format($row->revenue,0) }} <span class="text-gray-400 font-normal text-xs">({{ $pct }}%)</span></span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-2 bg-blue-400 rounded-full" style="width:{{ min(100,$pct) }}%"></div>
                </div>
                <div class="flex gap-4 text-xs text-gray-400 mt-0.5">
                    <span>{{ number_format($row->qty_sold) }} units</span>
                    <span>{{ number_format($row->order_count) }} orders</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </x-admin.card>

    {{-- By Brand --}}
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-4">Revenue by Brand</h3>
        @if($byBrand->isEmpty())
            <p class="text-sm text-gray-400 py-8 text-center">No brand-linked orders in this period.</p>
        @else
        @php $maxBrand = $byBrand->max('revenue') ?: 1; @endphp
        <div class="space-y-3">
            @foreach($byBrand as $row)
            @php $pct = $totalRevenue > 0 ? round(($row->revenue/$totalRevenue)*100,1) : 0; @endphp
            <div>
                <div class="flex justify-between text-sm mb-0.5">
                    <span class="font-medium text-gray-700">{{ $row->brand }}</span>
                    <span class="font-semibold">৳{{ number_format($row->revenue,0) }} <span class="text-gray-400 font-normal text-xs">({{ $pct }}%)</span></span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-2 bg-purple-400 rounded-full" style="width:{{ min(100,$pct) }}%"></div>
                </div>
                <div class="flex gap-4 text-xs text-gray-400 mt-0.5">
                    <span>{{ number_format($row->qty_sold) }} units</span>
                    <span>{{ number_format($row->order_count) }} orders</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </x-admin.card>
</div>

{{-- Combined table --}}
<div class="grid lg:grid-cols-2 gap-6 mt-6">
    @if($byCategory->isNotEmpty())
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-3">Category Breakdown Table</h3>
        <table class="w-full text-sm">
            <thead><tr class="border-b text-xs text-gray-400 uppercase">
                <th class="pb-2 text-left">Category</th>
                <th class="pb-2 text-right">Units</th>
                <th class="pb-2 text-right">Orders</th>
                <th class="pb-2 text-right">Revenue</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($byCategory as $row)
                <tr class="hover:bg-gray-50">
                    <td class="py-2">{{ $row->category }}</td>
                    <td class="py-2 text-right">{{ number_format($row->qty_sold) }}</td>
                    <td class="py-2 text-right">{{ number_format($row->order_count) }}</td>
                    <td class="py-2 text-right font-semibold">৳{{ number_format($row->revenue,0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
    @endif
    @if($byBrand->isNotEmpty())
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-3">Brand Breakdown Table</h3>
        <table class="w-full text-sm">
            <thead><tr class="border-b text-xs text-gray-400 uppercase">
                <th class="pb-2 text-left">Brand</th>
                <th class="pb-2 text-right">Units</th>
                <th class="pb-2 text-right">Orders</th>
                <th class="pb-2 text-right">Revenue</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($byBrand as $row)
                <tr class="hover:bg-gray-50">
                    <td class="py-2">{{ $row->brand }}</td>
                    <td class="py-2 text-right">{{ number_format($row->qty_sold) }}</td>
                    <td class="py-2 text-right">{{ number_format($row->order_count) }}</td>
                    <td class="py-2 text-right font-semibold">৳{{ number_format($row->revenue,0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
    @endif
</div>
@endsection

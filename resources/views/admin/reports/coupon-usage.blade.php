@extends('admin.layouts.admin')
@section('title', 'Coupon Usage Report')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.reports.sales') }}" class="hover:text-gray-700">Reports</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Coupon Usage</li>
</ol>
@endsection

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Coupon Usage Report</h1>
    <p class="text-sm text-gray-500 mt-1">Discount usage, coupon redemption rates, and revenue impact.</p>
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

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php $couponRate = $stats['all_orders'] > 0 ? round(($stats['total_coupon_orders']/$stats['all_orders'])*100,1) : 0; @endphp
    <x-admin.card class="p-5"><p class="text-xs text-gray-500 uppercase tracking-wide">Coupon Orders</p><p class="text-2xl font-bold mt-1">{{ number_format($stats['total_coupon_orders']) }}</p></x-admin.card>
    <x-admin.card class="p-5"><p class="text-xs text-gray-500 uppercase tracking-wide">Usage Rate</p><p class="text-2xl font-bold mt-1">{{ $couponRate }}%</p></x-admin.card>
    <x-admin.card class="p-5"><p class="text-xs text-gray-500 uppercase tracking-wide">Total Discount Given</p><p class="text-2xl font-bold mt-1">৳{{ number_format($stats['total_discount_given'],0) }}</p></x-admin.card>
    <x-admin.card class="p-5"><p class="text-xs text-gray-500 uppercase tracking-wide">Unique Coupons Used</p><p class="text-2xl font-bold mt-1">{{ $stats['unique_coupons_used'] }}</p></x-admin.card>
</div>

<x-admin.card>
    <h3 class="font-semibold text-gray-800 mb-4">Coupon Performance</h3>
    @if($coupons->isEmpty())
        <p class="text-sm text-gray-400 py-8 text-center">No coupons used in this period.</p>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b text-xs text-gray-400 uppercase">
                <th class="pb-2 text-left">Coupon Code</th>
                <th class="pb-2 text-right">Times Used</th>
                <th class="pb-2 text-right">Total Discount</th>
                <th class="pb-2 text-right">Revenue Generated</th>
                <th class="pb-2 text-right">Avg Order Value</th>
                <th class="pb-2 text-right">Avg Discount/Order</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($coupons as $row)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 font-mono font-semibold text-blue-700">{{ $row->coupon_code }}</td>
                    <td class="py-2 text-right font-semibold">{{ number_format($row->times_used) }}</td>
                    <td class="py-2 text-right text-red-600">৳{{ number_format($row->total_discount,0) }}</td>
                    <td class="py-2 text-right font-medium">৳{{ number_format($row->revenue_after_discount,0) }}</td>
                    <td class="py-2 text-right text-gray-500">৳{{ number_format($row->avg_order,0) }}</td>
                    <td class="py-2 text-right text-gray-500">৳{{ $row->times_used > 0 ? number_format($row->total_discount / $row->times_used, 0) : '0' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-admin.card>
@endsection

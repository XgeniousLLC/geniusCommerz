@extends('admin.layouts.admin')
@section('title', 'Refund Analysis')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.reports.sales') }}" class="hover:text-gray-700">Reports</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Refund Analysis</li>
</ol>
@endsection

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Refund Analysis</h1>
    <p class="text-sm text-gray-500 mt-1">Refund requests by reason, status, and financial impact.</p>
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

@php
    $refundRate = $stats['total_orders'] > 0 ? round(($stats['total_requests']/$stats['total_orders'])*100,2) : 0;
    $statusColors = ['pending'=>'yellow','approved'=>'blue','rejected'=>'red','processed'=>'green'];
@endphp

{{-- KPI Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-admin.card class="p-5"><p class="text-xs text-gray-500 uppercase tracking-wide">Total Requests</p><p class="text-2xl font-bold mt-1">{{ number_format($stats['total_requests']) }}</p></x-admin.card>
    <x-admin.card class="p-5"><p class="text-xs text-gray-500 uppercase tracking-wide">Refund Rate</p><p class="text-2xl font-bold mt-1">{{ $refundRate }}%</p></x-admin.card>
    <x-admin.card class="p-5"><p class="text-xs text-gray-500 uppercase tracking-wide">Total Refunded</p><p class="text-2xl font-bold mt-1 text-red-600">৳{{ number_format($stats['total_refunded'],0) }}</p></x-admin.card>
    <x-admin.card class="p-5"><p class="text-xs text-gray-500 uppercase tracking-wide">Pending Review</p><p class="text-2xl font-bold mt-1 text-yellow-600">{{ number_format($stats['pending']) }}</p></x-admin.card>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-6">
    {{-- By Status --}}
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-4">Requests by Status</h3>
        <div class="space-y-3">
            @foreach(['pending','approved','rejected','processed'] as $s)
            @php $row = $byStatus->get($s); $cnt = $row?->count ?? 0; $amt = $row?->total_amount ?? 0; @endphp
            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full {{ $s==='pending'?'bg-yellow-400':($s==='approved'?'bg-blue-400':($s==='rejected'?'bg-red-400':'bg-green-400')) }}"></span>
                    <span class="text-sm font-medium capitalize text-gray-700">{{ $s }}</span>
                </div>
                <div class="text-right">
                    <span class="text-sm font-bold text-gray-900">{{ $cnt }}</span>
                    <span class="text-xs text-gray-400 ml-2">৳{{ number_format($amt,0) }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </x-admin.card>

    {{-- By Reason --}}
    <x-admin.card>
        <h3 class="font-semibold text-gray-800 mb-4">Top Refund Reasons</h3>
        @if($byReason->isEmpty())
            <p class="text-sm text-gray-400 py-6 text-center">No refunds in this period.</p>
        @else
        @php $maxReason = $byReason->max('count') ?: 1; @endphp
        <div class="space-y-3">
            @foreach($byReason as $row)
            @php $pct = round(($row->count/$maxReason)*100); @endphp
            <div>
                <div class="flex justify-between text-sm mb-0.5">
                    <span class="text-gray-700 capitalize">{{ str_replace('_',' ',$row->reason) }}</span>
                    <span class="font-semibold">{{ $row->count }} <span class="text-xs text-gray-400">avg {{ round($row->avg_resolution_hours ?? 0) }}h</span></span>
                </div>
                <div class="w-full h-2 bg-gray-100 rounded overflow-hidden">
                    <div class="h-2 bg-red-300 rounded" style="width:{{ $pct }}%"></div>
                </div>
                <span class="text-xs text-gray-400">৳{{ number_format($row->total_amount,0) }} total</span>
            </div>
            @endforeach
        </div>
        @endif
    </x-admin.card>
</div>

{{-- Recent refunds --}}
@if($recent->isNotEmpty())
<x-admin.card>
    <h3 class="font-semibold text-gray-800 mb-4">Recent Refund Requests</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b text-xs text-gray-400 uppercase">
                <th class="pb-2 text-left">Order</th>
                <th class="pb-2 text-left">Customer</th>
                <th class="pb-2 text-left">Reason</th>
                <th class="pb-2 text-right">Amount</th>
                <th class="pb-2 text-center">Status</th>
                <th class="pb-2 text-right">Date</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($recent as $r)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 font-mono text-xs"><a href="{{ route('admin.refunds.show',$r->id) }}" class="text-blue-600 hover:underline">#{{ $r->order_number }}</a></td>
                    <td class="py-2">{{ $r->customer_name }}</td>
                    <td class="py-2 capitalize text-gray-600">{{ str_replace('_',' ',$r->reason) }}</td>
                    <td class="py-2 text-right">{{ $r->amount ? '৳'.number_format($r->amount,0) : '—' }}</td>
                    <td class="py-2 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                            {{ $r->status==='processed'?'bg-green-100 text-green-700':($r->status==='approved'?'bg-blue-100 text-blue-700':($r->status==='rejected'?'bg-red-100 text-red-700':'bg-yellow-100 text-yellow-700')) }}">
                            {{ $r->status }}
                        </span>
                    </td>
                    <td class="py-2 text-right text-xs text-gray-400">{{ \Carbon\Carbon::parse($r->created_at)->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin.card>
@endif
@endsection

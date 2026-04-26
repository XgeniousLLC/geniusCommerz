@extends('admin.layouts.admin')
@section('title', 'Top Products Report')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.reports.sales') }}" class="hover:text-gray-700">Reports</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Top Products</li>
</ol>
@endsection

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Top Products</h1>
    <p class="text-sm text-gray-500 mt-1">Best performing products by revenue, quantity sold, and profit margin.</p>
</div>
@endsection

@section('content')
<form method="GET" class="flex flex-wrap items-end gap-3 mb-6">
    <div>
        <label class="text-xs text-gray-500 block mb-1">Period</label>
        <select name="period" onchange="this.form.submit()" class="border border-gray-200 rounded px-3 py-2 text-sm">
            @foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year'] as $val=>$lbl)
                <option value="{{ $val }}" {{ $request->input('period','month') === $val ? 'selected':'' }}>{{ $lbl }}</option>
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
    <div>
        <label class="text-xs text-gray-500 block mb-1">Sort by</label>
        <select name="sort" onchange="this.form.submit()" class="border border-gray-200 rounded px-3 py-2 text-sm">
            <option value="revenue" {{ $sortBy==='revenue'?'selected':'' }}>Revenue</option>
            <option value="qty"     {{ $sortBy==='qty'?'selected':'' }}>Quantity Sold</option>
            <option value="orders"  {{ $sortBy==='orders'?'selected':'' }}>Order Count</option>
        </select>
    </div>
    <button type="submit" class="kb-btn kb-btn-primary text-sm px-4 py-2">Apply</button>
</form>

<x-admin.card>
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-800">Top 50 Products</h3>
        <span class="text-sm text-gray-500">Total revenue: ৳{{ number_format($totalRevenue,0) }}</span>
    </div>
    @if($products->isEmpty())
        <p class="text-sm text-gray-400 py-8 text-center">No orders in this period.</p>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="border-b border-gray-100 text-xs text-gray-500 uppercase">
                <th class="pb-2 text-left">#</th>
                <th class="pb-2 text-left">Product</th>
                <th class="pb-2 text-right">Qty Sold</th>
                <th class="pb-2 text-right">Revenue</th>
                <th class="pb-2 text-right">Avg Price</th>
                <th class="pb-2 text-right">Orders</th>
                <th class="pb-2 text-right">Profit</th>
                <th class="pb-2 text-right">Margin</th>
                <th class="pb-2 text-right">% of Total</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($products as $i => $p)
                @php $pct = $totalRevenue > 0 ? round(($p->revenue / $totalRevenue) * 100, 1) : 0; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="py-2 text-gray-400 text-xs">{{ $i+1 }}</td>
                    <td class="py-2 max-w-[220px]">
                        <div class="truncate font-medium text-gray-800">{{ $p->product_name }}</div>
                        @if($p->sku)<div class="text-xs text-gray-400">{{ $p->sku }}</div>@endif
                    </td>
                    <td class="py-2 text-right font-semibold {{ $sortBy==='qty'?'text-blue-700':'' }}">{{ number_format($p->qty_sold) }}</td>
                    <td class="py-2 text-right font-semibold {{ $sortBy==='revenue'?'text-blue-700':'' }}">৳{{ number_format($p->revenue,0) }}</td>
                    <td class="py-2 text-right text-gray-500">৳{{ number_format($p->avg_price,0) }}</td>
                    <td class="py-2 text-right {{ $sortBy==='orders'?'text-blue-700':'' }}">{{ number_format($p->order_count) }}</td>
                    <td class="py-2 text-right {{ is_null($p->profit) ? 'text-gray-300' : 'text-green-700 font-medium' }}">
                        {{ is_null($p->profit) ? '—' : '৳'.number_format($p->profit,0) }}
                    </td>
                    <td class="py-2 text-right">
                        @if(!is_null($p->margin_pct))
                        <span class="text-xs px-1.5 py-0.5 rounded {{ $p->margin_pct >= 30 ? 'bg-green-100 text-green-700' : ($p->margin_pct >= 10 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                            {{ number_format($p->margin_pct,1) }}%
                        </span>
                        @else<span class="text-gray-300 text-xs">—</span>@endif
                    </td>
                    <td class="py-2 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <div class="w-16 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-1.5 bg-blue-400 rounded-full" style="width:{{ min(100,$pct) }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500 w-8 text-right">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</x-admin.card>
@endsection

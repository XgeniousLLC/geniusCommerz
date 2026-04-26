@extends('admin.layouts.admin')

@section('title', 'Inventory Report')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><span class="text-gray-400">Reports</span></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Inventory</li>
</ol>
@endsection

@section('page-header')
<div class="flex items-center justify-between flex-wrap gap-4">
    <h1 class="text-2xl font-bold text-gray-900">Inventory Report</h1>
    <div class="flex gap-2">
        @foreach([
            ['href' => route('admin.reports.sales'),      'label' => 'Sales'],
            ['href' => route('admin.reports.customers'),  'label' => 'Customers'],
            ['href' => route('admin.reports.orders'),     'label' => 'Orders'],
        ] as $tab)
        <a href="{{ $tab['href'] }}" class="text-sm px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:border-blue-300 hover:text-blue-600 transition-colors">{{ $tab['label'] }}</a>
        @endforeach
    </div>
</div>
@endsection

@section('content')

{{-- Summary stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <x-admin.card class="text-center">
        <div class="text-2xl font-extrabold text-blue-600">{{ number_format($summary['total_products']) }}</div>
        <div class="text-xs text-gray-500 mt-1">Total Products</div>
    </x-admin.card>
    <x-admin.card class="text-center">
        <div class="text-2xl font-extrabold text-red-600">{{ number_format($summary['out_of_stock_count']) }}</div>
        <div class="text-xs text-gray-500 mt-1">Out of Stock</div>
    </x-admin.card>
    <x-admin.card class="text-center">
        <div class="text-2xl font-extrabold text-yellow-600">{{ number_format($summary['low_stock_count']) }}</div>
        <div class="text-xs text-gray-500 mt-1">Low Stock (≤{{ $summary['low_stock_threshold'] }})</div>
    </x-admin.card>
    <x-admin.card class="text-center">
        <div class="text-2xl font-extrabold text-green-600">৳{{ number_format($summary['total_stock_value'], 0) }}</div>
        <div class="text-xs text-gray-500 mt-1">Total Stock Value</div>
    </x-admin.card>
</div>

{{-- Filter & export --}}
<x-admin.card class="mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Filter</label>
            <select name="filter" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="all" @selected($filter === 'all')>All Products</option>
                <option value="low_stock" @selected($filter === 'low_stock')>Low Stock</option>
                <option value="out_of_stock" @selected($filter === 'out_of_stock')>Out of Stock</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sort</label>
            <select name="sort" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" onchange="this.form.submit()">
                <option value="asc" @selected($sort === 'asc')>Stock: Low → High</option>
                <option value="desc" @selected($sort === 'desc')>Stock: High → Low</option>
            </select>
        </div>
        <a href="{{ route('admin.reports.export', ['type' => 'inventory']) }}"
           class="ml-auto text-sm flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:border-green-400 hover:text-green-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
    </form>
</x-admin.card>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="pb-3 pr-4">Product</th>
                    <th class="pb-3 pr-4">SKU</th>
                    <th class="pb-3 pr-4">Type</th>
                    <th class="pb-3 pr-4">Price</th>
                    <th class="pb-3 pr-4">Stock</th>
                    <th class="pb-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($products as $p)
                @php
                    $stock = (int) $p->effective_stock;
                    if ($stock === 0) {
                        $badge = ['Out of Stock', 'bg-red-100 text-red-700'];
                    } elseif ($stock <= $summary['low_stock_threshold']) {
                        $badge = ['Low Stock', 'bg-yellow-100 text-yellow-800'];
                    } else {
                        $badge = ['In Stock', 'bg-green-100 text-green-800'];
                    }
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="py-3 pr-4 font-medium text-gray-900 max-w-[200px] truncate">{{ $p->name }}</td>
                    <td class="py-3 pr-4 text-gray-500 font-mono text-xs">{{ $p->sku ?? '—' }}</td>
                    <td class="py-3 pr-4 text-gray-500">{{ $p->has_variants ? 'Variants' : 'Simple' }}</td>
                    <td class="py-3 pr-4 text-gray-700">৳{{ number_format($p->price, 0) }}</td>
                    <td class="py-3 pr-4 font-bold {{ $stock === 0 ? 'text-red-600' : ($stock <= $summary['low_stock_threshold'] ? 'text-yellow-700' : 'text-gray-900') }}">
                        {{ number_format($stock) }}
                    </td>
                    <td class="py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge[1] }}">{{ $badge[0] }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div class="mt-4 pt-4 border-t border-gray-100">
            {{ $products->withQueryString()->links() }}
        </div>
    @endif
</x-admin.card>
@endsection

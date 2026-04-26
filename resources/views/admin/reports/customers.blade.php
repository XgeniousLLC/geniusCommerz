@extends('admin.layouts.admin')

@section('title', 'Customer Report')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><span class="text-gray-400">Reports</span></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Customers</li>
</ol>
@endsection

@section('page-header')
<div class="flex items-center justify-between flex-wrap gap-4">
    <h1 class="text-2xl font-bold text-gray-900">Customer Report</h1>
    <div class="flex gap-2">
        @foreach([
            ['href' => route('admin.reports.sales'),      'label' => 'Sales'],
            ['href' => route('admin.reports.inventory'),  'label' => 'Inventory'],
            ['href' => route('admin.reports.orders'),     'label' => 'Orders'],
        ] as $tab)
        <a href="{{ $tab['href'] }}" class="text-sm px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:border-blue-300 hover:text-blue-600 transition-colors">{{ $tab['label'] }}</a>
        @endforeach
    </div>
</div>
@endsection

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <x-admin.card class="text-center">
        <div class="text-3xl font-extrabold text-blue-600">{{ number_format($stats['total_customers']) }}</div>
        <div class="text-sm text-gray-500 mt-1">Total Customers</div>
    </x-admin.card>
    <x-admin.card class="text-center">
        <div class="text-3xl font-extrabold text-green-600">{{ number_format($stats['new_this_month']) }}</div>
        <div class="text-sm text-gray-500 mt-1">New This Month</div>
    </x-admin.card>
    <x-admin.card class="text-center">
        <div class="text-3xl font-extrabold text-purple-600">{{ number_format($stats['repeat_customers']) }}</div>
        <div class="text-sm text-gray-500 mt-1">Repeat Customers</div>
    </x-admin.card>
</div>

<div class="flex justify-end mb-4">
    <a href="{{ route('admin.reports.export', ['type' => 'customers']) }}"
       class="text-sm flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:border-green-400 hover:text-green-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Export CSV
    </a>
</div>

<x-admin.card>
    <h2 class="text-base font-semibold text-gray-900 mb-4">Top 20 Customers by Spend</h2>
    @if($topCustomers->isEmpty())
        <p class="text-center text-gray-400 py-8 text-sm">No customer data yet.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="pb-3 pr-4">#</th>
                        <th class="pb-3 pr-4">Customer</th>
                        <th class="pb-3 pr-4">Orders</th>
                        <th class="pb-3 pr-4">Total Spent</th>
                        <th class="pb-3">Last Order</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($topCustomers as $i => $c)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 pr-4 text-gray-400 font-bold">{{ $i + 1 }}</td>
                        <td class="py-3 pr-4">
                            <div class="font-medium text-gray-900">{{ $c->name }}</div>
                            <div class="text-xs text-gray-500">{{ $c->email }}</div>
                        </td>
                        <td class="py-3 pr-4 text-gray-700">{{ $c->orders_count }}</td>
                        <td class="py-3 pr-4 font-bold text-green-700">৳{{ number_format($c->total_spent, 0) }}</td>
                        <td class="py-3 text-gray-500 text-xs">{{ \Carbon\Carbon::parse($c->last_order_at)->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-admin.card>
@endsection

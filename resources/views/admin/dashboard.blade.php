@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('page-header')
<h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
@endsection

@section('content')

{{-- Primary stats row --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-4">
    <x-admin.card class="text-center">
        <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_orders']) }}</div>
        <div class="text-xs text-gray-500 mt-1">Total Orders</div>
    </x-admin.card>
    <x-admin.card class="text-center">
        <div class="text-2xl font-bold text-orange-500">{{ number_format($stats['orders_24h']) }}</div>
        <div class="text-xs text-gray-500 mt-1">Last 24 Hours</div>
    </x-admin.card>
    <x-admin.card class="text-center">
        <div class="text-2xl font-bold text-cyan-600">{{ number_format($stats['orders_7d']) }}</div>
        <div class="text-xs text-gray-500 mt-1">Last 7 Days</div>
    </x-admin.card>
    <x-admin.card class="text-center">
        <div class="text-2xl font-bold text-yellow-600">{{ number_format($stats['pending_orders']) }}</div>
        <div class="text-xs text-gray-500 mt-1">Pending</div>
    </x-admin.card>
    <x-admin.card class="text-center">
        <div class="text-2xl font-bold text-indigo-600">{{ number_format($stats['in_shipment']) }}</div>
        <div class="text-xs text-gray-500 mt-1">In Shipment</div>
    </x-admin.card>
</div>

{{-- Secondary stats row --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
    <x-admin.card class="text-center">
        <div class="text-2xl font-bold text-green-600">৳{{ number_format($stats['total_revenue'], 0) }}</div>
        <div class="text-xs text-gray-500 mt-1">Revenue (Paid)</div>
    </x-admin.card>
    <x-admin.card class="text-center">
        <div class="text-2xl font-bold text-teal-600">{{ number_format($stats['total_stock']) }}</div>
        <div class="text-xs text-gray-500 mt-1">Units in Stock</div>
    </x-admin.card>
    <x-admin.card class="text-center">
        <div class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_customers']) }}</div>
        <div class="text-xs text-gray-500 mt-1">Customers</div>
    </x-admin.card>
    <x-admin.card class="text-center">
        <div class="text-2xl font-bold text-pink-600">{{ number_format($stats['active_coupons']) }}</div>
        <div class="text-xs text-gray-500 mt-1">Active Coupons</div>
    </x-admin.card>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Recent Orders --}}
    <div class="lg:col-span-2">
        <x-admin.card>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-900">Recent Orders</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View all</a>
            </div>
            @if($recentOrders->isEmpty())
                <p class="text-sm text-gray-500 text-center py-8">No orders yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-gray-100">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 uppercase tracking-wide">
                                <th class="pb-2 pr-3">Order</th>
                                <th class="pb-2 pr-3">Customer</th>
                                <th class="pb-2 pr-3">Items</th>
                                <th class="pb-2 pr-3">Total</th>
                                <th class="pb-2 pr-3">Status</th>
                                <th class="pb-2">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($recentOrders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 pr-3 font-mono text-xs font-medium">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800">
                                        #{{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="py-2 pr-3 text-gray-700 max-w-[130px] truncate">{{ $order->customer_name }}</td>
                                <td class="py-2 pr-3 text-gray-500">{{ $order->items_count }}</td>
                                <td class="py-2 pr-3 font-medium text-gray-900">৳{{ number_format($order->total, 0) }}</td>
                                <td class="py-2 pr-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $order->statusBadgeClass() }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="py-2 text-xs text-gray-400 whitespace-nowrap">{{ $order->created_at->format('M d, H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin.card>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">

        {{-- Quick Actions --}}
        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.orders.create') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-colors text-sm font-medium text-gray-700 hover:text-blue-700">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Create Order
                </a>
                <a href="{{ route('admin.products.create') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition-colors text-sm font-medium text-gray-700 hover:text-indigo-700">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V7"/></svg>
                    Add Product
                </a>
                <a href="{{ route('admin.coupons.create') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-gray-200 hover:border-pink-300 hover:bg-pink-50 transition-colors text-sm font-medium text-gray-700 hover:text-pink-700">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Create Coupon
                </a>
                <a href="{{ route('admin.settings.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg border border-gray-200 hover:border-gray-400 hover:bg-gray-50 transition-colors text-sm font-medium text-gray-700">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Settings
                </a>
            </div>
        </x-admin.card>

        {{-- Order breakdown --}}
        <x-admin.card>
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Orders by Status</h3>
            <div class="space-y-2">
                @foreach(\App\Models\Order::STATUSES as $status)
                @php $count = \App\Models\Order::where('status', $status)->count(); @endphp
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        @php
                        $dot = match($status) {
                            'pending'    => 'bg-yellow-400',
                            'processing' => 'bg-blue-400',
                            'shipped'    => 'bg-indigo-400',
                            'delivered'  => 'bg-green-400',
                            'cancelled'  => 'bg-red-400',
                            'refunded'   => 'bg-gray-300',
                            default      => 'bg-gray-300',
                        };
                        @endphp
                        <span class="w-2 h-2 rounded-full {{ $dot }} shrink-0"></span>
                        <span class="text-gray-600">{{ ucfirst($status) }}</span>
                    </div>
                    <span class="font-medium text-gray-900">{{ $count }}</span>
                </div>
                @endforeach
                @if(\App\Models\Order::count() === 0)
                    <p class="text-xs text-gray-400">No orders yet</p>
                @endif
            </div>
        </x-admin.card>

    </div>

</div>
@endsection

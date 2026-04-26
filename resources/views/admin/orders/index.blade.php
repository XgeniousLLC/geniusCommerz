@extends('admin.layouts.admin')

@section('title', 'Orders')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Orders</li>
</ol>
@endsection

@section('page-header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Orders</h1>
        <p class="text-gray-600 mt-1">{{ $orders->total() }} total</p>
    </div>
    <x-admin.button href="{{ route('admin.orders.create') }}">+ Create Order</x-admin.button>
</div>
@endsection

@section('content')
<x-admin.card class="mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <x-admin.input type="text" name="search" value="{{ request('search') }}" placeholder="Order # or customer…" />
        </div>
        <div class="w-40">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <x-admin.select name="status">
                <option value="">All statuses</option>
                @foreach(\App\Models\Order::STATUSES as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </x-admin.select>
        </div>
        <div class="w-44">
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment</label>
            <x-admin.select name="payment_status">
                <option value="">All payments</option>
                @foreach(\App\Models\Order::PAYMENT_STATUSES as $s)
                    <option value="{{ $s }}" {{ request('payment_status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </x-admin.select>
        </div>
        <x-admin.button type="submit">Filter</x-admin.button>
        @if(request()->anyFilled(['search','status','payment_status']))
            <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700 self-center">Clear</a>
        @endif
    </form>
</x-admin.card>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Order</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Source</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Items</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900">
                        <a href="{{ route('admin.orders.show', $order) }}" class="hover:text-blue-600">#{{ $order->order_number }}</a>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $order->sourceBadgeClass() }}">
                            {{ \App\Models\Order::SOURCES[$order->source] ?? ucfirst($order->source) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-900">{{ $order->customer_name }}</div>
                        <div class="text-xs text-gray-400">{{ $order->customer_email }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $order->items_count }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">${{ number_format($order->total, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $order->statusBadgeClass() }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $order->paymentBadgeClass() }}">
                            {{ ucwords(str_replace('_', ' ', $order->payment_status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">View</a>
                        <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" class="inline"
                              onsubmit="return confirm('Delete order #{{ $order->order_number }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div class="mt-4 px-4">{{ $orders->links() }}</div>
    @endif
</x-admin.card>
@endsection

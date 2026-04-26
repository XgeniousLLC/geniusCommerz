@extends('admin.layouts.admin')

@section('title', 'Coupons')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Coupons</li>
</ol>
@endsection

@section('page-header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Coupons</h1>
        <p class="text-gray-600 mt-1">{{ $coupons->total() }} total</p>
    </div>
    <x-admin.button href="{{ route('admin.coupons.create') }}">Add Coupon</x-admin.button>
</div>
@endsection

@section('content')
<x-admin.card class="mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-48">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <x-admin.input type="text" name="search" value="{{ request('search') }}" placeholder="Coupon code…" />
        </div>
        <x-admin.button type="submit">Filter</x-admin.button>
        @if(request()->filled('search'))
            <a href="{{ route('admin.coupons.index') }}" class="text-sm text-gray-500 hover:text-gray-700 self-center">Clear</a>
        @endif
    </form>
</x-admin.card>

<x-admin.card>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Type / Value</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Validity</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($coupons as $coupon)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <span class="font-mono font-semibold text-gray-900 bg-gray-100 px-2 py-0.5 rounded">{{ $coupon->code }}</span>
                        @if($coupon->description)
                            <div class="text-xs text-gray-400 mt-0.5">{{ $coupon->description }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-700">
                        @if($coupon->type === 'percent')
                            {{ $coupon->value }}% off
                        @else
                            ${{ number_format($coupon->value, 2) }} off
                        @endif
                        @if($coupon->minimum_order)
                            <div class="text-xs text-gray-400">Min. ${{ number_format($coupon->minimum_order, 2) }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">
                        {{ $coupon->usage_count }}{{ $coupon->usage_limit ? ' / ' . $coupon->usage_limit : '' }}
                        <div class="text-xs text-gray-400">{{ $coupon->orders_count }} orders</div>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        @if($coupon->starts_at || $coupon->expires_at)
                            <div>{{ $coupon->starts_at?->format('M d, Y') ?? '∞' }} → {{ $coupon->expires_at?->format('M d, Y') ?? '∞' }}</div>
                        @else
                            <span class="text-gray-400">No limit</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1">
                        @if($coupon->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                        @endif
                        @if($coupon->expires_at?->isPast())
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-700">Expired</span>
                        @endif
                        @if($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700">Used up</span>
                        @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>
                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" class="inline"
                              onsubmit="return confirm('Delete coupon {{ $coupon->code }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">No coupons found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($coupons->hasPages())
        <div class="mt-4 px-4">{{ $coupons->links() }}</div>
    @endif
</x-admin.card>
@endsection

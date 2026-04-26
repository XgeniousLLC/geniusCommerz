@extends('admin.layouts.admin')

@section('title', 'Refund Request')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.refunds.index') }}" class="hover:text-gray-700">Refunds</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">#{{ $refund->order->order_number }}</li>
</ol>
@endsection

@section('page-header')
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">Refund #{{ $refund->id }}</h1>
    @php
        $badgeClass = match($refund->status) {
            'approved'  => 'bg-green-100 text-green-800',
            'processed' => 'bg-blue-100 text-blue-800',
            'rejected'  => 'bg-red-100 text-red-700',
            default     => 'bg-yellow-100 text-yellow-800',
        };
    @endphp
    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold capitalize {{ $badgeClass }}">
        {{ $refund->status }}
    </span>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Left: Details -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Refund info -->
        <x-admin.card>
            <h2 class="text-base font-semibold text-gray-900 mb-4">Request Details</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Customer</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $refund->user->name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Email</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $refund->user->email }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Order</dt>
                    <dd class="mt-0.5">
                        <a href="{{ route('admin.orders.show', $refund->order) }}" class="font-mono font-semibold text-blue-600 hover:underline">
                            #{{ $refund->order->order_number }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Refund Amount</dt>
                    <dd class="font-bold text-gray-900 mt-0.5">৳{{ number_format($refund->amount ?? $refund->order->total, 0) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Reason</dt>
                    <dd class="font-medium text-gray-900 capitalize mt-0.5">{{ \App\Models\Refund::REASONS[$refund->reason] ?? $refund->reason }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Requested</dt>
                    <dd class="text-gray-700 mt-0.5">{{ $refund->created_at->format('M d, Y H:i') }}</dd>
                </div>
            </dl>

            @if($refund->details)
                <div class="mt-4 p-3 rounded-lg bg-gray-50 text-sm text-gray-700">
                    <span class="font-semibold text-gray-900">Customer note: </span>{{ $refund->details }}
                </div>
            @endif

            @if($refund->admin_note)
                <div class="mt-3 p-3 rounded-lg bg-blue-50 text-sm text-blue-800">
                    <span class="font-semibold">Admin note: </span>{{ $refund->admin_note }}
                </div>
            @endif
        </x-admin.card>

        <!-- Order items -->
        <x-admin.card>
            <h2 class="text-base font-semibold text-gray-900 mb-4">Order Items</h2>
            <div class="space-y-3">
                @foreach($refund->order->items as $item)
                <div class="flex items-center gap-3 text-sm">
                    <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 shrink-0">
                        @if($item->product?->images->first())
                            <img src="{{ $item->product->images->first()->getUrl('thumb') }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ $item->product_name }}</p>
                        @if($item->variant_label)
                            <p class="text-xs text-gray-500">{{ $item->variant_label }}</p>
                        @endif
                        <p class="text-xs text-gray-500">৳{{ number_format($item->unit_price, 0) }} × {{ $item->quantity }}</p>
                    </div>
                    <p class="font-semibold text-gray-900 shrink-0">৳{{ number_format($item->total, 0) }}</p>
                </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between text-sm font-bold text-gray-900">
                <span>Order Total</span>
                <span>৳{{ number_format($refund->order->total, 0) }}</span>
            </div>
        </x-admin.card>
    </div>

    <!-- Right: Action -->
    <div>
        @if(in_array($refund->status, ['pending']))
        <x-admin.card>
            <h2 class="text-base font-semibold text-gray-900 mb-4">Take Action</h2>
            <form method="POST" action="{{ route('admin.refunds.update', $refund) }}">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Decision</label>
                    <select name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        <option value="">Select…</option>
                        <option value="approved">Approve</option>
                        <option value="rejected">Reject</option>
                        <option value="processed">Mark as Processed</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note to customer (optional)</label>
                    <textarea name="admin_note" rows="3" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none" placeholder="Explain your decision…"></textarea>
                </div>

                <x-admin.button type="submit" class="w-full justify-center">Submit Decision</x-admin.button>
            </form>
        </x-admin.card>
        @elseif($refund->status === 'approved')
        <x-admin.card>
            <h2 class="text-base font-semibold text-gray-900 mb-4">Mark as Processed</h2>
            <form method="POST" action="{{ route('admin.refunds.update', $refund) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="processed">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Processing note (optional)</label>
                    <textarea name="admin_note" rows="2" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none" placeholder="e.g. Refunded via bKash…"></textarea>
                </div>
                <x-admin.button type="submit" class="w-full justify-center">Mark Processed</x-admin.button>
            </form>
        </x-admin.card>
        @else
        <x-admin.card>
            <p class="text-sm text-gray-500 text-center py-4">This refund has been <span class="font-semibold capitalize">{{ $refund->status }}</span>.</p>
            @if($refund->resolved_at)
                <p class="text-xs text-gray-400 text-center">{{ $refund->resolved_at->format('M d, Y H:i') }}</p>
            @endif
        </x-admin.card>
        @endif

        <!-- Order summary card -->
        <x-admin.card class="mt-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Order Info</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Status</dt>
                    <dd class="font-medium capitalize text-gray-900">{{ $refund->order->status }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Payment</dt>
                    <dd class="font-medium capitalize text-gray-900">{{ str_replace('_', ' ', $refund->order->payment_status) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Method</dt>
                    <dd class="font-medium capitalize text-gray-900">{{ str_replace('_', ' ', $refund->order->payment_method ?? 'N/A') }}</dd>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-100 font-bold text-gray-900">
                    <dt>Total</dt>
                    <dd>৳{{ number_format($refund->order->total, 0) }}</dd>
                </div>
            </dl>
        </x-admin.card>
    </div>

</div>
@endsection

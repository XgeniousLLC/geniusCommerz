@extends('admin.layouts.admin')

@section('title', 'Order #' . $order->order_number)

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.orders.index') }}" class="hover:text-gray-700">Orders</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">#{{ $order->order_number }}</li>
</ol>
@endsection

@section('page-header')
<div class="flex items-center justify-between">
    <div class="flex items-center gap-2 flex-wrap">
        <h1 class="text-2xl font-bold text-gray-900">Order #{{ $order->order_number }}</h1>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-sm font-medium {{ $order->statusBadgeClass() }}">
            {{ ucfirst($order->status) }}
        </span>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-sm font-medium {{ $order->paymentBadgeClass() }}">
            {{ ucwords(str_replace('_', ' ', $order->payment_status)) }}
        </span>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-sm font-medium {{ $order->sourceBadgeClass() }}">
            {{ \App\Models\Order::SOURCES[$order->source] ?? ucfirst($order->source) }}
        </span>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y g:i A') }}</span>
        <a href="{{ route('admin.orders.print', $order) }}" target="_blank"
           style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-weight:500;color:#374151;text-decoration:none;background:#fff;transition:background .15s"
           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
            <svg style="width:15px;height:15px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Slip
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- Left: items + addresses --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Order Items --}}
        <x-admin.card>
            <h2 class="text-base font-semibold text-gray-900 mb-4">Items ({{ $order->items->count() }})</h2>
            <div class="divide-y divide-gray-100">
                @foreach($order->items as $item)
                <div class="flex items-center gap-4 py-3">
                    @if($item->product?->images->first())
                        <img src="{{ $item->product->images->first()->getUrl('thumb') }}"
                             class="w-12 h-12 rounded-lg object-cover border border-gray-200 shrink-0">
                    @else
                        <div class="w-12 h-12 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900">{{ $item->product_name }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">
                            {{ $item->sku ? 'SKU: ' . $item->sku : '' }}
                            {{ $item->variant_label ? '· ' . $item->variant_label : '' }}
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 shrink-0">× {{ $item->quantity }}</div>
                    <div class="text-sm font-medium text-gray-900 shrink-0 w-20 text-right">${{ number_format($item->total, 2) }}</div>
                </div>
                @endforeach
            </div>

            {{-- Totals --}}
            <div class="mt-4 pt-4 border-t border-gray-100 space-y-1.5 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span>${{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="flex justify-between text-green-600">
                    <span>Discount {{ $order->coupon_code ? '(' . $order->coupon_code . ')' : '' }}</span>
                    <span>−${{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-gray-600">
                    <span>Shipping</span>
                    <span>{{ $order->shipping_cost > 0 ? '$' . number_format($order->shipping_cost, 2) : 'Free' }}</span>
                </div>
                @if($order->tax > 0)
                <div class="flex justify-between text-gray-600">
                    <span>Tax</span>
                    <span>${{ number_format($order->tax, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between font-semibold text-gray-900 text-base pt-1 border-t border-gray-200">
                    <span>Total</span>
                    <span>${{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </x-admin.card>

        {{-- Addresses --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            @foreach(['shipping_address' => 'Shipping Address', 'billing_address' => 'Billing Address'] as $field => $label)
            @php $addr = $order->$field ?? []; @endphp
            <div x-data="{ editing: false }">
                <x-admin.card>
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-base font-semibold text-gray-900">{{ $label }}</h2>
                        <button type="button" @click="editing = !editing" class="text-xs text-blue-600 hover:underline" x-text="editing ? 'Cancel' : 'Edit'"></button>
                    </div>

                    {{-- View mode --}}
                    <div x-show="!editing">
                        @if($addr)
                        <address class="not-italic text-sm text-gray-600 space-y-0.5">
                            <div class="font-medium text-gray-900">{{ $addr['name'] ?? $order->customer_name }}</div>
                            @if(!empty($addr['phone']))<div>{{ $addr['phone'] }}</div>@endif
                            @if(!empty($addr['address']))<div>{{ $addr['address'] }}</div>@endif
                            @if(!empty($addr['city']))<div>{{ $addr['city'] }}{{ !empty($addr['state']) ? ', ' . $addr['state'] : '' }} {{ $addr['postcode'] ?? '' }}</div>@endif
                            @if(!empty($addr['country']))<div>{{ $addr['country'] }}</div>@endif
                        </address>
                        @else
                        <p class="text-sm text-gray-400">—</p>
                        @endif
                    </div>

                    {{-- Edit mode --}}
                    <form x-show="editing" method="POST" action="{{ route('admin.orders.update-address', $order) }}" class="space-y-2 text-sm" x-cloak>
                        @csrf @method('PATCH')
                        <input type="hidden" name="type" value="{{ $field }}">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs text-gray-500 block mb-0.5">Name</label>
                                <input name="name" value="{{ $addr['name'] ?? $order->customer_name }}" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:ring-1 focus:ring-blue-400 focus:border-blue-400" required>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 block mb-0.5">Phone</label>
                                <input name="phone" value="{{ $addr['phone'] ?? '' }}" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:ring-1 focus:ring-blue-400 focus:border-blue-400">
                            </div>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 block mb-0.5">Address</label>
                            <input name="address" value="{{ $addr['address'] ?? '' }}" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:ring-1 focus:ring-blue-400 focus:border-blue-400" required>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs text-gray-500 block mb-0.5">City</label>
                                <input name="city" value="{{ $addr['city'] ?? '' }}" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:ring-1 focus:ring-blue-400 focus:border-blue-400" required>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 block mb-0.5">State / Division</label>
                                <input name="state" value="{{ $addr['state'] ?? '' }}" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:ring-1 focus:ring-blue-400 focus:border-blue-400">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 block mb-0.5">Postcode</label>
                                <input name="postcode" value="{{ $addr['postcode'] ?? '' }}" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:ring-1 focus:ring-blue-400 focus:border-blue-400">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 block mb-0.5">Country</label>
                                <input name="country" value="{{ $addr['country'] ?? 'Bangladesh' }}" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:ring-1 focus:ring-blue-400 focus:border-blue-400">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white text-xs py-1.5 rounded hover:bg-blue-700 mt-1">Save {{ $label }}</button>
                    </form>
                </x-admin.card>
            </div>
            @endforeach
        </div>

        {{-- Notes --}}
        @if($order->notes || $order->admin_note)
        <x-admin.card>
            <h2 class="text-base font-semibold text-gray-900 mb-3">Notes</h2>
            @if($order->notes)
            <div class="mb-3">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Customer Note</p>
                <p class="text-sm text-gray-700">{{ $order->notes }}</p>
            </div>
            @endif
            @if($order->admin_note)
            <div class="pt-3 {{ $order->notes ? 'border-t border-gray-100' : '' }}">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Admin Note</p>
                <p class="text-sm text-gray-700 bg-yellow-50 border border-yellow-100 rounded-lg px-3 py-2">{{ $order->admin_note }}</p>
            </div>
            @endif
        </x-admin.card>
        @endif

        {{-- Activity Timeline --}}
        @if($order->activities->count())
        <x-admin.card>
            <h2 class="text-base font-semibold text-gray-900 mb-4">Activity Timeline</h2>
            <ol class="relative border-l border-gray-200 ml-3 space-y-0">
                @foreach($order->activities->sortByDesc('created_at') as $activity)
                <li class="mb-5 ml-5">
                    <span class="absolute -left-3 flex items-center justify-center w-6 h-6 rounded-full {{ $activity->iconClass() }} ring-4 ring-white">
                        @if($activity->type === 'created')
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        @elseif($activity->type === 'status_changed')
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        @elseif($activity->type === 'payment_updated')
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        @elseif($activity->type === 'tracking_added')
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        @else
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                        @endif
                    </span>
                    <div class="text-sm font-medium text-gray-900">{{ $activity->title }}</div>
                    @if($activity->description)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $activity->description }}</p>
                    @endif
                    <div class="flex items-center gap-2 mt-1">
                        <time class="text-xs text-gray-400">{{ $activity->created_at->format('M d, Y g:i A') }}</time>
                        @if($activity->admin)
                            <span class="text-xs text-gray-400">· {{ $activity->admin->name }}</span>
                        @endif
                    </div>
                </li>
                @endforeach
            </ol>
        </x-admin.card>
        @endif
    </div>

    {{-- Right: customer + update status --}}
    <div class="space-y-6">

        {{-- Customer --}}
        <x-admin.card>
            <h2 class="text-base font-semibold text-gray-900 mb-3">Customer</h2>
            <div class="space-y-1 text-sm">
                <div class="font-medium text-gray-900">{{ $order->customer_name }}</div>
                <div class="text-gray-500">{{ $order->customer_email }}</div>
                @if($order->customer_phone)
                <div class="text-gray-500">{{ $order->customer_phone }}</div>
                @endif
                @if($order->user)
                <div class="mt-2">
                    <a href="{{ route('admin.users.show', $order->user) }}" class="text-xs text-blue-600 hover:underline">View account</a>
                </div>
                @endif
            </div>
        </x-admin.card>

        {{-- Update Status --}}
        <x-admin.card>
            <h2 class="text-base font-semibold text-gray-900 mb-4">Update Order</h2>
            <form method="POST" action="{{ route('admin.orders.update-status', $order) }}" class="space-y-4">
                @csrf @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <x-admin.select name="status">
                        @foreach(\App\Models\Order::STATUSES as $s)
                            <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </x-admin.select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                    <x-admin.select name="payment_status">
                        @foreach(\App\Models\Order::PAYMENT_STATUSES as $s)
                            <option value="{{ $s }}" {{ $order->payment_status === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </x-admin.select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <x-admin.input type="text" name="payment_method" value="{{ $order->payment_method }}" placeholder="e.g. bkash, nagad, cod…" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tracking Number</label>
                    <x-admin.input type="text" name="tracking_number" value="{{ $order->tracking_number }}" placeholder="e.g. 1Z999AA1…" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer Note</label>
                    <textarea name="notes" rows="2"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400"
                        placeholder="Visible to customer…">{{ $order->notes }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admin Note</label>
                    <textarea name="admin_note" rows="2"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 bg-yellow-50"
                        placeholder="Internal only…">{{ $order->admin_note }}</textarea>
                </div>

                <x-admin.button type="submit" class="w-full justify-center">Save Changes</x-admin.button>
            </form>
        </x-admin.card>

        {{-- Payment info --}}
        <x-admin.card>
            <h2 class="text-base font-semibold text-gray-900 mb-3">Payment</h2>
            <dl class="space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Method</dt>
                    <dd class="text-gray-900">{{ $order->payment_method ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Paid at</dt>
                    <dd class="text-gray-900">{{ $order->paid_at?->format('M d, Y') ?? '—' }}</dd>
                </div>
                @if($order->coupon_code)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Coupon</dt>
                    <dd class="font-mono text-gray-900">{{ $order->coupon_code }}</dd>
                </div>
                @endif
            </dl>
        </x-admin.card>
    </div>

</div>
@endsection

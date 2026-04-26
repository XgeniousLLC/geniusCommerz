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

        {{-- Fraud Check --}}
        @if($order->customer_phone)
        <x-admin.card x-data="fraudCheck('{{ $order->customer_phone }}')">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Fraud Check
                </h2>
                <button @click="run" :disabled="loading"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-orange-50 text-orange-700 hover:bg-orange-100 border border-orange-200 disabled:opacity-50 transition-colors">
                    <svg x-show="!loading" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <svg x-show="loading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <span x-text="checked ? 'Re-check' : 'Check Now'"></span>
                </button>
            </div>

            {{-- Phone display --}}
            <p class="text-xs text-gray-500 mb-3">Checking: <strong class="text-gray-700">{{ $order->customer_phone }}</strong></p>

            {{-- Idle state --}}
            <template x-if="!checked && !loading && !error">
                <p class="text-xs text-gray-400 italic">Click "Check Now" to verify this customer's courier history via FraudBD.</p>
            </template>

            {{-- Loading --}}
            <template x-if="loading">
                <div class="flex items-center gap-2 text-xs text-gray-400">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    Fetching courier data…
                </div>
            </template>

            {{-- Error --}}
            <template x-if="error">
                <div class="text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2" x-text="error"></div>
            </template>

            {{-- Sandbox notice --}}
            <template x-if="sandbox && checked">
                <div class="mb-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    ⚠️ Sandbox mode — dummy data. Add your live API key in <a href="{{ route('admin.integrations.index') }}" class="underline">Integrations → FraudBD</a>.
                </div>
            </template>

            {{-- Results --}}
            <template x-if="checked && couriers.length > 0">
                <div class="space-y-2">
                    <template x-for="c in couriers" :key="c.name">
                        <div class="rounded-lg border p-3 text-sm"
                            :class="{
                                'border-green-200 bg-green-50':  c.risk_color === 'green',
                                'border-yellow-200 bg-yellow-50': c.risk_color === 'yellow',
                                'border-orange-200 bg-orange-50': c.risk_color === 'orange',
                                'border-red-200 bg-red-50':      c.risk_color === 'red',
                                'border-gray-200 bg-gray-50':    c.risk_color === 'gray',
                            }">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-gray-800 capitalize" x-text="c.name"></span>
                                <div class="flex items-center gap-1.5">
                                    {{-- Risk badge --}}
                                    <template x-if="c.risk_level">
                                        <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full"
                                            :class="{
                                                'bg-green-100 text-green-700':   c.risk_color === 'green',
                                                'bg-yellow-100 text-yellow-700': c.risk_color === 'yellow',
                                                'bg-orange-100 text-orange-700': c.risk_color === 'orange',
                                                'bg-red-100 text-red-700':       c.risk_color === 'red',
                                                'bg-gray-100 text-gray-500':     c.risk_color === 'gray',
                                            }"
                                            x-text="c.risk_level.replace('_',' ')"></span>
                                    </template>
                                    {{-- Rating badge --}}
                                    <template x-if="c.rating">
                                        <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full"
                                            :class="{
                                                'bg-green-100 text-green-700':  c.rating_color === 'green',
                                                'bg-blue-100 text-blue-700':    c.rating_color === 'blue',
                                                'bg-yellow-100 text-yellow-700':c.rating_color === 'yellow',
                                                'bg-red-100 text-red-700':      c.rating_color === 'red',
                                                'bg-gray-100 text-gray-500':    c.rating_color === 'gray',
                                            }"
                                            x-text="c.rating.replace('_',' ')"></span>
                                    </template>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-xs text-gray-600">
                                <template x-if="c.total_orders !== null">
                                    <div><span class="text-gray-400 block">Orders</span><span class="font-medium" x-text="c.total_orders"></span></div>
                                </template>
                                <template x-if="c.success_rate !== null">
                                    <div><span class="text-gray-400 block">Success</span><span class="font-medium text-green-700" x-text="c.success_rate"></span></div>
                                </template>
                                <template x-if="c.cancel_rate !== null">
                                    <div><span class="text-gray-400 block">Cancel</span><span class="font-medium text-red-600" x-text="c.cancel_rate"></span></div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            {{-- No data --}}
            <template x-if="checked && couriers.length === 0 && !error">
                <p class="text-xs text-gray-400 italic">No courier history found for this number.</p>
            </template>
        </x-admin.card>
        @endif

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

        {{-- Courier Dispatch --}}
        @php $hasCourier = \App\Services\CourierService::class && app(\App\Services\CourierService::class)->hasDefault(); @endphp
        <x-admin.card>
            <h2 class="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l1.993 1.994M2 3h11l4 7v4h-2m-4 0H9"/>
                </svg>
                Courier Dispatch
            </h2>

            @if($order->consignment_id)
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-3 text-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-medium text-green-800">Dispatched via {{ $order->courier_provider }}</p>
                            <p class="text-green-700 font-mono text-xs mt-0.5">{{ $order->consignment_id }}</p>
                            @if($order->courier_status)
                            <p class="text-green-600 text-xs mt-1">Status: <span class="font-medium">{{ $order->courier_status }}</span></p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.orders.refresh-courier-status', $order) }}">
                            @csrf
                            <button type="submit" class="text-xs text-green-700 border border-green-300 rounded px-2 py-1 hover:bg-green-100">
                                Refresh Status
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @if(! $hasCourier)
                <p class="text-sm text-gray-400 italic">
                    No default courier configured.
                    <a href="{{ route('admin.integrations.index') }}" class="text-blue-600 hover:underline">Set one up →</a>
                </p>
            @elseif(! $order->consignment_id)
                <form method="POST" action="{{ route('admin.orders.dispatch-courier', $order) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Weight (kg)</label>
                            <input type="number" name="item_weight" step="0.1" min="0.1" value="0.5"
                                class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">COD Amount (৳)</label>
                            <input type="number" name="cod_amount" step="0.01" value="{{ $order->total }}"
                                class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Delivery Area (if required)</label>
                        <input type="text" name="area" value="{{ ($order->shipping_address['city'] ?? '') }}"
                            class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300"
                            placeholder="e.g. Dhaka, Mirpur">
                    </div>
                    <x-admin.button type="submit" class="w-full justify-center">
                        Push to Courier
                    </x-admin.button>
                </form>
            @endif
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

@push('scripts')
<script>
function fraudCheck(phone) {
    return {
        phone,
        loading:  false,
        checked:  false,
        error:    null,
        sandbox:  false,
        couriers: [],

        async run() {
            this.loading  = true;
            this.error    = null;
            this.checked  = false;
            this.couriers = [];

            try {
                const res = await fetch('{{ route('admin.fraud.check') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ phone: this.phone }),
                });

                const json = await res.json();

                if (!json.success) {
                    this.error = json.message || 'Unexpected error.';
                } else {
                    this.couriers = json.data.couriers ?? [];
                    this.sandbox  = json.data.sandbox  ?? false;
                    this.checked  = true;
                }
            } catch (e) {
                this.error = 'Network error — could not reach FraudBD.';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush

@endsection

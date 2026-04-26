@extends('frontend.layout')

@section('title', 'Track Your Order')
@section('description', 'Enter your order number to get real-time delivery updates.')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-10 lg:py-14">

    {{-- Breadcrumb --}}
    <nav class="text-xs mb-6 flex items-center gap-1.5" style="color:var(--kb-ink-soft)">
        <a href="/" class="hover:text-slate-900">Home</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span style="color:var(--kb-ink)">Track Order</span>
    </nav>

    {{-- Search form --}}
    <div class="kb-card p-6 md:p-8 mb-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                 style="background:var(--kb-primary-50)">
                <i data-lucide="package" class="w-5 h-5" style="color:var(--kb-primary)"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold" style="color:var(--kb-ink)">Track Your Order</h1>
                <p class="text-sm" style="color:var(--kb-ink-soft)">Enter your order number for live status updates.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('order.track') }}" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold mb-1.5" style="color:var(--kb-ink)">Order Number</label>
                <input type="text" name="order_number"
                       value="{{ request('order_number') }}"
                       placeholder="e.g. ORD-A1B2C3D4"
                       autocomplete="off" required
                       class="kb-input">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1.5" style="color:var(--kb-ink)">
                    Phone Number
                    <span class="font-normal" style="color:var(--kb-ink-soft)">(optional, for verification)</span>
                </label>
                <input type="text" name="phone"
                       value="{{ request('phone') }}"
                       placeholder="+880 1xxx-xxxxxx"
                       autocomplete="off"
                       class="kb-input">
                <p class="text-xs mt-1" style="color:var(--kb-ink-soft)">Leave empty to find by order number only.</p>
            </div>
            <button type="submit" class="kb-btn kb-btn-primary kb-btn-lg w-full" style="border-radius:12px">
                <i data-lucide="search" class="w-5 h-5"></i>
                Track Order
            </button>
        </form>
    </div>

    {{-- Not found --}}
    @if($searched && !$order)
    <div class="kb-card p-4 flex items-start gap-3 mb-6" style="border-color:#fecaca;background:#fef2f2">
        <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:#dc2626"></i>
        <p class="text-sm" style="color:#dc2626">
            No order found with that order number{{ request('phone') ? ' and phone number' : '' }}.
            Please check and try again.
        </p>
    </div>
    @endif

    @if($order)

    {{-- Order summary card --}}
    <div class="kb-card p-6 mb-4">
        @php
        $statusStyle = [
            'pending'    => ['background:#fef9c3;color:#854d0e',  'clock'],
            'processing' => ['background:#dbeafe;color:#1e40af',  'refresh-cw'],
            'shipped'    => ['background:#e0e7ff;color:#3730a3',  'truck'],
            'delivered'  => ['background:#dcfce7;color:#166534',  'check-circle'],
            'cancelled'  => ['background:#fee2e2;color:#b91c1c',  'x-circle'],
            'refunded'   => ['background:#f3f4f6;color:#374151',  'rotate-ccw'],
        ];
        $payStyle = [
            'paid'               => ['background:#dcfce7;color:#166534', 'check'],
            'partially_refunded' => ['background:#fef9c3;color:#854d0e', 'minus-circle'],
            'refunded'           => ['background:#f3f4f6;color:#374151', 'rotate-ccw'],
            'unpaid'             => ['background:#fee2e2;color:#b91c1c', 'alert-triangle'],
        ];
        $s = $statusStyle[$order->status] ?? ['background:#f3f4f6;color:#374151', 'circle'];
        $p = $payStyle[$order->payment_status] ?? ['background:#f3f4f6;color:#374151', 'circle'];
        @endphp

        <div class="flex items-start justify-between flex-wrap gap-3 mb-5 pb-5" style="border-bottom:1px solid var(--kb-border)">
            <div>
                <p class="text-lg font-bold" style="color:var(--kb-ink)">#{{ $order->order_number }}</p>
                <p class="text-xs mt-0.5" style="color:var(--kb-ink-soft)">
                    Placed {{ $order->created_at->format('d M Y, g:i A') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="kb-chip font-semibold" style="{{ $s[0] }}">
                    <i data-lucide="{{ $s[1] }}" class="w-3 h-3"></i>
                    {{ ucfirst($order->status) }}
                </span>
                <span class="kb-chip font-semibold" style="{{ $p[0] }}">
                    <i data-lucide="{{ $p[1] }}" class="w-3 h-3"></i>
                    {{ ucwords(str_replace('_', ' ', $order->payment_status)) }}
                </span>
            </div>
        </div>

        {{-- Customer --}}
        <div class="mb-4">
            <p class="text-xs font-bold uppercase tracking-wider mb-1" style="color:var(--kb-ink-soft)">Customer</p>
            <p class="font-semibold text-sm" style="color:var(--kb-ink)">{{ $order->customer_name }}</p>
            @if($order->customer_phone)
            <p class="text-xs mt-0.5" style="color:var(--kb-ink-soft)">{{ $order->customer_phone }}</p>
            @endif
        </div>

        {{-- Tracking number --}}
        @if($order->tracking_number)
        <div class="rounded-xl p-3 mb-4" style="background:var(--kb-primary-50);border:1px solid rgba(31,58,138,.15)">
            <p class="text-xs font-bold uppercase tracking-wider mb-1" style="color:var(--kb-primary)">Tracking Number</p>
            <p class="text-base font-bold font-mono" style="color:var(--kb-primary)">{{ $order->tracking_number }}</p>
        </div>
        @endif

        {{-- Items --}}
        <div class="mb-4">
            <p class="text-xs font-bold uppercase tracking-wider mb-3" style="color:var(--kb-ink-soft)">
                Items ({{ $order->items->count() }})
            </p>
            <div class="space-y-2">
                @foreach($order->items as $item)
                <div class="flex items-start justify-between gap-3 text-sm py-2"
                     style="border-bottom:1px solid var(--kb-border)">
                    <div>
                        <p class="font-semibold" style="color:var(--kb-ink)">{{ $item->product_name }}</p>
                        <p class="text-xs mt-0.5" style="color:var(--kb-ink-soft)">
                            @if($item->sku)SKU: {{ $item->sku }}@endif
                            @if($item->variant_label) · {{ $item->variant_label }}@endif
                            × {{ $item->quantity }}
                        </p>
                    </div>
                    <span class="font-bold flex-shrink-0" style="color:var(--kb-ink)">৳{{ number_format($item->total, 2) }}</span>
                </div>
                @endforeach
            </div>

            {{-- Totals --}}
            <div class="mt-4 space-y-1.5 text-sm">
                <div class="flex justify-between" style="color:var(--kb-ink-muted)">
                    <span>Subtotal</span>
                    <span>৳{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->shipping_cost > 0)
                <div class="flex justify-between" style="color:var(--kb-ink-muted)">
                    <span>Shipping</span>
                    <span>৳{{ number_format($order->shipping_cost, 2) }}</span>
                </div>
                @endif
                @if($order->discount_amount > 0)
                <div class="flex justify-between" style="color:#16a34a">
                    <span>Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</span>
                    <span>−৳{{ number_format($order->discount_amount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between pt-2 font-bold text-base"
                     style="border-top:2px solid var(--kb-ink);color:var(--kb-ink)">
                    <span>Total</span>
                    <span>৳{{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Order note --}}
        @if($order->notes)
        <div>
            <p class="text-xs font-bold uppercase tracking-wider mb-2" style="color:var(--kb-ink-soft)">Order Note</p>
            <div class="rounded-xl p-3 text-sm leading-relaxed whitespace-pre-wrap"
                 style="background:#fffbeb;border:1px solid #fde68a;color:#444">{{ $order->notes }}</div>
        </div>
        @endif
    </div>

    {{-- Activity timeline --}}
    @if($order->activities->count())
    <div class="kb-card p-6">
        <p class="text-xs font-bold uppercase tracking-wider mb-5" style="color:var(--kb-ink-soft)">Order History</p>
        @php
        $dotColor = [
            'created'         => 'var(--kb-success)',
            'status_changed'  => 'var(--kb-primary)',
            'payment_updated' => '#a855f7',
            'tracking_added'  => '#6366f1',
            'note_added'      => 'var(--kb-warn)',
        ];
        @endphp
        <div class="relative" style="padding-left:24px">
            <div class="absolute" style="left:7px;top:0;bottom:0;width:2px;background:var(--kb-border);border-radius:2px"></div>
            @foreach($order->activities->sortByDesc('created_at') as $activity)
            <div class="relative mb-5 last:mb-0">
                <div class="absolute" style="left:-24px;top:3px;width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 2px {{ $dotColor[$activity->type] ?? 'var(--kb-ink-soft)' }};background:{{ $dotColor[$activity->type] ?? 'var(--kb-ink-soft)' }}"></div>
                <p class="text-sm font-semibold" style="color:var(--kb-ink)">{{ $activity->title }}</p>
                @if($activity->description)
                <p class="text-xs mt-0.5" style="color:var(--kb-ink-muted)">{{ $activity->description }}</p>
                @endif
                <p class="text-xs mt-1" style="color:var(--kb-ink-soft)">{{ $activity->created_at->format('d M Y, g:i A') }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @endif

    {{-- Track another --}}
    @if($searched)
    <div class="text-center mt-6">
        <a href="{{ route('order.track') }}" class="text-sm font-semibold hover:underline" style="color:var(--kb-primary)">
            ← Track another order
        </a>
    </div>
    @endif

</div>
@endsection

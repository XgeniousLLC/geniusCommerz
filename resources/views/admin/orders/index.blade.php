@extends('admin.layouts.admin')

@section('title', 'Orders')

@section('content')

@php
$avatarColors = ['var(--info)','var(--violet)','var(--pop)','var(--teal)','var(--warning)','var(--accent)','var(--success)'];
function orderAvatarColor(string $name, array $colors): string {
    return $colors[abs(crc32($name)) % count($colors)];
}
@endphp

<div class="page-head">
    <div>
        <h2 class="display">Orders</h2>
        <div class="sub">
            <b style="color:var(--text)">{{ $orders->total() }}</b> orders total
        </div>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.orders.create') }}">
        <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>Create order
    </a>
</div>

<div class="stat-grid">
    <div class="card lift stat">
        <span class="tile t-warning"><span class="ico" data-ico="clock"></span></span>
        <div><div class="num">{{ $stats['pending'] }}</div><div class="lbl">Pending</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-info"><span class="ico" data-ico="refresh"></span></span>
        <div><div class="num">{{ $stats['processing'] }}</div><div class="lbl">Processing</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-violet"><span class="ico" data-ico="truck"></span></span>
        <div><div class="num">{{ $stats['shipped'] }}</div><div class="lbl">In shipment</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-success"><span class="ico" data-ico="check"></span></span>
        <div><div class="num">{{ $stats['delivered'] }}</div><div class="lbl">Delivered</div></div>
    </div>
</div>

<div class="between wrap" style="margin-bottom:16px;gap:12px">
    <form method="GET" class="row" style="gap:10px;flex:1;max-width:480px">
        <div class="search-field grow" style="max-width:340px">
            <span class="ico" data-ico="search"></span>
            <input class="input" name="search" value="{{ request('search') }}" placeholder="Search by order # or customer…">
        </div>
        <select class="input" name="payment_status" style="max-width:160px" onchange="this.form.submit()">
            <option value="">All payments</option>
            @foreach(\App\Models\Order::PAYMENT_STATUSES as $s)
            <option value="{{ $s }}" {{ request('payment_status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        @if(request()->anyFilled(['search','status','payment_status']))
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline" style="padding:0 12px">Clear</a>
        @endif
    </form>

    <div class="seg sm">
        <a href="{{ route('admin.orders.index', request()->except('status','page')) }}"
           class="{{ !request('status') ? 'active' : '' }}" style="text-decoration:none">All</a>
        <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status'=>'pending'])) }}"
           class="{{ request('status')==='pending' ? 'active' : '' }}" style="text-decoration:none">Pending</a>
        <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status'=>'processing'])) }}"
           class="{{ request('status')==='processing' ? 'active' : '' }}" style="text-decoration:none">Processing</a>
        <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status'=>'shipped'])) }}"
           class="{{ request('status')==='shipped' ? 'active' : '' }}" style="text-decoration:none">Shipped</a>
        <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status'=>'delivered'])) }}"
           class="{{ request('status')==='delivered' ? 'active' : '' }}" style="text-decoration:none">Delivered</a>
    </div>
</div>

<div class="card flush">
    <div class="table-scroll"><table class="table">
        <thead><tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Channel</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Date</th>
            <th style="text-align:right">Total</th>
            <th></th>
        </tr></thead>
        <tbody>
            @forelse($orders as $order)
            @php
            $initials = collect(explode(' ', $order->customer_name))->map(fn($w) => strtoupper(mb_substr($w,0,1)))->take(2)->implode('');
            $avatarColor = $avatarColors[abs(crc32($order->customer_name)) % count($avatarColors)];
            $statusPill  = $order->statusPillClass();
            $paymentPill = $order->paymentPillClass();
            $sourcePill  = $order->sourcePillClass();
            @endphp
            <tr class="hoverable" onclick="location.href='{{ route('admin.orders.show', $order) }}'">
                <td>
                    <span class="mono" style="color:var(--accent);font-weight:600;font-size:12.5px">
                        #{{ $order->order_number }}
                    </span>
                </td>
                <td>
                    <div class="row" style="gap:10px">
                        <span class="avatar" style="width:32px;height:32px;font-size:12px;background:{{ $avatarColor }}">{{ $initials }}</span>
                        <span style="font-weight:700;font-size:13px">{{ $order->customer_name }}</span>
                    </div>
                </td>
                <td>
                    <span class="pill sm {{ $sourcePill }}">{{ \App\Models\Order::SOURCES[$order->source] ?? ucfirst($order->source) }}</span>
                </td>
                <td>
                    <span class="pill sm {{ $paymentPill }}">{{ ucwords(str_replace('_',' ',$order->payment_status)) }}</span>
                </td>
                <td>
                    <span class="pill sm {{ $statusPill }}"><span class="dot"></span>{{ ucfirst($order->status) }}</span>
                </td>
                <td class="faint" style="font-size:13px">{{ $order->created_at->format('M j, H:i') }}</td>
                <td style="text-align:right" class="tnum"><b>৳{{ number_format($order->total, 0) }}</b></td>
                <td style="text-align:right">
                    <span class="ico" data-ico="chevRight" style="width:17px;height:17px;color:var(--text-faint)"></span>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;padding:48px;color:var(--text-faint)">No orders found.</td></tr>
            @endforelse
        </tbody>
    </table></div>

    @if($orders->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--border)">
        {{ $orders->links() }}
    </div>
    @endif
</div>

@endsection

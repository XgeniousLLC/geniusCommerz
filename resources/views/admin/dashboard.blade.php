@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Good morning, {{ auth('admin')->user()->name }}</h2>
        <div class="sub">Here's what's happening in your store today</div>
    </div>
    <div class="row">
        <div class="seg sm">
            <button type="button">7d</button>
            <button type="button" class="active">30d</button>
            <button type="button">90d</button>
        </div>
        <a href="{{ route('admin.reports.sales') }}" class="btn btn-outline btn-sm">
            <span class="ico" data-ico="chart" style="width:16px;height:16px"></span>
            Reports
        </a>
    </div>
</div>

{{-- KPI stat grid --}}
<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">
    <div class="card lift stat" style="justify-content:space-between;align-items:flex-start;flex-direction:column;gap:10px;padding:20px">
        <div class="between" style="width:100%">
            <span class="tile t-accent"><span class="ico" data-ico="dollar"></span></span>
            @if($stats['orders_30d'] > 0)
                <span class="pill success"><span class="ico" data-ico="arrowUp" style="width:13px;height:13px"></span>30d</span>
            @endif
        </div>
        <div>
            <div class="num" style="font-size:28px">৳{{ number_format($stats['revenue_30d'], 0) }}</div>
            <div class="lbl">Revenue · last 30 days</div>
        </div>
    </div>

    <div class="card lift stat" style="justify-content:space-between;align-items:flex-start;flex-direction:column;gap:10px;padding:20px">
        <div class="between" style="width:100%">
            <span class="tile t-info"><span class="ico" data-ico="cart"></span></span>
            <span class="pill info"><span class="ico" data-ico="arrowUp" style="width:13px;height:13px"></span>30d</span>
        </div>
        <div>
            <div class="num" style="font-size:28px">{{ number_format($stats['orders_30d']) }}</div>
            <div class="lbl">Orders · last 30 days</div>
        </div>
    </div>

    <div class="card lift stat" style="justify-content:space-between;align-items:flex-start;flex-direction:column;gap:10px;padding:20px">
        <div class="between" style="width:100%">
            <span class="tile t-violet"><span class="ico" data-ico="receipt"></span></span>
        </div>
        <div>
            <div class="num" style="font-size:28px">৳{{ number_format($stats['aov'], 0) }}</div>
            <div class="lbl">Average order value</div>
        </div>
    </div>

    <div class="card lift stat" style="justify-content:space-between;align-items:flex-start;flex-direction:column;gap:10px;padding:20px">
        <div class="between" style="width:100%">
            <span class="tile t-teal"><span class="ico" data-ico="users"></span></span>
            @if($stats['new_customers'] > 0)
                <span class="pill success"><span class="ico" data-ico="arrowUp" style="width:13px;height:13px"></span>{{ $stats['new_customers'] }} new</span>
            @endif
        </div>
        <div>
            <div class="num" style="font-size:28px">{{ number_format($stats['total_customers']) }}</div>
            <div class="lbl">Total customers</div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1.7fr) minmax(0,1fr);gap:18px" class="grid-2">

    {{-- Recent orders --}}
    <div class="card flush">
        <div class="card-head" style="padding:20px 22px 14px;margin:0">
            <span class="tile sm t-accent"><span class="ico" data-ico="receipt" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Recent orders</h3><div class="sub">Latest activity across channels</div></div>
            <a class="link-btn head-action" href="{{ route('admin.orders.index') }}">
                View all <span class="ico" data-ico="arrowRight" style="width:14px;height:14px"></span>
            </a>
        </div>
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr><th>Order</th><th>Customer</th><th>Status</th><th style="text-align:right">Total</th></tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr class="hoverable">
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="mono" style="color:var(--accent);font-weight:600;font-size:12.5px">
                                #{{ $order->order_number }}
                            </a>
                        </td>
                        <td>
                            <div class="row" style="gap:10px">
                                @php
                                $colors = ['var(--accent)','var(--violet)','var(--teal)','var(--pop)','var(--info)'];
                                $bg = $colors[$order->id % count($colors)];
                                @endphp
                                <span class="avatar" style="width:32px;height:32px;font-size:12px;background:{{ $bg }}">
                                    {{ strtoupper(substr($order->customer_name ?? 'G', 0, 2)) }}
                                </span>
                                <div>
                                    <div style="font-weight:700;font-size:13px">{{ $order->customer_name ?? 'Guest' }}</div>
                                    <div class="faint" style="font-size:11.5px">{{ $order->items_count }} item{{ $order->items_count !== 1 ? 's' : '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                            $pillClass = match($order->status) {
                                'pending'    => 'warning',
                                'confirmed'  => 'info',
                                'processing' => 'info',
                                'shipped'    => 'violet',
                                'delivered'  => 'success',
                                'cancelled'  => 'danger',
                                default      => '',
                            };
                            @endphp
                            <span class="pill sm {{ $pillClass }}"><span class="dot"></span>{{ ucfirst($order->status) }}</span>
                        </td>
                        <td style="text-align:right" class="tnum"><b>৳{{ number_format($order->total, 0) }}</b></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center;padding:32px;color:var(--text-faint)">No orders yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Right column --}}
    <div class="col-gap">

        {{-- Orders by status --}}
        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-violet"><span class="ico" data-ico="chart" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Orders by status</h3><div class="sub">All time</div></div>
            </div>
            @php $maxCount = max($ordersByStatus->max(), 1); @endphp
            <div class="stack" style="gap:12px">
                @foreach($ordersByStatus as $status => $count)
                @php
                $barColor = match($status) {
                    'pending'    => 'var(--warning)',
                    'confirmed'  => 'var(--info)',
                    'processing' => 'var(--accent)',
                    'shipped'    => 'var(--violet)',
                    'delivered'  => 'var(--success)',
                    'cancelled'  => 'var(--danger)',
                    'refunded'   => 'var(--text-faint)',
                    default      => 'var(--accent)',
                };
                @endphp
                <div>
                    <div class="between" style="margin-bottom:5px">
                        <span style="font-weight:600;font-size:13.5px">{{ ucfirst($status) }}</span>
                        <span class="tnum faint" style="font-size:13px">{{ $count }}</span>
                    </div>
                    <div style="height:6px;border-radius:99px;background:var(--surface-3)">
                        <div style="width:{{ $maxCount > 0 ? round(($count / $maxCount) * 100) : 0 }}%;height:100%;border-radius:99px;background:{{ $barColor }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-warning"><span class="ico" data-ico="bolt" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Quick actions</h3></div>
            </div>
            <div class="stack" style="gap:9px">
                <a class="btn btn-outline" style="justify-content:flex-start" href="{{ route('admin.products.create') }}">
                    <span class="ico" data-ico="plus" style="width:17px;height:17px"></span>Add a product
                </a>
                <a class="btn btn-outline" style="justify-content:flex-start" href="{{ route('admin.orders.index') }}">
                    <span class="ico" data-ico="receipt" style="width:17px;height:17px"></span>Review orders
                </a>
                <a class="btn btn-outline" style="justify-content:flex-start" href="{{ route('admin.coupons.create') }}">
                    <span class="ico" data-ico="percent" style="width:17px;height:17px"></span>Create coupon
                </a>
                <a class="btn btn-outline" style="justify-content:flex-start" href="{{ route('admin.settings.index') }}">
                    <span class="ico" data-ico="gear" style="width:17px;height:17px"></span>Settings
                </a>
            </div>
        </div>

    </div>
</div>

{{-- Low stock alert --}}
@if($lowStock->isNotEmpty())
<div class="card pad" style="margin-top:18px;border-color:color-mix(in srgb,var(--warning) 40%,transparent);background:var(--warning-soft)">
    <div class="row">
        <span class="tile" style="background:var(--warning);color:#fff;flex-shrink:0">
            <span class="ico" data-ico="bell"></span>
        </span>
        <div class="grow">
            <div style="font-weight:700;font-size:14.5px">{{ $lowStock->count() }} product{{ $lowStock->count() !== 1 ? 's are' : ' is' }} running low on stock</div>
            <div class="muted" style="font-size:13px;margin-top:2px">
                {{ $lowStock->map(fn($p) => $p->name . ' (' . $p->stock_qty . ')')->implode(', ') }}
            </div>
        </div>
        <a class="btn btn-soft btn-sm" href="{{ route('admin.products.index') }}">Restock now</a>
    </div>
</div>
@endif

@endsection

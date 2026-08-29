@extends('admin.layouts.admin')

@section('title', 'User – ' . $user->name)

@section('content')
@php
$avatarColors = ['var(--info)','var(--violet)','var(--pop)','var(--teal)','var(--warning)','var(--accent)','var(--success)'];
$avatarColor  = $avatarColors[abs(crc32($user->name)) % count($avatarColors)];
$initials     = collect(explode(' ', $user->name))->map(fn($w) => strtoupper(mb_substr($w,0,1)))->take(2)->implode('');

$statusPill = fn($status) => match($status) {
    'delivered'  => 'success',
    'shipped'    => 'violet',
    'processing' => 'accent',
    'confirmed'  => 'info',
    'pending'    => 'warning',
    'cancelled'  => 'danger',
    default      => '',
};
$statusColor = fn($status) => match($status) {
    'delivered'  => 'var(--success)',
    'shipped'    => 'var(--violet)',
    'processing' => 'var(--accent)',
    'confirmed'  => 'var(--info)',
    'pending'    => 'var(--warning)',
    'cancelled'  => 'var(--danger)',
    default      => 'var(--text-faint)',
};
@endphp

<div class="row" style="gap:14px;margin-bottom:22px;flex-wrap:wrap">
    <a class="icon-btn" href="{{ route('admin.users.index') }}" style="width:40px;height:40px">
        <span class="ico" data-ico="chevLeft"></span>
    </a>
    <span class="avatar" style="width:48px;height:48px;font-size:17px;background:{{ $avatarColor }}">{{ $initials }}</span>
    <div class="grow" style="min-width:200px">
        <div class="breadcrumb"><a href="{{ route('admin.users.index') }}">Customers</a> / {{ $user->name }}</div>
        <div class="row" style="gap:10px;flex-wrap:wrap">
            <h2 class="display" style="font-size:24px;letter-spacing:-0.03em">{{ $user->name }}</h2>
            <span class="pill {{ $user->is_active ? 'success' : 'danger' }}">
                <span class="dot"></span>{{ $user->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <div class="faint" style="font-size:12.5px;margin-top:3px">
            {{ $user->email }}{{ $user->phone ? ' · ' . $user->phone : '' }} · Joined {{ $user->created_at->format('d M Y') }}
        </div>
    </div>
    <a class="btn btn-outline" href="{{ route('admin.users.edit', $user) }}">
        <span class="ico" data-ico="edit" style="width:18px;height:18px"></span>Edit customer
    </a>
</div>

<div class="stat-grid">
    <div class="card lift stat">
        <span class="tile t-info"><span class="ico" data-ico="cart"></span></span>
        <div><div class="num">{{ number_format($totalOrders) }}</div><div class="lbl">Total orders</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-success"><span class="ico" data-ico="wallet"></span></span>
        <div><div class="num">{{ money($totalSpent, 0) }}</div><div class="lbl">Total spent</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-accent"><span class="ico" data-ico="chart"></span></span>
        <div><div class="num">{{ money($avgOrderValue, 0) }}</div><div class="lbl">Avg order</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-teal"><span class="ico" data-ico="check"></span></span>
        <div><div class="num">{{ number_format($completedOrders) }}</div><div class="lbl">Delivered</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile" style="background:var(--danger-soft);color:var(--danger)"><span class="ico" data-ico="x"></span></span>
        <div><div class="num">{{ number_format($cancelledOrders) }}</div><div class="lbl">Cancelled</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-warning"><span class="ico" data-ico="star"></span></span>
        <div><div class="num">{{ number_format($loyaltyBalance) }}</div><div class="lbl">Loyalty points</div></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,2fr) minmax(0,1fr);gap:18px;align-items:start" class="grid-2">

<div class="col-gap">

    {{-- Monthly spend --}}
    @if($monthlySpend->isNotEmpty())
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-accent"><span class="ico" data-ico="chart" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Monthly spend</h3><div class="sub">Last 12 months</div></div>
        </div>
        @php $maxSpend = $monthlySpend->max() ?: 1; @endphp
        <div class="row" style="gap:6px;align-items:flex-end;height:132px">
            @foreach($monthlySpend as $month => $amount)
            <div class="grow stack" style="align-items:center;gap:6px"
                 title="{{ $month }}: {{ money($amount, 2) }}">
                <div style="width:100%;border-radius:6px 6px 0 0;background:var(--accent);height:{{ max(4, round(($amount / $maxSpend) * 104)) }}px"></div>
                <span class="faint" style="font-size:10.5px;font-weight:600;line-height:1">
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M') }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Most purchased --}}
    @if($topProducts->isNotEmpty())
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-violet"><span class="ico" data-ico="package" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Most purchased</h3><div class="sub">Top {{ $topProducts->count() }} {{ Str::plural('product', $topProducts->count()) }}</div></div>
        </div>
        @php $maxQty = $topProducts->max('total_qty') ?: 1; @endphp
        <div class="stack" style="gap:14px">
            @foreach($topProducts as $item)
            <div>
                <div class="between" style="margin-bottom:6px;gap:10px">
                    <span style="font-weight:600;font-size:13.5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $item->product_name }}</span>
                    <span class="tnum faint" style="font-size:13px;flex-shrink:0">×{{ $item->total_qty }} · {{ money($item->total_spent, 0) }}</span>
                </div>
                <div style="height:6px;border-radius:99px;background:var(--surface-3)">
                    <div style="width:{{ round(($item->total_qty / $maxQty) * 100) }}%;height:100%;border-radius:99px;background:var(--violet)"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Order status breakdown --}}
    @if($statusCounts->isNotEmpty())
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-teal"><span class="ico" data-ico="layers" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Order status breakdown</h3></div>
        </div>
        <div class="row wrap" style="gap:10px">
            @foreach($statusCounts as $status => $cnt)
            <div class="row" style="gap:8px;background:var(--surface-2);border:1px solid var(--border);padding:8px 13px;border-radius:99px">
                <span style="width:7px;height:7px;border-radius:99px;background:{{ $statusColor($status) }};flex-shrink:0"></span>
                <span class="muted" style="font-size:13px">{{ ucfirst($status) }}</span>
                <b class="tnum" style="font-size:13.5px">{{ $cnt }}</b>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Order history --}}
    <div class="card flush">
        <div class="card-head" style="padding:20px 22px 14px;margin:0">
            <span class="tile sm t-info"><span class="ico" data-ico="receipt" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Order history</h3><div class="sub">{{ $totalOrders }} total</div></div>
        </div>
        @if($orders->isEmpty())
        <div class="empty">
            <span class="tile"><span class="ico" data-ico="cart" style="width:26px;height:26px"></span></span>
            <h4>No orders yet</h4>
            <p>This customer has not placed an order.</p>
        </div>
        @else
        <div class="table-scroll"><table class="table">
            <thead><tr>
                <th>Order</th>
                <th>Items</th>
                <th>Status</th>
                <th>Payment</th>
                <th style="text-align:right">Total</th>
            </tr></thead>
            <tbody>
                @foreach($orders->take(20) as $order)
                <tr class="hoverable" onclick="location.href='{{ route('admin.orders.show', $order) }}'">
                    <td>
                        <div style="font-weight:700;font-size:13.5px">#{{ $order->order_number }}</div>
                        <div class="faint" style="font-size:12px">{{ $order->created_at->format('d M Y, H:i') }}</div>
                    </td>
                    <td>
                        @if($order->items->isNotEmpty())
                        <div class="faint" style="font-size:12.5px;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                            {{ $order->items->take(2)->map(fn($i) => $i->product_name . ' ×' . $i->quantity)->implode(', ') }}{{ $order->items->count() > 2 ? ' +' . ($order->items->count() - 2) . ' more' : '' }}
                        </div>
                        @else
                        <span class="faint">—</span>
                        @endif
                    </td>
                    <td><span class="pill sm {{ $statusPill($order->status) }}">{{ ucfirst($order->status) }}</span></td>
                    <td><span class="pill sm {{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">{{ ucfirst($order->payment_status) }}</span></td>
                    <td style="text-align:right" class="tnum"><b>{{ money($order->total, 0) }}</b></td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @if($orders->count() > 20)
        <div style="padding:12px 22px;border-top:1px solid var(--border)" class="faint">
            Showing 20 of {{ $orders->count() }} orders.
        </div>
        @endif
        @endif
    </div>

    {{-- Refunds --}}
    @if($refunds->isNotEmpty())
    <div class="card flush">
        <div class="card-head" style="padding:20px 22px 14px;margin:0">
            <span class="tile sm t-warning"><span class="ico" data-ico="refresh" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Refunds</h3><div class="sub">{{ $refunds->count() }} {{ Str::plural('request', $refunds->count()) }}</div></div>
        </div>
        <div class="table-scroll"><table class="table">
            <thead><tr>
                <th>Order</th>
                <th>Reason</th>
                <th style="text-align:right">Amount</th>
                <th>Status</th>
            </tr></thead>
            <tbody>
                @foreach($refunds as $refund)
                <tr>
                    <td>
                        @if($refund->order)
                        <a href="{{ route('admin.orders.show', $refund->order) }}" style="font-weight:700;font-size:13.5px;color:var(--accent)">
                            #{{ $refund->order->order_number }}
                        </a>
                        @else
                        <span class="faint">—</span>
                        @endif
                    </td>
                    <td class="muted" style="font-size:13px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $refund->reason ?? '—' }}</td>
                    <td style="text-align:right" class="tnum">{{ money($refund->amount ?? 0, 0) }}</td>
                    <td>
                        <span class="pill sm {{ match($refund->status ?? '') { 'approved' => 'success', 'rejected' => 'danger', default => 'warning' } }}">
                            {{ ucfirst($refund->status ?? 'pending') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
    </div>
    @endif

    {{-- Reviews --}}
    @if($reviews->isNotEmpty())
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-warning"><span class="ico" data-ico="star" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Product reviews</h3><div class="sub">{{ $reviews->count() }} {{ Str::plural('review', $reviews->count()) }}</div></div>
        </div>
        <div class="stack" style="gap:10px">
            @foreach($reviews as $review)
            <div style="border:1px solid var(--border);border-radius:var(--radius-sm);padding:12px 14px">
                <div class="between" style="gap:10px;margin-bottom:4px">
                    <span style="font-weight:600;font-size:13.5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        {{ $review->product?->name ?? 'Deleted product' }}
                    </span>
                    <span class="row" style="gap:2px;flex-shrink:0">
                        @for($i = 1; $i <= 5; $i++)
                        <span class="ico" data-ico="star"
                              style="width:13px;height:13px;color:{{ $i <= $review->rating ? 'var(--warning)' : 'var(--border-strong)' }}"></span>
                        @endfor
                    </span>
                </div>
                @if($review->body)
                <p class="muted" style="font-size:12.5px;line-height:1.6">{{ Str::limit($review->body, 140) }}</p>
                @endif
                <div class="faint" style="font-size:11.5px;margin-top:5px">{{ $review->created_at->format('d M Y') }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>{{-- /main --}}

<div class="col-gap">

    {{-- Contact --}}
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-info"><span class="ico" data-ico="mail" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Contact details</h3></div>
        </div>
        <div class="stack" style="gap:11px">
            @foreach([
                'Name'       => $user->name,
                'Email'      => $user->email ?: '—',
                'Phone'      => $user->phone ?: '—',
                'Registered' => $user->created_at->format('d M Y'),
                'Last seen'  => $user->updated_at->diffForHumans(),
            ] as $label => $value)
            <div class="between" style="gap:12px;align-items:flex-start">
                <span class="muted" style="font-size:13px;flex-shrink:0">{{ $label }}</span>
                <span style="font-weight:600;font-size:13px;text-align:right;word-break:break-word">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Addresses --}}
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-violet"><span class="ico" data-ico="pin" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Saved addresses</h3><div class="sub">{{ $user->addresses->count() }} saved</div></div>
        </div>
        @forelse($user->addresses as $addr)
        <div style="border:1px solid {{ $addr->is_default ? 'var(--accent)' : 'var(--border)' }};border-radius:var(--radius-sm);padding:11px 13px;margin-bottom:8px">
            <div class="row wrap" style="gap:7px;margin-bottom:3px">
                <span style="font-weight:700;font-size:13px">{{ $addr->name ?? $user->name }}</span>
                @if($addr->is_default)<span class="pill sm accent">Default</span>@endif
            </div>
            @if(isset($addr->phone))
            <div class="faint" style="font-size:12px">{{ $addr->phone }}</div>
            @endif
            <div class="muted" style="font-size:12.5px;line-height:1.55;margin-top:2px">
                {{ collect([$addr->address_line ?? null, $addr->city ?? null, $addr->state ?? null])->filter()->implode(', ') ?: '—' }}
            </div>
        </div>
        @empty
        <p class="faint" style="font-size:13px">No saved addresses.</p>
        @endforelse
    </div>

    {{-- Payment preference --}}
    @if($paymentPreference->isNotEmpty())
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-teal"><span class="ico" data-ico="card" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Payment preference</h3></div>
        </div>
        <div class="stack" style="gap:13px">
            @foreach($paymentPreference as $method => $count)
            @php $pct = $totalOrders ? round(($count / $totalOrders) * 100) : 0; @endphp
            <div>
                <div class="between" style="margin-bottom:6px;gap:10px">
                    <span style="font-weight:600;font-size:13px;text-transform:capitalize">{{ str_replace('_', ' ', $method ?: 'Unknown') }}</span>
                    <span class="tnum faint" style="font-size:12.5px;flex-shrink:0">{{ $count }}× · {{ $pct }}%</span>
                </div>
                <div style="height:6px;border-radius:99px;background:var(--surface-3)">
                    <div style="width:{{ $pct }}%;height:100%;border-radius:99px;background:var(--teal)"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Loyalty --}}
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-warning"><span class="ico" data-ico="star" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Loyalty points</h3></div>
            <span class="display head-action tnum" style="font-size:19px;font-weight:700;color:var(--warning)">
                {{ number_format($loyaltyBalance) }}
            </span>
        </div>
        @forelse($loyaltyHistory as $pt)
        <div class="between" style="gap:10px;padding:7px 0;border-bottom:1px solid var(--border)">
            <span class="muted" style="font-size:12.5px">{{ $pt->description ?? ($pt->points > 0 ? 'Earned' : 'Redeemed') }}</span>
            <b class="tnum" style="font-size:12.5px;flex-shrink:0;color:{{ $pt->points > 0 ? 'var(--success)' : 'var(--danger)' }}">
                {{ $pt->points > 0 ? '+' : '' }}{{ $pt->points }}
            </b>
        </div>
        @empty
        <p class="faint" style="font-size:13px">No loyalty activity yet.</p>
        @endforelse
    </div>

</div>{{-- /sidebar --}}
</div>

@endsection

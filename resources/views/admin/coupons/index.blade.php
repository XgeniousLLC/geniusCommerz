@extends('admin.layouts.admin')
@section('title', 'Coupons')
@section('content')
@php
$total   = $coupons->total();
$active  = \App\Models\Coupon::where('is_active', true)->count();
$expired = \App\Models\Coupon::where('is_active', true)->whereNotNull('expires_at')->where('expires_at', '<', now())->count();
@endphp

<div class="page-head">
    <div>
        <h2 class="display">Coupons</h2>
        <div class="sub">Discount codes for your storefront</div>
    </div>
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">
        <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>Add Coupon
    </a>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="card lift stat">
        <span class="tile t-info"><span class="ico" data-ico="percent"></span></span>
        <div><div class="num">{{ $total }}</div><div class="lbl">Total coupons</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-success"><span class="ico" data-ico="check"></span></span>
        <div><div class="num">{{ $active }}</div><div class="lbl">Active</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-warning"><span class="ico" data-ico="clock"></span></span>
        <div><div class="num">{{ $expired }}</div><div class="lbl">Expired</div></div>
    </div>
</div>

<div class="card flush" style="margin-bottom:14px">
    <form method="GET" style="padding:14px 18px;display:flex;gap:10px;align-items:center;border-bottom:1px solid var(--border)">
        <div style="flex:1;max-width:280px">
            <input class="input" type="text" name="search" value="{{ request('search') }}" placeholder="Search coupon code…">
        </div>
        <button type="submit" class="btn btn-sm btn-outline">Filter</button>
        @if(request()->filled('search'))
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-sm">Clear</a>
        @endif
    </form>
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type / Value</th>
                    <th>Usage</th>
                    <th>Validity</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                <tr class="hoverable">
                    <td>
                        <span class="mono pill sm" style="font-size:12.5px;font-weight:700">{{ $coupon->code }}</span>
                        @if($coupon->description)
                        <div class="faint" style="font-size:12px;margin-top:4px">{{ $coupon->description }}</div>
                        @endif
                    </td>
                    <td>
                        <span style="font-weight:600;font-size:13.5px">
                            @if($coupon->type === 'percent')
                                {{ $coupon->value }}% off
                            @else
                                {{ money($coupon->value, 2) }} off
                            @endif
                        </span>
                        @if($coupon->minimum_order)
                        <div class="faint" style="font-size:12px">Min {{ money($coupon->minimum_order, 2) }}</div>
                        @endif
                    </td>
                    <td class="tnum" style="font-size:13px">
                        {{ $coupon->usage_count }}{{ $coupon->usage_limit ? ' / ' . $coupon->usage_limit : '' }}
                        <div class="faint" style="font-size:12px">{{ $coupon->orders_count }} orders</div>
                    </td>
                    <td class="faint" style="font-size:12.5px">
                        @if($coupon->starts_at || $coupon->expires_at)
                            {{ $coupon->starts_at?->format('d M Y') ?? '∞' }} → {{ $coupon->expires_at?->format('d M Y') ?? '∞' }}
                        @else
                            No limit
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;flex-wrap:wrap;gap:5px">
                            <span class="pill sm {{ $coupon->is_active ? 'success' : '' }}">
                                <span class="dot"></span>{{ $coupon->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($coupon->expires_at?->isPast())
                            <span class="pill sm warning">Expired</span>
                            @endif
                            @if($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit)
                            <span class="pill sm danger">Used up</span>
                            @endif
                        </div>
                    </td>
                    <td style="text-align:right">
                        <div class="row" style="gap:6px;justify-content:flex-end">
                            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="icon-btn">
                                <span class="ico" data-ico="edit" style="width:15px;height:15px"></span>
                            </a>
                            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}"
                                  onsubmit="return confirm('Delete coupon {{ $coupon->code }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="icon-btn">
                                    <span class="ico" data-ico="trash" style="width:15px;height:15px"></span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:48px 20px">
                        <div class="faint" style="font-size:13.5px">No coupons found.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($coupons->hasPages())
    <div style="padding:14px 20px;border-top:1px solid var(--border)">{{ $coupons->links() }}</div>
    @endif
</div>

@endsection

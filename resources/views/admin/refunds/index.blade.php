@extends('admin.layouts.admin')
@section('title', 'Refunds')
@section('content')
@php
$pending  = \App\Models\Refund::where('status','pending')->count();
$approved = \App\Models\Refund::where('status','approved')->count();
$rejected = \App\Models\Refund::where('status','rejected')->count();
$statusColors = ['pending'=>'warning','approved'=>'success','rejected'=>'danger','processed'=>'info'];
$reasons = \App\Models\Refund::REASONS;
@endphp

<div class="page-head">
    <div>
        <h2 class="display">Refunds</h2>
        <div class="sub">{{ $refunds->total() }} total requests</div>
    </div>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="card lift stat">
        <span class="tile t-warning"><span class="ico" data-ico="clock"></span></span>
        <div><div class="num">{{ $pending }}</div><div class="lbl">Pending</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-success"><span class="ico" data-ico="check"></span></span>
        <div><div class="num">{{ $approved }}</div><div class="lbl">Approved</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-danger"><span class="ico" data-ico="x"></span></span>
        <div><div class="num">{{ $rejected }}</div><div class="lbl">Rejected</div></div>
    </div>
</div>

@php $filter = request('status',''); @endphp
<style>.ref-seg a{display:inline-flex;align-items:center;padding:0 14px;height:32px;font-size:13px;font-weight:600;border:none;background:none;color:var(--text-muted);cursor:pointer;border-radius:8px;text-decoration:none}.ref-seg a.active,.ref-seg a:hover{background:var(--surface-2);color:var(--text)}</style>
<div class="seg sm ref-seg" style="margin-bottom:16px">
    @foreach(['' => 'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','processed'=>'Processed'] as $val => $label)
    <a href="{{ route('admin.refunds.index', $val ? ['status'=>$val] : []) }}"
       class="{{ $filter === $val ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="card flush">
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Reason</th>
                    <th style="text-align:right">Amount</th>
                    <th>Status</th>
                    <th>Requested</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($refunds as $refund)
                <tr class="hoverable">
                    <td>
                        <a href="{{ route('admin.refunds.show', $refund) }}"
                           class="mono" style="font-weight:700;font-size:13px;color:var(--accent);text-decoration:none">
                            #{{ $refund->order->order_number }}
                        </a>
                    </td>
                    <td style="font-weight:600;font-size:13.5px">{{ $refund->user->name }}</td>
                    <td class="muted" style="font-size:13px">{{ $reasons[$refund->reason] ?? $refund->reason }}</td>
                    <td style="text-align:right;font-weight:700" class="tnum">
                        @if($refund->amount) {{ money($refund->amount, 0) }} @else — @endif
                    </td>
                    <td>
                        <span class="pill sm {{ $statusColors[$refund->status] ?? '' }}">
                            <span class="dot"></span>{{ ucfirst($refund->status) }}
                        </span>
                    </td>
                    <td class="faint" style="font-size:13px">{{ $refund->created_at->format('d M Y') }}</td>
                    <td style="text-align:right">
                        <a href="{{ route('admin.refunds.show', $refund) }}" class="btn btn-sm btn-outline">Review</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:48px 20px">
                        <div class="faint" style="font-size:13.5px">No refund requests found.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($refunds->hasPages())
    <div style="padding:14px 20px;border-top:1px solid var(--border)">{{ $refunds->withQueryString()->links() }}</div>
    @endif
</div>

@endsection

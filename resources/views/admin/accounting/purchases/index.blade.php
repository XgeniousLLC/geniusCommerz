@extends('admin.layouts.admin')

@section('title', 'Purchase Orders')

@section('content')
@php
use App\Models\PurchaseOrder;
$openCount      = PurchaseOrder::whereIn('status', ['draft','ordered','partial'])->count();
$inTransitUnits = \App\Models\PurchaseOrderItem::whereHas('purchaseOrder', fn($q) => $q->whereIn('status',['ordered','partial']))->sum(\DB::raw('quantity - received_qty'));
$committedSpend = PurchaseOrder::whereIn('status', ['draft','ordered','partial'])
    ->with('items')->get()->sum(fn($o) => $o->totalCost());
$receivedRecent = PurchaseOrder::where('status','received')
    ->where('updated_at','>=',now()->subDays(30))
    ->with('items')->get()->sum(fn($o) => $o->totalCost());
$statusColors   = ['draft'=>'','ordered'=>'info','partial'=>'warning','received'=>'success'];
@endphp

<div class="page-head">
    <div>
        <h2 class="display">Purchase Orders</h2>
        <div class="sub">Restock inventory &amp; track supplier shipments</div>
    </div>
    <a href="{{ route('admin.accounting.purchases.create') }}" class="btn btn-primary">
        <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>New Purchase Order
    </a>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr))">
    <div class="card lift stat">
        <span class="tile t-info"><span class="ico" data-ico="doc"></span></span>
        <div><div class="num">{{ $openCount }}</div><div class="lbl">Open orders</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-violet"><span class="ico" data-ico="truck"></span></span>
        <div><div class="num">{{ number_format($inTransitUnits) }} units</div><div class="lbl">In transit</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-warning"><span class="ico" data-ico="wallet"></span></span>
        <div><div class="num">{{ money($committedSpend, 0) }}</div><div class="lbl">Committed spend</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-success"><span class="ico" data-ico="package"></span></span>
        <div><div class="num">{{ money($receivedRecent, 0) }}</div><div class="lbl">Received (30d)</div></div>
    </div>
</div>

@php $filter = request('status', 'all'); @endphp
<style>.po-seg a{display:inline-flex;align-items:center;padding:0 14px;height:32px;font-size:13px;font-weight:600;border:none;background:none;color:var(--text-muted);cursor:pointer;border-radius:8px;text-decoration:none}.po-seg a.active,.po-seg a:hover{background:var(--surface-2);color:var(--text)}</style>
<div class="seg sm po-seg" style="margin-bottom:16px">
    @foreach(['all'=>'All','draft'=>'Draft','ordered'=>'Ordered','partial'=>'Partial','received'=>'Received'] as $key => $label)
    <a href="{{ route('admin.accounting.purchases.index', $key !== 'all' ? ['status'=>$key] : []) }}"
       class="{{ $filter === $key ? 'active' : '' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="card flush">
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th style="text-align:right">Items</th>
                    <th style="text-align:right">Total Cost</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $po)
                <tr>
                    <td>
                        <a href="{{ route('admin.accounting.purchases.show', $po) }}"
                           class="mono" style="font-size:13px;font-weight:700;color:var(--accent);text-decoration:none">
                            {{ $po->reference }}
                        </a>
                    </td>
                    <td>
                        <div class="row" style="gap:9px">
                            <span class="avatar" style="width:28px;height:28px;font-size:11px;background:var(--accent)">
                                {{ strtoupper(substr($po->supplier_name, 0, 2)) }}
                            </span>
                            <span style="font-weight:600;font-size:13.5px">{{ $po->supplier_name }}</span>
                        </div>
                    </td>
                    <td style="font-size:13px;font-weight:600">{{ $po->order_date->format('d M Y') }}</td>
                    <td style="text-align:right" class="tnum">{{ $po->items_count }}</td>
                    <td style="text-align:right" class="tnum"><b>{{ money($po->totalCost(), 2) }}</b></td>
                    <td>
                        <span class="pill sm {{ $statusColors[$po->status] ?? '' }}">
                            <span class="dot"></span>{{ ucfirst($po->status) }}
                        </span>
                    </td>
                    <td style="text-align:right">
                        <a href="{{ route('admin.accounting.purchases.show', $po) }}" class="btn btn-sm btn-outline">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:48px 20px">
                        <div class="faint" style="font-size:13.5px">No purchase orders yet.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div style="padding:14px 20px;border-top:1px solid var(--border)">{{ $orders->links() }}</div>
    @endif
</div>

@endsection

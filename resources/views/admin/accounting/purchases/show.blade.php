@extends('admin.layouts.admin')

@section('title', 'PO ' . $purchase->reference)

@section('content')
@php
$statusColors = ['draft'=>'','ordered'=>'info','partial'=>'warning','received'=>'success'];
@endphp

<div class="page-head">
    <div>
        <h2 class="display mono">{{ $purchase->reference }}</h2>
        <div class="sub">{{ $purchase->supplier_name }} · {{ $purchase->order_date->format('d M Y') }}</div>
    </div>
    <div class="row" style="gap:10px">
        <span class="pill {{ $statusColors[$purchase->status] ?? '' }}">
            <span class="dot"></span>{{ ucfirst($purchase->status) }}
        </span>
        <a href="{{ route('admin.accounting.purchases.index') }}" class="btn btn-outline">
            <span class="ico" data-ico="arrowLeft" style="width:16px;height:16px"></span>Back
        </a>
    </div>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:22px">
    <div class="card lift stat">
        <span class="tile t-info"><span class="ico" data-ico="package"></span></span>
        <div><div class="num">৳{{ number_format($purchase->totalCost(), 0) }}</div><div class="lbl">Product Cost</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-violet"><span class="ico" data-ico="truck"></span></span>
        <div><div class="num">৳{{ number_format($purchase->totalShipment(), 0) }}</div><div class="lbl">Shipment Cost</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-accent"><span class="ico" data-ico="wallet"></span></span>
        <div><div class="num">৳{{ number_format($purchase->landedCost(), 0) }}</div><div class="lbl">Landed Cost</div></div>
    </div>
</div>

<div class="col-gap" style="max-width:900px">

    <div class="card flush">
        <div class="between" style="padding:18px 20px 14px">
            <div class="card-head" style="margin:0">
                <span class="tile sm t-accent"><span class="ico" data-ico="package" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Items</h3></div>
            </div>
        </div>
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Variant</th>
                        <th style="text-align:right">Ordered</th>
                        <th style="text-align:right">Unit Cost</th>
                        <th style="text-align:right">Line Total</th>
                        <th style="text-align:right">Received</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->items as $item)
                    <tr>
                        <td style="font-weight:600;font-size:13.5px">{{ $item->product?->name ?? 'Deleted' }}</td>
                        <td class="muted" style="font-size:13px">{{ $item->variant?->label() ?? '—' }}</td>
                        <td style="text-align:right" class="tnum">{{ $item->quantity }}</td>
                        <td style="text-align:right" class="tnum">৳{{ number_format($item->unit_cost, 2) }}</td>
                        <td style="text-align:right" class="tnum"><b>৳{{ number_format($item->lineTotal(), 0) }}</b></td>
                        <td style="text-align:right">
                            <span style="font-weight:700;font-size:13px;color:{{ $item->received_qty >= $item->quantity ? 'var(--success)' : 'var(--warning)' }}">
                                {{ $item->received_qty }} / {{ $item->quantity }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($purchase->shipments->isNotEmpty())
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-violet"><span class="ico" data-ico="truck" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Shipment Costs</h3></div>
        </div>
        <div class="col-gap" style="--gap:0;margin-top:14px">
            @foreach($purchase->shipments as $s)
            <div class="between" style="padding:12px 0;border-bottom:1px solid var(--border)">
                <div>
                    <div style="font-weight:600;font-size:13.5px">{{ $s->description }}</div>
                    <div class="faint" style="font-size:12px">{{ $s->shipment_date->format('d M Y') }}</div>
                </div>
                <div style="font-weight:700;font-size:15px;color:var(--violet)">৳{{ number_format($s->amount, 0) }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($purchase->status !== 'received')
    <div class="card pad">
        <div class="card-head" style="margin-bottom:16px">
            <span class="tile sm t-success"><span class="ico" data-ico="check" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Update Received Quantities</h3></div>
        </div>
        <form method="POST" action="{{ route('admin.accounting.purchases.receive', $purchase) }}" class="col-gap" style="--gap:12px">
            @csrf @method('PATCH')
            @foreach($purchase->items as $item)
            <div class="between" style="gap:14px">
                <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                <div style="font-size:13.5px;color:var(--text)">
                    {{ $item->product?->name }}
                    @if($item->variant?->label())
                    <span class="muted"> · {{ $item->variant->label() }}</span>
                    @endif
                    <span class="faint" style="font-size:12px;margin-left:6px">(ordered: {{ $item->quantity }})</span>
                </div>
                <input class="input" type="number" name="items[{{ $loop->index }}][received_qty]"
                       value="{{ $item->received_qty }}" min="0" max="{{ $item->quantity }}"
                       style="width:100px;text-align:right">
            </div>
            @endforeach
            <div style="padding-top:6px">
                <button type="submit" class="btn btn-primary">Update Receiving</button>
            </div>
        </form>
    </div>
    @endif

    <div style="padding-top:4px">
        <form method="POST" action="{{ route('admin.accounting.purchases.destroy', $purchase) }}"
              onsubmit="return confirm('Delete this purchase order?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline"
                    style="color:var(--danger);border-color:var(--danger)">
                <span class="ico" data-ico="trash" style="width:16px;height:16px"></span>Delete Order
            </button>
        </form>
    </div>

</div>
@endsection

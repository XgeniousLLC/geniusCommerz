@extends('admin.layouts.admin')
@section('title', 'Orders Report')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Orders Report</h2>
        <div class="sub">Order volume, cancellations, and payment method breakdown</div>
    </div>
    <a href="{{ route('admin.reports.export', array_merge(request()->all(), ['type' => 'orders'])) }}" class="btn btn-outline btn-sm">
        <span class="ico" data-ico="download" style="width:15px;height:15px"></span>Export CSV
    </a>
</div>

<div class="card pad" style="margin-bottom:16px">
    <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
        <div class="field" style="min-width:150px">
            <label class="lbl">Period</label>
            <select name="period" class="select" onchange="this.form.submit()">
                @foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year','custom'=>'Custom Range'] as $val => $lbl)
                    <option value="{{ $val }}" @selected(request('period', 'month') === $val)>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        @if(request('period') === 'custom' || request('start_date'))
        <div class="field" style="min-width:140px">
            <label class="lbl">From</label>
            <input type="date" name="start_date" class="input" value="{{ request('start_date', $startDate->toDateString()) }}">
        </div>
        <div class="field" style="min-width:140px">
            <label class="lbl">To</label>
            <input type="date" name="end_date" class="input" value="{{ request('end_date', $endDate->toDateString()) }}">
        </div>
        <button type="submit" class="btn">Apply</button>
        @endif
    </form>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));margin-bottom:16px">
    <div class="card lift stat">
        <span class="tile sm t-info"><span class="ico" data-ico="receipt" style="width:18px;height:18px"></span></span>
        <div class="num">{{ number_format($totalOrders) }}</div>
        <div class="lbl">Total Orders</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-danger"><span class="ico" data-ico="close" style="width:18px;height:18px"></span></span>
        <div class="num">{{ number_format($cancelledOrders) }}</div>
        <div class="lbl">Cancelled Orders</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm {{ $cancellationRate > 20 ? 't-danger' : ($cancellationRate > 10 ? 't-warning' : 't-success') }}">
            <span class="ico" data-ico="chart" style="width:18px;height:18px"></span>
        </span>
        <div class="num">{{ $cancellationRate }}%</div>
        <div class="lbl">Cancellation Rate</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Orders by Status</h3></div></div>
        @php
            $statusPills = ['pending'=>'t-warning','processing'=>'t-info','shipped'=>'t-violet','delivered'=>'t-success','cancelled'=>'t-danger','refunded'=>'t-teal'];
        @endphp
        @if($byStatus->isEmpty())
            <p style="text-align:center;color:var(--text-muted);padding:24px 0;font-size:13px">No data.</p>
        @else
        <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px">
            @foreach($byStatus as $row)
            <div style="display:flex;align-items:center;justify-content:space-between">
                <span class="pill sm {{ $statusPills[$row->status] ?? 't-teal' }}" style="text-transform:capitalize">{{ $row->status }}</span>
                <div style="text-align:right">
                    <strong style="font-size:14px">{{ number_format($row->count) }}</strong>
                    <span style="font-size:12px;color:var(--text-muted);margin-left:8px">{{ money($row->revenue) }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Orders by Payment Method</h3></div></div>
        @if($byPaymentMethod->isEmpty())
            <p style="text-align:center;color:var(--text-muted);padding:24px 0;font-size:13px">No data.</p>
        @else
        @php $maxCount = $byPaymentMethod->max('count') ?: 1; @endphp
        <div style="display:flex;flex-direction:column;gap:14px;margin-top:8px">
            @foreach($byPaymentMethod as $row)
            @php $pct = round(($row->count / $maxCount) * 100); @endphp
            <div>
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
                    <span style="text-transform:capitalize;font-weight:600">{{ str_replace('_', ' ', $row->payment_method) }}</span>
                    <strong>{{ number_format($row->count) }} orders</strong>
                </div>
                <div style="background:var(--surface-3);border-radius:4px;height:6px;overflow:hidden">
                    <div style="width:{{ $pct }}%;height:6px;background:var(--violet);border-radius:4px"></div>
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ money($row->revenue) }}</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

@endsection

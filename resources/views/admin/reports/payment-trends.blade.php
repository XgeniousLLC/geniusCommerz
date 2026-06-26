@extends('admin.layouts.admin')
@section('title', 'Payment Trends')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Payment Trends</h2>
        <div class="sub">Payment method usage, payment status, and revenue by payment channel</div>
    </div>
</div>

<div class="card pad" style="margin-bottom:16px">
    <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
        <div class="field" style="min-width:150px">
            <label class="lbl">Period</label>
            <select name="period" class="select" onchange="this.form.submit()">
                @foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year','custom'=>'Custom'] as $val=>$lbl)
                    <option value="{{ $val }}" {{ $request->input('period','month')===$val?'selected':'' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="field" style="min-width:140px">
            <label class="lbl">From</label>
            <input type="date" name="start_date" class="input" value="{{ $request->input('start_date',$startDate->format('Y-m-d')) }}">
        </div>
        <div class="field" style="min-width:140px">
            <label class="lbl">To</label>
            <input type="date" name="end_date" class="input" value="{{ $request->input('end_date',$endDate->format('Y-m-d')) }}">
        </div>
        <button type="submit" class="btn">Apply</button>
    </form>
</div>

@php $totalOrders = $byMethod->sum('orders') ?: 1; $totalRev = $byMethod->sum('revenue') ?: 1; @endphp

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Revenue by Payment Method</h3></div></div>
        @if($byMethod->isEmpty())
            <p style="text-align:center;color:var(--text-muted);padding:32px 0;font-size:13px">No orders in this period.</p>
        @else
        <div style="display:flex;flex-direction:column;gap:14px;margin-top:8px">
            @foreach($byMethod as $row)
            @php $pct = round(($row->revenue/$totalRev)*100,1); @endphp
            <div>
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
                    <span style="font-weight:600;text-transform:capitalize">{{ str_replace('_',' ',$row->method) }}</span>
                    <span>৳{{ number_format($row->revenue,0) }} <span style="font-size:11px;color:var(--text-muted)">({{ $pct }}%)</span></span>
                </div>
                <div style="background:var(--surface-3);border-radius:4px;height:6px;overflow:hidden">
                    <div style="width:{{ $pct }}%;height:6px;background:var(--accent);border-radius:4px"></div>
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ number_format($row->orders) }} orders · AOV: ৳{{ number_format($row->avg_order,0) }}</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Payment Status Breakdown</h3></div></div>
        @php
            $statusPills = ['unpaid'=>'t-danger','paid'=>'t-success','partially_refunded'=>'t-warning','refunded'=>'t-info'];
            $statusTotal = $paymentStatus->sum('count') ?: 1;
        @endphp
        <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px">
            @foreach($paymentStatus as $row)
            @php $pct = round(($row->count/$statusTotal)*100,1); @endphp
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--surface-2);border-radius:8px">
                <span class="pill sm {{ $statusPills[$row->payment_status] ?? 't-teal' }}" style="text-transform:capitalize">{{ str_replace('_',' ',$row->payment_status) }}</span>
                <div style="text-align:right">
                    <strong>{{ number_format($row->count) }}</strong>
                    <span style="font-size:11px;color:var(--text-muted);margin-left:4px">({{ $pct }}%)</span>
                    <div style="font-size:11px;color:var(--text-muted)">৳{{ number_format($row->revenue,0) }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card flush">
    <div class="card-head" style="padding:16px 20px"><div class="ct"><h3>Detailed Payment Method Stats</h3></div></div>
    <table class="table">
        <thead><tr>
            <th>Method</th>
            <th style="text-align:right">Orders</th>
            <th style="text-align:right">% of Orders</th>
            <th style="text-align:right">Revenue</th>
            <th style="text-align:right">% of Revenue</th>
            <th style="text-align:right">Avg Order Value</th>
        </tr></thead>
        <tbody>
            @foreach($byMethod as $row)
            @php
                $orderPct = round(($row->orders/$totalOrders)*100,1);
                $revPct = round(($row->revenue/$totalRev)*100,1);
            @endphp
            <tr>
                <td style="font-weight:600;text-transform:capitalize">{{ str_replace('_',' ',$row->method) }}</td>
                <td style="text-align:right">{{ number_format($row->orders) }}</td>
                <td style="text-align:right;color:var(--text-muted)">{{ $orderPct }}%</td>
                <td style="text-align:right;font-weight:600">৳{{ number_format($row->revenue,0) }}</td>
                <td style="text-align:right;color:var(--text-muted)">{{ $revPct }}%</td>
                <td style="text-align:right;color:var(--text-muted)">৳{{ number_format($row->avg_order,0) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection

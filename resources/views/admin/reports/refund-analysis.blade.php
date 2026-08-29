@extends('admin.layouts.admin')
@section('title', 'Refund Analysis')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Refund Analysis</h2>
        <div class="sub">Refund requests by reason, status, and financial impact</div>
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

@php
    $refundRate = $stats['total_orders'] > 0 ? round(($stats['total_requests']/$stats['total_orders'])*100,2) : 0;
    $statusPills = ['pending'=>'t-warning','approved'=>'t-info','rejected'=>'t-danger','processed'=>'t-success'];
@endphp

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:16px">
    <div class="card lift stat">
        <span class="tile sm t-info"><span class="ico" data-ico="refresh" style="width:18px;height:18px"></span></span>
        <div class="num">{{ number_format($stats['total_requests']) }}</div>
        <div class="lbl">Total Requests</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-warning"><span class="ico" data-ico="chart" style="width:18px;height:18px"></span></span>
        <div class="num">{{ $refundRate }}%</div>
        <div class="lbl">Refund Rate</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-danger"><span class="ico" data-ico="dollar" style="width:18px;height:18px"></span></span>
        <div class="num">{{ money($stats['total_refunded'], 0) }}</div>
        <div class="lbl">Total Refunded</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-warning"><span class="ico" data-ico="alert" style="width:18px;height:18px"></span></span>
        <div class="num">{{ number_format($stats['pending']) }}</div>
        <div class="lbl">Pending Review</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Requests by Status</h3></div></div>
        <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px">
            @foreach(['pending','approved','rejected','processed'] as $s)
            @php $row = $byStatus->get($s); $cnt = $row?->count ?? 0; $amt = $row?->total_amount ?? 0; @endphp
            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--surface-2);border-radius:8px">
                <span class="pill sm {{ $statusPills[$s] ?? 't-teal' }}" style="text-transform:capitalize">{{ $s }}</span>
                <div style="text-align:right">
                    <strong style="font-size:14px">{{ $cnt }}</strong>
                    <span style="font-size:12px;color:var(--text-muted);margin-left:8px">{{ money($amt, 0) }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Top Refund Reasons</h3></div></div>
        @if($byReason->isEmpty())
            <p style="text-align:center;color:var(--text-muted);padding:32px 0;font-size:13px">No refunds in this period.</p>
        @else
        @php $maxReason = $byReason->max('count') ?: 1; @endphp
        <div style="display:flex;flex-direction:column;gap:14px;margin-top:8px">
            @foreach($byReason as $row)
            @php $pct = round(($row->count/$maxReason)*100); @endphp
            <div>
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
                    <span style="text-transform:capitalize">{{ str_replace('_',' ',$row->reason) }}</span>
                    <span><strong>{{ $row->count }}</strong> <span style="font-size:11px;color:var(--text-muted)">avg {{ round($row->avg_resolution_hours ?? 0) }}h</span></span>
                </div>
                <div style="background:var(--surface-3);border-radius:4px;height:6px;overflow:hidden">
                    <div style="width:{{ $pct }}%;height:6px;background:var(--danger);border-radius:4px;opacity:.7"></div>
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ money($row->total_amount, 0) }} total</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

@if($recent->isNotEmpty())
<div class="card flush">
    <div class="card-head" style="padding:16px 20px"><div class="ct"><h3>Recent Refund Requests</h3></div></div>
    <table class="table">
        <thead><tr>
            <th>Order</th>
            <th>Customer</th>
            <th>Reason</th>
            <th style="text-align:right">Amount</th>
            <th>Status</th>
            <th style="text-align:right">Date</th>
        </tr></thead>
        <tbody>
            @foreach($recent as $r)
            <tr>
                <td><a href="{{ route('admin.refunds.show',$r->id) }}" style="font-family:monospace;font-size:12px;color:var(--accent);text-decoration:none">#{{ $r->order_number }}</a></td>
                <td>{{ $r->customer_name }}</td>
                <td style="text-transform:capitalize;color:var(--text-muted)">{{ str_replace('_',' ',$r->reason) }}</td>
                <td style="text-align:right">{{ $r->amount ? money($r->amount, 0) : '—' }}</td>
                <td><span class="pill sm {{ $statusPills[$r->status] ?? 't-teal' }}" style="text-transform:capitalize">{{ $r->status }}</span></td>
                <td style="text-align:right;font-size:12px;color:var(--text-muted)">{{ \Carbon\Carbon::parse($r->created_at)->format('d M Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection

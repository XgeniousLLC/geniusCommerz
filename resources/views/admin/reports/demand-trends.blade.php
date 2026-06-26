@extends('admin.layouts.admin')
@section('title', 'Demand Trends')

@section('content')

@php
    $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $dayNames   = ['','Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    $maxMonthly = $monthly->max('revenue') ?: 1;
    $maxDow     = $dayOfWeek->max('orders') ?: 1;
    $maxH       = $hourly->max('orders') ?: 1;
@endphp

<div class="page-head">
    <div>
        <h2 class="display">Demand Trends</h2>
        <div class="sub">Monthly, hourly, and day-of-week sales patterns</div>
    </div>
    <form method="GET" style="display:flex;align-items:center;gap:8px">
        <label class="lbl" style="margin:0">Year</label>
        <select name="year" class="select" onchange="this.form.submit()" style="min-width:100px">
            @foreach(array_reverse($yearRange) as $y)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </form>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Monthly Revenue — {{ $year }}</h3></div></div>
        <div style="display:flex;align-items:flex-end;gap:3px;height:120px;margin-bottom:12px">
            @for($m = 1; $m <= 12; $m++)
            @php $row = $monthly->get($m); $pct = $row ? round(($row->revenue / $maxMonthly) * 100) : 0; @endphp
            <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px"
                 title="{{ $monthNames[$m-1] }}: {{ $row ? '৳'.number_format($row->revenue,0) : '৳0' }}">
                <div style="width:100%;height:{{ max(2,$pct) }}%;background:var(--accent);border-radius:3px 3px 0 0;opacity:.85"></div>
                <span style="font-size:9px;color:var(--text-muted)">{{ $monthNames[$m-1] }}</span>
            </div>
            @endfor
        </div>
        <table class="table" style="font-size:12px">
            <thead><tr><th>Month</th><th style="text-align:right">Orders</th><th style="text-align:right">Revenue</th><th style="text-align:right">AOV</th></tr></thead>
            <tbody>
                @for($m = 1; $m <= 12; $m++)
                @php $row = $monthly->get($m); @endphp
                <tr>
                    <td>{{ $monthNames[$m-1] }}</td>
                    <td style="text-align:right">{{ $row ? number_format($row->orders) : '—' }}</td>
                    <td style="text-align:right;font-weight:600">{{ $row ? '৳'.number_format($row->revenue,0) : '—' }}</td>
                    <td style="text-align:right;color:var(--text-muted)">{{ $row ? '৳'.number_format($row->avg_order,0) : '—' }}</td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Sales by Day of Week — {{ $year }}</h3></div></div>
        <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px">
            @foreach($dayOfWeek as $row)
            @php $pct = $maxDow > 0 ? round(($row->orders / $maxDow) * 100) : 0; @endphp
            <div style="display:flex;align-items:center;gap:10px">
                <span style="width:28px;font-size:12px;color:var(--text-muted);font-weight:600">{{ $dayNames[$row->dow] ?? $row->dow }}</span>
                <div style="flex:1;height:18px;background:var(--surface-3);border-radius:4px;overflow:hidden">
                    <div style="width:{{ max(2,$pct) }}%;height:18px;background:var(--violet);border-radius:4px;opacity:.8"></div>
                </div>
                <span style="font-size:12px;color:var(--text-muted);width:80px;text-align:right">{{ number_format($row->orders) }} orders</span>
                <span style="font-size:12px;color:var(--text-muted);width:90px;text-align:right">৳{{ number_format($row->revenue,0) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card pad" style="margin-bottom:16px">
    <div class="card-head"><div class="ct"><h3>Hourly Order Distribution — {{ $year }}</h3></div></div>
    <div style="display:flex;align-items:flex-end;gap:2px;height:100px;margin-bottom:8px">
        @for($h = 0; $h <= 23; $h++)
        @php $row = $hourly->get($h); $pct = $row ? round(($row->orders/$maxH)*100) : 0; @endphp
        @php $bg = $pct > 70 ? 'var(--warning)' : ($pct > 40 ? '#f59e0b80' : 'var(--surface-3)'); @endphp
        <div style="flex:1;display:flex;flex-direction:column;align-items:center"
             title="{{ $h }}:00 — {{ $row ? $row->orders.' orders' : '0' }}">
            <div style="width:100%;height:{{ max(2,$pct) }}%;background:{{ $bg }};border-radius:3px 3px 0 0"></div>
            <span style="font-size:8px;color:var(--text-muted);margin-top:2px">{{ str_pad($h,2,'0',STR_PAD_LEFT) }}</span>
        </div>
        @endfor
    </div>
    <p style="font-size:11px;color:var(--text-muted)">Hours 0–23 (server time). Darker = more orders. Useful for scheduling campaigns and flash sales.</p>
</div>

@if($yoy->isNotEmpty())
<div class="card flush">
    <div class="card-head" style="padding:16px 20px"><div class="ct"><h3>Year-over-Year Comparison</h3></div></div>
    @php
        $yoyByYear = $yoy->groupBy('year');
        $years = $yoyByYear->keys()->sort()->values();
    @endphp
    <table class="table">
        <thead><tr>
            <th>Month</th>
            @foreach($years as $y)<th style="text-align:right">{{ $y }}</th>@endforeach
        </tr></thead>
        <tbody>
            @for($m = 1; $m <= 12; $m++)
            <tr>
                <td style="font-weight:600">{{ $monthNames[$m-1] }}</td>
                @foreach($years as $y)
                @php $entry = $yoyByYear->get($y)?->firstWhere('month', $m); @endphp
                <td style="text-align:right">{{ $entry ? '৳'.number_format($entry->revenue,0) : '—' }}</td>
                @endforeach
            </tr>
            @endfor
        </tbody>
    </table>
</div>
@endif

@endsection

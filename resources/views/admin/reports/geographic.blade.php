@extends('admin.layouts.admin')
@section('title', 'Geographic Sales')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Geographic Sales Distribution</h2>
        <div class="sub">Revenue and order counts by city and state/division</div>
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

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Top Cities by Revenue</h3><div class="sub">Top 30</div></div></div>
        @if($byCity->isEmpty())
            <p style="text-align:center;color:var(--text-muted);padding:32px 0;font-size:13px">No geographic data. Ensure shipping addresses include city.</p>
        @else
        @php $maxCity = $byCity->max('revenue') ?: 1; @endphp
        <div style="display:flex;flex-direction:column;gap:10px;margin-top:8px;max-height:400px;overflow-y:auto">
            @foreach($byCity as $i => $row)
            @php $pct = $totalRevenue > 0 ? round(($row->revenue/$totalRevenue)*100,1) : 0; @endphp
            <div style="display:flex;align-items:center;gap:8px">
                <span style="font-size:11px;color:var(--text-muted);font-family:monospace;width:20px">{{ $i+1 }}</span>
                <div style="flex:1;min-width:0">
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:3px">
                        <span style="font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $row->city }}</span>
                        <span style="flex-shrink:0;margin-left:8px">৳{{ number_format($row->revenue,0) }} <span style="font-size:11px;color:var(--text-muted)">({{ $pct }}%)</span></span>
                    </div>
                    <div style="background:var(--surface-3);border-radius:3px;height:4px;overflow:hidden">
                        <div style="width:{{ min(100, round(($row->revenue/$maxCity)*100)) }}%;height:4px;background:var(--accent);border-radius:3px"></div>
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ number_format($row->orders) }} orders · AOV ৳{{ number_format($row->avg_order,0) }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Revenue by State / Division</h3></div></div>
        @if($byState->isEmpty())
            <p style="text-align:center;color:var(--text-muted);padding:32px 0;font-size:13px">No state data available.</p>
        @else
        @php $maxState = $byState->max('revenue') ?: 1; $stateTotalRev = $byState->sum('revenue') ?: 1; @endphp
        <div style="display:flex;flex-direction:column;gap:14px;margin-top:8px">
            @foreach($byState as $row)
            @php $pct = round(($row->revenue/$stateTotalRev)*100,1); @endphp
            <div>
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
                    <span style="font-weight:600">{{ $row->state }}</span>
                    <span>৳{{ number_format($row->revenue,0) }} <span style="font-size:11px;color:var(--text-muted)">({{ $pct }}%)</span></span>
                </div>
                <div style="background:var(--surface-3);border-radius:4px;height:6px;overflow:hidden">
                    <div style="width:{{ $pct }}%;height:6px;background:var(--violet);border-radius:4px"></div>
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ number_format($row->orders) }} orders</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

@if($byCity->isNotEmpty())
<div class="card flush">
    <div class="card-head" style="padding:16px 20px"><div class="ct"><h3>City Details Table</h3></div></div>
    <table class="table">
        <thead><tr>
            <th>#</th>
            <th>City</th>
            <th style="text-align:right">Orders</th>
            <th style="text-align:right">Revenue</th>
            <th style="text-align:right">Avg Order</th>
            <th style="text-align:right">% of Revenue</th>
        </tr></thead>
        <tbody>
            @foreach($byCity as $i => $row)
            @php $pct = $totalRevenue > 0 ? round(($row->revenue/$totalRevenue)*100,2) : 0; @endphp
            <tr>
                <td style="color:var(--text-muted);font-size:12px">{{ $i+1 }}</td>
                <td style="font-weight:600">{{ $row->city }}</td>
                <td style="text-align:right">{{ number_format($row->orders) }}</td>
                <td style="text-align:right;font-weight:600">৳{{ number_format($row->revenue,0) }}</td>
                <td style="text-align:right;color:var(--text-muted)">৳{{ number_format($row->avg_order,0) }}</td>
                <td style="text-align:right;color:var(--text-muted)">{{ $pct }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection

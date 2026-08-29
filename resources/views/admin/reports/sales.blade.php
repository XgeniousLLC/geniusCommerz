@extends('admin.layouts.admin')
@section('title', 'Sales Report')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Sales Report</h2>
        <div class="sub">Revenue, orders, and items sold for the selected period</div>
    </div>
    <a href="{{ route('admin.reports.export', array_merge(request()->all(), ['type' => 'sales'])) }}" class="btn btn-outline btn-sm">
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

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:16px">
    <div class="card lift stat">
        <span class="tile sm t-accent"><span class="ico" data-ico="dollar" style="width:18px;height:18px"></span></span>
        <div class="num">{{ money($stats['total_revenue'], 0) }}</div>
        <div class="lbl">Total Revenue</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-info"><span class="ico" data-ico="receipt" style="width:18px;height:18px"></span></span>
        <div class="num">{{ number_format($stats['total_orders']) }}</div>
        <div class="lbl">Total Orders</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-violet"><span class="ico" data-ico="chart" style="width:18px;height:18px"></span></span>
        <div class="num">{{ money($stats['avg_order_value'], 0) }}</div>
        <div class="lbl">Avg Order Value</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-success"><span class="ico" data-ico="package" style="width:18px;height:18px"></span></span>
        <div class="num">{{ number_format($stats['total_items_sold']) }}</div>
        <div class="lbl">Items Sold</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px">

    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card pad">
            <div class="card-head"><div class="ct"><h3>Revenue Over Time</h3></div></div>
            @if(empty($chartData))
                <p style="text-align:center;color:var(--text-muted);padding:32px 0;font-size:13px">No data for this period.</p>
            @else
                @php $maxRev = max(array_column($chartData, 'revenue')) ?: 1; @endphp
                <div style="display:flex;align-items:flex-end;gap:3px;height:120px;margin-bottom:12px">
                    @foreach($chartData as $row)
                    @php $h = max(2, round(($row['revenue'] / $maxRev) * 100)); @endphp
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;min-width:20px"
                         title="{{ $row['date'] }}: {{ money($row['revenue']) }} ({{ $row['orders'] }} orders)">
                        <div style="width:100%;height:{{ $h }}%;background:var(--accent);border-radius:3px 3px 0 0;opacity:.85"></div>
                        <span style="font-size:9px;color:var(--text-muted);white-space:nowrap;overflow:hidden;max-width:100%;text-align:center">{{ substr($row['date'], -5) }}</span>
                    </div>
                    @endforeach
                </div>
                <table class="table">
                    <thead><tr><th>Period</th><th>Orders</th><th>Revenue</th></tr></thead>
                    <tbody>
                        @foreach(array_slice($chartData, -10) as $row)
                        <tr>
                            <td style="font-family:monospace;font-size:12px">{{ $row['date'] }}</td>
                            <td>{{ $row['orders'] }}</td>
                            <td><strong>{{ money($row['revenue']) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="card pad">
            <div class="card-head"><div class="ct"><h3>Top Products by Revenue</h3></div></div>
            @if($topProducts->isEmpty())
                <p style="text-align:center;color:var(--text-muted);padding:24px 0;font-size:13px">No data.</p>
            @else
                <div style="display:flex;flex-direction:column;gap:12px">
                    @foreach($topProducts as $i => $p)
                    <div style="display:flex;align-items:center;gap:12px">
                        <span style="font-size:12px;font-weight:700;color:var(--text-muted);width:20px">{{ $i + 1 }}</span>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $p->product_name }}</div>
                            <div style="font-size:11px;color:var(--text-muted)">{{ $p->qty_sold }} units sold</div>
                        </div>
                        <strong style="color:var(--text);flex-shrink:0">{{ money($p->revenue) }}</strong>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Revenue by Payment Method</h3></div></div>
        @if($revenueByPaymentMethod->isEmpty())
            <p style="text-align:center;color:var(--text-muted);padding:24px 0;font-size:13px">No data.</p>
        @else
            @php $totalRev = $revenueByPaymentMethod->sum('revenue') ?: 1; @endphp
            <div style="display:flex;flex-direction:column;gap:16px">
                @foreach($revenueByPaymentMethod as $row)
                @php $pct = round(($row->revenue / $totalRev) * 100); @endphp
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
                        <span style="text-transform:capitalize;color:var(--text)">{{ str_replace('_', ' ', $row->payment_method) }}</span>
                        <strong>{{ money($row->revenue) }}</strong>
                    </div>
                    <div style="background:var(--surface-3);border-radius:4px;height:6px;overflow:hidden">
                        <div style="width:{{ $pct }}%;height:6px;background:var(--accent);border-radius:4px"></div>
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ $row->orders }} orders · {{ $pct }}%</div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

@endsection

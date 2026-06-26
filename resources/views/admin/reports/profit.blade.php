@extends('admin.layouts.admin')
@section('title', 'Profit Analysis')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Profit Analysis</h2>
        <div class="sub">Revenue, COGS, gross profit and margin breakdown</div>
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
            <input type="date" name="start_date" class="input" value="{{ $request->input('start_date', $startDate->format('Y-m-d')) }}">
        </div>
        <div class="field" style="min-width:140px">
            <label class="lbl">To</label>
            <input type="date" name="end_date" class="input" value="{{ $request->input('end_date', $endDate->format('Y-m-d')) }}">
        </div>
        <button type="submit" class="btn">Apply</button>
    </form>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:16px">
    <div class="card lift stat">
        <span class="tile sm t-accent"><span class="ico" data-ico="dollar" style="width:18px;height:18px"></span></span>
        <div class="num">৳{{ number_format($revenue, 0) }}</div>
        <div class="lbl">Total Revenue</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-danger"><span class="ico" data-ico="trendDown" style="width:18px;height:18px"></span></span>
        <div class="num">৳{{ number_format($cogs, 0) }}</div>
        <div class="lbl">COGS</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-success"><span class="ico" data-ico="trendUp" style="width:18px;height:18px"></span></span>
        <div class="num">৳{{ number_format($grossProfit, 0) }}</div>
        <div class="lbl">Gross Profit</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-violet"><span class="ico" data-ico="chart" style="width:18px;height:18px"></span></span>
        <div class="num">{{ $margin }}%</div>
        <div class="lbl">Gross Margin</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Monthly Revenue vs Discounts</h3></div></div>
        @if($byMonth->isEmpty())
            <p style="text-align:center;color:var(--text-muted);padding:32px 0;font-size:13px">No data for this period.</p>
        @else
        <table class="table">
            <thead><tr><th>Month</th><th style="text-align:right">Revenue</th><th style="text-align:right">Discount</th><th style="text-align:right">Shipping</th></tr></thead>
            <tbody>
                @foreach($byMonth as $row)
                <tr>
                    <td>{{ $row->month }}</td>
                    <td style="text-align:right;font-weight:600">৳{{ number_format($row->revenue,0) }}</td>
                    <td style="text-align:right;color:var(--danger)">-৳{{ number_format($row->discount,0) }}</td>
                    <td style="text-align:right;color:var(--accent)">+৳{{ number_format($row->shipping,0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>P&L Summary</h3></div></div>
        <div style="display:flex;flex-direction:column;gap:12px;font-size:13.5px">
            <div style="display:flex;justify-content:space-between">
                <span style="color:var(--text-muted)">Gross Revenue</span>
                <strong>৳{{ number_format($revenue,0) }}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;color:var(--danger)">
                <span>Cost of Goods (COGS)</span>
                <span>-৳{{ number_format($cogs,0) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-weight:700;border-top:1px solid var(--border);padding-top:10px">
                <span>Gross Profit</span>
                <span style="color:var(--success)">৳{{ number_format($grossProfit,0) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;color:var(--danger)">
                <span>Discounts Given</span>
                <span>-৳{{ number_format($discount,0) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;color:var(--accent)">
                <span>Shipping Collected</span>
                <span>+৳{{ number_format($shipping,0) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-weight:700;font-size:15px;border-top:1px solid var(--border);padding-top:10px">
                <span>Est. Net Profit</span>
                <span style="color:{{ $netProfit >= 0 ? 'var(--success)' : 'var(--danger)' }}">৳{{ number_format($netProfit,0) }}</span>
            </div>
            <p style="font-size:11px;color:var(--text-muted)">* COGS only calculated for products with cost price set. Net profit is an estimate.</p>
        </div>
    </div>
</div>

<div class="card flush">
    <div class="card-head" style="padding:16px 20px"><div class="ct"><h3>Most Profitable Products</h3></div></div>
    @if($topMargin->isEmpty())
        <p style="text-align:center;color:var(--text-muted);padding:32px 0;font-size:13px">No products with cost price set for this period.</p>
    @else
    <table class="table">
        <thead><tr>
            <th>Product</th>
            <th style="text-align:right">Revenue</th>
            <th style="text-align:right">Cost</th>
            <th style="text-align:right">Profit</th>
            <th style="text-align:right">Margin %</th>
        </tr></thead>
        <tbody>
            @foreach($topMargin as $p)
            <tr>
                <td style="max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $p->product_name }}</td>
                <td style="text-align:right">৳{{ number_format($p->revenue,0) }}</td>
                <td style="text-align:right;color:var(--danger)">৳{{ number_format($p->cost,0) }}</td>
                <td style="text-align:right;font-weight:600;color:var(--success)">৳{{ number_format($p->profit,0) }}</td>
                <td style="text-align:right">
                    <span class="pill sm {{ $p->margin_pct >= 30 ? 't-success' : ($p->margin_pct >= 10 ? 't-warning' : 't-danger') }}">{{ number_format($p->margin_pct,1) }}%</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection

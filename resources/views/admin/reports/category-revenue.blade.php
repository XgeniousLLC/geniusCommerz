@extends('admin.layouts.admin')
@section('title', 'Category & Brand Revenue')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Category & Brand Revenue</h2>
        <div class="sub">Revenue breakdown by product category and brand</div>
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

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Revenue by Category</h3></div></div>
        @if($byCategory->isEmpty())
            <p style="text-align:center;color:var(--text-muted);padding:32px 0;font-size:13px">No category-linked orders in this period.</p>
        @else
        <div style="display:flex;flex-direction:column;gap:14px;margin-top:8px">
            @foreach($byCategory as $row)
            @php $pct = $totalRevenue > 0 ? round(($row->revenue/$totalRevenue)*100,1) : 0; @endphp
            <div>
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
                    <span style="font-weight:600">{{ $row->category }}</span>
                    <span><strong>{{ money($row->revenue, 0) }}</strong> <span style="font-size:11px;color:var(--text-muted)">({{ $pct }}%)</span></span>
                </div>
                <div style="background:var(--surface-3);border-radius:4px;height:6px;overflow:hidden">
                    <div style="width:{{ min(100,$pct) }}%;height:6px;background:var(--accent);border-radius:4px"></div>
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ number_format($row->qty_sold) }} units · {{ number_format($row->order_count) }} orders</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Revenue by Brand</h3></div></div>
        @if($byBrand->isEmpty())
            <p style="text-align:center;color:var(--text-muted);padding:32px 0;font-size:13px">No brand-linked orders in this period.</p>
        @else
        <div style="display:flex;flex-direction:column;gap:14px;margin-top:8px">
            @foreach($byBrand as $row)
            @php $pct = $totalRevenue > 0 ? round(($row->revenue/$totalRevenue)*100,1) : 0; @endphp
            <div>
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:4px">
                    <span style="font-weight:600">{{ $row->brand }}</span>
                    <span><strong>{{ money($row->revenue, 0) }}</strong> <span style="font-size:11px;color:var(--text-muted)">({{ $pct }}%)</span></span>
                </div>
                <div style="background:var(--surface-3);border-radius:4px;height:6px;overflow:hidden">
                    <div style="width:{{ min(100,$pct) }}%;height:6px;background:var(--violet);border-radius:4px"></div>
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px">{{ number_format($row->qty_sold) }} units · {{ number_format($row->order_count) }} orders</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
    @if($byCategory->isNotEmpty())
    <div class="card flush">
        <div class="card-head" style="padding:16px 20px"><div class="ct"><h3>Category Breakdown</h3></div></div>
        <table class="table">
            <thead><tr><th>Category</th><th style="text-align:right">Units</th><th style="text-align:right">Orders</th><th style="text-align:right">Revenue</th></tr></thead>
            <tbody>
                @foreach($byCategory as $row)
                <tr>
                    <td>{{ $row->category }}</td>
                    <td style="text-align:right">{{ number_format($row->qty_sold) }}</td>
                    <td style="text-align:right">{{ number_format($row->order_count) }}</td>
                    <td style="text-align:right;font-weight:600">{{ money($row->revenue, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    @if($byBrand->isNotEmpty())
    <div class="card flush">
        <div class="card-head" style="padding:16px 20px"><div class="ct"><h3>Brand Breakdown</h3></div></div>
        <table class="table">
            <thead><tr><th>Brand</th><th style="text-align:right">Units</th><th style="text-align:right">Orders</th><th style="text-align:right">Revenue</th></tr></thead>
            <tbody>
                @foreach($byBrand as $row)
                <tr>
                    <td>{{ $row->brand }}</td>
                    <td style="text-align:right">{{ number_format($row->qty_sold) }}</td>
                    <td style="text-align:right">{{ number_format($row->order_count) }}</td>
                    <td style="text-align:right;font-weight:600">{{ money($row->revenue, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection

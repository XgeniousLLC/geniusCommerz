@extends('admin.layouts.admin')
@section('title', 'Top Products Report')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Top Products</h2>
        <div class="sub">Best performing products by revenue, quantity, and profit margin</div>
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
        <div class="field" style="min-width:140px">
            <label class="lbl">Sort by</label>
            <select name="sort" class="select" onchange="this.form.submit()">
                <option value="revenue" {{ $sortBy==='revenue'?'selected':'' }}>Revenue</option>
                <option value="qty"     {{ $sortBy==='qty'?'selected':'' }}>Quantity Sold</option>
                <option value="orders"  {{ $sortBy==='orders'?'selected':'' }}>Order Count</option>
            </select>
        </div>
        <button type="submit" class="btn">Apply</button>
    </form>
</div>

<div class="card flush">
    <div class="card-head" style="padding:16px 20px">
        <div class="ct"><h3>Top 50 Products</h3><div class="sub">Total revenue: {{ money($totalRevenue, 0) }}</div></div>
    </div>
    @if($products->isEmpty())
        <p style="text-align:center;color:var(--text-muted);padding:48px 0;font-size:13px">No orders in this period.</p>
    @else
    <table class="table">
        <thead><tr>
            <th>#</th>
            <th>Product</th>
            <th style="text-align:right">Qty</th>
            <th style="text-align:right">Revenue</th>
            <th style="text-align:right">Avg Price</th>
            <th style="text-align:right">Orders</th>
            <th style="text-align:right">Profit</th>
            <th style="text-align:right">Margin</th>
            <th style="text-align:right">% of Total</th>
        </tr></thead>
        <tbody>
            @foreach($products as $i => $p)
            @php $pct = $totalRevenue > 0 ? round(($p->revenue / $totalRevenue) * 100, 1) : 0; @endphp
            <tr>
                <td style="color:var(--text-muted);font-size:12px">{{ $i+1 }}</td>
                <td style="max-width:200px">
                    <div style="font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $p->product_name }}</div>
                    @if($p->sku)<div style="font-size:11px;color:var(--text-muted)">{{ $p->sku }}</div>@endif
                </td>
                <td style="text-align:right;{{ $sortBy==='qty'?'font-weight:700;color:var(--accent)':'' }}">{{ number_format($p->qty_sold) }}</td>
                <td style="text-align:right;{{ $sortBy==='revenue'?'font-weight:700;color:var(--accent)':'' }}">{{ money($p->revenue, 0) }}</td>
                <td style="text-align:right;color:var(--text-muted)">{{ money($p->avg_price, 0) }}</td>
                <td style="text-align:right;{{ $sortBy==='orders'?'font-weight:700;color:var(--accent)':'' }}">{{ number_format($p->order_count) }}</td>
                <td style="text-align:right;{{ is_null($p->profit) ? 'color:var(--text-muted)' : 'color:var(--success);font-weight:600' }}">
                    {{ is_null($p->profit) ? '—' : money($p->profit, 0) }}
                </td>
                <td style="text-align:right">
                    @if(!is_null($p->margin_pct))
                    <span class="pill sm {{ $p->margin_pct >= 30 ? 't-success' : ($p->margin_pct >= 10 ? 't-warning' : 't-danger') }}">{{ number_format($p->margin_pct,1) }}%</span>
                    @else<span style="color:var(--text-muted);font-size:12px">—</span>@endif
                </td>
                <td style="text-align:right">
                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
                        <div style="width:56px;height:5px;background:var(--surface-3);border-radius:3px;overflow:hidden">
                            <div style="width:{{ min(100,$pct) }}%;height:5px;background:var(--accent);border-radius:3px"></div>
                        </div>
                        <span style="font-size:11px;color:var(--text-muted);width:32px;text-align:right">{{ $pct }}%</span>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection

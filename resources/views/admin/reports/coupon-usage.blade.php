@extends('admin.layouts.admin')
@section('title', 'Coupon Usage Report')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Coupon Usage Report</h2>
        <div class="sub">Discount usage, coupon redemption rates, and revenue impact</div>
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

@php $couponRate = $stats['all_orders'] > 0 ? round(($stats['total_coupon_orders']/$stats['all_orders'])*100,1) : 0; @endphp

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:16px">
    <div class="card lift stat">
        <span class="tile sm t-accent"><span class="ico" data-ico="tag" style="width:18px;height:18px"></span></span>
        <div class="num">{{ number_format($stats['total_coupon_orders']) }}</div>
        <div class="lbl">Coupon Orders</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-info"><span class="ico" data-ico="chart" style="width:18px;height:18px"></span></span>
        <div class="num">{{ $couponRate }}%</div>
        <div class="lbl">Usage Rate</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-danger"><span class="ico" data-ico="dollar" style="width:18px;height:18px"></span></span>
        <div class="num">৳{{ number_format($stats['total_discount_given'],0) }}</div>
        <div class="lbl">Total Discount Given</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-teal"><span class="ico" data-ico="layers" style="width:18px;height:18px"></span></span>
        <div class="num">{{ $stats['unique_coupons_used'] }}</div>
        <div class="lbl">Unique Coupons Used</div>
    </div>
</div>

<div class="card flush">
    <div class="card-head" style="padding:16px 20px"><div class="ct"><h3>Coupon Performance</h3></div></div>
    @if($coupons->isEmpty())
        <p style="text-align:center;color:var(--text-muted);padding:48px 0;font-size:13px">No coupons used in this period.</p>
    @else
    <table class="table">
        <thead><tr>
            <th>Coupon Code</th>
            <th style="text-align:right">Times Used</th>
            <th style="text-align:right">Total Discount</th>
            <th style="text-align:right">Revenue Generated</th>
            <th style="text-align:right">Avg Order Value</th>
            <th style="text-align:right">Avg Discount/Order</th>
        </tr></thead>
        <tbody>
            @foreach($coupons as $row)
            <tr>
                <td><span style="font-family:monospace;font-weight:700;color:var(--accent)">{{ $row->coupon_code }}</span></td>
                <td style="text-align:right;font-weight:600">{{ number_format($row->times_used) }}</td>
                <td style="text-align:right;color:var(--danger)">৳{{ number_format($row->total_discount,0) }}</td>
                <td style="text-align:right;font-weight:600">৳{{ number_format($row->revenue_after_discount,0) }}</td>
                <td style="text-align:right;color:var(--text-muted)">৳{{ number_format($row->avg_order,0) }}</td>
                <td style="text-align:right;color:var(--text-muted)">৳{{ $row->times_used > 0 ? number_format($row->total_discount / $row->times_used, 0) : '0' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection

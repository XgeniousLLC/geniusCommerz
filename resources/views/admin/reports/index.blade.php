@extends('admin.layouts.admin')

@section('title', 'Reports & Analytics')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Reports & Analytics</h2>
        <div class="sub">Performance across sales, products and customers</div>
    </div>
    <div class="row" style="gap:10px">
        <div class="seg sm">
            <a href="{{ route('admin.reports.sales', ['from' => now()->subDays(7)->format('Y-m-d')]) }}" style="text-decoration:none">7d</a>
            <a style="text-decoration:none" class="active">30d</a>
            <a href="{{ route('admin.reports.sales', ['from' => now()->subDays(90)->format('Y-m-d')]) }}" style="text-decoration:none">90d</a>
        </div>
        <a href="{{ route('admin.reports.sales') }}" class="btn btn-outline btn-sm">
            <span class="ico" data-ico="chart" style="width:16px;height:16px"></span>Full reports
        </a>
    </div>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
    <div class="card lift stat" style="flex-direction:column;align-items:flex-start;gap:10px;padding:20px">
        <div class="between" style="width:100%">
            <span class="tile t-accent"><span class="ico" data-ico="dollar"></span></span>
        </div>
        <div><div class="num" style="font-size:26px">৳{{ number_format($revenue30, 0) }}</div><div class="lbl">Total revenue (30d)</div></div>
    </div>
    <div class="card lift stat" style="flex-direction:column;align-items:flex-start;gap:10px;padding:20px">
        <div class="between" style="width:100%">
            <span class="tile t-info"><span class="ico" data-ico="cart"></span></span>
        </div>
        <div><div class="num" style="font-size:26px">{{ number_format($unitsSold) }}</div><div class="lbl">Units sold</div></div>
    </div>
    <div class="card lift stat" style="flex-direction:column;align-items:flex-start;gap:10px;padding:20px">
        <div class="between" style="width:100%">
            <span class="tile t-violet"><span class="ico" data-ico="receipt"></span></span>
        </div>
        <div><div class="num" style="font-size:26px">{{ number_format($orders30) }}</div><div class="lbl">Orders</div></div>
    </div>
    <div class="card lift stat" style="flex-direction:column;align-items:flex-start;gap:10px;padding:20px">
        <div class="between" style="width:100%">
            <span class="tile t-teal"><span class="ico" data-ico="users"></span></span>
        </div>
        <div><div class="num" style="font-size:26px">{{ number_format($customers30) }}</div><div class="lbl">Active customers</div></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:18px" class="grid-2">

    {{-- Best sellers --}}
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-accent"><span class="ico" data-ico="package" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Best sellers</h3><div class="sub">By revenue, last 30 days</div></div>
            <a href="{{ route('admin.reports.top-products') }}" class="link-btn head-action">View all</a>
        </div>
        @if($topProducts->isEmpty())
        <p class="faint" style="font-size:13px">No sales data yet.</p>
        @else
        <div class="stack" style="gap:14px">
            @foreach($topProducts as $i => $p)
            <div class="row" style="gap:12px">
                <span class="tile sm" style="width:40px;height:40px;border-radius:10px;background:var(--surface-2);color:var(--text-faint);font-weight:700;font-size:14px;flex-shrink:0">{{ $i + 1 }}</span>
                <div class="grow">
                    <div style="font-weight:700;font-size:13.5px">{{ $p->product_name }}</div>
                    <div class="faint" style="font-size:12px">{{ number_format($p->qty_sold) }} sold</div>
                </div>
                <span class="tnum" style="font-weight:700">৳{{ number_format($p->revenue, 0) }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Revenue by category --}}
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-violet"><span class="ico" data-ico="layers" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Revenue by category</h3></div>
            <a href="{{ route('admin.reports.category-revenue') }}" class="link-btn head-action">View all</a>
        </div>
        @if($categoryRevenue->isEmpty())
        <p class="faint" style="font-size:13px">No category data yet.</p>
        @else
        @php
        $barColors = ['var(--chart-1)','var(--violet)','var(--teal)','var(--pop)','var(--warning)'];
        @endphp
        <div class="stack" style="gap:16px">
            @foreach($categoryRevenue as $i => $cat)
            @php $pct = round(($cat->revenue / $totalCatRevenue) * 100); @endphp
            <div>
                <div class="between" style="margin-bottom:6px">
                    <span style="font-weight:600;font-size:13.5px">{{ $cat->name }}</span>
                    <span class="tnum faint" style="font-size:13px">৳{{ number_format($cat->revenue, 0) }} · {{ $pct }}%</span>
                </div>
                <div style="height:8px;border-radius:99px;background:var(--surface-3)">
                    <div style="width:{{ $pct }}%;height:100%;border-radius:99px;background:{{ $barColors[$i % count($barColors)] }}"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- Quick links to sub-reports --}}
<div class="card pad" style="margin-top:0">
    <div class="card-head">
        <span class="tile sm t-teal"><span class="ico" data-ico="chart" style="width:18px;height:18px"></span></span>
        <div class="ct"><h3>All reports</h3></div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px">
        @foreach([
            ['Sales overview',    route('admin.reports.sales'),           'dollar',  't-accent'],
            ['Profit analysis',   route('admin.reports.profit'),          'trendUp', 't-success'],
            ['Top products',      route('admin.reports.top-products'),    'package', 't-info'],
            ['Demand trends',     route('admin.reports.demand-trends'),   'chart',   't-violet'],
            ['Category revenue',  route('admin.reports.category-revenue'),'layers',  't-teal'],
            ['Inventory',         route('admin.reports.inventory'),       'box',     't-warning'],
            ['Customers',         route('admin.reports.customers'),       'users',   't-pop'],
            ['Orders report',     route('admin.reports.orders'),          'receipt', 't-info'],
            ['Coupon usage',      route('admin.reports.coupon-usage'),    'tag',     't-accent'],
            ['Refund analysis',   route('admin.reports.refund-analysis'), 'refresh', 't-danger'],
            ['Payment trends',    route('admin.reports.payment-trends'),  'card',    't-teal'],
            ['Geographic',        route('admin.reports.geographic'),      'pin',     't-violet'],
        ] as [$label, $url, $icon, $tile])
        <a href="{{ $url }}" class="card pad lift" style="display:flex;gap:12px;align-items:center;text-decoration:none;padding:14px 16px">
            <span class="tile sm {{ $tile }}" style="flex-shrink:0"><span class="ico" data-ico="{{ $icon }}" style="width:17px;height:17px"></span></span>
            <span style="font-weight:600;font-size:13.5px;color:var(--text)">{{ $label }}</span>
        </a>
        @endforeach
    </div>
</div>

@endsection

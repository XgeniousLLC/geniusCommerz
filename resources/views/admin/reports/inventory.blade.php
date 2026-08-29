@extends('admin.layouts.admin')
@section('title', 'Inventory Report')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Inventory Report</h2>
        <div class="sub">Stock levels, low stock alerts, and total stock value</div>
    </div>
    <a href="{{ route('admin.reports.export', ['type' => 'inventory']) }}" class="btn btn-outline btn-sm">
        <span class="ico" data-ico="download" style="width:15px;height:15px"></span>Export CSV
    </a>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:16px">
    <div class="card lift stat">
        <span class="tile sm t-info"><span class="ico" data-ico="box" style="width:18px;height:18px"></span></span>
        <div class="num">{{ number_format($summary['total_products']) }}</div>
        <div class="lbl">Total Products</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-danger"><span class="ico" data-ico="close" style="width:18px;height:18px"></span></span>
        <div class="num">{{ number_format($summary['out_of_stock_count']) }}</div>
        <div class="lbl">Out of Stock</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-warning"><span class="ico" data-ico="alert" style="width:18px;height:18px"></span></span>
        <div class="num">{{ number_format($summary['low_stock_count']) }}</div>
        <div class="lbl">Low Stock (≤{{ $summary['low_stock_threshold'] }})</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-success"><span class="ico" data-ico="dollar" style="width:18px;height:18px"></span></span>
        <div class="num">{{ money($summary['total_stock_value'], 0) }}</div>
        <div class="lbl">Total Stock Value</div>
    </div>
</div>

<div class="card pad" style="margin-bottom:16px">
    <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end">
        <div class="field" style="min-width:160px">
            <label class="lbl">Filter</label>
            <select name="filter" class="select" onchange="this.form.submit()">
                <option value="all" @selected($filter === 'all')>All Products</option>
                <option value="low_stock" @selected($filter === 'low_stock')>Low Stock</option>
                <option value="out_of_stock" @selected($filter === 'out_of_stock')>Out of Stock</option>
            </select>
        </div>
        <div class="field" style="min-width:160px">
            <label class="lbl">Sort</label>
            <select name="sort" class="select" onchange="this.form.submit()">
                <option value="asc" @selected($sort === 'asc')>Stock: Low → High</option>
                <option value="desc" @selected($sort === 'desc')>Stock: High → Low</option>
            </select>
        </div>
    </form>
</div>

<div class="card flush">
    <table class="table">
        <thead><tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Type</th>
            <th style="text-align:right">Price</th>
            <th style="text-align:right">Stock</th>
            <th>Status</th>
        </tr></thead>
        <tbody>
            @foreach($products as $p)
            @php
                $stock = (int) $p->effective_stock;
                if ($stock === 0) {
                    $pill = ['Out of Stock', 't-danger'];
                } elseif ($stock <= $summary['low_stock_threshold']) {
                    $pill = ['Low Stock', 't-warning'];
                } else {
                    $pill = ['In Stock', 't-success'];
                }
            @endphp
            <tr>
                <td style="font-weight:600;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $p->name }}</td>
                <td style="font-family:monospace;font-size:12px;color:var(--text-muted)">{{ $p->sku ?? '—' }}</td>
                <td style="color:var(--text-muted)">{{ $p->has_variants ? 'Variants' : 'Simple' }}</td>
                <td style="text-align:right">{{ money($p->price, 0) }}</td>
                <td style="text-align:right;font-weight:700;color:{{ $stock === 0 ? 'var(--danger)' : ($stock <= $summary['low_stock_threshold'] ? 'var(--warning)' : 'var(--text)') }}">
                    {{ number_format($stock) }}
                </td>
                <td><span class="pill sm {{ $pill[1] }}">{{ $pill[0] }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($products->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--border)">
        {{ $products->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection

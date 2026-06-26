@extends('admin.layouts.admin')
@section('title', 'Customer Report')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Customer Report</h2>
        <div class="sub">Top customers by spend and customer acquisition overview</div>
    </div>
    <a href="{{ route('admin.reports.export', ['type' => 'customers']) }}" class="btn btn-outline btn-sm">
        <span class="ico" data-ico="download" style="width:15px;height:15px"></span>Export CSV
    </a>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));margin-bottom:16px">
    <div class="card lift stat">
        <span class="tile sm t-info"><span class="ico" data-ico="users" style="width:18px;height:18px"></span></span>
        <div class="num">{{ number_format($stats['total_customers']) }}</div>
        <div class="lbl">Total Customers</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-success"><span class="ico" data-ico="userPlus" style="width:18px;height:18px"></span></span>
        <div class="num">{{ number_format($stats['new_this_month']) }}</div>
        <div class="lbl">New This Month</div>
    </div>
    <div class="card lift stat">
        <span class="tile sm t-violet"><span class="ico" data-ico="refresh" style="width:18px;height:18px"></span></span>
        <div class="num">{{ number_format($stats['repeat_customers']) }}</div>
        <div class="lbl">Repeat Customers</div>
    </div>
</div>

<div class="card flush">
    <div class="card-head" style="padding:16px 20px"><div class="ct"><h3>Top 20 Customers by Spend</h3></div></div>
    @if($topCustomers->isEmpty())
        <p style="text-align:center;color:var(--text-muted);padding:48px 0;font-size:13px">No customer data yet.</p>
    @else
    <table class="table">
        <thead><tr>
            <th>#</th>
            <th>Customer</th>
            <th style="text-align:right">Orders</th>
            <th style="text-align:right">Total Spent</th>
            <th style="text-align:right">Last Order</th>
        </tr></thead>
        <tbody>
            @foreach($topCustomers as $i => $c)
            <tr>
                <td style="color:var(--text-muted);font-weight:700;font-size:12px">{{ $i + 1 }}</td>
                <td>
                    <div style="font-weight:600">{{ $c->name }}</div>
                    <div style="font-size:11px;color:var(--text-muted)">{{ $c->email }}</div>
                </td>
                <td style="text-align:right">{{ $c->orders_count }}</td>
                <td style="text-align:right;font-weight:700;color:var(--success)">৳{{ number_format($c->total_spent, 0) }}</td>
                <td style="text-align:right;font-size:12px;color:var(--text-muted)">{{ \Carbon\Carbon::parse($c->last_order_at)->format('M d, Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection

@extends('admin.layouts.admin')

@section('title', 'Orders')

@section('content')

@php
$avatarColors = ['var(--info)','var(--violet)','var(--pop)','var(--teal)','var(--warning)','var(--accent)','var(--success)'];
function orderAvatarColor(string $name, array $colors): string {
    return $colors[abs(crc32($name)) % count($colors)];
}
@endphp

<div class="page-head">
    <div>
        <h2 class="display">Orders</h2>
        <div class="sub">
            <b style="color:var(--text)">{{ $orders->total() }}</b> orders total
        </div>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.orders.create') }}">
        <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>Create order
    </a>
</div>

<div class="stat-grid">
    <div class="card lift stat">
        <span class="tile t-warning"><span class="ico" data-ico="clock"></span></span>
        <div><div class="num">{{ $stats['pending'] }}</div><div class="lbl">Pending</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-info"><span class="ico" data-ico="refresh"></span></span>
        <div><div class="num">{{ $stats['processing'] }}</div><div class="lbl">Processing</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-violet"><span class="ico" data-ico="truck"></span></span>
        <div><div class="num">{{ $stats['shipped'] }}</div><div class="lbl">In shipment</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-success"><span class="ico" data-ico="check"></span></span>
        <div><div class="num">{{ $stats['delivered'] }}</div><div class="lbl">Delivered</div></div>
    </div>
</div>

@php
$statusTabs = [
    ''          => 'All',
    'pending'   => 'Pending',
    'processing'=> 'Processing',
    'shipped'   => 'Shipped',
    'delivered' => 'Delivered',
];
$dateTabs = [
    ''       => 'All time',
    'today'  => 'Today',
    'yesterday'=> 'Yesterday',
    'last7'  => 'Last 7 days',
    'last30' => 'Last 30 days',
];
@endphp

{{-- Row 1: search + selects --}}
<form method="GET" id="orders-filter-form">
<div class="row wrap" style="gap:10px;margin-bottom:12px">
    <div class="search-field" style="flex:1;min-width:200px;max-width:340px">
        <span class="ico" data-ico="search"></span>
        <input class="input" name="search" value="{{ request('search') }}" placeholder="Search by order # or customer…">
    </div>
    <select class="input" name="payment_status" style="max-width:160px" onchange="this.form.submit()">
        <option value="">All payments</option>
        @foreach(\App\Models\Order::PAYMENT_STATUSES as $s)
        <option value="{{ $s }}" {{ request('payment_status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    <select class="input" name="date" style="max-width:150px" onchange="this.form.submit()">
        @foreach($dateTabs as $val => $label)
        <option value="{{ $val }}" {{ request('date') === $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    {{-- preserve status tab value --}}
    @if(request('status'))
    <input type="hidden" name="status" value="{{ request('status') }}">
    @endif
    @if(request()->anyFilled(['search','status','payment_status','date']))
    <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost">Clear</a>
    @endif
</div>

{{-- Row 2: status tabs --}}
<div style="margin-bottom:16px">
    <div class="seg">
        @foreach($statusTabs as $val => $label)
        <a href="{{ route('admin.orders.index', array_merge(request()->except('status','page'), $val ? ['status'=>$val] : [])) }}"
           class="{{ request('status','') === $val ? 'active' : '' }}"
           style="text-decoration:none">{{ $label }}</a>
        @endforeach
    </div>
</div>
</form>

<div x-data="orderBulk({{ $orders->pluck('id')->toJson() }})">

<div class="card pad between wrap" x-show="selected.length" x-cloak
     style="margin-bottom:12px;gap:12px;align-items:center">
    <div style="font-size:13px;font-weight:600">
        <b x-text="selected.length" style="color:var(--accent)"></b> order(s) selected
        <button type="button" class="btn btn-ghost btn-sm" @click="clear()" style="margin-left:8px">Clear</button>
    </div>
    <form method="POST" action="{{ route('admin.orders.bulk') }}" class="row wrap" style="gap:10px;align-items:center"
          @submit="if (action==='delete' && !confirm('Delete '+selected.length+' order(s)? This cannot be undone.')) $event.preventDefault()">
        @csrf
        <input type="hidden" name="action" :value="action">
        <template x-for="id in selected" :key="id"><input type="hidden" name="order_ids[]" :value="id"></template>

        <div class="row" style="gap:6px">
            <select class="input" name="status" x-model="statusVal" style="max-width:160px">
                <option value="">Change status…</option>
                @foreach(\App\Models\Order::STATUSES as $s)
                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline" @click="action='status'" :disabled="!statusVal">Apply</button>
        </div>

        <div class="row" style="gap:6px">
            <select class="input" name="payment_status" x-model="paymentVal" style="max-width:170px">
                <option value="">Change payment…</option>
                @foreach(\App\Models\Order::PAYMENT_STATUSES as $s)
                <option value="{{ $s }}">{{ ucwords(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline" @click="action='payment'" :disabled="!paymentVal">Apply</button>
        </div>

        <button type="submit" class="btn btn-danger" @click="action='delete'">
            <span class="ico" data-ico="trash" style="width:16px;height:16px"></span>Delete
        </button>
    </form>
</div>

<div class="card flush">
    <div class="table-scroll"><table class="table">
        <thead><tr>
            <th style="width:36px">
                <input type="checkbox" :checked="allChecked" @change="toggleAll($event.target.checked)">
            </th>
            <th>Order</th>
            <th>Customer</th>
            <th>Channel</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Fraud</th>
            <th>Date</th>
            <th style="text-align:right">Total</th>
            <th></th>
        </tr></thead>
        <tbody>
            @forelse($orders as $order)
            @php
            $initials = collect(explode(' ', $order->customer_name))->map(fn($w) => strtoupper(mb_substr($w,0,1)))->take(2)->implode('');
            $avatarColor = $avatarColors[abs(crc32($order->customer_name)) % count($avatarColors)];
            $statusPill  = $order->statusPillClass();
            $paymentPill = $order->paymentPillClass();
            $sourcePill  = $order->sourcePillClass();
            $fc          = $order->customer_phone ? ($fraudMap[$order->customer_phone] ?? null) : null;
            $fraudPill   = match($fc?->risk_level) {
                'safe'      => 'success',
                'low_risk'  => 'info',
                'mid_risk'  => 'warning',
                'high_risk' => 'danger',
                default     => '',
            };
            @endphp
            <tr class="hoverable" onclick="location.href='{{ route('admin.orders.show', $order) }}'">
                <td @click.stop>
                    <input type="checkbox" :checked="selected.includes({{ $order->id }})"
                           @change="toggle({{ $order->id }}, $event.target.checked)">
                </td>
                <td>
                    <span class="mono" style="color:var(--accent);font-weight:600;font-size:12.5px">
                        #{{ $order->order_number }}
                    </span>
                </td>
                <td>
                    <div class="row" style="gap:10px">
                        <span class="avatar" style="width:32px;height:32px;font-size:12px;background:{{ $avatarColor }}">{{ $initials }}</span>
                        <span style="font-weight:700;font-size:13px">{{ $order->customer_name }}</span>
                    </div>
                </td>
                <td>
                    <span class="pill sm {{ $sourcePill }}">{{ \App\Models\Order::SOURCES[$order->source] ?? ucfirst($order->source) }}</span>
                </td>
                <td>
                    <span class="pill sm {{ $paymentPill }}">{{ ucwords(str_replace('_',' ',$order->payment_status)) }}</span>
                </td>
                <td>
                    <span class="pill sm {{ $statusPill }}"><span class="dot"></span>{{ ucfirst($order->status) }}</span>
                </td>
                <td>
                    @if($fc)
                    <span class="pill sm {{ $fraudPill }}" title="Score: {{ $fc->risk_score }}/100">
                        {{ ucwords(str_replace('_', ' ', $fc->risk_level)) }}
                        <span style="opacity:.7;margin-left:3px">{{ $fc->risk_score }}</span>
                    </span>
                    @else
                    <span class="faint" style="font-size:12px">—</span>
                    @endif
                </td>
                <td class="faint" style="font-size:13px">{{ $order->created_at->format('M j, H:i') }}</td>
                <td style="text-align:right" class="tnum"><b>৳{{ number_format($order->total, 0) }}</b></td>
                <td style="text-align:right">
                    <span class="ico" data-ico="chevRight" style="width:17px;height:17px;color:var(--text-faint)"></span>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" style="text-align:center;padding:48px;color:var(--text-faint)">No orders found.</td></tr>
            @endforelse
        </tbody>
    </table></div>

    @if($orders->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--border)">
        {{ $orders->links() }}
    </div>
    @endif
</div>

</div>

@endsection

@push('scripts')
<script>
function orderBulk(ids) {
    return {
        ids: ids,
        selected: [],
        statusVal: '',
        paymentVal: '',
        action: '',
        get allChecked() { return this.ids.length > 0 && this.selected.length === this.ids.length; },
        toggle(id, checked) {
            if (checked) { if (!this.selected.includes(id)) this.selected.push(id); }
            else { this.selected = this.selected.filter(x => x !== id); }
        },
        toggleAll(checked) { this.selected = checked ? [...this.ids] : []; },
        clear() { this.selected = []; },
    };
}
</script>
@endpush

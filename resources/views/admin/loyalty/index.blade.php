@extends('admin.layouts.admin')

@section('title', 'Loyalty Transactions')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Loyalty Transactions</h2>
        <div class="sub">Points earned, redeemed, and adjusted across all customers</div>
    </div>
    <a class="btn btn-outline" href="{{ route('admin.loyalty.settings') }}">
        <span class="ico" data-ico="gear" style="width:18px;height:18px"></span>Settings
    </a>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:18px;align-items:start" class="grid-2">

<div class="col-gap">

    <div class="card pad" style="margin-bottom:0">
        <form method="GET" class="row" style="gap:10px">
            <select class="input" name="type" style="max-width:160px" onchange="this.form.submit()">
                <option value="">All types</option>
                @foreach(['earned','redeemed','expired','adjusted'] as $t)
                <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
            @if(request()->hasAny(['type','user_id']))
            <a href="{{ route('admin.loyalty.index') }}" class="btn btn-outline" style="padding:0 12px">Clear</a>
            @endif
        </form>
    </div>

    <div class="card flush">
        @if($transactions->isEmpty())
        <div style="padding:48px;text-align:center;color:var(--text-faint)">No transactions found.</div>
        @else
        <div class="table-scroll"><table class="table">
            <thead><tr>
                <th>Customer</th>
                <th>Type</th>
                <th>Points</th>
                <th>Balance after</th>
                <th>Order</th>
                <th>Date</th>
            </tr></thead>
            <tbody>
                @foreach($transactions as $t)
                @php
                $typePill = match($t->type) {
                    'earned'   => 'success',
                    'redeemed' => 'info',
                    'expired'  => '',
                    'adjusted' => 'warning',
                    default    => '',
                };
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:700;font-size:13px">{{ $t->user?->name ?? '—' }}</div>
                        <div class="faint" style="font-size:12px">{{ $t->user?->email }}</div>
                    </td>
                    <td><span class="pill sm {{ $typePill }}">{{ ucfirst($t->type) }}</span></td>
                    <td class="tnum" style="font-weight:700;color:{{ $t->points >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                        {{ $t->points >= 0 ? '+' : '' }}{{ number_format($t->points) }}
                    </td>
                    <td class="tnum mono" style="font-size:13px">{{ number_format($t->balance_after) }}</td>
                    <td>
                        @if($t->order)
                        <a href="{{ route('admin.orders.show', $t->order) }}" class="link-btn mono" style="font-size:12.5px">#{{ $t->order->order_number }}</a>
                        @else
                        <span class="faint">—</span>
                        @endif
                    </td>
                    <td class="faint" style="font-size:13px">{{ $t->created_at->format('M d, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table></div>
        @if($transactions->hasPages())
        <div style="padding:16px 20px;border-top:1px solid var(--border)">
            {{ $transactions->withQueryString()->links() }}
        </div>
        @endif
        @endif
    </div>

</div>{{-- /left --}}

<div class="col-gap">
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-accent"><span class="ico" data-ico="edit" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Manual Adjustment</h3></div>
        </div>
        <form method="POST" action="{{ route('admin.loyalty.adjust') }}"
              x-data="{ action: 'add' }">
            @csrf
            <input type="hidden" name="action" x-bind:value="action">

            <div class="field" style="margin-bottom:12px">
                <span class="lbl">Customer</span>
                <select class="input" name="user_id" required>
                    <option value="">— select customer —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
            </div>

            <div class="field" style="margin-bottom:12px">
                <span class="lbl">Action</span>
                <div class="seg" style="width:100%">
                    <button type="button" style="flex:1;justify-content:center"
                        :class="action==='add' ? 'active' : ''"
                        @click="action='add'">+ Add</button>
                    <button type="button" style="flex:1;justify-content:center"
                        :class="action==='deduct' ? 'active' : ''"
                        @click="action='deduct'">− Deduct</button>
                </div>
            </div>

            <div class="field" style="margin-bottom:12px">
                <span class="lbl">Points</span>
                <input class="input" type="number" name="points" min="1" placeholder="e.g. 500" required>
            </div>

            <div class="field" style="margin-bottom:16px">
                <span class="lbl">Reason</span>
                <input class="input" name="description" placeholder="e.g. Goodwill bonus" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Apply adjustment</button>
        </form>
    </div>
</div>

</div>

@endsection

@extends('admin.layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">{{ $user->name }}</h1>
        <p class="page-sub">{{ $user->email }}</p>
    </div>
    <div class="row" style="gap:10px">
        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">← Back</a>
    </div>
</div>

{{-- User summary pills --}}
<div class="row" style="gap:12px;margin-bottom:24px;flex-wrap:wrap">
    <div class="stat" style="flex:1;min-width:140px">
        <div class="lbl">Total Orders</div>
        <div class="num">{{ $orders->count() }}</div>
    </div>
    <div class="stat" style="flex:1;min-width:140px">
        <div class="lbl">Total Spent</div>
        <div class="num">{{ money($totalSpent, 0) }}</div>
    </div>
    <div class="stat" style="flex:1;min-width:140px">
        <div class="lbl">Refunds</div>
        <div class="num">{{ $refunds->count() }}</div>
    </div>
    <div class="stat" style="flex:1;min-width:140px">
        <div class="lbl">Loyalty Points</div>
        <div class="num">{{ number_format($loyaltyBalance) }}</div>
    </div>
    <div class="stat" style="flex:1;min-width:140px">
        <div class="lbl">Status</div>
        <div class="num" style="font-size:14px;padding-top:4px">
            @if($user->is_active)
                <span class="pill sm green">Active</span>
            @else
                <span class="pill sm red">Inactive</span>
            @endif
        </div>
    </div>
</div>

{{-- Tabs --}}
<div x-data="{ tab: '{{ session('_tab') ?? (session('tab_after_update') ?? 'timeline') }}' }">

    <div class="seg" style="margin-bottom:20px">
        <button type="button" :class="tab==='timeline' ? 'active' : ''" @click="tab='timeline'">Timeline</button>
        <button type="button" :class="tab==='info' ? 'active' : ''" @click="tab='info'">User Information</button>
        <button type="button" :class="tab==='password' ? 'active' : ''" @click="tab='password'">Change Password</button>
    </div>

    {{-- ── TAB 1: TIMELINE ── --}}
    <div x-show="tab==='timeline'" x-cloak>
        @if($timeline->isEmpty())
            <div class="card pad" style="text-align:center;color:var(--text-muted);padding:48px">No activity yet.</div>
        @else
        <div class="card flush">
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Amount / Points</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeline as $item)
                    <tr>
                        <td>
                            @if($item['type']==='order')
                                <span class="pill sm blue">Order</span>
                            @elseif($item['type']==='refund')
                                <span class="pill sm orange">Refund</span>
                            @else
                                <span class="pill sm purple">Loyalty</span>
                            @endif
                        </td>
                        <td>
                            @if($item['type']==='order')
                                <a href="{{ route('admin.orders.show', $item['data']->id) }}" style="color:var(--accent);font-weight:600">
                                    #{{ $item['data']->order_number ?? $item['data']->id }}
                                </a>
                                <div style="font-size:12px;color:var(--text-muted)">{{ $item['data']->items->count() }} item(s)</div>
                            @elseif($item['type']==='refund')
                                Refund for order #{{ $item['data']->order->order_number ?? $item['data']->order_id }}
                            @else
                                {{ $item['data']->description ?? ucfirst($item['data']->type) }}
                            @endif
                        </td>
                        <td>
                            @if($item['type']==='order')
                                {{ money($item['data']->total, 0) }}
                            @elseif($item['type']==='refund')
                                {{ money($item['data']->amount ?? 0, 0) }}
                            @else
                                @if($item['data']->type==='earned')
                                    <span style="color:var(--success)">+{{ $item['data']->points }}</span>
                                @else
                                    <span style="color:var(--danger)">−{{ $item['data']->points }}</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($item['type']==='order')
                                <span class="pill sm {{ match($item['data']->status) {
                                    'delivered' => 'green',
                                    'processing','shipped' => 'blue',
                                    'cancelled' => 'red',
                                    default => 'yellow'
                                } }}">{{ ucfirst($item['data']->status) }}</span>
                            @elseif($item['type']==='refund')
                                <span class="pill sm {{ match($item['data']->status ?? '') {
                                    'approved' => 'green',
                                    'rejected' => 'red',
                                    default => 'yellow'
                                } }}">{{ ucfirst($item['data']->status ?? 'Pending') }}</span>
                            @else
                                <span class="pill sm {{ match($item['data']->type) {
                                    'earned' => 'green',
                                    'redeemed' => 'blue',
                                    'expired' => 'red',
                                    default => 'gray'
                                } }}">{{ ucfirst($item['data']->type) }}</span>
                            @endif
                        </td>
                        <td style="color:var(--text-muted);font-size:13px;white-space:nowrap">
                            {{ $item['date']->format('M d, Y H:i') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ── TAB 2: USER INFORMATION ── --}}
    <div x-show="tab==='info'" x-cloak>
        <div class="card pad" style="max-width:600px">
            <form method="POST" action="{{ route('admin.users.update', $user) }}"
                  style="display:flex;flex-direction:column;gap:20px">
                @csrf
                @method('PUT')
                <input type="hidden" name="_tab" value="info">

                <div class="field">
                    <span class="lbl">Name *</span>
                    <input type="text" name="name" class="input" required
                        value="{{ old('name', $user->name) }}" placeholder="Full name">
                    @error('name')<div style="color:var(--danger);font-size:12px">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <span class="lbl">Email *</span>
                    <input type="email" name="email" class="input" required
                        value="{{ old('email', $user->email) }}" placeholder="user@example.com">
                    @error('email')<div style="color:var(--danger);font-size:12px">{{ $message }}</div>@enderror
                </div>

                <div style="display:flex;align-items:center;gap:8px">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                        style="width:16px;height:16px;accent-color:var(--accent);cursor:pointer">
                    <span class="lbl" style="margin:0;cursor:pointer">Active</span>
                </div>

                <div class="row" style="gap:10px">
                    <button type="submit" class="btn btn-primary">Update User</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ── TAB 3: CHANGE PASSWORD ── --}}
    <div x-show="tab==='password'" x-cloak>
        <div class="card pad" style="max-width:600px">
            <form method="POST" action="{{ route('admin.users.change-password', $user) }}"
                  style="display:flex;flex-direction:column;gap:20px">
                @csrf
                <input type="hidden" name="_tab" value="password">

                <div class="field">
                    <span class="lbl">New Password *</span>
                    <input type="password" name="password" class="input" required
                        placeholder="Minimum 8 characters">
                    @error('password')<div style="color:var(--danger);font-size:12px">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <span class="lbl">Confirm New Password *</span>
                    <input type="password" name="password_confirmation" class="input" required
                        placeholder="Confirm new password">
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>

</div>

{{-- Restore active tab after form submit --}}
@if(session('_tab'))
<script>
document.addEventListener('alpine:init', () => {
    // handled by x-data default above
});
</script>
@endif
@endsection

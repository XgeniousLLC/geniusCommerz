@extends('admin.layouts.admin')

@section('title', 'Customers')

@section('content')

@php
$avatarColors = ['var(--info)','var(--violet)','var(--pop)','var(--teal)','var(--warning)','var(--accent)','var(--success)'];
@endphp

<div class="page-head">
    <div>
        <h2 class="display">Customers</h2>
        <div class="sub"><b style="color:var(--text)">{{ $users->total() }}</b> customers</div>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.users.create') }}">
        <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>Add customer
    </a>
</div>

<div class="stat-grid">
    <div class="card lift stat">
        <span class="tile t-info"><span class="ico" data-ico="users"></span></span>
        <div><div class="num">{{ $stats['total'] }}</div><div class="lbl">Total</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-violet"><span class="ico" data-ico="star"></span></span>
        <div><div class="num">{{ $stats['vip'] }}</div><div class="lbl">VIP (5+ orders)</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-teal"><span class="ico" data-ico="refresh"></span></span>
        <div><div class="num">{{ $stats['returning'] }}</div><div class="lbl">Returning</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-pop"><span class="ico" data-ico="spark"></span></span>
        <div><div class="num">{{ $stats['new'] }}</div><div class="lbl">New this month</div></div>
    </div>
</div>

<div class="between wrap" style="margin-bottom:16px;gap:12px">
    <form method="GET" class="row" style="gap:10px;flex:1;max-width:440px">
        <div class="search-field grow">
            <span class="ico" data-ico="search"></span>
            <input class="input" name="search" value="{{ request('search') }}" placeholder="Search name or email…">
        </div>
        <select class="input" name="status" style="max-width:130px" onchange="this.form.submit()">
            <option value="">All status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        @if(request()->anyFilled(['search','status']))
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline" style="padding:0 12px">Clear</a>
        @endif
    </form>
</div>

<div class="card flush">
    <div class="table-scroll"><table class="table">
        <thead><tr>
            <th>Customer</th>
            <th style="text-align:center">Orders</th>
            <th style="text-align:right">Lifetime spend</th>
            <th>Segment</th>
            <th>Status</th>
            <th>Joined</th>
            <th></th>
        </tr></thead>
        <tbody>
            @forelse($users as $user)
            @php
            $initials    = collect(explode(' ', $user->name))->map(fn($w) => strtoupper(mb_substr($w,0,1)))->take(2)->implode('');
            $avatarColor = $avatarColors[abs(crc32($user->name)) % count($avatarColors)];
            $orderCount  = $user->orders_count ?? 0;
            $ltv         = $user->orders_total ?? 0;
            $segment     = $orderCount >= 5 ? ['VIP', 'violet'] : ($orderCount >= 2 ? ['Returning', 'info'] : ['New', 'teal']);
            @endphp
            <tr class="hoverable" onclick="location.href='{{ route('admin.users.show', $user) }}'">
                <td>
                    <div class="row" style="gap:11px">
                        <span class="avatar" style="background:{{ $avatarColor }}">{{ $initials }}</span>
                        <div>
                            <div style="font-weight:700;font-size:13.5px">{{ $user->name }}</div>
                            <div class="faint" style="font-size:12px">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td style="text-align:center" class="tnum"><b>{{ $orderCount }}</b></td>
                <td style="text-align:right" class="tnum"><b>{{ money($ltv, 0) }}</b></td>
                <td><span class="pill {{ $segment[1] }}">{{ $segment[0] }}</span></td>
                <td><span class="pill sm {{ $user->is_active ? 'success' : 'danger' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="faint" style="font-size:13px">{{ $user->created_at->format('Y-m') }}</td>
                <td style="text-align:right">
                    <div class="row" style="gap:4px;justify-content:flex-end">
                        <a class="icon-btn" href="{{ route('admin.users.edit', $user) }}" onclick="event.stopPropagation()" title="Edit">
                            <span class="ico" data-ico="edit" style="width:16px;height:16px"></span>
                        </a>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onclick="event.stopPropagation()"
                              onsubmit="return confirm('Delete {{ addslashes($user->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="icon-btn danger">
                                <span class="ico" data-ico="trash" style="width:16px;height:16px"></span>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;padding:48px;color:var(--text-faint)">No customers found.</td></tr>
            @endforelse
        </tbody>
    </table></div>

    @if($users->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--border)">
        {{ $users->links() }}
    </div>
    @endif
</div>

@endsection

@extends('admin.layouts.admin')

@section('title', 'Team')

@section('content')

@php
$avatarColors = ['var(--violet)','var(--accent)','var(--teal)','var(--pop)','var(--info)','var(--warning)','var(--success)'];
$roleColors   = ['super_admin'=>'var(--violet)','admin'=>'var(--accent)','manager'=>'var(--info)','staff'=>'var(--teal)'];
@endphp

<div class="page-head">
    <div>
        <h2 class="display">Team</h2>
        <div class="sub">Manage administrator accounts and access levels</div>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.admins.create') }}">
        <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>Add member
    </a>
</div>

<div class="stat-grid">
    <div class="card lift stat">
        <span class="tile t-info"><span class="ico" data-ico="team"></span></span>
        <div><div class="num">{{ $stats['total'] }}</div><div class="lbl">Members</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-success"><span class="ico" data-ico="check"></span></span>
        <div><div class="num">{{ $stats['active'] }}</div><div class="lbl">Active</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-violet"><span class="ico" data-ico="shield"></span></span>
        <div><div class="num">{{ \App\Models\Admin::whereIn('role',['super_admin','admin'])->count() }}</div><div class="lbl">Admins</div></div>
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
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        @if(request()->anyFilled(['search','status']))
        <a href="{{ route('admin.admins.index') }}" class="btn btn-outline" style="padding:0 12px">Clear</a>
        @endif
    </form>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline btn-sm">
        <span class="ico" data-ico="shield" style="width:16px;height:16px"></span>Roles & Permissions
    </a>
</div>

<div class="card flush">
    <div class="table-scroll"><table class="table">
        <thead><tr>
            <th>Member</th>
            <th>Role</th>
            <th>Last active</th>
            <th>Status</th>
            <th></th>
        </tr></thead>
        <tbody>
            @forelse($admins as $admin)
            @php
            $initials    = collect(explode(' ', $admin->name))->map(fn($w) => strtoupper(mb_substr($w,0,1)))->take(2)->implode('');
            $avatarColor = $avatarColors[abs(crc32($admin->name)) % count($avatarColors)];
            $roleLabel   = ucwords(str_replace('_', ' ', $admin->role ?? 'Admin'));
            $roleColor   = $roleColors[$admin->role ?? ''] ?? 'var(--text-muted)';
            @endphp
            <tr>
                <td>
                    <div class="row" style="gap:11px">
                        <span class="avatar" style="background:{{ $avatarColor }}">{{ $initials }}</span>
                        <div>
                            <div style="font-weight:700;font-size:13.5px">
                                {{ $admin->name }}
                                @if(auth('admin')->id() === $admin->id)
                                <span class="faint" style="font-weight:600;font-size:12px"> · you</span>
                                @endif
                            </div>
                            <div class="faint" style="font-size:12px">{{ $admin->email }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span style="font-weight:700;font-size:13px;color:{{ $roleColor }}">
                        <span style="display:inline-block;width:8px;height:8px;border-radius:99px;background:{{ $roleColor }};margin-right:6px"></span>{{ $roleLabel }}
                    </span>
                </td>
                <td class="faint" style="font-size:13px">
                    {{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : '—' }}
                </td>
                <td>
                    <span class="pill sm {{ $admin->is_active ? 'success' : 'danger' }}">
                        <span class="dot"></span>{{ $admin->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td style="text-align:right">
                    <div class="row" style="gap:4px;justify-content:flex-end">
                        <a class="icon-btn" href="{{ route('admin.admins.edit', $admin) }}" title="Edit">
                            <span class="ico" data-ico="edit" style="width:16px;height:16px"></span>
                        </a>
                        @if(auth('admin')->id() !== $admin->id)
                        <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}"
                              onsubmit="return confirm('Remove {{ addslashes($admin->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="icon-btn danger">
                                <span class="ico" data-ico="trash" style="width:16px;height:16px"></span>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center;padding:48px;color:var(--text-faint)">No team members found.</td></tr>
            @endforelse
        </tbody>
    </table></div>

    @if($admins->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--border)">
        {{ $admins->links() }}
    </div>
    @endif
</div>

@endsection

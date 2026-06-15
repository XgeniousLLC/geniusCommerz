@extends('admin.layouts.admin')

@section('title', 'Roles & Permissions')

@section('content')

@php
$rolePillColors = [
    'super-admin'       => 'violet',
    'store-manager'     => 'accent',
    'operations'        => 'info',
    'inventory-manager' => 'teal',
    'marketing'         => 'pop',
    'call-agent'        => 'warning',
    'call-supervisor'   => 'warning',
    'accountant'        => 'success',
    'content-editor'    => 'teal',
];
@endphp

<div class="page-head">
    <div>
        <h2 class="display">Roles & Permissions</h2>
        <div class="sub">Define what each role can access in the admin panel</div>
    </div>
    <div class="row" style="gap:10px">
        <a class="btn btn-outline" href="{{ route('admin.admins.index') }}">
            <span class="ico" data-ico="users" style="width:16px;height:16px"></span>Team members
        </a>
        <a class="btn btn-primary" href="{{ route('admin.roles.create') }}">
            <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>Create role
        </a>
    </div>
</div>

<div class="card flush">
    <div class="table-scroll"><table class="table">
        <thead><tr>
            <th>Role</th>
            <th style="text-align:center">Permissions</th>
            <th style="text-align:center">Members</th>
            <th></th>
        </tr></thead>
        <tbody>
            @forelse($roles as $role)
            @php $pillColor = $rolePillColors[$role->name] ?? ''; @endphp
            <tr>
                <td>
                    <div class="row" style="gap:10px">
                        <span class="tile sm {{ $pillColor ? 't-'.$pillColor : 'muted' }}">
                            <span class="ico" data-ico="shield" style="width:17px;height:17px"></span>
                        </span>
                        <div>
                            <div style="font-weight:700;font-size:13.5px">{{ ucwords(str_replace('-', ' ', $role->name)) }}</div>
                            @if($role->name === 'super-admin')
                            <span class="pill sm" style="font-size:11px">System</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td style="text-align:center" class="tnum">
                    <span style="font-weight:700">{{ $role->permissions_count }}</span>
                    <span class="faint" style="font-size:12.5px"> perms</span>
                </td>
                <td style="text-align:center" class="tnum">
                    <span style="font-weight:700">{{ $adminCounts[$role->name] ?? 0 }}</span>
                </td>
                <td style="text-align:right">
                    <div class="row" style="gap:4px;justify-content:flex-end">
                        <a class="icon-btn" href="{{ route('admin.roles.edit', $role) }}" title="Edit permissions">
                            <span class="ico" data-ico="edit" style="width:16px;height:16px"></span>
                        </a>
                        @if($role->name !== 'super-admin')
                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                              onsubmit="return confirm('Delete role {{ addslashes($role->name) }}? Cannot be undone.')">
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
            <tr><td colspan="4" style="text-align:center;padding:48px;color:var(--text-faint)">No roles found.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</div>

@endsection

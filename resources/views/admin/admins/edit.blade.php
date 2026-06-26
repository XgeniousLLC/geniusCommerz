@extends('admin.layouts.admin')
@section('title', 'Edit Admin')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Edit Admin</h2>
        <div class="sub">{{ $admin->name }} — {{ $admin->email }}</div>
    </div>
    <a href="{{ route('admin.admins.index') }}" class="btn btn-outline btn-sm">Back to Team</a>
</div>

@php $activeTab = session('active_tab', 'profile'); @endphp

<div style="max-width:640px" x-data="{ tab: '{{ old('_tab', $activeTab) }}' }">

    <div class="seg" style="margin-bottom:16px">
        <button type="button" class="focusable" :class="tab === 'profile' ? 'active' : ''" @click="tab = 'profile'">Profile</button>
        <button type="button" class="focusable" :class="tab === 'password' ? 'active' : ''" @click="tab = 'password'">Password</button>
    </div>

    {{-- Profile tab --}}
    <div x-show="tab === 'profile'">
        <form method="POST" action="{{ route('admin.admins.update', $admin) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="_tab" value="profile">

            <div class="card pad" style="display:flex;flex-direction:column;gap:20px">

                <div class="field">
                    <label class="lbl">Name *</label>
                    <input type="text" name="name" class="input" placeholder="Full name" value="{{ old('name', $admin->name) }}" required>
                    @error('name')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
                </div>

                <div class="field">
                    <label class="lbl">Email *</label>
                    <input type="email" name="email" class="input" placeholder="admin@example.com" value="{{ old('email', $admin->email) }}" required>
                    @error('email')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
                </div>

                @php $currentRole = old('role', $admin->getRoleNames()->first() ?? $admin->role); @endphp
                <div class="field">
                    <label class="lbl">Role *</label>
                    <select name="role" class="select" required>
                        <option value="">Select Role</option>
                        @foreach(\Database\Seeders\RoleSeeder::ROLES as $role)
                            <option value="{{ $role }}" {{ $currentRole == $role ? 'selected' : '' }}>
                                {{ ucwords(str_replace('-', ' ', $role)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
                </div>

                <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $admin->is_active) ? 'checked' : '' }}>
                    <span class="lbl" style="margin:0">Active</span>
                </label>

                <div style="display:flex;gap:8px;padding-top:8px;border-top:1px solid var(--border)">
                    <a href="{{ route('admin.admins.index') }}" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn">Update Admin</button>
                </div>

            </div>
        </form>
    </div>

    {{-- Password tab --}}
    <div x-show="tab === 'password'">
        <form method="POST" action="{{ route('admin.admins.change-password', $admin) }}">
            @csrf
            <input type="hidden" name="_tab" value="password">

            <div class="card pad" style="display:flex;flex-direction:column;gap:20px">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="field">
                        <label class="lbl">New Password *</label>
                        <input type="password" name="password" class="input" placeholder="Minimum 8 characters" required>
                        @error('password')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label class="lbl">Confirm New Password *</label>
                        <input type="password" name="password_confirmation" class="input" placeholder="Confirm new password" required>
                    </div>
                </div>

                <div style="padding-top:8px;border-top:1px solid var(--border)">
                    <button type="submit" class="btn">Change Password</button>
                </div>

            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
{{-- Keep active tab after form submission with errors --}}
@if($errors->has('current_password') || $errors->has('password'))
document.addEventListener('DOMContentLoaded', function () {
    const el = document.querySelector('[x-data]');
    if (el && el._x_dataStack) { el._x_dataStack[0].tab = 'password'; }
});
@endif
</script>
@endpush

@endsection

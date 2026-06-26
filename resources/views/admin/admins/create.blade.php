@extends('admin.layouts.admin')
@section('title', 'Create Admin')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Create New Admin</h2>
        <div class="sub">Add a new administrator account</div>
    </div>
</div>

<div style="max-width:720px">
    <form method="POST" action="{{ route('admin.admins.store') }}">
        @csrf
        <div class="card pad" style="display:flex;flex-direction:column;gap:20px">

            <div class="field">
                <label class="lbl">Name *</label>
                <input type="text" name="name" class="input" placeholder="Full name" value="{{ old('name') }}" required>
                @error('name')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div class="field">
                <label class="lbl">Email *</label>
                <input type="email" name="email" class="input" placeholder="admin@example.com" value="{{ old('email') }}" required>
                @error('email')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="field">
                    <label class="lbl">Password *</label>
                    <input type="password" name="password" class="input" placeholder="Minimum 8 characters" required>
                    @error('password')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
                </div>
                <div class="field">
                    <label class="lbl">Confirm Password *</label>
                    <input type="password" name="password_confirmation" class="input" placeholder="Confirm password" required>
                </div>
            </div>

            <div class="field">
                <label class="lbl">Role *</label>
                <select name="role" class="select" required>
                    <option value="">Select Role</option>
                    @foreach(\Database\Seeders\RoleSeeder::ROLES as $role)
                        <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                            {{ ucwords(str_replace('-', ' ', $role)) }}
                        </option>
                    @endforeach
                </select>
                @error('role')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <span class="lbl" style="margin:0">Active</span>
            </label>

            <div style="display:flex;gap:10px;padding-top:8px;border-top:1px solid var(--border)">
                <button type="button" class="btn btn-outline" onclick="window.history.back()">Cancel</button>
                <button type="submit" class="btn">Create Admin</button>
            </div>

        </div>
    </form>
</div>

@endsection

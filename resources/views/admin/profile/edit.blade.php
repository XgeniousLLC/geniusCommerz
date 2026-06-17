@extends('admin.layouts.admin')

@section('title', 'Edit Profile')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Edit Profile</h2>
        <div class="sub">Update your personal information and account settings</div>
    </div>
    <a href="{{ route('admin.profile.change-password') }}" class="btn btn-outline">
        <span class="ico" data-ico="lock" style="width:16px;height:16px"></span> Change Password
    </a>
</div>

<div class="card pad" style="margin-bottom:18px">
    <div class="card-head" style="margin-bottom:0">
        <span class="avatar" style="width:56px;height:56px;font-size:18px">{{ strtoupper(substr($admin->name, 0, 2)) }}</span>
        <div class="ct">
            <h3>{{ $admin->name }}</h3>
            <div class="sub">{{ $admin->email }} &middot; {{ ucfirst($admin->role) }} Administrator</div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.profile.update') }}" class="col-gap" style="--gap:18px">
@csrf

<div class="card pad">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-info"><span class="ico" data-ico="user" style="width:18px;height:18px"></span></span>
        <div class="ct">
            <h3>Personal Information</h3>
            <div class="sub">This email is used for login and notifications</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
        <div class="field">
            <span class="lbl">Full Name *</span>
            <input class="input" type="text" name="name"
                   placeholder="Enter your full name" value="{{ old('name', $admin->name) }}" required>
            @error('name')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <span class="lbl">Email Address *</span>
            <input class="input" type="email" name="email"
                   placeholder="Enter your email address" value="{{ old('email', $admin->email) }}" required>
            @error('email')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="card pad">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-violet"><span class="ico" data-ico="shield" style="width:18px;height:18px"></span></span>
        <div class="ct">
            <h3>Account Information</h3>
            <div class="sub">Read-only details about your account</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:18px">
        <div>
            <div class="faint" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px">Role</div>
            <span class="pill sm {{ $admin->role === 'admin' ? 'accent' : ($admin->role === 'manager' ? 'info' : 'warning') }}">{{ ucfirst($admin->role) }}</span>
        </div>
        <div>
            <div class="faint" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px">Status</div>
            <span class="pill sm {{ $admin->is_active ? 'success' : 'danger' }}">{{ $admin->is_active ? 'Active' : 'Inactive' }}</span>
        </div>
        <div>
            <div class="faint" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px">Member Since</div>
            <div style="font-size:13px;font-weight:600">{{ $admin->created_at->format('M d, Y') }}</div>
        </div>
        <div>
            <div class="faint" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px">Last Updated</div>
            <div style="font-size:13px;font-weight:600">{{ $admin->updated_at->format('M d, Y g:i A') }}</div>
        </div>
    </div>
</div>

<div class="row" style="gap:12px">
    <button type="submit" class="btn btn-primary">Update Profile</button>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">Cancel</a>
</div>

</form>
@endsection

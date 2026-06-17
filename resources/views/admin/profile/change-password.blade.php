@extends('admin.layouts.admin')

@section('title', 'Change Password')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Change Password</h2>
        <div class="sub">Update your account password for enhanced security</div>
    </div>
    <a href="{{ route('admin.profile.edit') }}" class="btn btn-outline">
        <span class="ico" data-ico="user" style="width:16px;height:16px"></span> Edit Profile
    </a>
</div>

<form method="POST" action="{{ route('admin.profile.update-password') }}" class="col-gap" style="--gap:18px">
@csrf

<div class="card pad" style="background:color-mix(in srgb,var(--accent) 8%,transparent);border-color:color-mix(in srgb,var(--accent) 25%,transparent)">
    <div class="card-head" style="margin-bottom:0">
        <span class="tile sm t-info"><span class="ico" data-ico="shield" style="width:18px;height:18px"></span></span>
        <div class="ct">
            <h3>Password Requirements</h3>
            <div class="sub">At least 8 characters. Mix upper/lowercase, numbers and special characters. Avoid personal information.</div>
        </div>
    </div>
</div>

<div class="card pad">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-violet"><span class="ico" data-ico="lock" style="width:18px;height:18px"></span></span>
        <div class="ct">
            <h3>Password Security</h3>
            <div class="sub">Ensure your account is secure with a strong password</div>
        </div>
    </div>

    <div class="field" style="margin-bottom:18px">
        <span class="lbl">Current Password *</span>
        <input class="input" type="password" name="current_password"
               placeholder="Enter your current password" autocomplete="current-password" required>
        @error('current_password')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
        <p class="hint">For security, please confirm your current password.</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
        <div class="field">
            <span class="lbl">New Password *</span>
            <input class="input" type="password" name="password"
                   placeholder="Enter a strong new password" autocomplete="new-password" required>
            @error('password')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
        </div>
        <div class="field">
            <span class="lbl">Confirm New Password *</span>
            <input class="input" type="password" name="password_confirmation"
                   placeholder="Confirm your new password" autocomplete="new-password" required>
            <p class="hint">Re-enter your new password to confirm.</p>
        </div>
    </div>
</div>

<div class="card pad" style="background:color-mix(in srgb,var(--warning) 10%,transparent);border-color:color-mix(in srgb,var(--warning) 30%,transparent)">
    <div class="card-head" style="margin-bottom:0">
        <span class="tile sm t-warning"><span class="ico" data-ico="shield" style="width:18px;height:18px"></span></span>
        <div class="ct">
            <h3>Security Notice</h3>
            <div class="sub">After changing your password, you may be logged out of other devices to protect your account.</div>
        </div>
    </div>
</div>

<div class="row" style="gap:12px">
    <button type="submit" class="btn btn-primary">Update Password</button>
    <a href="{{ route('admin.profile.edit') }}" class="btn btn-outline">Cancel</a>
</div>

</form>
@endsection

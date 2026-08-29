@extends('admin.layouts.admin')

@section('title', 'SMS Gateways')

@section('content')
<style>
.pg-row{display:flex;align-items:center;gap:14px;padding:16px 18px;border-bottom:1px solid var(--border)}
.pg-row:last-child{border-bottom:0}
</style>

<div class="page-head">
    <div>
        <h2 class="display">SMS Gateways</h2>
        <div class="sub">Order notifications and login codes</div>
    </div>
</div>

@unless($hasDefault)
<div class="row" style="gap:12px;padding:14px 18px;border-radius:14px;background:color-mix(in srgb,var(--warning) 10%,transparent);border:1px solid color-mix(in srgb,var(--warning) 30%,transparent);margin-bottom:22px">
    <span class="tile sm" style="background:var(--warning);color:#fff"><span class="ico" data-ico="message" style="width:18px;height:18px"></span></span>
    <div style="font-size:13.5px;font-weight:600">No default gateway — order SMS and login codes will not be sent.</div>
</div>
@endunless

<div class="card flush" style="margin-bottom:16px">
    @foreach($gateways as $gateway)
    @php
        $def      = $gateway['definition'];
        $row      = $gateway['row'];
        $isActive = $row->exists && $row->is_active;
        $default  = $row->exists && $row->is_default;
        $planned  = ! $def->isImplemented();
    @endphp
    <div class="pg-row" style="{{ $planned ? 'opacity:.6' : '' }}">
        <span class="tile {{ $default ? 't-pop' : 'muted' }}">
            <span class="ico" data-ico="message" style="width:20px;height:20px"></span>
        </span>
        <div class="grow">
            <div class="row wrap" style="gap:7px;align-items:center">
                <span style="font-weight:700;font-size:14.5px">{{ $def->label }}</span>
                <span class="pill sm {{ $isActive ? 'success' : '' }}"><span class="dot"></span>{{ $isActive ? 'Enabled' : 'Disabled' }}</span>
                @if($default)<span class="pill sm accent">Default</span>@endif
            </div>
            <div class="faint" style="font-size:12px;margin-top:3px">
                {{ $def->countries === ['*'] ? 'Worldwide' : 'Best for '.implode(', ', $def->countries) }}
                @if($def->hint) · {{ $def->hint }}@endif
            </div>
        </div>
        <a href="{{ route('admin.integrations.edit', $def->slug) }}" class="link-btn">
            <span class="ico" data-ico="gear" style="width:15px;height:15px"></span>Credentials
        </a>
        @if($isActive && ! $default)
        <form method="POST" action="{{ route('admin.sms.default', $def->slug) }}" style="margin:0">
            @csrf<button type="submit" class="link-btn" style="font-size:13px;font-weight:600">Make default</button>
        </form>
        @endif
        <form method="POST" action="{{ route('admin.sms.toggle', $def->slug) }}" style="margin:0">
            @csrf<button type="submit" class="btn btn-outline btn-sm">{{ $isActive ? 'Disable' : 'Enable' }}</button>
        </form>
    </div>
    @endforeach
</div>

<div class="card pad">
    <div class="card-head" style="margin-bottom:12px">
        <span class="tile sm t-pop"><span class="ico" data-ico="message" style="width:17px;height:17px"></span></span>
        <div class="ct"><h3>Send a test message</h3><div class="sub">Sent through the same normalisation a real order uses</div></div>
    </div>
    @php $default = collect($gateways)->first(fn ($g) => $g['row']->exists && $g['row']->is_default); @endphp
    @if($default)
    <form method="POST" action="{{ route('admin.sms.test', $default['definition']->slug) }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 2fr auto;gap:12px;align-items:end">
            <div class="field" style="margin:0">
                <span class="lbl">Phone</span>
                <input class="input" name="phone" placeholder="+8801711111111" required>
            </div>
            <div class="field" style="margin:0">
                <span class="lbl">Message</span>
                <input class="input" name="message" value="Test SMS from {{ config('app.name') }}" required>
            </div>
            <div class="row" style="gap:8px">
                <button type="submit" class="btn btn-primary">Send</button>
            </div>
        </div>
    </form>
    <form method="POST" action="{{ route('admin.sms.balance', $default['definition']->slug) }}" style="margin-top:10px">
        @csrf<button type="submit" class="link-btn" style="font-size:12.5px">Check balance</button>
    </form>
    @else
    <p style="font-size:13.5px;color:var(--text-muted);margin:0">Enable a gateway and set it as default to send a test.</p>
    @endif
</div>
@endsection

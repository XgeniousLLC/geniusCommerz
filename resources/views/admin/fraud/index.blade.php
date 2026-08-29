@extends('admin.layouts.admin')

@section('title', 'Fraud Checks')

@section('content')
<style>
.pg-row{display:flex;align-items:center;gap:14px;padding:16px 18px;border-bottom:1px solid var(--border)}
.pg-row:last-child{border-bottom:0}
</style>

<div class="page-head">
    <div>
        <h2 class="display">Fraud Checks</h2>
        <div class="sub">Score a customer before dispatch</div>
    </div>
    @if($active)
    <div style="text-align:right">
        <span class="pill sm success"><span class="dot"></span>{{ $active }}</span>
        <div class="faint" style="font-size:12px;margin-top:4px">active checker</div>
    </div>
    @endif
</div>

<div class="row" style="gap:12px;padding:14px 18px;border-radius:14px;background:var(--surface-2);border:1px solid var(--border);margin-bottom:22px">
    <span class="tile sm t-info"><span class="ico" data-ico="shield" style="width:18px;height:18px"></span></span>
    <div style="font-size:13px;line-height:1.6">
        The Bangladeshi checkers score a phone against local courier delivery history, so
        they only mean anything for orders shipping to Bangladesh. IPQualityScore scores
        phone, email and IP reputation and works anywhere.
    </div>
</div>

<div class="card flush" style="margin-bottom:16px">
    @foreach($checkers as $checker)
    @php
        $def      = $checker['definition'];
        $row      = $checker['row'];
        $isActive = $row->exists && $row->is_active;
        $default  = $row->exists && $row->is_default;
    @endphp
    <div class="pg-row">
        <span class="tile {{ $default ? 't-info' : 'muted' }}">
            <span class="ico" data-ico="shield" style="width:20px;height:20px"></span>
        </span>
        <div class="grow">
            <div class="row wrap" style="gap:7px;align-items:center">
                <span style="font-weight:700;font-size:14.5px">{{ $def->label }}</span>
                <span class="pill sm {{ $isActive ? 'success' : '' }}"><span class="dot"></span>{{ $isActive ? 'Enabled' : 'Disabled' }}</span>
                @if($default)<span class="pill sm accent">Default</span>@endif
            </div>
            <div class="faint" style="font-size:12px;margin-top:3px">
                {{ $def->countries === ['*'] ? 'Worldwide' : implode(', ', $def->countries).' only' }}
                @if($def->hint) · {{ $def->hint }}@endif
            </div>
        </div>
        <a href="{{ route('admin.integrations.edit', $def->slug) }}" class="link-btn">
            <span class="ico" data-ico="gear" style="width:15px;height:15px"></span>Credentials
        </a>
        @if($isActive && ! $default)
        <form method="POST" action="{{ route('admin.fraud.default', $def->slug) }}" style="margin:0">
            @csrf<button type="submit" class="link-btn" style="font-size:13px;font-weight:600">Make default</button>
        </form>
        @endif
        <form method="POST" action="{{ route('admin.fraud.toggle', $def->slug) }}" style="margin:0">
            @csrf<button type="submit" class="btn btn-outline btn-sm">{{ $isActive ? 'Disable' : 'Enable' }}</button>
        </form>
    </div>
    @endforeach
</div>

<div class="card pad" style="margin-bottom:16px">
    <div class="card-head" style="margin-bottom:12px">
        <span class="tile sm t-info"><span class="ico" data-ico="search" style="width:17px;height:17px"></span></span>
        <div class="ct"><h3>Check a number</h3><div class="sub">Runs a live check against the default checker</div></div>
    </div>
    <form method="POST" action="{{ route('admin.fraud.test') }}">
        @csrf
        <div style="display:grid;grid-template-columns:2fr auto;gap:12px;align-items:end">
            <div class="field" style="margin:0">
                <span class="lbl">Phone</span>
                <input class="input" name="phone" placeholder="+8801711111111" required>
            </div>
            <button type="submit" class="btn btn-primary">Check</button>
        </div>
    </form>
</div>

@if($recent->isNotEmpty())
<div class="card flush">
    <div class="pad" style="padding-bottom:0"><h3 class="section-label">Recent checks</h3></div>
    <table class="table">
        <thead><tr><th>Phone</th><th>Provider</th><th>Risk</th><th style="text-align:right">Checked</th></tr></thead>
        <tbody>
            @foreach($recent as $check)
            <tr>
                <td class="mono" style="font-size:13px">{{ $check->phone }}</td>
                <td class="faint" style="font-size:12.5px">{{ $check->provider }}</td>
                <td>
                    <span class="pill sm {{ \App\Services\FraudScorer::riskColor($check->risk_level) }}">
                        {{ \App\Services\FraudScorer::riskLabel($check->risk_level) }} · {{ $check->risk_score }}
                    </span>
                </td>
                <td style="text-align:right" class="faint">{{ $check->updated_at?->diffForHumans() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection

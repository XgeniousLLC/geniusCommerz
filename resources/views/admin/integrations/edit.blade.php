@extends('admin.layouts.admin')

@section('title', 'Configure — ' . $definition->label)

@section('content')
@php
    $environment = $integration->environment ?: ($definition->environments[0] ?? 'sandbox');
@endphp

<div class="page-head">
    <div>
        <h2 class="display">{{ $definition->label }}</h2>
        <div class="sub">
            Credentials are encrypted at rest
            @if($definition->supportsEnvironments())
                · saved separately per environment
            @endif
        </div>
    </div>
    <a href="{{ route('admin.integrations.index') }}" class="btn btn-ghost">
        <span class="ico" data-ico="arrowLeft" style="width:16px;height:16px"></span>
        Integrations
    </a>
</div>

<div style="max-width:680px">
<form method="POST" action="{{ route('admin.integrations.update', $definition->slug) }}">
    @csrf
    @method('PUT')

    <div class="card pad" style="margin-bottom:16px">

        @if($definition->supportsEnvironments())
        <div class="field" style="margin-bottom:18px">
            <span class="lbl">Environment</span>
            <select class="input" name="environment">
                @foreach($definition->environments as $env)
                <option value="{{ $env }}" {{ $environment === $env ? 'selected' : '' }}>{{ ucfirst($env) }}</option>
                @endforeach
            </select>
            <div class="faint" style="font-size:12px;margin-top:4px">
                Secrets are stored per environment — switching here will not overwrite the other set.
            </div>
        </div>
        @else
            <input type="hidden" name="environment" value="{{ $definition->environments[0] ?? 'live' }}">
        @endif

        {{-- Credential fields, from the provider definition --}}
        @foreach($definition->fields as $field)
        @php
            $stored = $integration->exists ? $integration->getCredential($field->key) : null;
            $hasValue = $stored !== null && $stored !== '';
        @endphp
        <div class="field" style="margin-bottom:16px">
            <span class="lbl">
                {{ $field->label }}
                @unless($field->required)<span class="faint" style="font-weight:400">(optional)</span>@endunless
                @if($field->isSecret() && $definition->supportsEnvironments())
                    <span class="faint" style="font-weight:400">· {{ $environment }}</span>
                @endif
            </span>

            @if($field->type === 'textarea')
                <textarea class="input" name="credentials[{{ $field->key }}]" rows="4"
                    style="height:auto;resize:vertical;font-family:monospace;font-size:13px">{{ $stored }}</textarea>
            @elseif($field->type === 'select')
                <select class="input" name="credentials[{{ $field->key }}]">
                    @foreach($field->options as $value => $label)
                    <option value="{{ $value }}" {{ $stored === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            @else
                <input
                    class="input"
                    type="{{ $field->type }}"
                    name="credentials[{{ $field->key }}]"
                    value="{{ $field->isSecret() ? '' : $stored }}"
                    placeholder="{{ $field->isSecret() && $hasValue ? '••••••••  (leave blank to keep)' : ($field->hint ?? '') }}"
                >
            @endif

            @if($field->hint && ! $field->isSecret())
                <div class="faint" style="font-size:12px;margin-top:4px">{{ $field->hint }}</div>
            @endif
        </div>
        @endforeach

        <div style="border-top:1px solid var(--border);padding-top:16px;margin-top:4px">
            <div class="field" style="margin-bottom:14px">
                <span class="lbl">Status</span>
                <label class="row" style="gap:8px;margin-top:6px;cursor:pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                        {{ $integration->is_active ? 'checked' : '' }}
                        style="width:16px;height:16px;accent-color:var(--accent)">
                    <span style="font-size:13.5px">Active</span>
                </label>
            </div>

            <div class="field" style="margin-bottom:0">
                <span class="lbl">Notes <span class="faint" style="font-weight:400">(internal only)</span></span>
                <textarea class="input" name="notes" rows="2" style="height:auto;resize:vertical">{{ $integration->notes }}</textarea>
            </div>
        </div>
    </div>

    @if($definition->capabilities || $definition->docsUrl)
    <div class="card pad" style="margin-bottom:16px">
        <div class="row wrap" style="gap:7px;align-items:center">
            @foreach($definition->capabilities as $capability)
            <span class="pill sm">{{ $capability->label() }}</span>
            @endforeach
            @if($definition->docsUrl)
            <a href="{{ $definition->docsUrl }}" target="_blank" rel="noopener"
               class="link-btn" style="margin-left:auto;font-size:12.5px">Provider docs</a>
            @endif
        </div>
    </div>
    @endif

    <div class="row" style="gap:10px;justify-content:flex-end">
        <a href="{{ route('admin.integrations.index') }}" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Credentials</button>
    </div>
</form>

{{-- SMS test panel --}}
@if($definition->group === 'sms')
<div class="card pad" style="margin-top:20px">
    <div class="card-head" style="margin-bottom:12px">
        <span class="tile sm t-pop"><span class="ico" data-ico="message" style="width:17px;height:17px"></span></span>
        <div class="ct"><h3>Send Test SMS</h3></div>
        <form method="POST" action="{{ route('admin.integrations.sms-balance', $definition->slug) }}" style="margin:0">
            @csrf
            <button type="submit" class="link-btn head-action" style="font-size:12.5px">Check Balance</button>
        </form>
    </div>
    <form method="POST" action="{{ route('admin.integrations.test-sms', $definition->slug) }}">
        @csrf
        <div class="field" style="margin-bottom:12px">
            <span class="lbl">Phone Number</span>
            <input class="input" type="text" name="phone" placeholder="+15551234567">
        </div>
        <div class="field" style="margin-bottom:14px">
            <span class="lbl">Message</span>
            <textarea class="input" name="message" rows="2" style="height:auto;resize:vertical">Test SMS from {{ config('app.name') }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Send Test SMS</button>
    </form>
</div>
@endif

</div>
@endsection

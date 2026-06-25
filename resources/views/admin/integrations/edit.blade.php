@extends('admin.layouts.admin')

@section('title', 'Configure — ' . $integration->label)

@section('content')
@php
$fields = match($integration->provider) {
    'bkash' => [
        'app_key'         => ['label' => 'App Key',          'type' => 'text'],
        'app_secret'      => ['label' => 'App Secret',       'type' => 'password'],
        'username'        => ['label' => 'Username',          'type' => 'text'],
        'password'        => ['label' => 'Password',          'type' => 'password'],
        'base_url'        => ['label' => 'Base URL',          'type' => 'text', 'hint' => 'e.g. https://tokenized.sandbox.bka.sh/v1.2.0-beta'],
    ],
    'nagad' => [
        'merchant_id'     => ['label' => 'Merchant ID',      'type' => 'text'],
        'merchant_number' => ['label' => 'Merchant Number',  'type' => 'text'],
        'public_key'      => ['label' => 'Public Key (PEM)', 'type' => 'textarea'],
        'private_key'     => ['label' => 'Private Key (PEM)','type' => 'textarea'],
        'base_url'        => ['label' => 'Base URL',          'type' => 'text'],
    ],
    'sslcommerz' => [
        'store_id'        => ['label' => 'Store ID',          'type' => 'text'],
        'store_password'  => ['label' => 'Store Password',    'type' => 'password'],
        'base_url'        => ['label' => 'Base URL',          'type' => 'text', 'hint' => 'https://sandbox.sslcommerz.com or https://securepay.sslcommerz.com'],
    ],
    'pathao' => [
        'client_id'       => ['label' => 'Client ID',         'type' => 'text'],
        'client_secret'   => ['label' => 'Client Secret',     'type' => 'password'],
        'username'        => ['label' => 'Username',           'type' => 'text'],
        'password'        => ['label' => 'Password',           'type' => 'password'],
        'base_url'        => ['label' => 'Base URL',           'type' => 'text'],
    ],
    'steadfast' => [
        'api_key'         => ['label' => 'API Key',            'type' => 'text'],
        'secret_key'      => ['label' => 'Secret Key',         'type' => 'password'],
        'base_url'        => ['label' => 'Base URL',           'type' => 'text'],
    ],
    'redx' => [
        'api_key'         => ['label' => 'API Key',            'type' => 'text'],
        'base_url'        => ['label' => 'Base URL',           'type' => 'text'],
    ],
    'fraudbd' => [
        'api_key'         => ['label' => 'API Key',            'type' => 'text'],
        'base_url'        => ['label' => 'Base URL',           'type' => 'text'],
    ],
    'bdcourier' => [
        'api_key'         => ['label' => 'API Key',            'type' => 'password', 'hint' => 'Bearer token from api.bdcourier.com'],
    ],
    'bulksmsbd' => [
        'api_key'         => ['label' => 'API Key',            'type' => 'password'],
        'sender_id'       => ['label' => 'Sender ID',          'type' => 'text',     'hint' => 'Approved sender name or number'],
        'base_url'        => ['label' => 'Base URL',           'type' => 'text',     'hint' => 'https://bulksmsbd.net/api/smsapi'],
    ],
    'smsbd' => [
        'api_key'         => ['label' => 'API Key',            'type' => 'password'],
        'sender_id'       => ['label' => 'Sender ID',          'type' => 'text'],
        'base_url'        => ['label' => 'Base URL',           'type' => 'text',     'hint' => 'https://sms.smsbd.in/api/v2/send'],
    ],
    'mram' => [
        'api_key'         => ['label' => 'API Key',            'type' => 'password', 'hint' => 'From msg.mram.com.bd → Developers API'],
        'sender_id'       => ['label' => 'Sender ID',          'type' => 'text',     'hint' => 'Approved sender ID / masking'],
        'label'           => ['label' => 'SMS Label',          'type' => 'text',     'hint' => 'Optional — transactional or promotional'],
        'base_url'        => ['label' => 'Base URL',           'type' => 'text',     'hint' => 'https://msg.mram.com.bd/smsapi'],
    ],
    'twilio' => [
        'account_sid'     => ['label' => 'Account SID',        'type' => 'text'],
        'auth_token'      => ['label' => 'Auth Token',          'type' => 'password'],
        'from_number'     => ['label' => 'From Number',         'type' => 'text',    'hint' => 'e.g. +15551234567'],
    ],
    'openai' => [
        'api_key'         => ['label' => 'API Key',             'type' => 'password', 'hint' => 'Starts with sk-…'],
        'model'           => ['label' => 'Model',               'type' => 'text',     'hint' => 'e.g. gpt-4o, gpt-4o-mini'],
    ],
    'gemini' => [
        'api_key'         => ['label' => 'API Key',             'type' => 'password', 'hint' => 'From Google AI Studio'],
        'model'           => ['label' => 'Model',               'type' => 'text',     'hint' => 'e.g. gemini-1.5-flash, gemini-1.5-pro'],
    ],
    'claude' => [
        'api_key'         => ['label' => 'API Key',             'type' => 'password', 'hint' => 'Starts with sk-ant-…'],
        'model'           => ['label' => 'Model',               'type' => 'text',     'hint' => 'e.g. claude-haiku-4-5-20251001, claude-sonnet-4-6'],
    ],
    'deepseek' => [
        'api_key'         => ['label' => 'API Key',             'type' => 'password'],
        'model'           => ['label' => 'Model',               'type' => 'text',     'hint' => 'e.g. deepseek-chat, deepseek-reasoner'],
    ],
    default => [],
};

$creds          = $integration->credentials ?? [];
$isFraud        = in_array($integration->provider, \App\Models\Integration::FRAUD_PROVIDERS);
$showEnvironment = ! $isFraud;
@endphp

<div class="page-head">
    <div>
        <h2 class="display">{{ $integration->label }}</h2>
        <div class="sub">Credentials are encrypted at rest</div>
    </div>
    <a href="{{ route('admin.integrations.index') }}" class="btn btn-ghost">
        <span class="ico" data-ico="arrowLeft" style="width:16px;height:16px"></span>
        Integrations
    </a>
</div>

<div style="max-width:680px">
<form method="POST" action="{{ route('admin.integrations.update', $integration) }}">
    @csrf
    @method('PUT')

    <div class="card pad" style="margin-bottom:16px">

        {{-- Credential fields --}}
        @foreach($fields as $key => $field)
        <div class="field" style="margin-bottom:16px">
            <span class="lbl">{{ $field['label'] }}</span>
            @if(($field['type'] ?? 'text') === 'textarea')
                <textarea class="input" name="credentials[{{ $key }}]" rows="4"
                    style="height:auto;resize:vertical;font-family:monospace;font-size:13px">{{ $creds[$key] ?? '' }}</textarea>
            @else
                <input
                    class="input"
                    type="{{ $field['type'] }}"
                    name="credentials[{{ $key }}]"
                    value="{{ $field['type'] === 'password' ? '' : ($creds[$key] ?? '') }}"
                    placeholder="{{ $field['type'] === 'password' && isset($creds[$key]) && $creds[$key] !== '' ? '••••••••  (leave blank to keep)' : ($field['hint'] ?? '') }}"
                >
            @endif
            @if(!empty($field['hint']) && $field['type'] !== 'password')
                <div class="faint" style="font-size:12px;margin-top:4px">{{ $field['hint'] }}</div>
            @endif
        </div>
        @endforeach

        <div style="border-top:1px solid var(--border);padding-top:16px;margin-top:4px">
            <div style="display:grid;grid-template-columns:{{ $showEnvironment ? '1fr 1fr' : '1fr' }};gap:14px;margin-bottom:14px">

                @if($showEnvironment)
                <div class="field" style="margin:0">
                    <span class="lbl">Environment</span>
                    <select class="input" name="environment">
                        <option value="sandbox" {{ $integration->environment === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                        <option value="live"    {{ $integration->environment === 'live'    ? 'selected' : '' }}>Live</option>
                    </select>
                </div>
                @else
                    {{-- Fraud providers are always live --}}
                    <input type="hidden" name="environment" value="live">
                @endif

                <div class="field" style="margin:0">
                    <span class="lbl">Status</span>
                    <label class="row" style="gap:8px;margin-top:6px;cursor:pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                            {{ $integration->is_active ? 'checked' : '' }}
                            style="width:16px;height:16px;accent-color:var(--accent)">
                        <span style="font-size:13.5px">Active</span>
                    </label>
                </div>
            </div>

            <div class="field" style="margin-bottom:0">
                <span class="lbl">Notes <span class="faint" style="font-weight:400">(internal only)</span></span>
                <textarea class="input" name="notes" rows="2" style="height:auto;resize:vertical">{{ $integration->notes }}</textarea>
            </div>
        </div>
    </div>

    <div class="row" style="gap:10px;justify-content:flex-end">
        <a href="{{ route('admin.integrations.index') }}" class="btn btn-ghost">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Credentials</button>
    </div>
</form>

{{-- SMS test panel --}}
@if(in_array($integration->provider, \App\Models\Integration::SMS_PROVIDERS))
<div class="card pad" style="margin-top:20px">
    <div class="card-head" style="margin-bottom:12px">
        <span class="tile sm t-pop"><span class="ico" data-ico="message" style="width:17px;height:17px"></span></span>
        <div class="ct"><h3>Send Test SMS</h3></div>
        <form method="POST" action="{{ route('admin.integrations.sms-balance', $integration) }}" style="margin:0">
            @csrf
            <button type="submit" class="link-btn head-action" style="font-size:12.5px">Check Balance</button>
        </form>
    </div>
    <form method="POST" action="{{ route('admin.integrations.test-sms', $integration) }}">
        @csrf
        <div class="field" style="margin-bottom:12px">
            <span class="lbl">Phone Number</span>
            <input class="input" type="text" name="phone" placeholder="+8801XXXXXXXXX">
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

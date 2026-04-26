@extends('admin.layouts.admin')

@section('title', 'Configure — ' . $integration->label)

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li><a href="{{ route('admin.integrations.index') }}" class="hover:text-gray-700">Integrations</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">{{ $integration->label }}</li>
    </ol>
@endsection

@section('page-header')
    <h1 class="text-2xl font-bold text-gray-900">Configure: {{ $integration->label }}</h1>
    <p class="text-gray-600 mt-1">Credentials are encrypted at rest</p>
@endsection

@section('content')
@php
$fields = match($integration->provider) {
    'bkash' => [
        'app_key'         => ['label' => 'App Key',         'type' => 'text'],
        'app_secret'      => ['label' => 'App Secret',      'type' => 'password'],
        'username'        => ['label' => 'Username',         'type' => 'text'],
        'password'        => ['label' => 'Password',         'type' => 'password'],
        'base_url'        => ['label' => 'Base URL',         'type' => 'text', 'hint' => 'e.g. https://tokenized.sandbox.bka.sh/v1.2.0-beta'],
    ],
    'nagad' => [
        'merchant_id'     => ['label' => 'Merchant ID',     'type' => 'text'],
        'merchant_number' => ['label' => 'Merchant Number', 'type' => 'text'],
        'public_key'      => ['label' => 'Public Key (PEM)','type' => 'textarea'],
        'private_key'     => ['label' => 'Private Key (PEM)','type' => 'textarea'],
        'base_url'        => ['label' => 'Base URL',         'type' => 'text'],
    ],
    'sslcommerz' => [
        'store_id'        => ['label' => 'Store ID',         'type' => 'text'],
        'store_password'  => ['label' => 'Store Password',   'type' => 'password'],
        'base_url'        => ['label' => 'Base URL',         'type' => 'text', 'hint' => 'https://sandbox.sslcommerz.com or https://securepay.sslcommerz.com'],
    ],
    'pathao' => [
        'client_id'       => ['label' => 'Client ID',        'type' => 'text'],
        'client_secret'   => ['label' => 'Client Secret',    'type' => 'password'],
        'username'        => ['label' => 'Username',          'type' => 'text'],
        'password'        => ['label' => 'Password',          'type' => 'password'],
        'base_url'        => ['label' => 'Base URL',          'type' => 'text'],
    ],
    'steadfast' => [
        'api_key'         => ['label' => 'API Key',           'type' => 'text'],
        'secret_key'      => ['label' => 'Secret Key',        'type' => 'password'],
        'base_url'        => ['label' => 'Base URL',          'type' => 'text'],
    ],
    'redx' => [
        'api_key'         => ['label' => 'API Key',           'type' => 'text'],
        'base_url'        => ['label' => 'Base URL',          'type' => 'text'],
    ],
    'fraudbd' => [
        'api_key'         => ['label' => 'API Key',           'type' => 'text'],
        'base_url'        => ['label' => 'Base URL',          'type' => 'text'],
    ],
    'bulksmsbd' => [
        'api_key'         => ['label' => 'API Key',           'type' => 'password'],
        'sender_id'       => ['label' => 'Sender ID',         'type' => 'text', 'hint' => 'Approved sender name or number'],
        'base_url'        => ['label' => 'Base URL',          'type' => 'text', 'hint' => 'https://bulksmsbd.net/api/smsapi'],
    ],
    'smsbd' => [
        'api_key'         => ['label' => 'API Key',           'type' => 'password'],
        'sender_id'       => ['label' => 'Sender ID',         'type' => 'text'],
        'base_url'        => ['label' => 'Base URL',          'type' => 'text', 'hint' => 'https://sms.smsbd.in/api/v2/send'],
    ],
    'twilio' => [
        'account_sid'     => ['label' => 'Account SID',       'type' => 'text'],
        'auth_token'      => ['label' => 'Auth Token',         'type' => 'password'],
        'from_number'     => ['label' => 'From Number',        'type' => 'text', 'hint' => 'e.g. +15551234567'],
    ],
    default => [],
};
$creds = $integration->credentials ?? [];
@endphp

<div class="max-w-2xl">
<form method="POST" action="{{ route('admin.integrations.update', $integration) }}">
    @csrf
    @method('PUT')

    <x-admin.card>
        <div class="grid grid-cols-1 gap-5">

            @foreach($fields as $key => $field)
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">{{ $field['label'] }}</label>
                    @if(($field['type'] ?? 'text') === 'textarea')
                        <textarea name="credentials[{{ $key }}]" rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm font-mono">{{ $creds[$key] ?? '' }}</textarea>
                    @else
                        <x-admin.input
                            type="{{ $field['type'] }}"
                            name="credentials[{{ $key }}]"
                            value="{{ $field['type'] === 'password' ? '' : ($creds[$key] ?? '') }}"
                            :placeholder="$field['type'] === 'password' && isset($creds[$key]) ? '••••••••  (leave blank to keep)' : ''"
                        />
                    @endif
                    @if(!empty($field['hint']))
                        <p class="text-xs text-gray-400 mt-1">{{ $field['hint'] }}</p>
                    @endif
                </x-admin.form-group>
            @endforeach

            <hr class="border-gray-200">

            <div class="grid grid-cols-2 gap-4">
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Environment</label>
                    <x-admin.select name="environment">
                        <option value="sandbox" {{ $integration->environment === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                        <option value="live"    {{ $integration->environment === 'live'    ? 'selected' : '' }}>Live</option>
                    </x-admin.select>
                </x-admin.form-group>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <label class="flex items-center space-x-2 mt-2">
                        <input type="checkbox" name="is_active" value="1"
                            {{ $integration->is_active ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Active</span>
                    </label>
                </x-admin.form-group>
            </div>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">{{ $integration->notes }}</textarea>
            </x-admin.form-group>

        </div>

        <div class="flex justify-end space-x-3 mt-6">
            <x-admin.button variant="secondary" type="button" onclick="window.history.back()">Cancel</x-admin.button>
            <x-admin.button type="submit">Save Credentials</x-admin.button>
        </div>
    </x-admin.card>
</form>
</div>
@endsection

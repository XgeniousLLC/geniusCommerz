@extends('admin.layouts.admin')

@section('title', 'Integrations')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Integrations</li>
    </ol>
@endsection

@section('page-header')
    <h1 class="text-2xl font-bold text-gray-900">Integrations</h1>
    <p class="text-gray-600 mt-1">API credentials for payment gateways, couriers, and services</p>
@endsection

@section('content')
@php
    $groups = [
        'Payments' => ['bkash', 'nagad', 'sslcommerz'],
        'Couriers' => ['pathao', 'steadfast', 'redx'],
        'SMS Gateways' => ['bulksmsbd', 'smsbd', 'twilio'],
        'Services'  => ['fraudbd'],
    ];
    $courierProviders = \App\Models\Integration::COURIER_PROVIDERS;
    $smsProviders     = \App\Models\Integration::SMS_PROVIDERS;
@endphp

@foreach($groups as $groupName => $providers)
    @php $groupIntegrations = $integrations->whereIn('provider', $providers); @endphp
    @if($groupIntegrations->isNotEmpty())
        <div class="flex items-center justify-between mb-3 mt-6 first:mt-0">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ $groupName }}</h2>
            @if($groupName === 'Couriers')
                <span class="text-xs text-gray-400">Only one courier can be the default</span>
            @elseif($groupName === 'SMS Gateways')
                <span class="text-xs text-gray-400">Only one gateway can be the default</span>
            @endif
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
            @foreach($groupIntegrations as $integration)
                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow
                    {{ $integration->is_default ? 'ring-2 ring-blue-500' : '' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center flex-wrap gap-1.5">
                                <span class="text-sm font-semibold text-gray-900">{{ $integration->label }}</span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium
                                    {{ $integration->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $integration->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($integration->is_default)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                        Default
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ ucfirst($integration->environment) }} mode ·
                                {{ count($integration->credentials ?? []) }} credential(s) saved
                            </p>
                        </div>
                        <a href="{{ route('admin.integrations.edit', $integration) }}"
                           class="ml-3 text-blue-600 hover:text-blue-800 text-xs font-medium shrink-0">
                            Configure
                        </a>
                    </div>

                    @if(in_array($integration->provider, $courierProviders) || in_array($integration->provider, $smsProviders))
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            @if($integration->is_default)
                                <span class="text-xs text-blue-600 font-medium">✓ Currently default</span>
                            @elseif($integration->is_active)
                                <form method="POST" action="{{ route('admin.integrations.set-default', $integration) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="text-xs text-gray-600 hover:text-blue-700 font-medium underline underline-offset-2">
                                        Set as default
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-400">Activate to set as default</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
@endforeach
@endsection

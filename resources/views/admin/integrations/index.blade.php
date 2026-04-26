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
        'Payments'  => ['bkash', 'nagad', 'sslcommerz'],
        'Couriers'  => ['pathao', 'steadfast', 'redx'],
        'Services'  => ['fraudbd', 'sms'],
    ];
@endphp

@foreach($groups as $groupName => $providers)
    <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 mt-6 first:mt-0">
        {{ $groupName }}
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
        @foreach($integrations->whereIn('provider', $providers) as $integration)
            <div class="bg-white border border-gray-200 rounded-lg p-4 flex items-start justify-between hover:shadow-sm transition-shadow">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-semibold text-gray-900">{{ $integration->label }}</span>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium
                            {{ $integration->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $integration->is_active ? 'Active' : 'Inactive' }}
                        </span>
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
        @endforeach
    </div>
@endforeach
@endsection

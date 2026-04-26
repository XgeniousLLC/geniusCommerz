@extends('admin.layouts.admin')

@section('title', 'Social Proof')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Social Proof</li>
    </ol>
@endsection

@section('page-header')
    <h1 class="text-2xl font-bold text-gray-900">Social Proof</h1>
    <p class="text-gray-600 mt-1">Increase conversions with live visitor counters and purchase alerts</p>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.social-proof.update') }}">
    @csrf
    <div class="space-y-6 max-w-3xl">

        {{-- Visitor Counter --}}
        <x-admin.card>
            <div class="flex items-start gap-4 mb-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Custom Product Visitor Counter</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Shows "<strong>X people are viewing this item right now</strong>" on product pages. Uses a random number within your configured range to create urgency.</p>
                </div>
            </div>

            <div class="space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="settings[storefront.visitor_counter_enabled]" value="0">
                    <input type="checkbox" name="settings[storefront.visitor_counter_enabled]" value="1"
                        {{ old('storefront.visitor_counter_enabled', $settings->get('storefront.visitor_counter_enabled')?->value) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Enable visitor counter on product pages</span>
                </label>

                <div class="grid grid-cols-2 gap-4">
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Min Visitors</label>
                        <x-admin.input type="number" name="settings[storefront.visitor_counter_min]" min="1"
                            value="{{ old('storefront.visitor_counter_min', $settings->get('storefront.visitor_counter_min')?->value ?? '5') }}" />
                        <p class="text-xs text-gray-400 mt-1">Minimum number to show</p>
                    </x-admin.form-group>
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Max Visitors</label>
                        <x-admin.input type="number" name="settings[storefront.visitor_counter_max]" min="1"
                            value="{{ old('storefront.visitor_counter_max', $settings->get('storefront.visitor_counter_max')?->value ?? '20') }}" />
                        <p class="text-xs text-gray-400 mt-1">Maximum number to show</p>
                    </x-admin.form-group>
                </div>

                <div class="rounded-lg bg-gray-50 border border-gray-200 p-3 text-sm text-gray-600">
                    <strong>Preview:</strong> "{{ $settings->get('storefront.visitor_counter_min')?->value ?? '5' }}–{{ $settings->get('storefront.visitor_counter_max')?->value ?? '20' }} people are viewing this item right now"
                </div>
            </div>
        </x-admin.card>

        {{-- Sale Alert --}}
        <x-admin.card>
            <div class="flex items-start gap-4 mb-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Custom Sale Alert</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Shows a popup toast like "<strong>Product Name — ordered just now!</strong>" at random intervals on every page. Builds trust and urgency through social proof.</p>
                </div>
            </div>

            <div class="space-y-5">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="settings[storefront.sale_alert_enabled]" value="0">
                    <input type="checkbox" name="settings[storefront.sale_alert_enabled]" value="1"
                        {{ old('storefront.sale_alert_enabled', $settings->get('storefront.sale_alert_enabled')?->value) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Enable sale alert popups</span>
                </label>

                <div class="grid grid-cols-2 gap-4">
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Min Interval (seconds)</label>
                        <x-admin.input type="number" name="settings[storefront.sale_alert_interval_min]" min="5"
                            value="{{ old('storefront.sale_alert_interval_min', $settings->get('storefront.sale_alert_interval_min')?->value ?? '10') }}" />
                        <p class="text-xs text-gray-400 mt-1">Shortest gap between alerts</p>
                    </x-admin.form-group>
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Max Interval (seconds)</label>
                        <x-admin.input type="number" name="settings[storefront.sale_alert_interval_max]" min="5"
                            value="{{ old('storefront.sale_alert_interval_max', $settings->get('storefront.sale_alert_interval_max')?->value ?? '30') }}" />
                        <p class="text-xs text-gray-400 mt-1">Longest gap between alerts</p>
                    </x-admin.form-group>
                </div>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Products to Feature in Alerts</label>
                    <p class="text-xs text-gray-500 mb-3">Select which products appear in the sale notifications. Alerts cycle through them in order. <strong>No alerts will show if none are selected.</strong></p>

                    @if($products->isNotEmpty())
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-3 py-2 border-b border-gray-200 flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Active Products ({{ $products->count() }})</span>
                                <span class="text-xs text-gray-400">{{ count($saleAlertProductIds) }} selected</span>
                            </div>
                            <div class="max-h-[300px] overflow-y-auto divide-y divide-gray-100">
                                @foreach($products as $product)
                                    <label class="flex items-center gap-3 px-3 py-2.5 hover:bg-gray-50 cursor-pointer transition-colors">
                                        <input type="checkbox" name="sale_alert_product_ids[]"
                                            value="{{ $product->id }}"
                                            {{ in_array($product->id, $saleAlertProductIds) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 flex-shrink-0">
                                        <span class="text-sm text-gray-700">{{ $product->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="rounded-lg border border-dashed border-gray-300 p-6 text-center">
                            <p class="text-sm text-gray-400">No active products found. <a href="{{ route('admin.products.create') }}" class="text-blue-600 hover:underline">Add a product</a> first.</p>
                        </div>
                    @endif
                </x-admin.form-group>
            </div>
        </x-admin.card>

        <div class="flex justify-end">
            <x-admin.button type="submit">Save Settings</x-admin.button>
        </div>

    </div>
</form>
@endsection

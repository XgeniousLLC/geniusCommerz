@extends('admin.layouts.admin')
@section('title', 'Shop Settings')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Storefront → Shop</li>
</ol>
@endsection

@section('page-header')
<h1 class="text-2xl font-bold text-gray-900">Shop Page Settings</h1>
<p class="text-gray-600 mt-1">Control the default layout, sorting and filters for the shop listing</p>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.storefront.shop.update') }}">
@csrf
<div class="space-y-6 max-w-3xl">

    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-6">Listing Defaults</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Products per page</label>
                <x-admin.select name="settings[storefront.shop_per_page]">
                    @foreach([12, 24, 36, 48] as $n)
                        <option value="{{ $n }}" {{ ($settings->get('storefront.shop_per_page')?->value ?? '12') == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </x-admin.select>
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Default sort order</label>
                <x-admin.select name="settings[storefront.shop_default_sort]">
                    @foreach([
                        'newest'     => 'Newest first',
                        'oldest'     => 'Oldest first',
                        'price_asc'  => 'Price: low to high',
                        'price_desc' => 'Price: high to low',
                        'featured'   => 'Featured',
                        'name_asc'   => 'Name A–Z',
                    ] as $val => $label)
                        <option value="{{ $val }}" {{ ($settings->get('storefront.shop_default_sort')?->value ?? 'newest') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </x-admin.select>
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Default layout</label>
                <x-admin.select name="settings[storefront.shop_layout]">
                    <option value="grid" {{ ($settings->get('storefront.shop_layout')?->value ?? 'grid') === 'grid' ? 'selected' : '' }}>Grid</option>
                    <option value="list" {{ ($settings->get('storefront.shop_layout')?->value ?? 'grid') === 'list' ? 'selected' : '' }}>List</option>
                </x-admin.select>
            </x-admin.form-group>

        </div>
    </x-admin.card>

    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-4">Visibility</h3>
        <div class="space-y-3">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="settings[storefront.shop_show_filters]" value="0">
                <input type="checkbox" name="settings[storefront.shop_show_filters]" value="1"
                    {{ ($settings->get('storefront.shop_show_filters')?->value ?? '1') ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                <span class="text-sm text-gray-700">Show filter sidebar</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="settings[storefront.shop_show_out_of_stock]" value="0">
                <input type="checkbox" name="settings[storefront.shop_show_out_of_stock]" value="1"
                    {{ ($settings->get('storefront.shop_show_out_of_stock')?->value ?? '1') ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                <span class="text-sm text-gray-700">Show out-of-stock products</span>
            </label>
        </div>
    </x-admin.card>

    <div class="flex justify-end">
        <x-admin.button type="submit">Save Shop Settings</x-admin.button>
    </div>
</div>
</form>
@endsection

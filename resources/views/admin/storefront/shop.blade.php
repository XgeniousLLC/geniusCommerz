@extends('admin.layouts.admin')
@section('title', 'Shop Page Settings')
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Shop Page</h2>
        <div class="sub">Default layout, sort order and filter visibility for the product listing</div>
    </div>
    <div class="actions">
        <a href="{{ url('/shop') }}" target="_blank" class="btn sm secondary">
            <span class="ico" data-ico="external" style="width:15px;height:15px"></span> Preview shop
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.storefront.shop.update') }}" class="col-gap" style="--gap:18px">
@csrf

<div class="card pad">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-accent"><span class="ico" data-ico="sliders" style="width:18px;height:18px"></span></span>
        <div class="ct"><h3>Listing Defaults</h3><div class="sub">Applied when no query params override them</div></div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
        <div class="field">
            <span class="lbl">Products per page</span>
            <select class="input" name="settings[storefront.shop_per_page]">
                @foreach([12, 24, 36, 48] as $n)
                    <option value="{{ $n }}" {{ ($settings->get('storefront.shop_per_page')?->value ?? '12') == $n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <span class="lbl">Default sort order</span>
            <select class="input" name="settings[storefront.shop_default_sort]">
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
            </select>
        </div>
        <div class="field">
            <span class="lbl">Default layout</span>
            <select class="input" name="settings[storefront.shop_layout]">
                <option value="grid" {{ ($settings->get('storefront.shop_layout')?->value ?? 'grid') === 'grid' ? 'selected' : '' }}>Grid</option>
                <option value="list" {{ ($settings->get('storefront.shop_layout')?->value ?? 'grid') === 'list' ? 'selected' : '' }}>List</option>
            </select>
        </div>
    </div>
</div>

<div class="card pad">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-success"><span class="ico" data-ico="eye" style="width:18px;height:18px"></span></span>
        <div class="ct"><h3>Visibility</h3><div class="sub">Control what appears on the shop listing page</div></div>
    </div>
    <div style="display:grid;gap:12px">
        <label class="toggle-row">
            <input type="hidden" name="settings[storefront.shop_show_filters]" value="0">
            <input type="checkbox" role="switch" name="settings[storefront.shop_show_filters]" value="1"
                {{ ($settings->get('storefront.shop_show_filters')?->value ?? '1') ? 'checked' : '' }}>
            <span>Show filter sidebar</span>
        </label>
        <label class="toggle-row">
            <input type="hidden" name="settings[storefront.shop_show_out_of_stock]" value="0">
            <input type="checkbox" role="switch" name="settings[storefront.shop_show_out_of_stock]" value="1"
                {{ ($settings->get('storefront.shop_show_out_of_stock')?->value ?? '1') ? 'checked' : '' }}>
            <span>Show out-of-stock products</span>
        </label>
    </div>
</div>

<div style="display:flex;justify-content:flex-end">
    <button type="submit" class="btn primary">Save shop settings</button>
</div>
</form>
@endsection

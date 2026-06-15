@extends('admin.layouts.admin')
@section('title', 'Homepage Settings')
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Homepage</h2>
        <div class="sub">Announcement bar, hero section and section visibility</div>
    </div>
    <div class="actions">
        <a href="{{ url('/') }}" target="_blank" class="btn sm secondary">
            <span class="ico" data-ico="external" style="width:15px;height:15px"></span> Preview
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.storefront.homepage.update') }}" class="col-gap" style="--gap:18px">
@csrf

{{-- Announcement Bar --}}
<div class="card pad">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-info"><span class="ico" data-ico="bell" style="width:18px;height:18px"></span></span>
        <div class="ct"><h3>Announcement Bar</h3><div class="sub">Thin banner shown at the very top of every page</div></div>
    </div>
    <div style="display:grid;gap:16px">
        <label class="toggle-row">
            <input type="hidden" name="settings[storefront.announce_bar_enabled]" value="0">
            <input type="checkbox" role="switch" name="settings[storefront.announce_bar_enabled]" value="1"
                {{ $settings->get('storefront.announce_bar_enabled')?->value ? 'checked' : '' }}>
            <span>Show announcement bar</span>
        </label>
        <div class="field">
            <span class="lbl">Announcement text</span>
            <input class="input" type="text" name="settings[storefront.announce_bar_text]"
                value="{{ $settings->get('storefront.announce_bar_text')?->value ?? '' }}"
                placeholder="Free delivery on orders over ৳500">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="field">
                <span class="lbl">Background colour</span>
                <div style="display:flex;align-items:center;gap:10px;margin-top:4px">
                    <input type="color" name="settings[storefront.announce_bar_bg]"
                        value="{{ $settings->get('storefront.announce_bar_bg')?->value ?? '#1F3A8A' }}"
                        style="width:42px;height:38px;border:1px solid var(--border);border-radius:8px;cursor:pointer;padding:2px">
                    <span class="hint" style="margin:0">Background for the bar</span>
                </div>
            </div>
            <div class="field">
                <span class="lbl">Link (optional)</span>
                <input class="input" type="text" name="settings[storefront.announce_bar_link]"
                    value="{{ $settings->get('storefront.announce_bar_link')?->value ?? '' }}"
                    placeholder="/shop">
            </div>
        </div>
    </div>
</div>

{{-- Hero --}}
<div class="card pad">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-accent"><span class="ico" data-ico="image" style="width:18px;height:18px"></span></span>
        <div class="ct"><h3>Hero Section</h3><div class="sub">Full-width banner at the top of the homepage</div></div>
    </div>
    <div style="display:grid;gap:16px">
        <div class="field">
            <span class="lbl">Heading</span>
            <input class="input" type="text" name="settings[general.hero_title]"
                value="{{ $settings->get('general.hero_title')?->value ?? '' }}"
                placeholder="Quiet luxury, everyday wear">
        </div>
        <div class="field">
            <span class="lbl">Subheading</span>
            <input class="input" type="text" name="settings[general.hero_subtitle]"
                value="{{ $settings->get('general.hero_subtitle')?->value ?? '' }}"
                placeholder="Discover curated collections for every occasion">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="field">
                <span class="lbl">CTA button label</span>
                <input class="input" type="text" name="settings[storefront.hero_cta_label]"
                    value="{{ $settings->get('storefront.hero_cta_label')?->value ?? '' }}"
                    placeholder="Shop the collection">
            </div>
            <div class="field">
                <span class="lbl">CTA link</span>
                <input class="input" type="text" name="settings[storefront.hero_cta_link]"
                    value="{{ $settings->get('storefront.hero_cta_link')?->value ?? '' }}"
                    placeholder="/shop">
            </div>
        </div>
        <div class="field">
            <span class="lbl">Background image</span>
            <x-admin.media-picker name="settings[storefront.hero_image_media_id]" accept="image"
                label="Upload hero image"
                :value="$settings->get('storefront.hero_image_media_id')?->value" />
        </div>
    </div>
</div>

{{-- Section Visibility --}}
<div class="card pad">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-success"><span class="ico" data-ico="eye" style="width:18px;height:18px"></span></span>
        <div class="ct"><h3>Section Visibility</h3><div class="sub">Toggle which sections appear on the homepage</div></div>
    </div>
    <div style="display:grid;gap:12px;margin-bottom:20px">
        @foreach([
            'storefront.show_featured_products' => 'Featured Products section',
            'storefront.show_new_arrivals'      => 'New Arrivals section',
            'storefront.show_categories'        => 'Shop by Categories section',
            'storefront.show_blog'              => 'Latest Blog Posts section',
        ] as $key => $label)
        <label class="toggle-row">
            <input type="hidden" name="settings[{{ $key }}]" value="0">
            <input type="checkbox" role="switch" name="settings[{{ $key }}]" value="1"
                {{ ($settings->get($key)?->value ?? '1') ? 'checked' : '' }}>
            <span>{{ $label }}</span>
        </label>
        @endforeach
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;padding-top:16px;border-top:1px solid var(--border)">
        <div class="field">
            <span class="lbl">Featured products to show</span>
            <input class="input" type="number" name="settings[storefront.featured_count]" min="1" max="24"
                value="{{ $settings->get('storefront.featured_count')?->value ?? '8' }}">
        </div>
        <div class="field">
            <span class="lbl">New arrivals to show</span>
            <input class="input" type="number" name="settings[storefront.arrivals_count]" min="1" max="24"
                value="{{ $settings->get('storefront.arrivals_count')?->value ?? '8' }}">
        </div>
    </div>
</div>

<div style="display:flex;justify-content:flex-end">
    <button type="submit" class="btn primary">Save homepage settings</button>
</div>
</form>
@endsection

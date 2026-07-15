@extends('admin.layouts.admin')
@section('title', 'Homepage Settings')

@push('styles')
<style>
.sortable-ghost { opacity: 0.4; background: color-mix(in srgb, var(--info) 8%, transparent) !important; }
</style>
@endpush

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
@php
    $sectionMeta = [
        'featured_products' => ['storefront.show_featured_products', 'Featured Products section'],
        'categories'        => ['storefront.show_categories', 'Shop by Categories section'],
        'sale_banner'       => ['storefront.show_sale_banner', 'Sale Banner section'],
        'new_arrivals'      => ['storefront.show_new_arrivals', 'New Arrivals section'],
        'brands'            => ['storefront.show_brands', 'Brand Strip section'],
        'blog'              => ['storefront.show_blog', 'Latest Blog Posts section'],
        'track_order'       => ['storefront.show_track_order', 'Track Order section'],
    ];
    $defaultOrder = array_keys($sectionMeta);
    $storedOrder = json_decode($settings->get('storefront.section_order')?->value ?? '[]', true) ?: [];
    $storedOrder = array_values(array_intersect($storedOrder, $defaultOrder));
    $sectionOrder = array_values(array_unique(array_merge($storedOrder, $defaultOrder)));
@endphp
<div class="card pad">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-success"><span class="ico" data-ico="eye" style="width:18px;height:18px"></span></span>
        <div class="ct"><h3>Section Visibility & Order</h3><div class="sub">Toggle and drag to reorder which sections appear on the homepage</div></div>
    </div>
    <input type="hidden" id="section-order-input" name="settings[storefront.section_order]" value="{{ json_encode($sectionOrder) }}">
    <div id="section-order-list" style="display:grid;gap:8px;margin-bottom:20px">
        @foreach($sectionOrder as $sectionKey)
        @php [$key, $label] = $sectionMeta[$sectionKey]; @endphp
        <div class="toggle-row" data-section="{{ $sectionKey }}" style="display:flex;align-items:center;gap:10px;padding:8px 10px;border:1px solid var(--border);border-radius:8px;background:var(--surface)">
            <span class="drag-handle" aria-hidden="true" style="color:var(--muted);letter-spacing:2px;user-select:none;cursor:grab;padding:2px 4px">⠿⠿</span>
            <input type="hidden" name="settings[{{ $key }}]" value="0">
            <input type="checkbox" role="switch" name="settings[{{ $key }}]" value="1"
                {{ ($settings->get($key)?->value ?? '1') ? 'checked' : '' }}>
            <span>{{ $label }}</span>
        </div>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('section-order-list');
    const input = document.getElementById('section-order-input');
    if (!list || !input) return;

    new Sortable(list, {
        animation: 150,
        handle: '.drag-handle',
        forceFallback: true,
        ghostClass: 'sortable-ghost',
        onEnd: () => {
            const order = [...list.querySelectorAll('[data-section]')].map(el => el.dataset.section);
            input.value = JSON.stringify(order);
        },
    });

    list.closest('form').addEventListener('submit', () => {
        const order = [...list.querySelectorAll('[data-section]')].map(el => el.dataset.section);
        input.value = JSON.stringify(order);
    });
});
</script>
@endpush
@endsection

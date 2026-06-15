@extends('admin.layouts.admin')
@section('title', 'Footer Settings')
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Footer</h2>
        <div class="sub">Copyright, social links, newsletter strip and navigation columns</div>
    </div>
</div>

<form method="POST" action="{{ route('admin.storefront.footer.update') }}" class="col-gap" style="--gap:18px">
@csrf

{{-- General --}}
<div class="card pad">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-info"><span class="ico" data-ico="doc" style="width:18px;height:18px"></span></span>
        <div class="ct"><h3>General</h3><div class="sub">Copyright line shown at the bottom of the footer</div></div>
    </div>
    <div class="field">
        <span class="lbl">Copyright text</span>
        <input class="input" type="text" name="settings[storefront.copyright]"
            value="{{ $settings->get('storefront.copyright')?->value ?? '' }}"
            placeholder="© {year} Your Store · All rights reserved.">
        <p class="hint">Use <code style="background:var(--surface-2);padding:1px 5px;border-radius:4px;font-size:11px">{year}</code> for the current year.</p>
    </div>
</div>

{{-- Newsletter --}}
<div class="card pad">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-accent"><span class="ico" data-ico="mail" style="width:18px;height:18px"></span></span>
        <div class="ct"><h3>Newsletter Strip</h3><div class="sub">Email subscription section above the footer</div></div>
    </div>
    <div style="display:grid;gap:16px">
        <label class="toggle-row">
            <input type="hidden" name="settings[storefront.newsletter_enabled]" value="0">
            <input type="checkbox" role="switch" name="settings[storefront.newsletter_enabled]" value="1"
                {{ ($settings->get('storefront.newsletter_enabled')?->value ?? '1') ? 'checked' : '' }}>
            <span>Show newsletter subscription strip</span>
        </label>
        <div class="field">
            <span class="lbl">Heading</span>
            <input class="input" type="text" name="settings[storefront.newsletter_heading]"
                value="{{ $settings->get('storefront.newsletter_heading')?->value ?? '' }}"
                placeholder="Subscribe to our newsletter">
        </div>
    </div>
</div>

{{-- Social Links --}}
<div class="card pad">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-info"><span class="ico" data-ico="globe" style="width:18px;height:18px"></span></span>
        <div class="ct"><h3>Social Links</h3><div class="sub">Leave blank to hide a platform</div></div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
        @foreach([
            'storefront.facebook_url'  => ['Facebook',  'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z'],
            'storefront.instagram_url' => ['Instagram', 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01M7.5 2h9A5.5 5.5 0 0122 7.5v9A5.5 5.5 0 0116.5 22h-9A5.5 5.5 0 012 16.5v-9A5.5 5.5 0 017.5 2z'],
            'storefront.youtube_url'   => ['YouTube',   'M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.4a2.78 2.78 0 001.95-1.95A29 29 0 0023 12a29 29 0 00-.46-5.58zM9.75 15.02V8.98L15.5 12l-5.75 3.02z'],
            'storefront.tiktok_url'    => ['TikTok',    'M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.75a4.85 4.85 0 01-1.01-.06z'],
            'storefront.twitter_url'   => ['Twitter/X', 'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z'],
            'storefront.linkedin_url'  => ['LinkedIn',  'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z M4 6a2 2 0 100-4 2 2 0 000 4z'],
        ] as $key => [$platform, $iconPath])
        <div class="field">
            <span class="lbl" style="display:flex;align-items:center;gap:6px">
                <svg style="width:13px;height:13px;flex-shrink:0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/>
                </svg>
                {{ $platform }}
            </span>
            <input class="input" type="url" name="settings[{{ $key }}]"
                value="{{ $settings->get($key)?->value ?? '' }}"
                placeholder="https://{{ strtolower(str_replace('/', '', $platform)) }}.com/yourpage">
        </div>
        @endforeach
    </div>
</div>

{{-- Footer Columns --}}
@foreach([
    ['col1', 'Footer Column 1', 'col1Links', 'Help'],
    ['col2', 'Footer Column 2', 'col2Links', 'Company'],
] as [$col, $heading, $var, $placeholder])
@php $links = $$var; @endphp
<div class="card pad" x-data="{
    links: {{ json_encode(count($links) ? $links : [['label'=>'','url'=>'']]) }},
    add() { this.links.push({ label: '', url: '' }) },
    remove(i) { this.links.splice(i, 1) }
}">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-warning"><span class="ico" data-ico="list" style="width:18px;height:18px"></span></span>
        <div class="ct"><h3>{{ $heading }}</h3><div class="sub">Column of links in the footer grid</div></div>
    </div>
    <div style="display:grid;gap:16px">
        <div class="field">
            <span class="lbl">Column heading</span>
            <input class="input" type="text" name="settings[storefront.footer_{{ $col }}_title]"
                value="{{ $settings->get('storefront.footer_' . $col . '_title')?->value ?? '' }}"
                placeholder="{{ $placeholder }}">
        </div>
        <div>
            <span class="lbl" style="display:block;margin-bottom:8px">Links</span>
            <div style="display:flex;flex-direction:column;gap:8px">
                <template x-for="(link, i) in links" :key="i">
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="text" x-model="link.label" :name="`footer_{{ $col }}_links[${i}][label]`"
                            placeholder="Label" class="input" style="flex:1">
                        <input type="text" x-model="link.url" :name="`footer_{{ $col }}_links[${i}][url]`"
                            placeholder="/page/about" class="input" style="flex:1">
                        <button type="button" @click="remove(i)"
                            style="flex-shrink:0;background:none;border:none;cursor:pointer;color:var(--ink-3);line-height:1;padding:4px" title="Remove">
                            <span class="ico" data-ico="x" style="width:14px;height:14px"></span>
                        </button>
                    </div>
                </template>
            </div>
            <button type="button" @click="add()" class="btn sm secondary" style="margin-top:10px">
                <span class="ico" data-ico="plus" style="width:14px;height:14px"></span> Add link
            </button>
        </div>
    </div>
</div>
@endforeach

<div style="display:flex;justify-content:flex-end">
    <button type="submit" class="btn primary">Save footer settings</button>
</div>
</form>
@endsection

@extends('admin.layouts.admin')

@php
$tabLabels = [
    'general'       => 'General',
    'meta'          => 'SEO / Meta',
    'social'        => 'Social',
    'storefront'    => 'Storefront',
    'payment'       => 'Payment',
    'shipping'      => 'Shipping',
    'cart'          => 'Cart',
    'legal'         => 'Legal',
    'tracking'      => 'Tracking',
    'feeds'         => 'Product Feeds',
    'currencies'    => 'Currencies',
    'accounting'    => 'Accounting',
    'storage'       => 'Storage',
    'notifications' => 'Notifications',
];
$currentTabLabel = $tabLabels[$tab] ?? ucfirst($tab);
$sidebarTabs = [
    'general'       => ['icon' => 'gear',     'label' => 'General'],
    'meta'          => ['icon' => 'search',   'label' => 'SEO / Meta'],
    'social'        => ['icon' => 'globe',    'label' => 'Social'],
    'storefront'    => ['icon' => 'store',    'label' => 'Storefront'],
    'payment'       => ['icon' => 'card',     'label' => 'Payment'],
    'shipping'      => ['icon' => 'truck',    'label' => 'Shipping'],
    'cart'          => ['icon' => 'cart',     'label' => 'Cart'],
    'legal'         => ['icon' => 'doc',      'label' => 'Legal'],
    'tracking'      => ['icon' => 'bolt',     'label' => 'Tracking'],
    'feeds'         => ['icon' => 'bookmark', 'label' => 'Product Feeds'],
    'currencies'    => ['icon' => 'dollar',   'label' => 'Currencies'],
    'accounting'    => ['icon' => 'receipt',  'label' => 'Accounting'],
    'storage'       => ['icon' => 'layers',   'label' => 'Storage'],
    'notifications' => ['icon' => 'bell',     'label' => 'Notifications'],
];
@endphp

@section('title', $currentTabLabel . ' — Settings')

@section('content')
<style>
.set-grid{display:grid;grid-template-columns:220px 1fr;gap:22px;align-items:start}
.set-tab{display:flex;align-items:center;gap:11px;width:100%;padding:11px 13px;border-radius:10px;border:none;background:transparent;color:var(--text-muted);font-weight:700;font-size:13.5px;text-align:left;margin-bottom:2px;text-decoration:none}
.set-tab.active{background:var(--accent-soft);color:var(--accent)}
.set-tab:hover:not(.active){background:var(--surface-2);color:var(--text)}
.set-row{display:flex;align-items:center;gap:16px;padding:16px 0;border-bottom:1px solid var(--border)}
.set-row:last-child{border-bottom:none}
.check-card{border:1px solid var(--border);border-radius:10px;padding:13px 15px;cursor:pointer;display:flex;align-items:flex-start;gap:12px;transition:background .12s}
.check-card:hover{background:var(--surface-2)}
@media(max-width:900px){.set-grid{grid-template-columns:1fr}}
</style>

<div class="page-head">
    <div>
        <h2 class="display">Settings</h2>
        <div class="sub">Manage your store configuration &amp; preferences</div>
    </div>
</div>

<div class="set-grid">

    {{-- Sidebar --}}
    <div class="card" style="padding:8px;position:sticky;top:86px">
        @foreach($sidebarTabs as $tabKey => $tabInfo)
        <a href="{{ route('admin.settings.index', ['tab' => $tabKey]) }}"
           class="set-tab {{ $tab === $tabKey ? 'active' : '' }}">
            <span class="ico" data-ico="{{ $tabInfo['icon'] }}" style="width:17px;height:17px"></span>
            {{ $tabInfo['label'] }}
        </a>
        @endforeach
    </div>

    {{-- Panel --}}
    <div>

{{-- ═══════════════════ GENERAL ═══════════════════ --}}
@if($tab === 'general')
<form method="POST" action="{{ route('admin.settings.update', 'general') }}">
    @csrf
    <div class="col-gap">

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-accent"><span class="ico" data-ico="store" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Store Details</h3><div class="sub">Basic information about your business</div></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="field">
                    <span class="lbl">Site Name</span>
                    <input class="input" type="text" name="settings[general.site_name]"
                        value="{{ old('general.site_name', $settings->get('general.site_name')?->value ?? '') }}">
                </div>
                <div class="field">
                    <span class="lbl">Admin Email</span>
                    <input class="input" type="email" name="settings[general.admin_email]"
                        value="{{ old('general.admin_email', $settings->get('general.admin_email')?->value ?? '') }}">
                </div>
                <div style="grid-column:1/-1;display:grid;grid-template-columns:1fr 1fr;gap:16px;padding-bottom:16px;border-bottom:1px solid var(--border)">
                    <div class="field">
                        <span class="lbl">Site Logo</span>
                        <x-admin.media-picker name="settings[general.logo_media_id]" accept="image" label="Upload Logo"
                            :value="old('general.logo_media_id', $settings->get('general.logo_media_id')?->value)" />
                        <p class="hint">Appears in the header and emails.</p>
                    </div>
                    <div class="field">
                        <span class="lbl">Favicon</span>
                        <x-admin.media-picker name="settings[general.favicon_media_id]" accept="image" label="Upload Favicon"
                            :value="old('general.favicon_media_id', $settings->get('general.favicon_media_id')?->value)" />
                        <p class="hint">Recommended: 32×32 or 64×64 PNG/ICO.</p>
                    </div>
                </div>
                <div class="field">
                    <span class="lbl">Tagline</span>
                    <input class="input" type="text" name="settings[general.site_tagline]"
                        value="{{ old('general.site_tagline', $settings->get('general.site_tagline')?->value ?? '') }}">
                </div>
                <div class="field">
                    <span class="lbl">Support Phone</span>
                    <input class="input" type="text" name="settings[general.phone]"
                        value="{{ old('general.phone', $settings->get('general.phone')?->value ?? '') }}">
                </div>
                <div class="field">
                    <span class="lbl">Timezone</span>
                    @php $selectedTz = old('general.timezone', $settings->get('general.timezone')?->value ?? 'Asia/Dhaka'); @endphp
                    <select class="input" name="settings[general.timezone]">
                        @foreach(\DateTimeZone::listIdentifiers() as $tz)
                        <option value="{{ $tz }}" {{ $selectedTz === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <span class="lbl">Currency Code</span>
                    @php $selectedCurrency = old('general.currency', $settings->get('general.currency')?->value ?? 'BDT'); @endphp
                    <select class="input" name="settings[general.currency]" id="currency-select">
                        @foreach(\App\Support\Currencies::all() as $code => $info)
                        <option value="{{ $code }}" data-symbol="{{ $info['symbol'] }}"
                            {{ $selectedCurrency === $code ? 'selected' : '' }}>
                            {{ $code }} — {{ $info['name'] }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <span class="lbl">Currency Symbol</span>
                    <input class="input" type="text" name="settings[general.currency_symbol]" id="currency-symbol"
                        value="{{ old('general.currency_symbol', $settings->get('general.currency_symbol')?->value ?? '৳') }}">
                    <p class="hint">Auto-filled when you change the currency code.</p>
                </div>
                <div class="field" style="grid-column:1/-1">
                    <span class="lbl">Business Address</span>
                    <textarea class="input" name="settings[general.address]" rows="2" style="resize:vertical">{{ old('general.address', $settings->get('general.address')?->value ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-info"><span class="ico" data-ico="sliders" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Access &amp; Features</h3><div class="sub">Login methods and module toggles</div></div>
            </div>
            <div class="set-row" x-data="{on: {{ old('general.maintenance_mode', $settings->get('general.maintenance_mode')?->value) ? 'true' : 'false' }}}">
                <div class="grow">
                    <div style="font-weight:700;font-size:14px">Maintenance Mode</div>
                    <div class="faint" style="font-size:12.5px">Visitors see a maintenance page; admins can still log in</div>
                </div>
                <span :class="on ? 'toggle on' : 'toggle'" @click="on=!on"><span class="knob"></span></span>
                <input type="hidden" name="settings[general.maintenance_mode]" :value="on ? '1' : '0'">
            </div>
            <div class="set-row" x-data="{on: {{ old('general.multilingual_enabled', $settings->get('general.multilingual_enabled')?->value) ? 'true' : 'false' }}}">
                <div class="grow">
                    <div style="font-weight:700;font-size:14px">Multi-Language</div>
                    <div class="faint" style="font-size:12.5px">Shows language switcher when 2+ active languages exist. Manage in <a href="{{ route('admin.languages.index') }}" class="link-btn">Languages</a></div>
                </div>
                <span :class="on ? 'toggle on' : 'toggle'" @click="on=!on"><span class="knob"></span></span>
                <input type="hidden" name="settings[general.multilingual_enabled]" :value="on ? '1' : '0'">
            </div>
            <div style="padding-top:16px">
                <div class="field">
                    <span class="lbl">Customer Login Method</span>
                    @php $loginMethod = old('auth.login_method', $settings->get('auth.login_method')?->value ?? 'email_password'); @endphp
                    <select class="input" name="settings[auth.login_method]">
                        <option value="email_password" {{ $loginMethod === 'email_password' ? 'selected' : '' }}>Email &amp; Password only</option>
                        <option value="phone_otp"      {{ $loginMethod === 'phone_otp'      ? 'selected' : '' }}>Phone OTP only</option>
                        <option value="both"           {{ $loginMethod === 'both'           ? 'selected' : '' }}>Both (user can choose)</option>
                    </select>
                    <p class="hint">Phone OTP requires an active default SMS gateway in <a href="{{ route('admin.integrations.index') }}" class="link-btn">Integrations</a>.</p>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end">
            <button class="btn btn-primary" type="submit">
                <span class="ico" data-ico="check" style="width:17px;height:17px"></span>Save General Settings
            </button>
        </div>
    </div>
</form>
@endif

{{-- ═══════════════════ SEO / META ═══════════════════ --}}
@if($tab === 'meta')
<form method="POST" action="{{ route('admin.settings.update', 'meta') }}">
    @csrf
    <div class="col-gap">
        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-info"><span class="ico" data-ico="search" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>SEO / Meta Defaults</h3><div class="sub">Default values for all pages that don't have specific meta tags</div></div>
            </div>
            <div class="col-gap" style="--gap:14px">
                <div class="field">
                    <span class="lbl">Default Title Template</span>
                    <input class="input" type="text" name="settings[meta.default_title_template]"
                        value="{{ old('meta.default_title_template', $settings->get('meta.default_title_template')?->value ?? '') }}">
                    <p class="hint">Use <code>&#123;&#123;page_title&#125;&#125;</code> and <code>&#123;&#123;site_name&#125;&#125;</code> as placeholders.</p>
                </div>
                <div class="field">
                    <span class="lbl">Default Meta Description</span>
                    <textarea class="input" name="settings[meta.default_description]" rows="3" style="resize:vertical">{{ old('meta.default_description', $settings->get('meta.default_description')?->value ?? '') }}</textarea>
                </div>
                <div class="field">
                    <span class="lbl">Default Keywords</span>
                    <input class="input" type="text" name="settings[meta.default_keywords]"
                        value="{{ old('meta.default_keywords', $settings->get('meta.default_keywords')?->value ?? '') }}">
                </div>
                <div class="field">
                    <span class="lbl">Default Robots</span>
                    <select class="input" name="settings[seo.robots_default]">
                        @foreach(['index,follow', 'noindex,nofollow', 'noindex,follow', 'index,nofollow'] as $opt)
                        <option value="{{ $opt }}" {{ ($settings->get('seo.robots_default')?->value ?? 'index,follow') === $opt ? 'selected' : '' }}>
                            {{ $opt }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end">
            <button class="btn btn-primary" type="submit">
                <span class="ico" data-ico="check" style="width:17px;height:17px"></span>Save SEO Settings
            </button>
        </div>
    </div>
</form>
@endif

{{-- ═══════════════════ SOCIAL ═══════════════════ --}}
@if($tab === 'social')
<form method="POST" action="{{ route('admin.settings.update', 'social') }}">
    @csrf
    <div class="col-gap">
        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-violet"><span class="ico" data-ico="share" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Social / Open Graph</h3><div class="sub">Controls how your pages appear when shared on social networks</div></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="field">
                    <span class="lbl">OG Site Name</span>
                    <input class="input" type="text" name="settings[og.site_name]"
                        value="{{ old('og.site_name', $settings->get('og.site_name')?->value ?? '') }}">
                </div>
                <div class="field">
                    <span class="lbl">Default OG Image URL</span>
                    <input class="input" type="text" name="settings[og.default_image]"
                        value="{{ old('og.default_image', $settings->get('og.default_image')?->value ?? '') }}">
                </div>
                <div class="field">
                    <span class="lbl">Twitter @site</span>
                    <input class="input" type="text" name="settings[twitter.site]"
                        value="{{ old('twitter.site', $settings->get('twitter.site')?->value ?? '') }}">
                </div>
                <div class="field">
                    <span class="lbl">Twitter @creator</span>
                    <input class="input" type="text" name="settings[twitter.creator]"
                        value="{{ old('twitter.creator', $settings->get('twitter.creator')?->value ?? '') }}">
                </div>
                <div class="field" style="grid-column:1/-1">
                    <span class="lbl">Default Twitter Card</span>
                    <select class="input" name="settings[twitter.default_card]">
                        @foreach(['summary_large_image', 'summary', 'app', 'player'] as $opt)
                        <option value="{{ $opt }}" {{ ($settings->get('twitter.default_card')?->value ?? 'summary_large_image') === $opt ? 'selected' : '' }}>
                            {{ $opt }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end">
            <button class="btn btn-primary" type="submit">
                <span class="ico" data-ico="check" style="width:17px;height:17px"></span>Save Social Settings
            </button>
        </div>
    </div>
</form>
@endif

{{-- ═══════════════════ STOREFRONT ═══════════════════ --}}
@if($tab === 'storefront')
<form method="POST" action="{{ route('admin.settings.update', 'storefront') }}">
    @csrf
    <div class="col-gap">

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-accent"><span class="ico" data-ico="bolt" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Announcement Bar</h3><div class="sub">Sticky banner shown at the top of the storefront</div></div>
            </div>
            <div class="set-row" x-data="{on: {{ old('storefront.announce_bar_enabled', $settings->get('storefront.announce_bar_enabled')?->value) ? 'true' : 'false' }}}">
                <div class="grow">
                    <div style="font-weight:700;font-size:14px">Show announcement bar</div>
                </div>
                <span :class="on ? 'toggle on' : 'toggle'" @click="on=!on"><span class="knob"></span></span>
                <input type="hidden" name="settings[storefront.announce_bar_enabled]" :value="on ? '1' : '0'">
            </div>
            <div style="padding-top:16px;display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="field" style="grid-column:1/-1">
                    <span class="lbl">Announcement Text</span>
                    <input class="input" type="text" name="settings[storefront.announce_bar_text]"
                        value="{{ old('storefront.announce_bar_text', $settings->get('storefront.announce_bar_text')?->value ?? '') }}"
                        placeholder="e.g. Free delivery on orders over ৳500">
                </div>
                <div class="field">
                    <span class="lbl">Background Color</span>
                    <div style="display:flex;align-items:center;gap:10px">
                        <input type="color" name="settings[storefront.announce_bar_bg]"
                            value="{{ old('storefront.announce_bar_bg', $settings->get('storefront.announce_bar_bg')?->value ?? '#1F3A8A') }}"
                            style="width:40px;height:38px;border:1px solid var(--border);border-radius:8px;cursor:pointer;padding:2px">
                        <input class="input" type="text" name="settings[storefront.announce_bar_bg_text]"
                            value="{{ old('storefront.announce_bar_bg', $settings->get('storefront.announce_bar_bg')?->value ?? '#1F3A8A') }}"
                            placeholder="#1F3A8A" style="width:120px">
                    </div>
                </div>
                <div class="field">
                    <span class="lbl">Link (optional)</span>
                    <input class="input" type="text" name="settings[storefront.announce_bar_link]"
                        value="{{ old('storefront.announce_bar_link', $settings->get('storefront.announce_bar_link')?->value ?? '') }}"
                        placeholder="/page/offers">
                    <p class="hint">Make the bar clickable. Leave blank to disable.</p>
                </div>
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-teal"><span class="ico" data-ico="image" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Homepage Hero Banner</h3><div class="sub">The main banner shown at the top of the home page</div></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="field">
                    <span class="lbl">Heading</span>
                    <input class="input" type="text" name="settings[storefront.hero_heading]"
                        value="{{ old('storefront.hero_heading', $settings->get('storefront.hero_heading')?->value ?? '') }}"
                        placeholder="e.g. Shop the Latest Trends">
                </div>
                <div class="field">
                    <span class="lbl">Sub-heading</span>
                    <input class="input" type="text" name="settings[storefront.hero_subheading]"
                        value="{{ old('storefront.hero_subheading', $settings->get('storefront.hero_subheading')?->value ?? '') }}"
                        placeholder="e.g. Free delivery on orders over ৳500">
                </div>
                <div class="field">
                    <span class="lbl">Button Label</span>
                    <input class="input" type="text" name="settings[storefront.hero_cta_label]"
                        value="{{ old('storefront.hero_cta_label', $settings->get('storefront.hero_cta_label')?->value ?? 'Shop Now') }}">
                </div>
                <div class="field">
                    <span class="lbl">Button Link</span>
                    <input class="input" type="text" name="settings[storefront.hero_cta_link]"
                        value="{{ old('storefront.hero_cta_link', $settings->get('storefront.hero_cta_link')?->value ?? '/products') }}">
                </div>
                <div class="field" style="grid-column:1/-1">
                    <span class="lbl">Hero Image</span>
                    <x-admin.media-picker name="settings[storefront.hero_image_media_id]" accept="image" label="Upload Banner Image"
                        :value="old('storefront.hero_image_media_id', $settings->get('storefront.hero_image_media_id')?->value)" />
                    <p class="hint">Recommended: 1440×600px.</p>
                </div>
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-pop"><span class="ico" data-ico="mail" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Newsletter</h3><div class="sub">Subscribe banner shown above the footer</div></div>
            </div>
            <div class="set-row" x-data="{on: {{ (($settings->get('storefront.newsletter_enabled')?->value ?? '1') === '1') ? 'true' : 'false' }}}">
                <div class="grow">
                    <div style="font-weight:700;font-size:14px">Show newsletter subscribe banner</div>
                </div>
                <span :class="on ? 'toggle on' : 'toggle'" @click="on=!on"><span class="knob"></span></span>
                <input type="hidden" name="settings[storefront.newsletter_enabled]" :value="on ? '1' : '0'">
            </div>
            <div style="padding-top:16px">
                <div class="field">
                    <span class="lbl">Newsletter Heading</span>
                    <input class="input" type="text" name="settings[storefront.newsletter_heading]"
                        value="{{ old('storefront.newsletter_heading', $settings->get('storefront.newsletter_heading')?->value ?? '') }}"
                        placeholder="Subscribe to our newsletter">
                </div>
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-info"><span class="ico" data-ico="store" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Footer</h3><div class="sub">Copyright text and social links shown in the footer</div></div>
            </div>
            <div class="col-gap" style="--gap:14px">
                <div class="field">
                    <span class="lbl">Copyright Text</span>
                    <input class="input" type="text" name="settings[storefront.copyright]"
                        value="{{ old('storefront.copyright', $settings->get('storefront.copyright')?->value ?? '') }}"
                        placeholder="e.g. © {year} My Store · All rights reserved.">
                    <p class="hint">Use <code>{year}</code> to auto-insert the current year.</p>
                </div>
                <div class="field">
                    <span class="lbl">Facebook URL</span>
                    <input class="input" type="url" name="settings[storefront.facebook_url]"
                        value="{{ old('storefront.facebook_url', $settings->get('storefront.facebook_url')?->value ?? '') }}"
                        placeholder="https://facebook.com/yourpage">
                </div>
                <div class="field">
                    <span class="lbl">Instagram URL</span>
                    <input class="input" type="url" name="settings[storefront.instagram_url]"
                        value="{{ old('storefront.instagram_url', $settings->get('storefront.instagram_url')?->value ?? '') }}"
                        placeholder="https://instagram.com/yourhandle">
                </div>
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-warning"><span class="ico" data-ico="refresh" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Global Return Policy</h3><div class="sub">Shown on all product pages unless overridden per product</div></div>
            </div>
            <div class="field">
                <span class="lbl">Return Policy</span>
                <textarea class="input" name="settings[storefront.global_return_policy]" rows="4" style="resize:vertical"
                    placeholder="e.g. We accept returns within 7 days of delivery.">{{ old('storefront.global_return_policy', $settings->get('storefront.global_return_policy')?->value ?? '') }}</textarea>
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-success"><span class="ico" data-ico="message" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Product Page Buttons</h3><div class="sub">Show call / WhatsApp buttons so customers can order directly</div></div>
            </div>
            <div class="set-row" x-data="{on: {{ old('storefront.product_whatsapp_enabled', $settings->get('storefront.product_whatsapp_enabled')?->value) ? 'true' : 'false' }}}">
                <div class="grow">
                    <div style="font-weight:700;font-size:14px">Show WhatsApp Order button</div>
                </div>
                <span :class="on ? 'toggle on' : 'toggle'" @click="on=!on"><span class="knob"></span></span>
                <input type="hidden" name="settings[storefront.product_whatsapp_enabled]" :value="on ? '1' : '0'">
            </div>
            <div style="padding:16px 0;border-bottom:1px solid var(--border);display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="field">
                    <span class="lbl">WhatsApp Number</span>
                    <input class="input" type="text" name="settings[storefront.product_whatsapp_number]"
                        value="{{ old('storefront.product_whatsapp_number', $settings->get('storefront.product_whatsapp_number')?->value ?? '') }}"
                        placeholder="8801XXXXXXXXX (with country code, no +)">
                    <p class="hint">Include country code without the + sign.</p>
                </div>
                <div class="field">
                    <span class="lbl">WhatsApp Message Template</span>
                    <input class="input" type="text" name="settings[storefront.product_whatsapp_message]"
                        value="{{ old('storefront.product_whatsapp_message', $settings->get('storefront.product_whatsapp_message')?->value ?? 'Hi, I want to order: {product}') }}"
                        placeholder="Hi, I want to order: {product}">
                    <p class="hint">Use <code>{product}</code> for the product name.</p>
                </div>
            </div>
            <div class="set-row" x-data="{on: {{ old('storefront.product_call_enabled', $settings->get('storefront.product_call_enabled')?->value) ? 'true' : 'false' }}}">
                <div class="grow">
                    <div style="font-weight:700;font-size:14px">Show Call to Order button</div>
                </div>
                <span :class="on ? 'toggle on' : 'toggle'" @click="on=!on"><span class="knob"></span></span>
                <input type="hidden" name="settings[storefront.product_call_enabled]" :value="on ? '1' : '0'">
            </div>
            <div style="padding-top:16px">
                <div class="field">
                    <span class="lbl">Call Number</span>
                    <input class="input" type="text" name="settings[storefront.product_call_number]"
                        value="{{ old('storefront.product_call_number', $settings->get('storefront.product_call_number')?->value ?? '') }}"
                        placeholder="e.g. +8801XXXXXXXXX">
                </div>
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-violet"><span class="ico" data-ico="star" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Pre-order Module</h3><div class="sub">Accept pre-orders on out-of-stock products</div></div>
            </div>
            <div class="set-row" x-data="{on: {{ old('storefront.preorder_enabled', $settings->get('storefront.preorder_enabled')?->value) ? 'true' : 'false' }}}">
                <div class="grow">
                    <div style="font-weight:700;font-size:14px">Enable pre-order feature</div>
                    <div class="faint" style="font-size:12.5px">Products can accept pre-orders even when out of stock. "Add to Cart" becomes "Pre-order Now".</div>
                </div>
                <span :class="on ? 'toggle on' : 'toggle'" @click="on=!on"><span class="knob"></span></span>
                <input type="hidden" name="settings[storefront.preorder_enabled]" :value="on ? '1' : '0'">
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end">
            <button class="btn btn-primary" type="submit">
                <span class="ico" data-ico="check" style="width:17px;height:17px"></span>Save Storefront Settings
            </button>
        </div>
    </div>
</form>
@endif

{{-- ═══════════════════ PAYMENT ═══════════════════ --}}
@if($tab === 'payment')
<form method="POST" action="{{ route('admin.settings.update', 'payment') }}">
    @csrf
    <div class="col-gap">

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-success"><span class="ico" data-ico="card" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Accepted Payment Methods</h3><div class="sub">Enable the methods you accept. These appear in checkout and footer.</div></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                @foreach([
                    'payment.cod_enabled'         => ['label' => 'Cash on Delivery',   'desc' => 'Customer pays when order is delivered'],
                    'payment.bkash_enabled'        => ['label' => 'bKash',              'desc' => 'bKash mobile banking'],
                    'payment.nagad_enabled'        => ['label' => 'Nagad',              'desc' => 'Nagad mobile banking'],
                    'payment.rocket_enabled'       => ['label' => 'Rocket',             'desc' => 'Dutch-Bangla Rocket'],
                    'payment.sslcommerz_enabled'   => ['label' => 'SSLCOMMERZ',         'desc' => 'Cards & internet banking via SSLCOMMERZ'],
                    'payment.stripe_enabled'       => ['label' => 'Stripe',             'desc' => 'International cards via Stripe'],
                ] as $key => $info)
                <label class="check-card">
                    <input type="hidden" name="settings[{{ $key }}]" value="0">
                    <input type="checkbox" name="settings[{{ $key }}]" value="1"
                        {{ old($key, $settings->get($key)?->value) ? 'checked' : '' }}
                        style="margin-top:2px;width:15px;height:15px;flex-shrink:0">
                    <span>
                        <span style="display:block;font-weight:700;font-size:14px">{{ $info['label'] }}</span>
                        <span class="faint" style="display:block;font-size:12px">{{ $info['desc'] }}</span>
                    </span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-teal"><span class="ico" data-ico="wallet" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Mobile Banking Numbers</h3><div class="sub">Merchant numbers shown to customers during checkout</div></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="field">
                    <span class="lbl">bKash Merchant Number</span>
                    <input class="input" type="text" name="settings[payment.bkash_number]"
                        value="{{ old('payment.bkash_number', $settings->get('payment.bkash_number')?->value ?? '') }}"
                        placeholder="01XXXXXXXXX">
                </div>
                <div class="field">
                    <span class="lbl">Nagad Merchant Number</span>
                    <input class="input" type="text" name="settings[payment.nagad_number]"
                        value="{{ old('payment.nagad_number', $settings->get('payment.nagad_number')?->value ?? '') }}"
                        placeholder="01XXXXXXXXX">
                </div>
                <div class="field">
                    <span class="lbl">Rocket Account Number</span>
                    <input class="input" type="text" name="settings[payment.rocket_number]"
                        value="{{ old('payment.rocket_number', $settings->get('payment.rocket_number')?->value ?? '') }}"
                        placeholder="01XXXXXXXXX-X">
                </div>
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-info"><span class="ico" data-ico="lock" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>SSLCOMMERZ / Stripe</h3><div class="sub">API credentials for card payment gateways</div></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="field">
                    <span class="lbl">SSLCOMMERZ Store ID</span>
                    <input class="input" type="text" name="settings[payment.sslcommerz_store_id]"
                        value="{{ old('payment.sslcommerz_store_id', $settings->get('payment.sslcommerz_store_id')?->value ?? '') }}">
                </div>
                <div class="field">
                    <span class="lbl">SSLCOMMERZ Store Password</span>
                    <input class="input" type="password" name="settings[payment.sslcommerz_store_password]"
                        value="{{ old('payment.sslcommerz_store_password', $settings->get('payment.sslcommerz_store_password')?->value ?? '') }}">
                </div>
                <div class="field">
                    <span class="lbl">Stripe Publishable Key</span>
                    <input class="input" type="text" name="settings[payment.stripe_pk]"
                        value="{{ old('payment.stripe_pk', $settings->get('payment.stripe_pk')?->value ?? '') }}"
                        placeholder="pk_live_...">
                </div>
                <div class="field">
                    <span class="lbl">Stripe Secret Key</span>
                    <input class="input" type="password" name="settings[payment.stripe_sk]"
                        value="{{ old('payment.stripe_sk', $settings->get('payment.stripe_sk')?->value ?? '') }}"
                        placeholder="sk_live_...">
                </div>
            </div>
            <div class="set-row" x-data="{on: {{ old('payment.sslcommerz_sandbox', $settings->get('payment.sslcommerz_sandbox')?->value) ? 'true' : 'false' }}}">
                <div class="grow">
                    <div style="font-weight:700;font-size:14px">SSLCOMMERZ Sandbox</div>
                    <div class="faint" style="font-size:12.5px">Use test mode for SSLCOMMERZ (disable in production)</div>
                </div>
                <span :class="on ? 'toggle on' : 'toggle'" @click="on=!on"><span class="knob"></span></span>
                <input type="hidden" name="settings[payment.sslcommerz_sandbox]" :value="on ? '1' : '0'">
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end">
            <button class="btn btn-primary" type="submit">
                <span class="ico" data-ico="check" style="width:17px;height:17px"></span>Save Payment Settings
            </button>
        </div>
    </div>
</form>
@endif

{{-- ═══════════════════ SHIPPING ═══════════════════ --}}
@if($tab === 'shipping')
<form method="POST" action="{{ route('admin.settings.update', 'shipping') }}">
    @csrf
    <div class="col-gap">

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-pop"><span class="ico" data-ico="truck" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Shipping Rates</h3><div class="sub">Flat rate or free shipping above a threshold</div></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="field">
                    <span class="lbl">Flat Rate Charge</span>
                    <div class="input-prefix">
                        <span>৳</span>
                        <input class="input" type="number" name="settings[shipping.flat_rate]" min="0" step="0.01"
                            value="{{ old('shipping.flat_rate', $settings->get('shipping.flat_rate')?->value ?? '0') }}"
                            style="padding-left:36px">
                    </div>
                    <p class="hint">Set to 0 for free shipping on all orders.</p>
                </div>
                <div class="field">
                    <span class="lbl">Free Shipping Threshold</span>
                    <div class="input-prefix">
                        <span>৳</span>
                        <input class="input" type="number" name="settings[shipping.free_above]" min="0" step="0.01"
                            value="{{ old('shipping.free_above', $settings->get('shipping.free_above')?->value ?? '') }}"
                            placeholder="Leave blank to disable"
                            style="padding-left:36px">
                    </div>
                    <p class="hint">Orders above this amount get free shipping.</p>
                </div>
                <div class="field">
                    <span class="lbl">Inside Dhaka Rate</span>
                    <div class="input-prefix">
                        <span>৳</span>
                        <input class="input" type="number" name="settings[shipping.rate_inside_dhaka]" min="0" step="0.01"
                            value="{{ old('shipping.rate_inside_dhaka', $settings->get('shipping.rate_inside_dhaka')?->value ?? '60') }}"
                            style="padding-left:36px">
                    </div>
                </div>
                <div class="field">
                    <span class="lbl">Outside Dhaka Rate</span>
                    <div class="input-prefix">
                        <span>৳</span>
                        <input class="input" type="number" name="settings[shipping.rate_outside_dhaka]" min="0" step="0.01"
                            value="{{ old('shipping.rate_outside_dhaka', $settings->get('shipping.rate_outside_dhaka')?->value ?? '120') }}"
                            style="padding-left:36px">
                    </div>
                </div>
                <div class="field">
                    <span class="lbl">Inside Dhaka ETA</span>
                    <input class="input" type="text" name="settings[shipping.delivery_inside_dhaka]"
                        value="{{ old('shipping.delivery_inside_dhaka', $settings->get('shipping.delivery_inside_dhaka')?->value ?? '1-2 business days') }}"
                        placeholder="e.g. 1-2 business days">
                </div>
                <div class="field">
                    <span class="lbl">Outside Dhaka ETA</span>
                    <input class="input" type="text" name="settings[shipping.delivery_outside_dhaka]"
                        value="{{ old('shipping.delivery_outside_dhaka', $settings->get('shipping.delivery_outside_dhaka')?->value ?? '3-5 business days') }}"
                        placeholder="e.g. 3-5 business days">
                </div>
            </div>
            <div class="set-row" x-data="{on: {{ old('shipping.courier_location_charges', $settings->get('shipping.courier_location_charges')?->value) ? 'true' : 'false' }}}">
                <div class="grow">
                    <div style="font-weight:700;font-size:14px">Location-based courier charges</div>
                    <div class="faint" style="font-size:12.5px">Shows city/zone dropdowns and live courier charge at checkout. Requires active default courier in <a href="{{ route('admin.integrations.index') }}" class="link-btn">Integrations</a>.</div>
                </div>
                <span :class="on ? 'toggle on' : 'toggle'" @click="on=!on"><span class="knob"></span></span>
                <input type="hidden" name="settings[shipping.courier_location_charges]" :value="on ? '1' : '0'">
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-teal"><span class="ico" data-ico="truck" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Courier Providers</h3><div class="sub">Enable the courier partners you use</div></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                @foreach([
                    'shipping.courier_pathao' => ['label' => 'Pathao',      'desc' => 'Pathao courier service'],
                    'shipping.courier_shohoz' => ['label' => 'Shohoz',      'desc' => 'Shohoz delivery'],
                    'shipping.courier_redx'   => ['label' => 'RedX',        'desc' => 'RedX last-mile delivery'],
                    'shipping.courier_custom' => ['label' => 'Own Courier', 'desc' => 'In-house or custom delivery'],
                ] as $key => $info)
                <label class="check-card">
                    <input type="hidden" name="settings[{{ $key }}]" value="0">
                    <input type="checkbox" name="settings[{{ $key }}]" value="1"
                        {{ old($key, $settings->get($key)?->value) ? 'checked' : '' }}
                        style="margin-top:2px;width:15px;height:15px;flex-shrink:0">
                    <span>
                        <span style="display:block;font-weight:700;font-size:14px">{{ $info['label'] }}</span>
                        <span class="faint" style="display:block;font-size:12px">{{ $info['desc'] }}</span>
                    </span>
                </label>
                @endforeach
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end">
            <button class="btn btn-primary" type="submit">
                <span class="ico" data-ico="check" style="width:17px;height:17px"></span>Save Shipping Settings
            </button>
        </div>
    </div>
</form>
@endif

{{-- ═══════════════════ CART ═══════════════════ --}}
@if($tab === 'cart')
<form method="POST" action="{{ route('admin.settings.update', 'cart') }}" x-data="cartGoals()">
    @csrf
    <div class="col-gap">

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-warning"><span class="ico" data-ico="cart" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Free Shipping Threshold</h3><div class="sub">When the cart total reaches this amount, shipping is waived</div></div>
            </div>
            <div class="field" style="max-width:220px">
                <span class="lbl">Free shipping above</span>
                <div class="input-prefix">
                    <span>৳</span>
                    <input class="input" type="number" name="settings[shipping.free_above]" min="0" step="0.01"
                        value="{{ old('shipping.free_above', \App\Models\SiteSetting::get('shipping.free_above', 0)) }}"
                        placeholder="e.g. 1000"
                        style="padding-left:36px">
                </div>
                <p class="hint">Set to 0 to disable.</p>
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-accent"><span class="ico" data-ico="star" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Cart Goals / Progress Milestones</h3><div class="sub">Show a progress bar in the cart drawer: "Add ৳X more to get Y"</div></div>
            </div>

            <div class="col-gap" style="margin-bottom:16px;--gap:10px">
                <template x-for="(goal, i) in goals" :key="i">
                    <div style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:12px;padding:13px 15px;border:1px solid var(--border);border-radius:10px;background:var(--surface-2)">
                        <div class="field" style="min-width:120px">
                            <span class="lbl" style="font-size:12px">Cart Amount (৳)</span>
                            <input class="input" type="number" x-model="goal.amount" min="0" step="1">
                        </div>
                        <div class="field" style="min-width:160px">
                            <span class="lbl" style="font-size:12px">Reward</span>
                            <select class="input" x-model="goal.reward">
                                <option value="free_shipping">Free Shipping</option>
                                <option value="discount_pct">% Discount</option>
                                <option value="discount_fixed">Fixed Discount (৳)</option>
                            </select>
                        </div>
                        <div class="field" style="min-width:90px" x-show="goal.reward !== 'free_shipping'">
                            <span class="lbl" style="font-size:12px">Value</span>
                            <input class="input" type="number" x-model="goal.value" min="0"
                                :placeholder="goal.reward === 'discount_pct' ? 'e.g. 10' : 'e.g. 50'">
                        </div>
                        <div class="field" style="min-width:180px">
                            <span class="lbl" style="font-size:12px">Custom Label <span class="faint">(optional)</span></span>
                            <input class="input" type="text" x-model="goal.label" placeholder="e.g. a free gift">
                        </div>
                        <button type="button" @click="goals.splice(i,1)"
                            style="padding:6px 10px;border-radius:8px;border:1px solid var(--danger);color:var(--danger);background:transparent;cursor:pointer;font-size:13px;margin-bottom:1px">
                            Remove
                        </button>
                    </div>
                </template>
                <button type="button" @click="goals.push({amount:0,reward:'free_shipping',value:'',label:''})"
                    class="btn btn-outline btn-sm">
                    <span class="ico" data-ico="plus" style="width:15px;height:15px"></span>Add Goal
                </button>
            </div>

            <template x-if="goals.length > 0">
                <div style="padding:12px 14px;background:var(--accent-soft);border-radius:10px;font-size:12.5px;color:var(--accent)">
                    <strong>Preview:</strong>
                    <template x-for="(g, i) in goals.slice().sort((a,b)=>a.amount-b.amount)" :key="i">
                        <span x-text="`৳${g.amount} → ${g.reward === 'free_shipping' ? 'free shipping' : (g.label || (g.reward === 'discount_pct' ? g.value+'% off' : '৳'+g.value+' off'))}`"
                              style="margin-left:8px;display:inline-block;background:rgba(255,255,255,.6);padding:2px 8px;border-radius:99px"></span>
                    </template>
                </div>
            </template>

            <input type="hidden" name="settings[cart.goals_json]" :value="JSON.stringify(goals)">
        </div>

        <div style="display:flex;justify-content:flex-end">
            <button class="btn btn-primary" type="submit">
                <span class="ico" data-ico="check" style="width:17px;height:17px"></span>Save Cart Settings
            </button>
        </div>
    </div>
</form>

<script>
function cartGoals() {
    return {
        goals: @json(json_decode(\App\Models\SiteSetting::get('cart.goals', '[]'), true) ?: []),
    };
}
</script>
@endif

{{-- ═══════════════════ LEGAL ═══════════════════ --}}
@if($tab === 'legal')
<form method="POST" action="{{ route('admin.settings.update', 'legal') }}">
    @csrf
    <div class="col-gap">
        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-violet"><span class="ico" data-ico="file" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Legal Page URLs</h3><div class="sub">Links used in checkout and footer for legal documentation</div></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="field">
                    <span class="lbl">Privacy Policy URL</span>
                    <input class="input" type="text" name="settings[legal.privacy_policy_url]"
                        value="{{ old('legal.privacy_policy_url', $settings->get('legal.privacy_policy_url')?->value ?? '/privacy') }}">
                </div>
                <div class="field">
                    <span class="lbl">Terms &amp; Conditions URL</span>
                    <input class="input" type="text" name="settings[legal.terms_url]"
                        value="{{ old('legal.terms_url', $settings->get('legal.terms_url')?->value ?? '/terms') }}">
                </div>
                <div class="field">
                    <span class="lbl">Refund Policy URL</span>
                    <input class="input" type="text" name="settings[legal.refund_policy_url]"
                        value="{{ old('legal.refund_policy_url', $settings->get('legal.refund_policy_url')?->value ?? '/refund-policy') }}">
                </div>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end">
            <button class="btn btn-primary" type="submit">
                <span class="ico" data-ico="check" style="width:17px;height:17px"></span>Save Legal Settings
            </button>
        </div>
    </div>
</form>
@endif

{{-- ═══════════════════ TRACKING ═══════════════════ --}}
@if($tab === 'tracking')
@php
$stepStyle = 'display:inline-flex;align-items:center;justify-content:center;width:1.375rem;height:1.375rem;border-radius:9999px;background:var(--accent);color:#fff;font-size:.7rem;font-weight:700;flex-shrink:0;margin-top:.1rem';
$infoBox   = 'margin-top:.75rem;padding:.75rem 1rem;background:var(--accent-soft);border:1px solid var(--border);border-radius:.625rem;font-size:.8rem;color:var(--accent);line-height:1.6';
@endphp
<form method="POST" action="{{ route('admin.settings.update', 'tracking') }}">
    @csrf
    <div class="col-gap">

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-info"><span class="ico" data-ico="activity" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Google Tag Manager (GTM)</h3><div class="sub">Manage all tracking scripts from one place — no code changes needed</div></div>
            </div>
            <div style="{{ $infoBox }}">
                <strong style="display:block;margin-bottom:6px">How to get your GTM Container ID</strong>
                <ol style="padding-left:4px">
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">1</span><span>Go to <strong>tagmanager.google.com</strong> and sign in.</span></li>
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">2</span><span>Create or open an account. Choose <strong>Web</strong> as the platform.</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">3</span><span>Your Container ID looks like <strong>GTM-XXXXXXXX</strong>. Copy and paste it below.</span></li>
                </ol>
            </div>
            <div class="field" style="max-width:240px;margin-top:16px">
                <span class="lbl">Container ID</span>
                <input class="input" type="text" name="settings[tracking.gtm_id]"
                    value="{{ old('tracking.gtm_id', $settings->get('tracking.gtm_id')?->value ?? '') }}"
                    placeholder="GTM-XXXXXXXX">
                <p class="hint">Leave blank to disable GTM.</p>
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-pop"><span class="ico" data-ico="activity" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Meta (Facebook) Pixel</h3><div class="sub">Track visitors for ad optimisation and retargeting</div></div>
            </div>
            <div style="{{ $infoBox }}">
                <strong style="display:block;margin-bottom:6px">How to get your Pixel ID</strong>
                <ol style="padding-left:4px">
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">1</span><span><strong>business.facebook.com</strong> → Events Manager.</span></li>
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">2</span><span>Select your Pixel (or create one via Connect Data Sources → Web).</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">3</span><span>Copy the <strong>Pixel ID</strong> — 15–16 digit number at the top.</span></li>
                </ol>
            </div>
            <div style="{{ $infoBox }};margin-top:8px">
                <strong style="display:block;margin-bottom:6px">How to get your CAPI Access Token</strong>
                <ol style="padding-left:4px">
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">1</span><span>Events Manager → your Pixel → <strong>Settings</strong> tab.</span></li>
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">2</span><span>Scroll to <strong>Conversions API</strong> → click <strong>Generate Access Token</strong>.</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">3</span><span>Copy the token starting with <strong>EAAx…</strong></span></li>
                </ol>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                <div class="field">
                    <span class="lbl">Pixel ID</span>
                    <input class="input" type="text" name="settings[tracking.meta_pixel_id]"
                        value="{{ old('tracking.meta_pixel_id', $settings->get('tracking.meta_pixel_id')?->value ?? '') }}"
                        placeholder="123456789012345">
                    <p class="hint">Leave blank to disable the Pixel.</p>
                </div>
                <div class="field">
                    <span class="lbl">CAPI Access Token <span class="muted" style="font-weight:400">(recommended)</span></span>
                    <input class="input" type="password" name="settings[tracking.meta_capi_token]"
                        value="{{ old('tracking.meta_capi_token', $settings->get('tracking.meta_capi_token')?->value ?? '') }}"
                        placeholder="EAAx...">
                    <p class="hint">Enables server-side Purchase events. Improves accuracy on iOS.</p>
                </div>
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-accent"><span class="ico" data-ico="activity" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Google Analytics 4 (GA4)</h3><div class="sub">Track traffic, user behaviour, and revenue</div></div>
            </div>
            <div style="{{ $infoBox }}">
                <strong style="display:block;margin-bottom:6px">How to get your Measurement ID</strong>
                <ol style="padding-left:4px">
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">1</span><span><strong>analytics.google.com</strong> → Admin → Data Streams → your web stream.</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">2</span><span>Copy the <strong>Measurement ID</strong> starting with <strong>G-</strong>.</span></li>
                </ol>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                <div class="field">
                    <span class="lbl">Measurement ID</span>
                    <input class="input" type="text" name="settings[tracking.ga4_measurement_id]"
                        value="{{ old('tracking.ga4_measurement_id', $settings->get('tracking.ga4_measurement_id')?->value ?? '') }}"
                        placeholder="G-XXXXXXXXXX">
                </div>
                <div class="field">
                    <span class="lbl">API Secret <span class="muted" style="font-weight:400">(optional)</span></span>
                    <input class="input" type="password" name="settings[tracking.ga4_api_secret]"
                        value="{{ old('tracking.ga4_api_secret', $settings->get('tracking.ga4_api_secret')?->value ?? '') }}"
                        placeholder="your_api_secret">
                    <p class="hint">Enables server-side purchase events.</p>
                </div>
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-violet"><span class="ico" data-ico="activity" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>TikTok Pixel</h3><div class="sub">Track visitor actions and send conversions to TikTok Ads Manager</div></div>
            </div>
            <div style="{{ $infoBox }}">
                <strong style="display:block;margin-bottom:6px">How to get your TikTok Pixel ID</strong>
                <ol style="padding-left:4px">
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">1</span><span><strong>ads.tiktok.com</strong> → Assets → Events → Manage Web Events.</span></li>
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">2</span><span>Set Up Web Events → TikTok Pixel → create or select a pixel.</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">3</span><span>Copy the <strong>Pixel ID</strong> — looks like <strong>C1XXXXXXXXXXXXXXXX</strong>.</span></li>
                </ol>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                <div class="field">
                    <span class="lbl">Pixel ID</span>
                    <input class="input" type="text" name="settings[tracking.tiktok_pixel_id]"
                        value="{{ old('tracking.tiktok_pixel_id', $settings->get('tracking.tiktok_pixel_id')?->value ?? '') }}"
                        placeholder="C1XXXXXXXXXXXXXXXX">
                    <p class="hint">Leave blank to disable TikTok tracking.</p>
                </div>
                <div class="field">
                    <span class="lbl">Events API Access Token <span class="muted" style="font-weight:400">(optional)</span></span>
                    <input class="input" type="password" name="settings[tracking.tiktok_access_token]"
                        value="{{ old('tracking.tiktok_access_token', $settings->get('tracking.tiktok_access_token')?->value ?? '') }}"
                        placeholder="Your access token…">
                    <p class="hint">Enables server-side Purchase events after orders.</p>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end">
            <button class="btn btn-primary" type="submit">
                <span class="ico" data-ico="check" style="width:17px;height:17px"></span>Save Tracking Settings
            </button>
        </div>
    </div>
</form>
@endif

{{-- ═══════════════════ PRODUCT FEEDS ═══════════════════ --}}
@if($tab === 'feeds')
@php
$stepStyle = 'display:inline-flex;align-items:center;justify-content:center;width:1.375rem;height:1.375rem;border-radius:9999px;background:var(--accent);color:#fff;font-size:.7rem;font-weight:700;flex-shrink:0;margin-top:.1rem';
$infoBox   = 'padding:.875rem 1rem;background:var(--accent-soft);border-top:1px solid var(--border);font-size:.8rem;color:var(--accent);line-height:1.6';
@endphp
<form method="POST" action="{{ route('admin.settings.update', 'feeds') }}">
    @csrf
    <div class="col-gap">

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-info"><span class="ico" data-ico="bookmark" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Product Feeds</h3><div class="sub">Auto-generated product catalogue files for Google Shopping and Facebook Shop</div></div>
            </div>
            <p class="muted" style="font-size:13.5px">
                Enable a feed below, then submit its URL to the platform — the store keeps it up-to-date automatically.
            </p>
        </div>

        <div class="card flush">
            <div style="padding:16px 20px">
                <div style="font-weight:700;font-size:15px;margin-bottom:4px">Google Merchant Center (Google Shopping)</div>
                <div class="faint" style="font-size:13px;margin-bottom:14px">Once enabled, Google can fetch your product list and show it in Shopping results, Images, and Search. Free to list.</div>
            </div>
            <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;align-items:flex-start;gap:14px"
                 x-data="{on: {{ old('feeds.google_merchant_enabled', $settings->get('feeds.google_merchant_enabled')?->value) ? 'true' : 'false' }}}">
                <span :class="on ? 'toggle on' : 'toggle'" @click="on=!on"><span class="knob"></span></span>
                <input type="hidden" name="settings[feeds.google_merchant_enabled]" :value="on ? '1' : '0'">
                <div>
                    <div style="font-weight:700;font-size:14px">Enable Google Merchant Center Feed</div>
                    <div class="faint" style="font-size:12.5px;margin-top:3px">
                        Feed URL: <code style="background:var(--surface-2);padding:1px 6px;border-radius:5px">{{ url('feeds/google-merchant.xml') }}</code>
                    </div>
                </div>
            </div>
            <div style="{{ $infoBox }}">
                <strong style="display:block;margin-bottom:8px">How to connect to Google Merchant Center</strong>
                <ol style="padding-left:4px">
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">1</span><span>Enable the feed above and save settings.</span></li>
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">2</span><span>Open <strong>merchants.google.com</strong> → Products → Feeds → click <strong>+</strong>.</span></li>
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">3</span><span>Select country and language → name your feed → choose <strong>Scheduled fetch</strong>.</span></li>
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">4</span><span>Paste the feed URL above → set frequency to <strong>Daily</strong> → Save.</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">5</span><span>Google reviews your products within 1–3 business days.</span></li>
                </ol>
            </div>
        </div>

        <div class="card flush">
            <div style="padding:16px 20px">
                <div style="font-weight:700;font-size:15px;margin-bottom:4px">Facebook &amp; Instagram Shop (Commerce Manager)</div>
                <div class="faint" style="font-size:13px;margin-bottom:14px">Facebook reads your product list and syncs it to your Facebook/Instagram Shop and Dynamic Product Ads.</div>
            </div>
            <div style="padding:14px 20px;border-top:1px solid var(--border);display:flex;align-items:flex-start;gap:14px"
                 x-data="{on: {{ old('feeds.facebook_catalog_enabled', $settings->get('feeds.facebook_catalog_enabled')?->value) ? 'true' : 'false' }}}">
                <span :class="on ? 'toggle on' : 'toggle'" @click="on=!on"><span class="knob"></span></span>
                <input type="hidden" name="settings[feeds.facebook_catalog_enabled]" :value="on ? '1' : '0'">
                <div>
                    <div style="font-weight:700;font-size:14px">Enable Facebook &amp; Instagram Catalog Feed</div>
                    <div class="faint" style="font-size:12.5px;margin-top:3px">
                        Feed URL: <code style="background:var(--surface-2);padding:1px 6px;border-radius:5px">{{ url('feeds/facebook-catalog.json') }}</code>
                    </div>
                </div>
            </div>
            <div style="{{ $infoBox }}">
                <strong style="display:block;margin-bottom:8px">How to connect to Facebook Commerce Manager</strong>
                <ol style="padding-left:4px">
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">1</span><span>Enable the feed above and save settings.</span></li>
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">2</span><span><strong>business.facebook.com</strong> → Commerce Manager → your catalog → Data sources → Add items.</span></li>
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">3</span><span>Choose <strong>Use a data feed</strong> → Set a URL for scheduled uploads.</span></li>
                    <li style="display:flex;gap:.5rem;margin-bottom:4px"><span style="{{ $stepStyle }}">4</span><span>Paste the feed URL → set to <strong>Daily</strong> → Upload.</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">5</span><span>Once approved, connect the catalog to Dynamic Ads for retargeting.</span></li>
                </ol>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end">
            <button class="btn btn-primary" type="submit">
                <span class="ico" data-ico="check" style="width:17px;height:17px"></span>Save Product Feed Settings
            </button>
        </div>
    </div>
</form>
@endif

{{-- ═══════════════════ CURRENCIES ═══════════════════ --}}
@if($tab === 'currencies')
<form method="POST" action="{{ route('admin.settings.update', 'currencies') }}">
    @csrf
    <div class="col-gap">
        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-teal"><span class="ico" data-ico="dollar" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Multi-Currency Settings</h3><div class="sub">Let customers switch between currencies in the storefront</div></div>
            </div>
            <div class="set-row" x-data="{on: {{ \App\Models\SiteSetting::get('currencies.enabled', '0') == '1' ? 'true' : 'false' }}}">
                <div class="grow">
                    <div style="font-weight:700;font-size:14px">Enable Multi-Currency</div>
                    <div class="faint" style="font-size:12.5px">Prices are converted using rates you configure below</div>
                </div>
                <span :class="on ? 'toggle on' : 'toggle'" @click="on=!on"><span class="knob"></span></span>
                <input type="hidden" name="settings[currencies.enabled]" :value="on ? '1' : '0'">
            </div>
            <div class="set-row" x-data="{on: {{ \App\Models\SiteSetting::get('currencies.show_switcher', '1') == '1' ? 'true' : 'false' }}}">
                <div class="grow">
                    <div style="font-weight:700;font-size:14px">Show Currency Switcher</div>
                    <div class="faint" style="font-size:12.5px">Display a currency selector in the storefront header</div>
                </div>
                <span :class="on ? 'toggle on' : 'toggle'" @click="on=!on"><span class="knob"></span></span>
                <input type="hidden" name="settings[currencies.show_switcher]" :value="on ? '1' : '0'">
            </div>
        </div>

        <div class="card pad">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
                <div>
                    <div style="font-weight:700;font-size:15px">Configured Currencies</div>
                    <div class="faint" style="font-size:12.5px">{{ $currencies->count() }} {{ Str::plural('currency', $currencies->count()) }} configured</div>
                </div>
                <a href="{{ route('admin.currencies.index') }}" class="btn btn-outline btn-sm">
                    <span class="ico" data-ico="edit" style="width:15px;height:15px"></span>Manage currencies
                </a>
            </div>
            @if($currencies->isEmpty())
            <div style="text-align:center;padding:40px 0;color:var(--text-faint);border:2px dashed var(--border);border-radius:10px">
                No currencies configured yet. <a href="{{ route('admin.currencies.index') }}" class="link-btn">Add one</a>.
            </div>
            @else
            <div class="table-scroll"><table class="table">
                <thead><tr><th>Currency</th><th>Code</th><th>Rate</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($currencies as $currency)
                <tr>
                    <td>
                        <div class="row" style="gap:10px">
                            <span class="tile sm muted" style="font-weight:700;font-size:15px">{{ $currency->symbol }}</span>
                            <span style="font-weight:700">{{ $currency->name }}</span>
                        </div>
                    </td>
                    <td class="mono">{{ $currency->code }}</td>
                    <td class="tnum">{{ $currency->rate }}</td>
                    <td>
                        @if($currency->is_default)
                        <span class="pill sm info">Default</span>
                        @elseif($currency->is_active)
                        <span class="pill sm success">Active</span>
                        @else
                        <span class="pill sm">Inactive</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table></div>
            @endif
        </div>

        <div style="display:flex;justify-content:flex-end">
            <button class="btn btn-primary" type="submit">
                <span class="ico" data-ico="check" style="width:17px;height:17px"></span>Save Currency Settings
            </button>
        </div>
    </div>
</form>
@endif

{{-- ═══════════════════ STORAGE ═══════════════════ --}}
@if($tab === 'storage')
<form method="POST" action="{{ route('admin.settings.update', 'storage') }}">
    @csrf @method('PUT')
    <div class="col-gap">

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-info"><span class="ico" data-ico="cloud" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Storage Driver</h3><div class="sub">Choose where uploaded media is stored</div></div>
            </div>
            <div class="col-gap" style="--gap:10px">
                @foreach(['public' => 'Local (server disk — default)', 's3' => 'AWS S3', 'r2' => 'Cloudflare R2'] as $val => $label)
                <label style="display:flex;align-items:center;gap:12px;cursor:pointer;padding:12px 14px;border:1px solid var(--border);border-radius:10px">
                    <input type="radio" name="settings[storage.driver]" value="{{ $val }}"
                        {{ ($settings->get('storage.driver')?->value ?? 'public') === $val ? 'checked' : '' }}
                        style="width:15px;height:15px">
                    <span style="font-weight:700;font-size:14px">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-warning"><span class="ico" data-ico="cloud" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>AWS S3</h3><div class="sub">Credentials for Amazon S3 object storage</div></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="field">
                    <span class="lbl">Access Key ID</span>
                    <input class="input" type="text" name="settings[storage.s3_key]"
                        value="{{ old('storage.s3_key', $settings->get('storage.s3_key')?->value ?? '') }}"
                        placeholder="AKIA...">
                </div>
                <div class="field">
                    <span class="lbl">Secret Access Key</span>
                    <input class="input" type="password" name="settings[storage.s3_secret]"
                        value="{{ old('storage.s3_secret', $settings->get('storage.s3_secret')?->value ?? '') }}"
                        placeholder="Leave blank to keep existing">
                </div>
                <div class="field">
                    <span class="lbl">Region</span>
                    <input class="input" type="text" name="settings[storage.s3_region]"
                        value="{{ old('storage.s3_region', $settings->get('storage.s3_region')?->value ?? '') }}"
                        placeholder="us-east-1">
                </div>
                <div class="field">
                    <span class="lbl">Bucket</span>
                    <input class="input" type="text" name="settings[storage.s3_bucket]"
                        value="{{ old('storage.s3_bucket', $settings->get('storage.s3_bucket')?->value ?? '') }}"
                        placeholder="my-bucket">
                </div>
                <div class="field" style="grid-column:1/-1">
                    <span class="lbl">CDN / Public URL <span class="muted" style="font-weight:400">(optional)</span></span>
                    <input class="input" type="url" name="settings[storage.s3_url]"
                        value="{{ old('storage.s3_url', $settings->get('storage.s3_url')?->value ?? '') }}"
                        placeholder="https://cdn.example.com">
                    <p class="hint">All media URLs use this base instead of the S3 URL.</p>
                </div>
            </div>
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-pop"><span class="ico" data-ico="cloud" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Cloudflare R2</h3><div class="sub">R2 uses the S3 API. Generate a token in Cloudflare → R2 → Manage API tokens.</div></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="field">
                    <span class="lbl">Access Key ID</span>
                    <input class="input" type="text" name="settings[storage.r2_key]"
                        value="{{ old('storage.r2_key', $settings->get('storage.r2_key')?->value ?? '') }}"
                        placeholder="R2 Access Key ID">
                </div>
                <div class="field">
                    <span class="lbl">Secret Access Key</span>
                    <input class="input" type="password" name="settings[storage.r2_secret]"
                        value="{{ old('storage.r2_secret', $settings->get('storage.r2_secret')?->value ?? '') }}"
                        placeholder="Leave blank to keep existing">
                </div>
                <div class="field">
                    <span class="lbl">Bucket</span>
                    <input class="input" type="text" name="settings[storage.r2_bucket]"
                        value="{{ old('storage.r2_bucket', $settings->get('storage.r2_bucket')?->value ?? '') }}"
                        placeholder="my-bucket">
                </div>
                <div class="field">
                    <span class="lbl">Endpoint</span>
                    <input class="input" type="url" name="settings[storage.r2_endpoint]"
                        value="{{ old('storage.r2_endpoint', $settings->get('storage.r2_endpoint')?->value ?? '') }}"
                        placeholder="https://<accountid>.r2.cloudflarestorage.com">
                </div>
                <div class="field" style="grid-column:1/-1">
                    <span class="lbl">Public / CDN URL <span class="danger">*</span></span>
                    <input class="input" type="url" name="settings[storage.r2_url]"
                        value="{{ old('storage.r2_url', $settings->get('storage.r2_url')?->value ?? '') }}"
                        placeholder="https://pub-xxx.r2.dev or https://media.yourstore.com">
                    <p class="hint">R2 buckets are not public by default — enable "Public Access" in Cloudflare or point a custom domain.</p>
                </div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end">
            <button class="btn btn-primary" type="submit">
                <span class="ico" data-ico="check" style="width:17px;height:17px"></span>Save Storage Settings
            </button>
        </div>
    </div>
</form>
@endif

{{-- ═══════════════════ NOTIFICATIONS ═══════════════════ --}}
@if($tab === 'notifications')
<form method="POST" action="{{ route('admin.settings.update', 'notifications') }}">
    @csrf
    <div class="col-gap">
        @php
        $smsOnOrder     = $settings->get('notifications.sms_on_order')?->value     ?? '0';
        $smsOnConfirmed = $settings->get('notifications.sms_on_confirmed')?->value ?? '1';
        $smsOnDelivered = $settings->get('notifications.sms_on_delivered')?->value ?? '1';
        @endphp

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-warning"><span class="ico" data-ico="bell" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>SMS Notifications</h3><div class="sub">Requires an active SMS gateway (Admin → Integrations)</div></div>
            </div>
            @foreach([
                ['key' => 'notifications.sms_on_order',     'val' => $smsOnOrder,     'label' => 'Send SMS when order is placed',    'desc' => 'Customer receives an SMS as soon as they complete checkout.'],
                ['key' => 'notifications.sms_on_confirmed', 'val' => $smsOnConfirmed, 'label' => 'Send SMS when order is confirmed', 'desc' => 'Triggered when admin changes status to Confirmed.'],
                ['key' => 'notifications.sms_on_delivered', 'val' => $smsOnDelivered, 'label' => 'Send SMS when order is delivered', 'desc' => 'Triggered when admin changes status to Delivered.'],
            ] as $row)
            <div class="set-row" x-data="{on: {{ $row['val'] === '1' ? 'true' : 'false' }}}">
                <div class="grow">
                    <div style="font-weight:700;font-size:14px">{{ $row['label'] }}</div>
                    <div class="faint" style="font-size:12.5px">{{ $row['desc'] }}</div>
                </div>
                <span :class="on ? 'toggle on' : 'toggle'" @click="on=!on"><span class="knob"></span></span>
                <input type="hidden" name="settings[{{ $row['key'] }}]" :value="on ? '1' : '0'">
            </div>
            @endforeach
        </div>

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-info"><span class="ico" data-ico="mail" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>SMS Message Templates</h3><div class="sub">Customize the message for each event. Placeholders are replaced automatically.</div></div>
            </div>
            @php
            $smsPlaceholders = [
                '{{order_id}}'      => 'Order number',
                '{{amount}}'        => 'Order total',
                '{{customer_name}}' => 'Customer name',
            ];
            $tplPlaced    = $settings->get('notifications.sms_template_placed')?->value    ?? "Thank you for your order at KlixBD!\nYour order #{{order_id}} has been placed successfully.\nTotal: {{amount}} BDT.";
            $tplConfirmed = $settings->get('notifications.sms_template_confirmed')?->value ?? "Your order #{{order_id}} has been confirmed by KlixBD.\nTotal: {{amount}} BDT.\nWe are now preparing your order for delivery. Thank you!";
            $tplDelivered = $settings->get('notifications.sms_template_delivered')?->value ?? "Your order #{{order_id}} has been delivered. Thank you for shopping with us!";
            @endphp
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
                @foreach($smsPlaceholders as $placeholder => $hint)
                <span style="display:inline-flex;align-items:center;gap:4px;background:var(--accent-soft);border:1px solid var(--border);border-radius:6px;padding:2px 8px;font-size:.75rem">
                    <code style="font-family:monospace;color:var(--accent);font-weight:600">{{ $placeholder }}</code>
                    <span class="faint">— {{ $hint }}</span>
                </span>
                @endforeach
            </div>
            <div class="col-gap" style="--gap:14px">
                @foreach([
                    ['key' => 'notifications.sms_template_placed',    'label' => 'Order Placed',    'val' => $tplPlaced],
                    ['key' => 'notifications.sms_template_confirmed',  'label' => 'Order Confirmed',  'val' => $tplConfirmed],
                    ['key' => 'notifications.sms_template_delivered',  'label' => 'Order Delivered',  'val' => $tplDelivered],
                ] as $tpl)
                <div class="field">
                    <span class="lbl">{{ $tpl['label'] }}</span>
                    <textarea class="input mono" name="settings[{{ $tpl['key'] }}]" rows="3" style="resize:vertical">{{ old('settings[' . $tpl['key'] . ']', $tpl['val']) }}</textarea>
                </div>
                @endforeach
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end">
            <button class="btn btn-primary" type="submit">
                <span class="ico" data-ico="check" style="width:17px;height:17px"></span>Save Notifications
            </button>
        </div>
    </div>
</form>
@endif

    </div>{{-- /panel --}}
</div>{{-- /set-grid --}}

@push('scripts')
<script>
(function () {
    const sel = document.getElementById('currency-select');
    const sym = document.getElementById('currency-symbol');
    if (!sel || !sym) return;
    sel.addEventListener('change', function () {
        const opt = sel.options[sel.selectedIndex];
        const symbol = opt.dataset.symbol;
        if (symbol) sym.value = symbol;
    });
})();
</script>
@endpush

@endsection

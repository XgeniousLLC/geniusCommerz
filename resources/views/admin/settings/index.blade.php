@extends('admin.layouts.admin')

@section('title', 'Settings')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Settings</li>
    </ol>
@endsection

@section('page-header')
    <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
    <p class="text-gray-600 mt-1">Manage global store configuration</p>
@endsection

@section('content')
{{-- Tab nav --}}
<div style="border-bottom:1px solid #e5e7eb;margin-bottom:1.5rem">
    <nav style="display:flex;gap:0;margin-bottom:-1px" aria-label="Settings tabs">
        @foreach([
            'general'    => 'General',
            'meta'       => 'SEO / Meta',
            'social'     => 'Social',
            'storefront' => 'Storefront',
            'payment'    => 'Payment',
            'shipping'   => 'Shipping',
            'cart'       => 'Cart',
            'legal'      => 'Legal',
            'tracking'   => 'Tracking',
        ] as $slug => $label)
            <a href="{{ route('admin.settings.index', ['tab' => $slug]) }}"
               style="display:inline-block;padding:10px 18px;font-size:0.875rem;font-weight:500;white-space:nowrap;text-decoration:none;border-bottom:2px solid {{ $tab === $slug ? '#3b82f6' : 'transparent' }};color:{{ $tab === $slug ? '#2563eb' : '#6b7280' }};transition:color .15s,border-color .15s">
                {{ $label }}
            </a>
        @endforeach
        {{-- Orders settings is a separate page --}}
        <a href="{{ route('admin.order-settings.index') }}"
           style="display:inline-block;padding:10px 18px;font-size:0.875rem;font-weight:500;white-space:nowrap;text-decoration:none;border-bottom:2px solid transparent;color:#6b7280;transition:color .15s,border-color .15s">
            Orders
        </a>
    </nav>
</div>

{{-- General tab --}}
@if($tab === 'general')
<form method="POST" action="{{ route('admin.settings.update', 'general') }}">
    @csrf
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-6">General Settings</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Site Name</label>
                <x-admin.input type="text" name="settings[general.site_name]"
                    value="{{ old('general.site_name', $settings->get('general.site_name')?->value ?? '') }}" />
            </x-admin.form-group>

            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 pb-4 border-b border-gray-100">
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Site Logo</label>
                    <x-admin.media-picker
                        name="settings[general.logo_media_id]"
                        accept="image"
                        label="Upload Logo"
                        :value="old('general.logo_media_id', $settings->get('general.logo_media_id')?->value)" />
                    <p class="text-xs text-gray-400 mt-1">Appears in the header and emails.</p>
                </x-admin.form-group>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Favicon</label>
                    <x-admin.media-picker
                        name="settings[general.favicon_media_id]"
                        accept="image"
                        label="Upload Favicon"
                        :value="old('general.favicon_media_id', $settings->get('general.favicon_media_id')?->value)" />
                    <p class="text-xs text-gray-400 mt-1">Recommended: 32×32 or 64×64 PNG/ICO.</p>
                </x-admin.form-group>
            </div>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Tagline</label>
                <x-admin.input type="text" name="settings[general.site_tagline]"
                    value="{{ old('general.site_tagline', $settings->get('general.site_tagline')?->value ?? '') }}" />
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Admin Email</label>
                <x-admin.input type="email" name="settings[general.admin_email]"
                    value="{{ old('general.admin_email', $settings->get('general.admin_email')?->value ?? '') }}" />
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Support Phone</label>
                <x-admin.input type="text" name="settings[general.phone]"
                    value="{{ old('general.phone', $settings->get('general.phone')?->value ?? '') }}" />
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Timezone</label>
                @php $selectedTz = old('general.timezone', $settings->get('general.timezone')?->value ?? 'Asia/Dhaka'); @endphp
                <x-admin.select name="settings[general.timezone]">
                    @foreach(\DateTimeZone::listIdentifiers() as $tz)
                        <option value="{{ $tz }}" {{ $selectedTz === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                    @endforeach
                </x-admin.select>
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Currency Code</label>
                @php
                    $selectedCurrency = old('general.currency', $settings->get('general.currency')?->value ?? 'BDT');
                @endphp
                <x-admin.select name="settings[general.currency]" id="currency-select">
                    @foreach(\App\Support\Currencies::all() as $code => $info)
                        <option value="{{ $code }}"
                            data-symbol="{{ $info['symbol'] }}"
                            {{ $selectedCurrency === $code ? 'selected' : '' }}>
                            {{ $code }} — {{ $info['name'] }}
                        </option>
                    @endforeach
                </x-admin.select>
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Currency Symbol</label>
                <x-admin.input type="text" name="settings[general.currency_symbol]" id="currency-symbol"
                    value="{{ old('general.currency_symbol', $settings->get('general.currency_symbol')?->value ?? '৳') }}" />
                <p class="text-xs text-gray-400 mt-1">Auto-filled when you change the currency code. You can override it.</p>
            </x-admin.form-group>


            <div class="md:col-span-2">
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Business Address</label>
                    <textarea name="settings[general.address]" rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">{{ old('general.address', $settings->get('general.address')?->value ?? '') }}</textarea>
                </x-admin.form-group>
            </div>

            <x-admin.form-group>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="settings[general.maintenance_mode]" value="1"
                        {{ old('general.maintenance_mode', $settings->get('general.maintenance_mode')?->value) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Maintenance Mode</span>
                </label>
                <p class="text-xs text-gray-400 mt-1">Visitors see a maintenance page; admins can still log in.</p>
            </x-admin.form-group>

        </div>
        <div class="flex justify-end mt-6">
            <x-admin.button type="submit">Save General Settings</x-admin.button>
        </div>
    </x-admin.card>
</form>
@endif

{{-- SEO / Meta tab --}}
@if($tab === 'meta')
<form method="POST" action="{{ route('admin.settings.update', 'meta') }}">
    @csrf
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-6">SEO / Meta Defaults</h3>
        <div class="space-y-6">

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Default Title Template</label>
                <x-admin.input type="text" name="settings[meta.default_title_template]"
                    value="{{ old('meta.default_title_template', $settings->get('meta.default_title_template')?->value ?? '') }}" />
                <p class="text-xs text-gray-400 mt-1">Use <code>&#123;&#123;page_title&#125;&#125;</code> and <code>&#123;&#123;site_name&#125;&#125;</code> as placeholders.</p>
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Default Meta Description</label>
                <textarea name="settings[meta.default_description]" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">{{ old('meta.default_description', $settings->get('meta.default_description')?->value ?? '') }}</textarea>
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Default Keywords</label>
                <x-admin.input type="text" name="settings[meta.default_keywords]"
                    value="{{ old('meta.default_keywords', $settings->get('meta.default_keywords')?->value ?? '') }}" />
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Default Robots</label>
                <x-admin.select name="settings[seo.robots_default]">
                    @foreach(['index,follow', 'noindex,nofollow', 'noindex,follow', 'index,nofollow'] as $opt)
                        <option value="{{ $opt }}" {{ ($settings->get('seo.robots_default')?->value ?? 'index,follow') === $opt ? 'selected' : '' }}>
                            {{ $opt }}
                        </option>
                    @endforeach
                </x-admin.select>
            </x-admin.form-group>

        </div>
        <div class="flex justify-end mt-6">
            <x-admin.button type="submit">Save SEO Settings</x-admin.button>
        </div>
    </x-admin.card>
</form>
@endif

{{-- Social tab --}}
@if($tab === 'social')
<form method="POST" action="{{ route('admin.settings.update', 'social') }}">
    @csrf
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-6">Social / Open Graph</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">OG Site Name</label>
                <x-admin.input type="text" name="settings[og.site_name]"
                    value="{{ old('og.site_name', $settings->get('og.site_name')?->value ?? '') }}" />
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Default OG Image URL</label>
                <x-admin.input type="text" name="settings[og.default_image]"
                    value="{{ old('og.default_image', $settings->get('og.default_image')?->value ?? '') }}" />
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Twitter @site</label>
                <x-admin.input type="text" name="settings[twitter.site]"
                    value="{{ old('twitter.site', $settings->get('twitter.site')?->value ?? '') }}" />
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Twitter @creator</label>
                <x-admin.input type="text" name="settings[twitter.creator]"
                    value="{{ old('twitter.creator', $settings->get('twitter.creator')?->value ?? '') }}" />
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Default Twitter Card</label>
                <x-admin.select name="settings[twitter.default_card]">
                    @foreach(['summary_large_image', 'summary', 'app', 'player'] as $opt)
                        <option value="{{ $opt }}" {{ ($settings->get('twitter.default_card')?->value ?? 'summary_large_image') === $opt ? 'selected' : '' }}>
                            {{ $opt }}
                        </option>
                    @endforeach
                </x-admin.select>
            </x-admin.form-group>

        </div>
        <div class="flex justify-end mt-6">
            <x-admin.button type="submit">Save Social Settings</x-admin.button>
        </div>
    </x-admin.card>
</form>
@endif

{{-- Storefront tab --}}
@if($tab === 'storefront')
<form method="POST" action="{{ route('admin.settings.update', 'storefront') }}">
    @csrf
    <div class="space-y-6">

        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-6">Announcement Bar</h3>
            <div class="space-y-4">

                <div class="flex items-center gap-3">
                    <input type="hidden" name="settings[storefront.announce_bar_enabled]" value="0">
                    <input type="checkbox" name="settings[storefront.announce_bar_enabled]" id="announce-enabled" value="1"
                        {{ old('storefront.announce_bar_enabled', $settings->get('storefront.announce_bar_enabled')?->value) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <label for="announce-enabled" class="text-sm font-medium text-gray-700">Show announcement bar</label>
                </div>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Announcement Text</label>
                    <x-admin.input type="text" name="settings[storefront.announce_bar_text]"
                        value="{{ old('storefront.announce_bar_text', $settings->get('storefront.announce_bar_text')?->value ?? '') }}"
                        placeholder="e.g. Free delivery on orders over ৳500" />
                </x-admin.form-group>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Background Color</label>
                        <div class="flex items-center gap-3 mt-1">
                            <input type="color" name="settings[storefront.announce_bar_bg]"
                                value="{{ old('storefront.announce_bar_bg', $settings->get('storefront.announce_bar_bg')?->value ?? '#1F3A8A') }}"
                                class="h-9 w-16 rounded border-gray-300 cursor-pointer" />
                            <x-admin.input type="text" name="settings[storefront.announce_bar_bg_text]"
                                value="{{ old('storefront.announce_bar_bg', $settings->get('storefront.announce_bar_bg')?->value ?? '#1F3A8A') }}"
                                placeholder="#1F3A8A" class="w-28" />
                        </div>
                    </x-admin.form-group>

                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Link (optional)</label>
                        <x-admin.input type="text" name="settings[storefront.announce_bar_link]"
                            value="{{ old('storefront.announce_bar_link', $settings->get('storefront.announce_bar_link')?->value ?? '') }}"
                            placeholder="/page/offers" />
                        <p class="text-xs text-gray-400 mt-1">Make the bar clickable (leave blank to disable).</p>
                    </x-admin.form-group>
                </div>
            </div>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-6">Homepage Hero Banner</h3>
            <div class="space-y-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Heading</label>
                        <x-admin.input type="text" name="settings[storefront.hero_heading]"
                            value="{{ old('storefront.hero_heading', $settings->get('storefront.hero_heading')?->value ?? '') }}"
                            placeholder="e.g. Shop the Latest Trends" />
                    </x-admin.form-group>

                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Sub-heading</label>
                        <x-admin.input type="text" name="settings[storefront.hero_subheading]"
                            value="{{ old('storefront.hero_subheading', $settings->get('storefront.hero_subheading')?->value ?? '') }}"
                            placeholder="e.g. Free delivery on orders over ৳500" />
                    </x-admin.form-group>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Button Label</label>
                        <x-admin.input type="text" name="settings[storefront.hero_cta_label]"
                            value="{{ old('storefront.hero_cta_label', $settings->get('storefront.hero_cta_label')?->value ?? 'Shop Now') }}" />
                    </x-admin.form-group>

                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Button Link</label>
                        <x-admin.input type="text" name="settings[storefront.hero_cta_link]"
                            value="{{ old('storefront.hero_cta_link', $settings->get('storefront.hero_cta_link')?->value ?? '/products') }}" />
                    </x-admin.form-group>
                </div>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hero Image</label>
                    <x-admin.media-picker
                        name="settings[storefront.hero_image_media_id]"
                        accept="image"
                        label="Upload Banner Image"
                        :value="old('storefront.hero_image_media_id', $settings->get('storefront.hero_image_media_id')?->value)" />
                    <p class="text-xs text-gray-400 mt-1">Recommended: 1440×600px.</p>
                </x-admin.form-group>
            </div>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-6">Footer</h3>
            <div class="space-y-4">
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Copyright Text</label>
                    <x-admin.input type="text" name="settings[storefront.copyright]"
                        value="{{ old('storefront.copyright', $settings->get('storefront.copyright')?->value ?? '') }}"
                        placeholder="e.g. © {year} My Store · All rights reserved." />
                    <p class="text-xs text-gray-400 mt-1">Use <code class="bg-gray-100 px-1 rounded">{year}</code> to auto-insert the current year.</p>
                </x-admin.form-group>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Facebook URL</label>
                    <x-admin.input type="url" name="settings[storefront.facebook_url]"
                        value="{{ old('storefront.facebook_url', $settings->get('storefront.facebook_url')?->value ?? '') }}"
                        placeholder="https://facebook.com/yourpage" />
                </x-admin.form-group>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Instagram URL</label>
                    <x-admin.input type="url" name="settings[storefront.instagram_url]"
                        value="{{ old('storefront.instagram_url', $settings->get('storefront.instagram_url')?->value ?? '') }}"
                        placeholder="https://instagram.com/yourhandle" />
                </x-admin.form-group>
            </div>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-2">Global Return Policy</h3>
            <p class="text-sm text-gray-500 mb-4">Shown on all product pages unless overridden per product.</p>
            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Return Policy</label>
                <textarea name="settings[storefront.global_return_policy]" rows="4"
                    placeholder="e.g. We accept returns within 7 days of delivery. Items must be unused and in original packaging."
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">{{ old('storefront.global_return_policy', $settings->get('storefront.global_return_policy')?->value ?? '') }}</textarea>
            </x-admin.form-group>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-2">Product Page</h3>
            <p class="text-sm text-gray-500 mb-5">Show call / WhatsApp buttons on product pages so customers can order directly.</p>
            <div class="space-y-4">

                <div class="flex items-center gap-3">
                    <input type="hidden" name="settings[storefront.product_whatsapp_enabled]" value="0">
                    <input type="checkbox" name="settings[storefront.product_whatsapp_enabled]" id="wa-enabled" value="1"
                        {{ old('storefront.product_whatsapp_enabled', $settings->get('storefront.product_whatsapp_enabled')?->value) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <label for="wa-enabled" class="text-sm font-medium text-gray-700">Show WhatsApp Order button</label>
                </div>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">WhatsApp Number</label>
                    <x-admin.input type="text" name="settings[storefront.product_whatsapp_number]"
                        value="{{ old('storefront.product_whatsapp_number', $settings->get('storefront.product_whatsapp_number')?->value ?? '') }}"
                        placeholder="e.g. 8801XXXXXXXXX (with country code, no +)" />
                    <p class="text-xs text-gray-400 mt-1">Include country code without the + sign.</p>
                </x-admin.form-group>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">WhatsApp Message Template</label>
                    <x-admin.input type="text" name="settings[storefront.product_whatsapp_message]"
                        value="{{ old('storefront.product_whatsapp_message', $settings->get('storefront.product_whatsapp_message')?->value ?? 'Hi, I want to order: {product}') }}"
                        placeholder="Hi, I want to order: {product}" />
                    <p class="text-xs text-gray-400 mt-1">Use <code class="bg-gray-100 px-1 rounded">{product}</code> for the product name.</p>
                </x-admin.form-group>

                <hr class="border-gray-100">

                <div class="flex items-center gap-3">
                    <input type="hidden" name="settings[storefront.product_call_enabled]" value="0">
                    <input type="checkbox" name="settings[storefront.product_call_enabled]" id="call-enabled" value="1"
                        {{ old('storefront.product_call_enabled', $settings->get('storefront.product_call_enabled')?->value) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <label for="call-enabled" class="text-sm font-medium text-gray-700">Show Call to Order button</label>
                </div>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Call Number</label>
                    <x-admin.input type="text" name="settings[storefront.product_call_number]"
                        value="{{ old('storefront.product_call_number', $settings->get('storefront.product_call_number')?->value ?? '') }}"
                        placeholder="e.g. +8801XXXXXXXXX" />
                </x-admin.form-group>

            </div>
        </x-admin.card>

        <div class="flex justify-end">
            <x-admin.button type="submit">Save Storefront Settings</x-admin.button>
        </div>
    </div>
</form>
@endif

{{-- Payment tab --}}
@if($tab === 'payment')
<form method="POST" action="{{ route('admin.settings.update', 'payment') }}">
    @csrf
    <div class="space-y-6">

        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-6">Accepted Payment Methods</h3>
            <p class="text-sm text-gray-500 mb-4">Enable the methods you accept. These appear in the checkout and footer.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach([
                    'payment.cod_enabled'         => ['label' => 'Cash on Delivery',      'desc' => 'Customer pays when order is delivered'],
                    'payment.bkash_enabled'        => ['label' => 'bKash',                 'desc' => 'bKash mobile banking'],
                    'payment.nagad_enabled'        => ['label' => 'Nagad',                 'desc' => 'Nagad mobile banking'],
                    'payment.rocket_enabled'       => ['label' => 'Rocket',                'desc' => 'Dutch-Bangla Rocket'],
                    'payment.sslcommerz_enabled'   => ['label' => 'SSLCOMMERZ',            'desc' => 'Cards & internet banking via SSLCOMMERZ'],
                    'payment.stripe_enabled'       => ['label' => 'Stripe',                'desc' => 'International cards via Stripe'],
                ] as $key => $info)
                <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="hidden" name="settings[{{ $key }}]" value="0">
                    <input type="checkbox" name="settings[{{ $key }}]" value="1"
                        {{ old($key, $settings->get($key)?->value) ? 'checked' : '' }}
                        class="mt-0.5 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <span>
                        <span class="block text-sm font-medium text-gray-800">{{ $info['label'] }}</span>
                        <span class="block text-xs text-gray-400">{{ $info['desc'] }}</span>
                    </span>
                </label>
                @endforeach
            </div>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-6">bKash Configuration</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">bKash Merchant Number</label>
                    <x-admin.input type="text" name="settings[payment.bkash_number]"
                        value="{{ old('payment.bkash_number', $settings->get('payment.bkash_number')?->value ?? '') }}"
                        placeholder="01XXXXXXXXX" />
                </x-admin.form-group>
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Nagad Merchant Number</label>
                    <x-admin.input type="text" name="settings[payment.nagad_number]"
                        value="{{ old('payment.nagad_number', $settings->get('payment.nagad_number')?->value ?? '') }}"
                        placeholder="01XXXXXXXXX" />
                </x-admin.form-group>
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Rocket Account Number</label>
                    <x-admin.input type="text" name="settings[payment.rocket_number]"
                        value="{{ old('payment.rocket_number', $settings->get('payment.rocket_number')?->value ?? '') }}"
                        placeholder="01XXXXXXXXX-X" />
                </x-admin.form-group>
            </div>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-6">SSLCOMMERZ / Stripe</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">SSLCOMMERZ Store ID</label>
                    <x-admin.input type="text" name="settings[payment.sslcommerz_store_id]"
                        value="{{ old('payment.sslcommerz_store_id', $settings->get('payment.sslcommerz_store_id')?->value ?? '') }}" />
                </x-admin.form-group>
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">SSLCOMMERZ Store Password</label>
                    <x-admin.input type="password" name="settings[payment.sslcommerz_store_password]"
                        value="{{ old('payment.sslcommerz_store_password', $settings->get('payment.sslcommerz_store_password')?->value ?? '') }}" />
                </x-admin.form-group>
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Stripe Publishable Key</label>
                    <x-admin.input type="text" name="settings[payment.stripe_pk]"
                        value="{{ old('payment.stripe_pk', $settings->get('payment.stripe_pk')?->value ?? '') }}"
                        placeholder="pk_live_..." />
                </x-admin.form-group>
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Stripe Secret Key</label>
                    <x-admin.input type="password" name="settings[payment.stripe_sk]"
                        value="{{ old('payment.stripe_sk', $settings->get('payment.stripe_sk')?->value ?? '') }}"
                        placeholder="sk_live_..." />
                </x-admin.form-group>
            </div>
            <div class="mt-4 flex items-center gap-3">
                <input type="hidden" name="settings[payment.sslcommerz_sandbox]" value="0">
                <input type="checkbox" name="settings[payment.sslcommerz_sandbox]" id="ssl-sandbox" value="1"
                    {{ old('payment.sslcommerz_sandbox', $settings->get('payment.sslcommerz_sandbox')?->value) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                <label for="ssl-sandbox" class="text-sm font-medium text-gray-700">Use SSLCOMMERZ Sandbox (test mode)</label>
            </div>
        </x-admin.card>

        <div class="flex justify-end">
            <x-admin.button type="submit">Save Payment Settings</x-admin.button>
        </div>
    </div>
</form>
@endif

{{-- Shipping tab --}}
@if($tab === 'shipping')
<form method="POST" action="{{ route('admin.settings.update', 'shipping') }}">
    @csrf
    <div class="space-y-6">

        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-2">Shipping Rates</h3>
            <p class="text-sm text-gray-500 mb-6">Set a flat rate or offer free shipping above a threshold. Rates apply to all orders unless overridden.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Flat Rate Charge</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">৳</span>
                        <input type="number" name="settings[shipping.flat_rate]" min="0" step="0.01"
                            value="{{ old('shipping.flat_rate', $settings->get('shipping.flat_rate')?->value ?? '0') }}"
                            class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm h-9 px-3" />
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Set to 0 for free shipping on all orders.</p>
                </x-admin.form-group>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Free Shipping Threshold</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">৳</span>
                        <input type="number" name="settings[shipping.free_above]" min="0" step="0.01"
                            value="{{ old('shipping.free_above', $settings->get('shipping.free_above')?->value ?? '') }}"
                            placeholder="Leave blank to disable"
                            class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm h-9 px-3" />
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Orders above this amount get free shipping.</p>
                </x-admin.form-group>

            </div>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-6">Courier Providers</h3>
            <p class="text-sm text-gray-500 mb-4">Enable the courier partners you use. Rates shown at checkout are based on your flat rate above.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                @foreach([
                    'shipping.courier_pathao'    => ['label' => 'Pathao',     'desc' => 'Pathao courier service'],
                    'shipping.courier_shohoz'    => ['label' => 'Shohoz',     'desc' => 'Shohoz delivery'],
                    'shipping.courier_redx'      => ['label' => 'RedX',       'desc' => 'RedX last-mile delivery'],
                    'shipping.courier_paperfly'  => ['label' => 'Paperfly',   'desc' => 'Paperfly courier'],
                    'shipping.courier_sundarban' => ['label' => 'Sundarban',  'desc' => 'Sundarban courier service'],
                    'shipping.courier_custom'    => ['label' => 'Own Courier','desc' => 'In-house or custom delivery'],
                ] as $key => $info)
                <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="hidden" name="settings[{{ $key }}]" value="0">
                    <input type="checkbox" name="settings[{{ $key }}]" value="1"
                        {{ old($key, $settings->get($key)?->value) ? 'checked' : '' }}
                        class="mt-0.5 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <span>
                        <span class="block text-sm font-medium text-gray-800">{{ $info['label'] }}</span>
                        <span class="block text-xs text-gray-400">{{ $info['desc'] }}</span>
                    </span>
                </label>
                @endforeach
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Inside Dhaka Rate</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">৳</span>
                        <input type="number" name="settings[shipping.rate_inside_dhaka]" min="0" step="0.01"
                            value="{{ old('shipping.rate_inside_dhaka', $settings->get('shipping.rate_inside_dhaka')?->value ?? '60') }}"
                            class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm h-9 px-3" />
                    </div>
                </x-admin.form-group>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Outside Dhaka Rate</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">৳</span>
                        <input type="number" name="settings[shipping.rate_outside_dhaka]" min="0" step="0.01"
                            value="{{ old('shipping.rate_outside_dhaka', $settings->get('shipping.rate_outside_dhaka')?->value ?? '120') }}"
                            class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm h-9 px-3" />
                    </div>
                </x-admin.form-group>
            </div>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-4">Delivery Time Estimate</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Inside Dhaka</label>
                    <x-admin.input type="text" name="settings[shipping.delivery_inside_dhaka]"
                        value="{{ old('shipping.delivery_inside_dhaka', $settings->get('shipping.delivery_inside_dhaka')?->value ?? '1-2 business days') }}"
                        placeholder="e.g. 1-2 business days" />
                </x-admin.form-group>
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Outside Dhaka</label>
                    <x-admin.input type="text" name="settings[shipping.delivery_outside_dhaka]"
                        value="{{ old('shipping.delivery_outside_dhaka', $settings->get('shipping.delivery_outside_dhaka')?->value ?? '3-5 business days') }}"
                        placeholder="e.g. 3-5 business days" />
                </x-admin.form-group>
            </div>
        </x-admin.card>

        <div class="flex justify-end">
            <x-admin.button type="submit">Save Shipping Settings</x-admin.button>
        </div>
    </div>
</form>
@endif

{{-- Cart tab --}}
@if($tab === 'cart')
<form method="POST" action="{{ route('admin.settings.update', 'cart') }}" x-data="cartGoals()">
    @csrf
    <div class="space-y-6">

        {{-- Free shipping threshold (mirror from shipping for convenience) --}}
        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-1">Free Shipping Threshold</h3>
            <p class="text-sm text-gray-500 mb-4">When the cart total reaches this amount, shipping is waived. Set to 0 to disable.</p>
            <x-admin.form-group class="max-w-xs">
                <label class="block text-sm font-medium text-gray-700">Free shipping above (৳)</label>
                <x-admin.input type="number" name="settings[shipping.free_above]" min="0" step="0.01"
                    value="{{ old('shipping.free_above', \App\Models\SiteSetting::get('shipping.free_above', 0)) }}"
                    placeholder="e.g. 1000" />
                <p class="text-xs text-gray-400 mt-1">Orders above this amount get free shipping automatically.</p>
            </x-admin.form-group>
        </x-admin.card>

        {{-- Cart Goals --}}
        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-1">Cart Goals / Progress Milestones</h3>
            <p class="text-sm text-gray-500 mb-4">Show a progress bar in the cart drawer: "Add ৳X more to get Y". Goals are evaluated in ascending order of amount.</p>

            <div class="space-y-3 mb-4">
                <template x-for="(goal, i) in goals" :key="i">
                    <div class="flex flex-wrap items-end gap-3 p-3 rounded-lg border border-gray-100 bg-gray-50">
                        <div>
                            <label class="text-xs text-gray-500 block mb-0.5">Cart Amount (৳)</label>
                            <input type="number" x-model="goal.amount" min="0" step="1"
                                class="w-28 border border-gray-200 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 block mb-0.5">Reward</label>
                            <select x-model="goal.reward" class="border border-gray-200 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400">
                                <option value="free_shipping">Free Shipping</option>
                                <option value="discount_pct">% Discount</option>
                                <option value="discount_fixed">Fixed Discount (৳)</option>
                            </select>
                        </div>
                        <div x-show="goal.reward !== 'free_shipping'">
                            <label class="text-xs text-gray-500 block mb-0.5">Value</label>
                            <input type="number" x-model="goal.value" min="0"
                                class="w-20 border border-gray-200 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400"
                                :placeholder="goal.reward === 'discount_pct' ? 'e.g. 10' : 'e.g. 50'">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 block mb-0.5">Custom Label <span class="text-gray-400">(optional)</span></label>
                            <input type="text" x-model="goal.label"
                                class="w-44 border border-gray-200 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-400"
                                placeholder="e.g. a free gift">
                        </div>
                        <button type="button" @click="goals.splice(i,1)" class="text-red-400 hover:text-red-600 pb-1.5" title="Remove">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>

                <button type="button" @click="goals.push({amount:0,reward:'free_shipping',value:'',label:''})"
                    class="text-sm text-blue-600 hover:underline font-medium">+ Add Goal</button>
            </div>

            {{-- Preview --}}
            <template x-if="goals.length > 0">
                <div class="mt-4 p-3 bg-sky-50 rounded-lg border border-sky-100 text-xs text-sky-700">
                    <strong>Preview:</strong>
                    <template x-for="(g, i) in goals.slice().sort((a,b)=>a.amount-b.amount)" :key="i">
                        <span x-text="`৳${g.amount} → ${g.reward === 'free_shipping' ? 'free shipping' : (g.label || (g.reward === 'discount_pct' ? g.value+'% off' : '৳'+g.value+' off'))}`"
                              class="ml-2 inline-block bg-sky-100 px-2 py-0.5 rounded-full"></span>
                    </template>
                </div>
            </template>

            {{-- Single hidden field — no individual settings[cart.goals][...] names --}}
            <input type="hidden" name="settings[cart.goals_json]" :value="JSON.stringify(goals)">
        </x-admin.card>

        <div class="flex justify-end">
            <x-admin.button type="submit">Save Cart Settings</x-admin.button>
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

{{-- Legal tab --}}
@if($tab === 'legal')
<form method="POST" action="{{ route('admin.settings.update', 'legal') }}">
    @csrf
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-6">Legal Page URLs</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Privacy Policy URL</label>
                <x-admin.input type="text" name="settings[legal.privacy_policy_url]"
                    value="{{ old('legal.privacy_policy_url', $settings->get('legal.privacy_policy_url')?->value ?? '/privacy') }}" />
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Terms & Conditions URL</label>
                <x-admin.input type="text" name="settings[legal.terms_url]"
                    value="{{ old('legal.terms_url', $settings->get('legal.terms_url')?->value ?? '/terms') }}" />
            </x-admin.form-group>

            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Refund Policy URL</label>
                <x-admin.input type="text" name="settings[legal.refund_policy_url]"
                    value="{{ old('legal.refund_policy_url', $settings->get('legal.refund_policy_url')?->value ?? '/refund-policy') }}" />
            </x-admin.form-group>

        </div>
        <div class="flex justify-end mt-6">
            <x-admin.button type="submit">Save Legal Settings</x-admin.button>
        </div>
    </x-admin.card>
</form>
@endif

{{-- Tracking tab --}}
@if($tab === 'tracking')

@php
$stepStyle = 'display:inline-flex;align-items:center;justify-content:center;width:1.375rem;height:1.375rem;border-radius:9999px;background:#2563eb;color:#fff;font-size:.7rem;font-weight:700;flex-shrink:0;margin-top:.1rem';
$infoBoxStyle = 'margin-top:.75rem;padding:.75rem 1rem;background:#f0f9ff;border:1px solid #bae6fd;border-radius:.625rem;font-size:.8rem;color:#0369a1;line-height:1.6';
$warnBoxStyle = 'margin-top:.75rem;padding:.75rem 1rem;background:#fffbeb;border:1px solid #fde68a;border-radius:.625rem;font-size:.8rem;color:#92400e;line-height:1.6';
@endphp

<form method="POST" action="{{ route('admin.settings.update', 'tracking') }}">
    @csrf
    <div class="space-y-8">

        {{-- ── Google Tag Manager ── --}}
        <x-admin.card>
            <div class="flex items-start gap-3 mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Google Tag Manager (GTM)</h3>
                    <p class="text-sm text-gray-500 mt-0.5">
                        GTM is a free Google tool that lets you add/manage all your tracking scripts (GA4, Facebook Pixel, Google Ads, etc.) from one place — without touching the website code every time.
                    </p>
                </div>
            </div>

            {{-- How to find GTM ID --}}
            <div style="{{ $infoBoxStyle }}">
                <strong class="block mb-1">📋 How to get your GTM Container ID</strong>
                <ol class="space-y-1 pl-1">
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">1</span><span>Go to <strong>tagmanager.google.com</strong> and sign in with your Google account.</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">2</span><span>Create a new account (or open an existing one). Choose <strong>Web</strong> as the platform.</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">3</span><span>Your Container ID is shown at the top — it looks like <strong>GTM-XXXXXXXX</strong>. Copy and paste it below.</span></li>
                </ol>
                <p class="mt-1.5 text-xs text-sky-700">✅ Once saved, GTM loads on every page automatically. You can then add GA4, Ads conversion tags etc. inside GTM without needing to change the store settings again.</p>
            </div>

            <div class="mt-4 max-w-xs">
                <label class="block text-sm font-medium text-gray-700 mb-1">Container ID</label>
                <x-admin.input type="text" name="settings[tracking.gtm_id]"
                    value="{{ old('tracking.gtm_id', $settings->get('tracking.gtm_id')?->value ?? '') }}"
                    placeholder="GTM-XXXXXXXX" />
                <p class="text-xs text-gray-400 mt-1">Leave blank to disable GTM entirely.</p>
            </div>
        </x-admin.card>

        {{-- ── Meta (Facebook) Pixel ── --}}
        <x-admin.card>
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900">Meta (Facebook) Pixel</h3>
                <p class="text-sm text-gray-500 mt-0.5">
                    The Pixel tracks visitors on your store so Facebook can optimise your ads and show them to people likely to buy. It also powers Retargeting (showing ads to people who visited your store but didn't buy).
                </p>
            </div>

            {{-- Two-part explanation --}}
            <div class="grid md:grid-cols-2 gap-4 mb-5">
                <div style="padding:.875rem 1rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.625rem;font-size:.8rem;color:#334155;line-height:1.6">
                    <strong class="block text-gray-700 mb-1">🌐 Pixel ID — client-side</strong>
                    Fires a <em>PageView</em> event every time a visitor opens a page. Required for Facebook Ads to work properly. Free and no extra setup needed beyond the ID.
                </div>
                <div style="padding:.875rem 1rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.625rem;font-size:.8rem;color:#334155;line-height:1.6">
                    <strong class="block text-gray-700 mb-1">🔒 CAPI Token — server-side</strong>
                    Sends a <em>Purchase</em> event directly from the server after each order — bypassing ad blockers and iOS privacy restrictions. Strongly recommended for accurate purchase tracking.
                </div>
            </div>

            <div style="{{ $infoBoxStyle }}">
                <strong class="block mb-1">📋 How to get your Pixel ID</strong>
                <ol class="space-y-1 pl-1">
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">1</span><span>Open <strong>business.facebook.com</strong> → <strong>Events Manager</strong>.</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">2</span><span>Select your Pixel (or click <strong>Connect Data Sources → Web</strong> to create one).</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">3</span><span>Copy the <strong>Pixel ID</strong> — a 15–16 digit number shown at the top of the page.</span></li>
                </ol>
            </div>

            <div style="{{ $infoBoxStyle }} margin-top:.625rem">
                <strong class="block mb-1">📋 How to get your CAPI Access Token</strong>
                <ol class="space-y-1 pl-1">
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">1</span><span>In <strong>Events Manager</strong>, click your Pixel → <strong>Settings</strong> tab.</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">2</span><span>Scroll to <strong>Conversions API</strong> → click <strong>Generate Access Token</strong>.</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">3</span><span>Copy the long token that starts with <strong>EAAx…</strong> and paste it below.</span></li>
                </ol>
                <p class="mt-1.5 text-xs" style="color:#0369a1">⚠️ Keep this token private — it has permission to send events on behalf of your Pixel.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-5">
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Pixel ID</label>
                    <x-admin.input type="text" name="settings[tracking.meta_pixel_id]"
                        value="{{ old('tracking.meta_pixel_id', $settings->get('tracking.meta_pixel_id')?->value ?? '') }}"
                        placeholder="123456789012345" />
                    <p class="text-xs text-gray-400 mt-1">15–16 digit number from Events Manager. Leave blank to disable the Pixel.</p>
                </x-admin.form-group>
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">CAPI Access Token <span class="text-gray-400 font-normal">(optional but recommended)</span></label>
                    <x-admin.input type="password" name="settings[tracking.meta_capi_token]"
                        value="{{ old('tracking.meta_capi_token', $settings->get('tracking.meta_capi_token')?->value ?? '') }}"
                        placeholder="EAAx..." />
                    <p class="text-xs text-gray-400 mt-1">Enables server-side Purchase events. Improves accuracy on iOS devices and with ad blockers.</p>
                </x-admin.form-group>
            </div>
        </x-admin.card>

        {{-- ── Google Analytics 4 ── --}}
        <x-admin.card>
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900">Google Analytics 4 (GA4)</h3>
                <p class="text-sm text-gray-500 mt-0.5">
                    GA4 tracks your store's traffic, user behaviour, and revenue. The <strong>Measurement ID</strong> is enough for basic page tracking (add it via GTM). The <strong>API Secret</strong> enables a server-side purchase event that sends reliable order data even when JavaScript is blocked.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-4 mb-5">
                <div style="padding:.875rem 1rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.625rem;font-size:.8rem;color:#334155;line-height:1.6">
                    <strong class="block text-gray-700 mb-1">📊 Measurement ID — for GTM / basic tracking</strong>
                    Looks like <strong>G-XXXXXXXXXX</strong>. Add it inside Google Tag Manager to track page views, sessions, and events automatically.
                </div>
                <div style="padding:.875rem 1rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.625rem;font-size:.8rem;color:#334155;line-height:1.6">
                    <strong class="block text-gray-700 mb-1">🔒 API Secret — server-side purchase events</strong>
                    Sends a <em>purchase</em> event directly from the server after every order. Orders appear in GA4 → Reports → Monetisation → Ecommerce purchases.
                </div>
            </div>

            <div style="{{ $infoBoxStyle }}">
                <strong class="block mb-1">📋 How to get your Measurement ID</strong>
                <ol class="space-y-1 pl-1">
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">1</span><span>Open <strong>analytics.google.com</strong> → go to your GA4 property.</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">2</span><span>Click <strong>Admin</strong> (bottom-left cog icon) → <strong>Data Streams</strong> → click your web stream.</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">3</span><span>Copy the <strong>Measurement ID</strong> at the top right — it starts with <strong>G-</strong>.</span></li>
                </ol>
            </div>

            <div style="{{ $infoBoxStyle }} margin-top:.625rem">
                <strong class="block mb-1">📋 How to get the API Secret (for server-side events)</strong>
                <ol class="space-y-1 pl-1">
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">1</span><span>Inside the same <strong>Data Stream</strong> page, scroll down to <strong>Measurement Protocol API secrets</strong>.</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">2</span><span>Click <strong>Create</strong>, give it a name (e.g. "Store server"), then click <strong>Create</strong> again.</span></li>
                    <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">3</span><span>Copy the <strong>Secret value</strong> shown and paste it below.</span></li>
                </ol>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-5">
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Measurement ID</label>
                    <x-admin.input type="text" name="settings[tracking.ga4_measurement_id]"
                        value="{{ old('tracking.ga4_measurement_id', $settings->get('tracking.ga4_measurement_id')?->value ?? '') }}"
                        placeholder="G-XXXXXXXXXX" />
                    <p class="text-xs text-gray-400 mt-1">Starts with <strong>G-</strong>. Also used if you add GA4 via GTM.</p>
                </x-admin.form-group>
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">API Secret <span class="text-gray-400 font-normal">(optional but recommended)</span></label>
                    <x-admin.input type="password" name="settings[tracking.ga4_api_secret]"
                        value="{{ old('tracking.ga4_api_secret', $settings->get('tracking.ga4_api_secret')?->value ?? '') }}"
                        placeholder="your_api_secret" />
                    <p class="text-xs text-gray-400 mt-1">Enables server-side purchase events. Keeps revenue data accurate when users have ad blockers.</p>
                </x-admin.form-group>
            </div>
        </x-admin.card>

        {{-- ── Product Feeds ── --}}
        <x-admin.card>
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900">Product Feeds</h3>
                <p class="text-sm text-gray-500 mt-0.5">
                    Product feeds are files the store generates automatically containing your full product catalogue. Google and Facebook read these files to show your products in Shopping ads and the Facebook/Instagram Shop.
                </p>
            </div>

            <div class="space-y-5">

                {{-- Google Merchant --}}
                <div style="border:1px solid #e5e7eb;border-radius:.75rem;overflow:hidden">
                    <label class="flex items-start gap-4 p-4 cursor-pointer hover:bg-gray-50 transition-colors">
                        <div class="mt-0.5">
                            <input type="hidden" name="settings[tracking.google_merchant_enabled]" value="0">
                            <input type="checkbox" name="settings[tracking.google_merchant_enabled]" value="1"
                                id="gmc-toggle"
                                {{ old('tracking.google_merchant_enabled', $settings->get('tracking.google_merchant_enabled')?->value) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        </div>
                        <div class="flex-1">
                            <span class="block text-sm font-semibold text-gray-900">Google Merchant Center Feed</span>
                            <span class="block text-xs text-gray-500 mt-0.5">
                                Enables a product feed at
                                <code class="bg-gray-100 px-1 py-0.5 rounded text-gray-700">{{ url('feeds/google-merchant.xml') }}</code>
                                — submit this URL to Google Merchant Center so your products appear in Google Shopping.
                            </span>
                        </div>
                    </label>
                    <div style="padding:.875rem 1rem 1rem;background:#f0f9ff;border-top:1px solid #bae6fd;font-size:.8rem;color:#0369a1;line-height:1.6">
                        <strong class="block mb-1">📋 How to submit to Google Merchant Center</strong>
                        <ol class="space-y-1 pl-1">
                            <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">1</span><span>Enable the feed above and click <strong>Save</strong>.</span></li>
                            <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">2</span><span>Go to <strong>merchants.google.com</strong> and sign in.</span></li>
                            <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">3</span><span>Navigate to <strong>Products → Feeds → Add a primary feed</strong>.</span></li>
                            <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">4</span><span>Choose <strong>Scheduled fetch</strong>, paste the URL above, and set fetch frequency to <strong>Daily</strong>.</span></li>
                            <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">5</span><span>Google will review products within 1–3 business days and then show them in Shopping.</span></li>
                        </ol>
                    </div>
                </div>

                {{-- Facebook Catalog --}}
                <div style="border:1px solid #e5e7eb;border-radius:.75rem;overflow:hidden">
                    <label class="flex items-start gap-4 p-4 cursor-pointer hover:bg-gray-50 transition-colors">
                        <div class="mt-0.5">
                            <input type="hidden" name="settings[tracking.facebook_catalog_enabled]" value="0">
                            <input type="checkbox" name="settings[tracking.facebook_catalog_enabled]" value="1"
                                id="fb-catalog-toggle"
                                {{ old('tracking.facebook_catalog_enabled', $settings->get('tracking.facebook_catalog_enabled')?->value) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        </div>
                        <div class="flex-1">
                            <span class="block text-sm font-semibold text-gray-900">Facebook &amp; Instagram Shop Catalog Feed</span>
                            <span class="block text-xs text-gray-500 mt-0.5">
                                Enables a product feed at
                                <code class="bg-gray-100 px-1 py-0.5 rounded text-gray-700">{{ url('feeds/facebook-catalog.json') }}</code>
                                — submit this URL to Facebook Commerce Manager to power Dynamic Ads and the Instagram Shop.
                            </span>
                        </div>
                    </label>
                    <div style="padding:.875rem 1rem 1rem;background:#f0f9ff;border-top:1px solid #bae6fd;font-size:.8rem;color:#0369a1;line-height:1.6">
                        <strong class="block mb-1">📋 How to submit to Facebook Commerce Manager</strong>
                        <ol class="space-y-1 pl-1">
                            <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">1</span><span>Enable the feed above and click <strong>Save</strong>.</span></li>
                            <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">2</span><span>Go to <strong>business.facebook.com → Commerce Manager</strong>.</span></li>
                            <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">3</span><span>Open your catalog → <strong>Data sources → Add items → Use a data feed</strong>.</span></li>
                            <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">4</span><span>Choose <strong>Scheduled recurring upload</strong>, paste the URL above, set schedule to <strong>Daily</strong>.</span></li>
                            <li style="display:flex;gap:.5rem"><span style="{{ $stepStyle }}">5</span><span>Facebook will sync products automatically. You can then run <strong>Dynamic Product Ads</strong> or set up the <strong>Instagram Shop</strong>.</span></li>
                        </ol>
                    </div>
                </div>

            </div>
        </x-admin.card>

        <div class="flex justify-end">
            <x-admin.button type="submit">Save Tracking Settings</x-admin.button>
        </div>
    </div>
</form>
@endif

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

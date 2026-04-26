@extends('admin.layouts.admin')
@section('title', 'Footer Settings')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Storefront → Footer</li>
</ol>
@endsection

@section('page-header')
<h1 class="text-2xl font-bold text-gray-900">Footer Settings</h1>
<p class="text-gray-600 mt-1">Manage copyright, social links, newsletter and footer columns</p>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.storefront.footer.update') }}">
@csrf
<div class="space-y-6 max-w-3xl">

    {{-- General --}}
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-4">General</h3>
        <div class="space-y-4">
            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Copyright Text</label>
                <x-admin.input type="text" name="settings[storefront.copyright]"
                    value="{{ $settings->get('storefront.copyright')?->value ?? '' }}"
                    placeholder="© {year} Your Store · All rights reserved." />
                <p class="text-xs text-gray-400 mt-1">Use <code>{year}</code> for the current year.</p>
            </x-admin.form-group>
        </div>
    </x-admin.card>

    {{-- Newsletter --}}
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-4">Newsletter Strip</h3>
        <div class="space-y-4">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="settings[storefront.newsletter_enabled]" value="0">
                <input type="checkbox" name="settings[storefront.newsletter_enabled]" value="1"
                    {{ ($settings->get('storefront.newsletter_enabled')?->value ?? '1') ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                <span class="text-sm font-medium text-gray-700">Show newsletter subscription strip</span>
            </label>
            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Heading</label>
                <x-admin.input type="text" name="settings[storefront.newsletter_heading]"
                    value="{{ $settings->get('storefront.newsletter_heading')?->value ?? '' }}"
                    placeholder="Subscribe to our newsletter" />
            </x-admin.form-group>
        </div>
    </x-admin.card>

    {{-- Social Links --}}
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-4">Social Links</h3>
        <p class="text-sm text-gray-500 mb-4">Leave blank to hide an icon.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach([
                'storefront.facebook_url'  => ['Facebook',  'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z'],
                'storefront.instagram_url' => ['Instagram', 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01M7.5 2h9A5.5 5.5 0 0122 7.5v9A5.5 5.5 0 0116.5 22h-9A5.5 5.5 0 012 16.5v-9A5.5 5.5 0 017.5 2z'],
                'storefront.youtube_url'   => ['YouTube',   'M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.4a2.78 2.78 0 001.95-1.95A29 29 0 0023 12a29 29 0 00-.46-5.58zM9.75 15.02V8.98L15.5 12l-5.75 3.02z'],
                'storefront.tiktok_url'    => ['TikTok',    'M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.75a4.85 4.85 0 01-1.01-.06z'],
                'storefront.twitter_url'   => ['Twitter/X', 'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z'],
                'storefront.linkedin_url'  => ['LinkedIn',  'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z M4 6a2 2 0 100-4 2 2 0 000 4z'],
            ] as $key => [$platform, $icon])
            <x-admin.form-group>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-1">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                    </svg>
                    {{ $platform }}
                </label>
                <x-admin.input type="url" name="settings[{{ $key }}]"
                    value="{{ $settings->get($key)?->value ?? '' }}"
                    placeholder="https://{{ strtolower($platform) }}.com/yourpage" />
            </x-admin.form-group>
            @endforeach
        </div>
    </x-admin.card>

    {{-- Footer Columns --}}
    @foreach([
        ['col1', 'Footer Column 1 (e.g. Help)', 'col1Links'],
        ['col2', 'Footer Column 2 (e.g. Company)', 'col2Links'],
    ] as [$col, $label, $var])
    @php $links = $$var; @endphp
    <x-admin.card x-data="{
        links: {{ json_encode(count($links) ? $links : [['label'=>'','url'=>'']]) }},
        add() { this.links.push({ label: '', url: '' }) },
        remove(i) { this.links.splice(i, 1) }
    }">
        <h3 class="text-base font-semibold text-gray-900 mb-4">{{ $label }}</h3>
        <x-admin.form-group>
            <label class="block text-sm font-medium text-gray-700">Column Heading</label>
            <x-admin.input type="text" name="settings[storefront.footer_{{ $col }}_title]"
                value="{{ $settings->get('storefront.footer_' . $col . '_title')?->value ?? '' }}"
                placeholder="{{ $col === 'col1' ? 'Help' : 'Company' }}" />
        </x-admin.form-group>
        <div class="mt-4 space-y-2">
            <label class="block text-sm font-medium text-gray-700">Links</label>
            <template x-for="(link, i) in links" :key="i">
                <div class="flex items-center gap-2">
                    <input type="text" x-model="link.label" :name="`footer_{{ $col }}_links[${i}][label]`"
                        placeholder="Link label"
                        class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 h-9 px-3" />
                    <input type="text" x-model="link.url" :name="`footer_{{ $col }}_links[${i}][url]`"
                        placeholder="/page/about-us"
                        class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 h-9 px-3" />
                    <button type="button" @click="remove(i)"
                        class="flex-shrink-0 text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
            <button type="button" @click="add()"
                class="mt-1 text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add link
            </button>
        </div>
    </x-admin.card>
    @endforeach

    <div class="flex justify-end">
        <x-admin.button type="submit">Save Footer Settings</x-admin.button>
    </div>
</div>
</form>
@endsection

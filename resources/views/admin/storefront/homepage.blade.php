@extends('admin.layouts.admin')
@section('title', 'Homepage Settings')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Storefront → Homepage</li>
</ol>
@endsection

@section('page-header')
<h1 class="text-2xl font-bold text-gray-900">Homepage Settings</h1>
<p class="text-gray-600 mt-1">Control the announcement bar, hero section, and visible sections</p>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.storefront.homepage.update') }}">
@csrf
<div class="space-y-6 max-w-3xl">

    {{-- Announcement Bar --}}
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-4">Announcement Bar</h3>
        <div class="space-y-4">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="settings[storefront.announce_bar_enabled]" value="0">
                <input type="checkbox" name="settings[storefront.announce_bar_enabled]" value="1"
                    {{ $settings->get('storefront.announce_bar_enabled')?->value ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                <span class="text-sm font-medium text-gray-700">Show announcement bar</span>
            </label>
            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Announcement Text</label>
                <x-admin.input type="text" name="settings[storefront.announce_bar_text]"
                    value="{{ $settings->get('storefront.announce_bar_text')?->value ?? '' }}"
                    placeholder="e.g. Free delivery on orders over ৳500" />
            </x-admin.form-group>
            <div class="grid grid-cols-2 gap-4">
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Background Colour</label>
                    <div class="flex items-center gap-2 mt-1">
                        <input type="color" name="settings[storefront.announce_bar_bg]"
                            value="{{ $settings->get('storefront.announce_bar_bg')?->value ?? '#1F3A8A' }}"
                            class="h-9 w-16 rounded border-gray-300 cursor-pointer" />
                    </div>
                </x-admin.form-group>
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Link (optional)</label>
                    <x-admin.input type="text" name="settings[storefront.announce_bar_link]"
                        value="{{ $settings->get('storefront.announce_bar_link')?->value ?? '' }}"
                        placeholder="/shop" />
                </x-admin.form-group>
            </div>
        </div>
    </x-admin.card>

    {{-- Hero Section --}}
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-4">Hero Section</h3>
        <div class="space-y-4">
            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Heading</label>
                <x-admin.input type="text" name="settings[general.hero_title]"
                    value="{{ $settings->get('general.hero_title')?->value ?? '' }}"
                    placeholder="Shop Smart, Save More" />
            </x-admin.form-group>
            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Subheading</label>
                <x-admin.input type="text" name="settings[general.hero_subtitle]"
                    value="{{ $settings->get('general.hero_subtitle')?->value ?? '' }}"
                    placeholder="Thousands of quality products..." />
            </x-admin.form-group>
            <div class="grid grid-cols-2 gap-4">
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">CTA Button Label</label>
                    <x-admin.input type="text" name="settings[storefront.hero_cta_label]"
                        value="{{ $settings->get('storefront.hero_cta_label')?->value ?? '' }}"
                        placeholder="Shop Now →" />
                </x-admin.form-group>
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">CTA Link</label>
                    <x-admin.input type="text" name="settings[storefront.hero_cta_link]"
                        value="{{ $settings->get('storefront.hero_cta_link')?->value ?? '' }}"
                        placeholder="/shop" />
                </x-admin.form-group>
            </div>
            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700 mb-1">Background Image</label>
                <x-admin.media-picker name="settings[storefront.hero_image_media_id]" accept="image" label="Upload Hero Image"
                    :value="$settings->get('storefront.hero_image_media_id')?->value" />
            </x-admin.form-group>
        </div>
    </x-admin.card>

    {{-- Section Visibility --}}
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-1">Section Visibility</h3>
        <p class="text-sm text-gray-500 mb-4">Choose which sections appear on the homepage.</p>
        <div class="space-y-3">
            @foreach([
                'storefront.show_featured_products' => 'Featured Products section',
                'storefront.show_new_arrivals'      => 'New Arrivals section',
                'storefront.show_categories'        => 'Shop by Categories section',
                'storefront.show_blog'              => 'Latest Blog Posts section',
            ] as $key => $label)
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="settings[{{ $key }}]" value="0">
                <input type="checkbox" name="settings[{{ $key }}]" value="1"
                    {{ ($settings->get($key)?->value ?? '1') ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                <span class="text-sm text-gray-700">{{ $label }}</span>
            </label>
            @endforeach
        </div>
        <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-100">
            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Featured Products to show</label>
                <x-admin.input type="number" name="settings[storefront.featured_count]" min="1" max="24"
                    value="{{ $settings->get('storefront.featured_count')?->value ?? '8' }}" />
            </x-admin.form-group>
            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">New Arrivals to show</label>
                <x-admin.input type="number" name="settings[storefront.arrivals_count]" min="1" max="24"
                    value="{{ $settings->get('storefront.arrivals_count')?->value ?? '8' }}" />
            </x-admin.form-group>
        </div>
    </x-admin.card>

    <div class="flex justify-end">
        <x-admin.button type="submit">Save Homepage Settings</x-admin.button>
    </div>
</div>
</form>
@endsection

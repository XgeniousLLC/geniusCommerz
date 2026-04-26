@extends('admin.layouts.admin')

@section('title', 'Edit Brand')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li><a href="{{ route('admin.brands.index') }}" class="hover:text-gray-700">Brands</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Edit</li>
    </ol>
@endsection

@section('page-header')
    <h1 class="text-2xl font-bold text-gray-900">Edit: {{ $brand->name }}</h1>
@endsection

@section('content')
<div class="max-w-2xl">
    <x-admin.card>
        <form method="POST" action="{{ route('admin.brands.update', $brand) }}">
            @csrf @method('PUT')

            <h3 class="text-base font-semibold text-gray-900 mb-5">Brand Info</h3>
            <div class="space-y-4">

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required
                        value="{{ old('name', $brand->name) }}"
                        class="flex h-10 w-full rounded-md border border-gray-300 bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2" />
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </x-admin.form-group>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" name="slug" readonly
                        value="{{ old('slug', $brand->slug) }}"
                        class="flex h-10 w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-500 cursor-not-allowed" />
                    <p class="text-xs text-gray-400 mt-1">Slug is locked after creation.</p>
                    @error('slug')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </x-admin.form-group>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">{{ old('description', $brand->description) }}</textarea>
                </x-admin.form-group>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Logo</label>
                        <x-admin.media-picker name="logo_media_id" accept="image" label="Add Logo"
                            :value="old('logo_media_id', $brand->logo_media_id)" />
                        @error('logo_media_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </x-admin.form-group>

                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Website</label>
                        <x-admin.input type="url" name="website"
                            value="{{ old('website', $brand->website) }}" placeholder="https://…" />
                        @error('website')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </x-admin.form-group>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $brand->is_active) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">Active</span>
                    </label>
                    <div class="flex items-center space-x-3">
                        <x-admin.button href="{{ route('admin.brands.index') }}" variant="outline">Cancel</x-admin.button>
                        <x-admin.button type="submit">Update Brand</x-admin.button>
                    </div>
                </div>

            </div>
        </form>
    </x-admin.card>
</div>
@endsection

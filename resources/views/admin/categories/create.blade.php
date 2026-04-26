@extends('admin.layouts.admin')

@section('title', 'Add Category')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li><a href="{{ route('admin.categories.index') }}" class="hover:text-gray-700">Categories</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Add</li>
    </ol>
@endsection

@section('page-header')
    <h1 class="text-2xl font-bold text-gray-900">Add Category</h1>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.categories.store') }}">
    @csrf
    <div class="space-y-6">

        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-4">Category Info</h3>
            <div class="space-y-4">
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                    <x-admin.input type="text" name="name" id="cat-name" value="{{ old('name') }}" required />
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </x-admin.form-group>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Slug <span class="text-red-500">*</span></label>
                    <x-admin.input type="text" name="slug" id="cat-slug" value="{{ old('slug') }}" required />
                    @error('slug')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </x-admin.form-group>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">{{ old('description') }}</textarea>
                </x-admin.form-group>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Image</label>
                    <x-admin.media-picker name="image_media_id" accept="image" label="Add Image"
                        :value="old('image_media_id')" />
                    @error('image_media_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </x-admin.form-group>
            </div>
        </x-admin.card>

        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-4">Options</h3>
            <div class="space-y-4">
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Parent Category</label>
                    <x-admin.select name="parent_id">
                        <option value="">— None (top level) —</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </x-admin.select>
                </x-admin.form-group>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                    <x-admin.input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" />
                </x-admin.form-group>

                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', '1') ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Active</span>
                </label>
            </div>
        </x-admin.card>

        <div class="flex space-x-3">
            <x-admin.button type="submit" class="justify-center">Save</x-admin.button>
            <x-admin.button href="{{ route('admin.categories.index') }}" variant="outline">Cancel</x-admin.button>
        </div>

    </div>
</form>

@push('scripts')
<script>
document.getElementById('cat-name').addEventListener('input', function () {
    document.getElementById('cat-slug').value = this.value
        .toLowerCase().trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-');
});
</script>
@endpush
@endsection

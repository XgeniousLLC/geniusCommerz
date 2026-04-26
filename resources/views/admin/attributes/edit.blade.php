@extends('admin.layouts.admin')

@section('title', 'Edit Attribute')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li><a href="{{ route('admin.attributes.index') }}" class="hover:text-gray-700">Attributes</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Edit</li>
    </ol>
@endsection

@section('page-header')
    <h1 class="text-2xl font-bold text-gray-900">Edit: {{ $attribute->name }}</h1>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.attributes.update', $attribute) }}">
    @csrf @method('PUT')
    <div class="space-y-6">
        <x-admin.card>
            <h3 class="text-base font-semibold text-gray-900 mb-4">Attribute</h3>
            <div class="space-y-4">
                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                    <x-admin.input type="text" name="name" value="{{ old('name', $attribute->name) }}" required />
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </x-admin.form-group>

                <x-admin.form-group>
                    <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                    <x-admin.input type="number" name="sort_order"
                        value="{{ old('sort_order', $attribute->sort_order) }}" min="0" class="w-24" />
                </x-admin.form-group>
            </div>
        </x-admin.card>

        <x-admin.card>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-900">Values</h3>
                <button type="button" id="add-value"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium">+ Add value</button>
            </div>
            <p class="text-xs text-amber-600 mb-3">Saving replaces all values. Existing variants referencing removed values will be affected.</p>
            <div id="values-list" class="space-y-2">
                @php $existingValues = old('values', $attribute->values->pluck('value')->toArray()); @endphp
                @foreach($existingValues as $val)
                <div class="flex items-center space-x-2 value-row">
                    <x-admin.input type="text" name="values[]" value="{{ $val }}" class="flex-1" />
                    <button type="button" class="remove-value text-gray-400 hover:text-red-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                @endforeach
            </div>
        </x-admin.card>

        <div class="flex space-x-3">
            <x-admin.button type="submit" class="justify-center">Update</x-admin.button>
            <x-admin.button href="{{ route('admin.attributes.index') }}" variant="outline">Cancel</x-admin.button>
        </div>
    </div>
</form>

@push('scripts')
<script>
const list = document.getElementById('values-list');

document.getElementById('add-value').addEventListener('click', function () {
    const row = document.createElement('div');
    row.className = 'flex items-center space-x-2 value-row';
    row.innerHTML = `
        <input type="text" name="values[]"
            class="flex-1 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm h-10 px-3" />
        <button type="button" class="remove-value text-gray-400 hover:text-red-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>`;
    list.appendChild(row);
    row.querySelector('input').focus();
});

list.addEventListener('click', function (e) {
    const btn = e.target.closest('.remove-value');
    if (btn && list.children.length > 1) btn.closest('.value-row').remove();
});
</script>
@endpush
@endsection

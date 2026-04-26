@extends('admin.layouts.admin')

@section('title', 'Edit Role — ' . ucwords(str_replace('-', ' ', $role->name)))

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li><a href="{{ route('admin.roles.index') }}" class="hover:text-gray-700">Roles & Permissions</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Edit — {{ ucwords(str_replace('-', ' ', $role->name)) }}</li>
    </ol>
@endsection

@section('page-header')
    <h1 class="text-2xl font-bold text-gray-900">
        Edit Role: <span class="text-blue-600">{{ ucwords(str_replace('-', ' ', $role->name)) }}</span>
    </h1>
    <p class="text-gray-600 mt-1">Adjust permissions assigned to this role</p>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.roles.update', $role) }}">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Role details --}}
        <div class="lg:col-span-1">
            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Role Details</h3>

                <x-admin.form-group>
                    <label for="name" class="block text-sm font-medium text-gray-700">Role Name *</label>
                    @if($role->name === 'super-admin')
                        <x-admin.input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ $role->name }}"
                            disabled
                        />
                        <p class="mt-1 text-xs text-gray-500">System role — name cannot be changed.</p>
                        {{-- hidden to satisfy validation --}}
                        <input type="hidden" name="name" value="{{ $role->name }}">
                    @else
                        <x-admin.input
                            type="text"
                            id="name"
                            name="name"
                            required
                            value="{{ old('name', $role->name) }}"
                        />
                        @error('name')
                            <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                        @enderror
                    @endif
                </x-admin.form-group>

                {{-- Stats --}}
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900">{{ $role->permissions->count() }}</div>
                        <div class="text-xs text-gray-500 mt-1">Permissions</div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900">
                            {{ \App\Models\Admin::role($role->name)->count() }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Admins</div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <x-admin.button variant="secondary" type="button" onclick="window.history.back()">
                        Cancel
                    </x-admin.button>
                    <x-admin.button type="submit">
                        Save Changes
                    </x-admin.button>
                </div>
            </x-admin.card>
        </div>

        {{-- Permission matrix --}}
        <div class="lg:col-span-2">
            <x-admin.card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900">Permissions</h3>
                    <button type="button" id="toggle-all"
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                        Select all
                    </button>
                </div>

                @error('permissions')
                    <div class="text-red-500 text-sm mb-4">{{ $message }}</div>
                @enderror

                @php $activeAssigned = old('permissions', $assigned); @endphp

                <div class="space-y-6">
                    @foreach($permissionGroups as $module => $permissions)
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                                    {{ ucfirst($module) }}
                                </h4>
                                <button type="button"
                                        class="toggle-group text-xs text-gray-400 hover:text-blue-600"
                                        data-group="{{ $module }}">
                                    Select all
                                </button>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 bg-gray-50 rounded-lg">
                                @foreach($permissions as $permission)
                                    <label class="flex items-center space-x-2 cursor-pointer group">
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $permission->name }}"
                                            data-group="{{ $module }}"
                                            {{ in_array($permission->name, $activeAssigned) ? 'checked' : '' }}
                                            class="perm-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                        >
                                        <span class="text-sm text-gray-700 group-hover:text-gray-900">
                                            {{ ucfirst(explode(' ', $permission->name)[0]) }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-admin.card>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-group').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const group = this.dataset.group;
            const boxes = document.querySelectorAll(`input[data-group="${group}"]`);
            const allChecked = Array.from(boxes).every(b => b.checked);
            boxes.forEach(b => b.checked = !allChecked);
            this.textContent = allChecked ? 'Select all' : 'Deselect all';
        });
    });

    const toggleAll = document.getElementById('toggle-all');
    toggleAll.addEventListener('click', function () {
        const boxes = document.querySelectorAll('.perm-checkbox');
        const allChecked = Array.from(boxes).every(b => b.checked);
        boxes.forEach(b => b.checked = !allChecked);
        this.textContent = allChecked ? 'Select all' : 'Deselect all';
    });
});
</script>
@endpush

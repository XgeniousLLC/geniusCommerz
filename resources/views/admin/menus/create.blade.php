@extends('admin.layouts.admin')

@section('title', 'New Menu')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.menus.index') }}" class="hover:text-gray-700">Menus</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">New Menu</li>
</ol>
@endsection

@section('page-header')
<h1 class="text-2xl font-bold text-gray-900">Create Menu</h1>
@endsection

@section('content')
<x-admin.card class="max-w-lg">
    <form method="POST" action="{{ route('admin.menus.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Menu Name</label>
            <x-admin.input name="name" value="{{ old('name') }}" placeholder="e.g. Main Navigation" required />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
            <select name="location" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                <option value="">Select a location…</option>
                @foreach($locations as $key => $label)
                    <option value="{{ $key }}" {{ in_array($key, $usedLocations) ? 'disabled' : '' }} {{ old('location') === $key ? 'selected' : '' }}>
                        {{ $label }} {{ in_array($key, $usedLocations) ? '(in use)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-3 pt-2">
            <x-admin.button type="submit">Create Menu</x-admin.button>
            <a href="{{ route('admin.menus.index') }}" class="text-sm text-gray-500 hover:text-gray-700 self-center">Cancel</a>
        </div>
    </form>
</x-admin.card>
@endsection

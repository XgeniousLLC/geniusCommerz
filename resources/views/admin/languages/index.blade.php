@extends('admin.layouts.admin')

@section('title', 'Languages')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Languages</li>
    </ol>
@endsection

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Languages</h1>
            <p class="text-gray-600 mt-1">Manage storefront languages and AI-powered translations</p>
        </div>
    </div>
@endsection

@section('content')
<div x-data="languagePage()" class="space-y-6">

    {{-- Add language form --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Add Language</h2>
        <form method="POST" action="{{ route('admin.languages.store') }}" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Language Name</label>
                <input type="text" name="name" placeholder="e.g. Bengali" value="{{ old('name') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-44 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Language Code</label>
                <input type="text" name="code" placeholder="e.g. bn" value="{{ old('code') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-28 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
                @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit"
                class="bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                Add Language
            </button>
        </form>
    </div>

    {{-- Language list --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Language</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Code</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Status</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Default</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($languages as $lang)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-medium text-gray-900">{{ $lang->name }}</td>
                    <td class="px-5 py-3 text-gray-500 font-mono">{{ $lang->code }}</td>
                    <td class="px-5 py-3">
                        @if($lang->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if($lang->is_default)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Default</span>
                        @else
                            <form method="POST" action="{{ route('admin.languages.set-default', $lang) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:underline">Set as Default</button>
                            </form>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right space-x-3">
                        <a href="{{ route('admin.languages.edit', $lang) }}"
                            class="text-sm text-blue-600 hover:underline">Edit / Translate</a>
                        @unless($lang->is_default)
                        <form method="POST" action="{{ route('admin.languages.destroy', $lang) }}" class="inline"
                            onsubmit="return confirm('Delete {{ $lang->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                        </form>
                        @endunless
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-8 text-center text-gray-400">No languages added yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-xs text-gray-400">
        The storefront detects the visitor's preferred language via a cookie (<code>locale</code>).
        Add a language then use "Translate All with AI" to auto-generate translations.
    </p>
</div>

<script>
function languagePage() {
    return {};
}
</script>
@endsection

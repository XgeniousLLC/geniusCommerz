@extends('admin.layouts.admin')

@section('title', 'Blog Categories')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.blogs.index') }}" class="hover:text-gray-700">Blog Posts</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Categories</li>
</ol>
@endsection

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Blog Categories</h1>
    <p class="text-gray-500 mt-1 text-sm">Manage categories used to organise blog posts.</p>
</div>
@endsection

@section('content')

<div class="grid lg:grid-cols-[340px_1fr] gap-6 items-start">

    {{-- Add category form --}}
    <x-admin.card>
        <h3 class="text-base font-semibold text-gray-900 mb-4">Add Category</h3>
        <form method="POST" action="{{ route('admin.blog-categories.store') }}" class="space-y-4">
            @csrf
            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                <x-admin.input type="text" name="name" value="{{ old('name') }}"
                    placeholder="e.g. Style, Tech, Beauty" required />
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </x-admin.form-group>
            <x-admin.form-group>
                <label class="block text-sm font-medium text-gray-700">Slug</label>
                <x-admin.input type="text" name="slug" value="{{ old('slug') }}"
                    placeholder="auto-generated" />
                @error('slug')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </x-admin.form-group>
            <x-admin.button type="submit">Add Category</x-admin.button>
        </form>
    </x-admin.card>

    {{-- Categories list --}}
    <x-admin.card>
        @if($categories->isEmpty())
        <div class="py-10 text-center text-gray-400 text-sm">No categories yet.</div>
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Name</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Slug</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-500">Posts</th>
                    <th class="py-3 px-4"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($categories as $cat)
                <tr class="hover:bg-gray-50 group" x-data="{ editing: false }">
                    <td class="py-3 px-4">
                        <span x-show="!editing" class="font-medium text-gray-900">{{ $cat->name }}</span>
                        <form x-show="editing" method="POST"
                              action="{{ route('admin.blog-categories.update', $cat) }}"
                              class="flex items-center gap-2">
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $cat->name }}"
                                   class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm h-8 px-2 w-36" />
                            <button type="submit"
                                    class="px-2 py-1 rounded bg-blue-600 text-white text-xs hover:bg-blue-700">Save</button>
                            <button type="button" @click="editing = false"
                                    class="px-2 py-1 rounded bg-gray-100 text-gray-600 text-xs hover:bg-gray-200">Cancel</button>
                        </form>
                    </td>
                    <td class="py-3 px-4 text-gray-400">{{ $cat->slug }}</td>
                    <td class="py-3 px-4 text-gray-500">{{ $cat->blogs_count }}</td>
                    <td class="py-3 px-4 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition">
                            <button type="button" @click="editing = !editing"
                                    class="p-1.5 text-gray-400 hover:text-blue-600 rounded" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <form method="POST" action="{{ route('admin.blog-categories.destroy', $cat) }}"
                                  onsubmit="return confirm('Delete {{ $cat->name }}? Posts in this category will be unassigned.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </x-admin.card>

</div>
@endsection

@extends('admin.layouts.admin')

@section('title', 'Categories')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Categories</li>
    </ol>
@endsection

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Categories</h1>
            <p class="text-gray-600 mt-1">{{ $categories->total() }} total</p>
        </div>
        <x-admin.button href="{{ route('admin.categories.create') }}">Add Category</x-admin.button>
    </div>
@endsection

@section('content')
<x-admin.card>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Parent</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Order</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($categories as $category)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($category->imageMedia)
                                <img src="{{ $category->imageMedia->getUrl('thumb') }}" alt="{{ $category->name }}"
                                     class="h-8 w-8 rounded object-cover bg-gray-100 shrink-0">
                            @else
                                <div class="h-8 w-8 rounded bg-gray-100 flex items-center justify-center text-gray-400 text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($category->name, 0, 2)) }}
                                </div>
                            @endif
                            <span class="font-medium text-gray-900">{{ $category->parent ? '— ' : '' }}{{ $category->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $category->parent?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $category->sort_order }}</td>
                    <td class="px-4 py-3">
                        @if($category->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline"
                              onsubmit="return confirm('Delete {{ addslashes($category->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
        <div class="mt-4 px-4">{{ $categories->links() }}</div>
    @endif
</x-admin.card>
@endsection

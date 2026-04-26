@extends('admin.layouts.admin')

@section('title', 'Attributes')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Attributes</li>
    </ol>
@endsection

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Attributes</h1>
            <p class="text-gray-600 mt-1">Variant axes — e.g. Size, Color, Material</p>
        </div>
        <x-admin.button href="{{ route('admin.attributes.create') }}">Add Attribute</x-admin.button>
    </div>
@endsection

@section('content')
<x-admin.card>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Values</th>
                    <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Order</th>
                    <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($attributes as $attr)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $attr->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $attr->values_count }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $attr->sort_order }}</td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ route('admin.attributes.edit', $attr) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Edit</a>
                        <form method="POST" action="{{ route('admin.attributes.destroy', $attr) }}" class="inline"
                              onsubmit="return confirm('Delete {{ addslashes($attr->name) }} and all its values?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">No attributes yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
@endsection

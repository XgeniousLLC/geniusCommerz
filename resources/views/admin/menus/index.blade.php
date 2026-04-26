@extends('admin.layouts.admin')

@section('title', 'Menus')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Menus</li>
</ol>
@endsection

@section('page-header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Navigation Menus</h1>
        <p class="text-gray-600 mt-1">Manage your site's navigation structure</p>
    </div>
    <x-admin.button href="{{ route('admin.menus.create') }}">+ New Menu</x-admin.button>
</div>
@endsection

@section('content')
<x-admin.card>
    @if($menus->isEmpty())
        <div class="text-center py-12">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
            </svg>
            <p class="text-gray-500 mb-4">No menus created yet.</p>
            <x-admin.button href="{{ route('admin.menus.create') }}">Create your first menu</x-admin.button>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($menus as $menu)
            @php $locations = \App\Models\Menu::LOCATIONS; @endphp
            <div class="border border-gray-200 rounded-xl p-4 hover:border-blue-200 transition-colors">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $menu->name }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $locations[$menu->location] ?? $menu->location }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $menu->items_count }} items</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.menus.edit', $menu) }}" class="text-xs text-blue-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}" onsubmit="return confirm('Delete this menu?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</x-admin.card>
@endsection

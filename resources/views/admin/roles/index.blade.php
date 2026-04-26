@extends('admin.layouts.admin')

@section('title', 'Roles & Permissions')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Roles & Permissions</li>
    </ol>
@endsection

@section('page-header')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Roles & Permissions</h1>
            <p class="text-gray-600 mt-1">Manage roles and their permission assignments</p>
        </div>
        <a href="{{ route('admin.roles.create') }}">
            <x-admin.button>+ Create Role</x-admin.button>
        </a>
    </div>
@endsection

@section('content')
<x-admin.card>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admins</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($roles as $role)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-3">
                                @php
                                    $badgeColor = match($role->name) {
                                        'super-admin'       => 'bg-red-100 text-red-800',
                                        'store-manager'     => 'bg-purple-100 text-purple-800',
                                        'operations'        => 'bg-blue-100 text-blue-800',
                                        'inventory-manager' => 'bg-indigo-100 text-indigo-800',
                                        'marketing'         => 'bg-pink-100 text-pink-800',
                                        'call-agent'        => 'bg-yellow-100 text-yellow-800',
                                        'call-supervisor'   => 'bg-orange-100 text-orange-800',
                                        'accountant'        => 'bg-green-100 text-green-800',
                                        'content-editor'    => 'bg-teal-100 text-teal-800',
                                        default             => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
                                    {{ ucwords(str_replace('-', ' ', $role->name)) }}
                                </span>
                                @if($role->name === 'super-admin')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
                                        System
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900 font-medium">{{ $role->permissions_count }}</span>
                            <span class="text-sm text-gray-500"> permissions</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900 font-medium">{{ $adminCounts[$role->name] ?? 0 }}</span>
                            <span class="text-sm text-gray-500"> admin(s)</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('admin.roles.edit', $role) }}"
                               class="text-blue-600 hover:text-blue-900" title="Edit permissions">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            @if($role->name !== 'super-admin')
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                      class="inline-block"
                                      onsubmit="return confirm('Delete role \'{{ $role->name }}\'? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">No roles found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin.card>
@endsection

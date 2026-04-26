@extends('admin.layouts.admin')

@section('title', 'Currencies')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Currencies</li>
    </ol>
@endsection

@section('page-header')
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Currencies</h1>
        <p class="text-gray-600 mt-1">Manage currencies and exchange rates. Prices are stored in your base currency and converted at display time.</p>
    </div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Add currency --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Add Currency</h2>
        <form method="POST" action="{{ route('admin.currencies.store') }}" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Code</label>
                <input type="text" name="code" placeholder="USD" value="{{ old('code') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-24 uppercase focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
                @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Symbol</label>
                <input type="text" name="symbol" placeholder="$" value="{{ old('symbol') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-20 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Name</label>
                <input type="text" name="name" placeholder="US Dollar" value="{{ old('name') }}"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Rate (1 base = ?)</label>
                <input type="number" name="rate" placeholder="0.0094" value="{{ old('rate') }}"
                    step="0.000001" min="0.000001"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-36 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
            </div>
            <button type="submit"
                class="bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                Add Currency
            </button>
        </form>
    </div>

    {{-- Currency list --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Code</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Symbol</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Name</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Rate</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Status</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600">Default</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($currencies as $currency)
                <tr x-data="{ editing: false }" class="hover:bg-gray-50">
                    <td class="px-5 py-3 font-mono font-semibold text-gray-900">{{ $currency->code }}</td>
                    <td class="px-5 py-3 text-gray-700">{{ $currency->symbol }}</td>
                    <td class="px-5 py-3">
                        <span x-show="!editing">{{ $currency->name }}</span>
                        <form x-show="editing" method="POST" action="{{ route('admin.currencies.update', $currency) }}" class="flex flex-wrap gap-2 items-center">
                            @csrf @method('PUT')
                            <input type="text" name="symbol" value="{{ $currency->symbol }}"
                                class="border border-gray-300 rounded px-2 py-1 text-sm w-16" required>
                            <input type="text" name="name" value="{{ $currency->name }}"
                                class="border border-gray-300 rounded px-2 py-1 text-sm w-32" required>
                            <input type="number" name="rate" value="{{ $currency->rate }}"
                                step="0.000001" min="0.000001"
                                class="border border-gray-300 rounded px-2 py-1 text-sm w-28" required>
                            <label class="flex items-center gap-1 text-xs">
                                <input type="checkbox" name="is_active" value="1" {{ $currency->is_active ? 'checked' : '' }}>
                                Active
                            </label>
                            <button type="submit" class="text-xs bg-blue-600 text-white px-2 py-1 rounded">Save</button>
                            <button type="button" @click="editing=false" class="text-xs text-gray-500 hover:text-gray-700">Cancel</button>
                        </form>
                    </td>
                    <td class="px-5 py-3 font-mono text-gray-600">{{ $currency->rate }}</td>
                    <td class="px-5 py-3">
                        @if($currency->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if($currency->is_default)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Default</span>
                        @else
                            <form method="POST" action="{{ route('admin.currencies.set-default', $currency) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:underline">Set Default</button>
                            </form>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right space-x-3">
                        <button type="button" @click="editing=!editing" class="text-sm text-blue-600 hover:underline">Edit</button>
                        @unless($currency->is_default)
                        <form method="POST" action="{{ route('admin.currencies.destroy', $currency) }}" class="inline"
                            onsubmit="return confirm('Delete {{ $currency->code }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:underline">Delete</button>
                        </form>
                        @endunless
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-8 text-center text-gray-400">No currencies added yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-xs text-gray-400">
        The base currency is your store's default. Set exchange rates relative to 1 unit of the base currency.
        Rates are applied at display time — orders are always stored in the base currency.
    </p>
</div>
@endsection

@extends('admin.layouts.admin')

@section('title', 'Loyalty Program')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Loyalty Program</li>
</ol>
@endsection

@section('page-header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Loyalty Program</h1>
        <p class="text-gray-600 mt-1">Configure points earning, redemption, and tiers</p>
    </div>
    <div class="flex items-center gap-3">
        @if($settings['enabled'])
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                <span class="w-2 h-2 rounded-full bg-green-500"></span> Active
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-500">
                <span class="w-2 h-2 rounded-full bg-gray-400"></span> Disabled
            </span>
        @endif
        <a href="{{ route('admin.loyalty.index') }}" class="text-sm text-blue-600 hover:underline">View Transactions →</a>
    </div>
</div>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.loyalty.update') }}" x-data="{ tiers: {{ json_encode($settings['tiers']) }} }">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Main settings --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Enable/disable --}}
            <x-admin.card>
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Enable Loyalty Program</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Customers earn points when they place orders</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="enabled" value="0">
                        <input type="checkbox" name="enabled" value="1" class="sr-only peer" {{ $settings['enabled'] ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </x-admin.card>

            {{-- Points rules --}}
            <x-admin.card>
                <h2 class="text-base font-semibold text-gray-900 mb-4">Points Rules</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Points earned per ৳1 spent</label>
                        <div class="flex items-center gap-2">
                            <x-admin.input type="number" name="points_per_taka" value="{{ $settings['points_per_taka'] }}" step="0.01" min="0" class="flex-1" required />
                            <span class="text-sm text-gray-500 shrink-0">pts / ৳1</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">e.g. 0.1 = 1 point per ৳10</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Taka value per point</label>
                        <div class="flex items-center gap-2">
                            <x-admin.input type="number" name="taka_per_point" value="{{ $settings['taka_per_point'] }}" step="0.01" min="0" class="flex-1" required />
                            <span class="text-sm text-gray-500 shrink-0">৳ / pt</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">e.g. 0.5 = 100 pts = ৳50 discount</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimum points to redeem</label>
                        <x-admin.input type="number" name="min_redemption" value="{{ $settings['min_redemption'] }}" min="1" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max % of order payable by points</label>
                        <div class="flex items-center gap-2">
                            <x-admin.input type="number" name="max_redemption_pct" value="{{ $settings['max_redemption_pct'] }}" min="1" max="100" class="flex-1" required />
                            <span class="text-sm text-gray-500 shrink-0">%</span>
                        </div>
                    </div>
                </div>
            </x-admin.card>

            {{-- Tiers --}}
            <x-admin.card>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-gray-900">Membership Tiers</h2>
                    <button type="button" @click="tiers.push({name:'New Tier', min:0, max:null, bonus_pct:0})" class="text-xs text-blue-600 hover:underline">+ Add Tier</button>
                </div>

                <div class="space-y-3">
                    <template x-for="(tier, i) in tiers" :key="i">
                        <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 bg-gray-50">
                            <div class="flex-1 grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <div>
                                    <label class="text-xs text-gray-500 block mb-0.5">Name</label>
                                    <input :name="`tiers[${i}][name]`" x-model="tier.name" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm" required>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 block mb-0.5">Min points</label>
                                    <input type="number" :name="`tiers[${i}][min]`" x-model="tier.min" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm" min="0" required>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 block mb-0.5">Max points</label>
                                    <input type="number" :name="`tiers[${i}][max]`" x-model="tier.max" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm" min="0" placeholder="∞">
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500 block mb-0.5">Bonus %</label>
                                    <input type="number" :name="`tiers[${i}][bonus_pct]`" x-model="tier.bonus_pct" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm" min="0" max="100" required>
                                </div>
                            </div>
                            <button type="button" @click="tiers.splice(i, 1)" class="text-red-400 hover:text-red-600 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
                <p class="text-xs text-gray-400 mt-3">Bonus % adds extra points per order for customers in that tier.</p>
            </x-admin.card>
        </div>

        {{-- Right: Preview + save --}}
        <div class="space-y-4">
            <x-admin.card>
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Quick Preview</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Order ৳1,000</span>
                        <span class="font-medium text-gray-900">= {{ round($settings['points_per_taka'] * 1000) }} pts</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>100 points</span>
                        <span class="font-medium text-gray-900">= ৳{{ round($settings['taka_per_point'] * 100) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Min to redeem</span>
                        <span class="font-medium text-gray-900">{{ $settings['min_redemption'] }} pts</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Max per order</span>
                        <span class="font-medium text-gray-900">{{ $settings['max_redemption_pct'] }}% of total</span>
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card>
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Current Tiers</h3>
                <div class="space-y-2">
                    @foreach($settings['tiers'] as $tier)
                    @php
                        $colors = ['Silver' => ['bg-gray-100','text-gray-700'], 'Gold' => ['bg-yellow-100','text-yellow-800'], 'Platinum' => ['bg-blue-100','text-blue-800']];
                        $c = $colors[$tier['name']] ?? ['bg-purple-100','text-purple-800'];
                    @endphp
                    <div class="flex items-center justify-between text-sm">
                        <span class="inline-flex items-center px-2 py-0.5 rounded font-semibold text-xs {{ $c[0] }} {{ $c[1] }}">{{ $tier['name'] }}</span>
                        <span class="text-gray-500">
                            {{ number_format($tier['min']) }}{{ $tier['max'] ? '–' . number_format($tier['max']) : '+' }} pts
                            @if($tier['bonus_pct'] > 0)
                                <span class="text-green-600 font-medium">+{{ $tier['bonus_pct'] }}%</span>
                            @endif
                        </span>
                    </div>
                    @endforeach
                </div>
            </x-admin.card>

            <x-admin.button type="submit" class="w-full justify-center">Save Settings</x-admin.button>
        </div>
    </div>
</form>
@endsection

@extends('admin.layouts.admin')
@section('title', 'Accounting')
@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Accounting</h1>
            <p class="text-sm text-gray-500 mt-0.5">Purchase history, ad spend, and profit margin analysis</p>
        </div>
        <div class="flex gap-2">
            <x-admin.button href="{{ route('admin.accounting.purchases.create') }}">+ New Purchase</x-admin.button>
            <x-admin.button href="{{ route('admin.accounting.ad-spend.index') }}" variant="outline">Ad Spend</x-admin.button>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['label' => 'Total Purchase Cost', 'value' => '৳'.number_format($totalPurchaseCost, 0), 'color' => 'text-blue-600', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10'],
            ['label' => 'Total Shipment Cost', 'value' => '৳'.number_format($totalShipmentCost, 0), 'color' => 'text-purple-600', 'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
            ['label' => 'Ad Spend (This Month)', 'value' => '৳'.number_format($totalAdSpendMonth, 0), 'color' => 'text-pink-600', 'icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z'],
            ['label' => 'Total Ad Spend', 'value' => '৳'.number_format($totalAdSpend, 0), 'color' => 'text-orange-600', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ] as $card)
        <x-admin.card class="p-4">
            <div class="flex items-start gap-3">
                <div class="p-2 bg-gray-100 rounded-lg shrink-0">
                    <svg class="w-5 h-5 {{ $card['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/></svg>
                </div>
                <div>
                    <div class="text-xs text-gray-500">{{ $card['label'] }}</div>
                    <div class="text-xl font-bold {{ $card['color'] }} mt-0.5">{{ $card['value'] }}</div>
                </div>
            </div>
        </x-admin.card>
        @endforeach
    </div>

    <x-admin.card>
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h3 class="font-semibold text-gray-900">Profit Margin Suggestions</h3>
                <p class="text-xs text-gray-500 mt-0.5">40% gross margin target · Landed = Unit cost + Shipment/unit + Ad spend/unit</p>
            </div>
            <form method="POST" action="{{ route('admin.settings.update', 'accounting') }}" class="flex items-center gap-2">
                @csrf
                @method('PUT')
                <label class="text-xs font-medium text-gray-600 whitespace-nowrap">Fixed Ad Spend / Unit (৳)</label>
                <input type="number" name="settings[accounting.ad_spend_per_unit]" value="{{ $adSpendPerUnit }}" min="0" step="0.01"
                    class="w-28 border border-gray-300 rounded-lg text-sm px-2 py-1.5 focus:border-blue-500 focus:ring-blue-500 focus:outline-none" />
                <button type="submit" class="text-xs bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 font-medium">Save</button>
            </form>
        </div>
        @if($suggestions->isEmpty())
            <p class="text-sm text-gray-500 text-center py-10">No purchase data yet. <a href="{{ route('admin.accounting.purchases.create') }}" class="text-blue-600">Add a purchase order</a> to see suggestions.</p>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Avg Unit Cost</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Shipment/Unit</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Ad Spend/Unit</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Landed Cost</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Current Price</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Suggested Price</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Current Margin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($suggestions as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $s['product_name'] }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">৳{{ number_format($s['avg_unit_cost'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">৳{{ number_format($s['shipment_cost'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">৳{{ number_format($s['ad_spend_unit'], 2) }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-800">৳{{ number_format($s['landed_cost'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">৳{{ number_format($s['current_price'], 2) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-blue-700">৳{{ number_format($s['suggested_price'], 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($s['current_margin'] !== null)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $s['current_margin'] >= 40 ? 'bg-green-100 text-green-700' : ($s['current_margin'] >= 20 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $s['current_margin'] }}%
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-admin.card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-admin.card>
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Recent Purchases</h3>
                <a href="{{ route('admin.accounting.purchases.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
            </div>
            @forelse($recentPurchases as $po)
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50">
                <div>
                    <a href="{{ route('admin.accounting.purchases.show', $po) }}" class="text-sm font-semibold text-gray-900 hover:text-blue-600">{{ $po->reference }}</a>
                    <div class="text-xs text-gray-500">{{ $po->supplier_name }} · {{ $po->order_date->format('d M Y') }}</div>
                </div>
                <div class="text-right">
                    <div class="text-sm font-bold text-gray-800">৳{{ number_format($po->totalCost(), 0) }}</div>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ ['draft'=>'bg-gray-100 text-gray-600','ordered'=>'bg-blue-100 text-blue-700','partial'=>'bg-yellow-100 text-yellow-700','received'=>'bg-green-100 text-green-700'][$po->status] }}">
                        {{ ucfirst($po->status) }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-6">No purchases yet.</p>
            @endforelse
        </x-admin.card>

        <x-admin.card>
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Recent Ad Spend</h3>
                <a href="{{ route('admin.accounting.ad-spend.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
            </div>
            @forelse($recentAdSpends as $s)
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50">
                <div>
                    <div class="text-sm font-semibold text-gray-900">{{ \App\Models\AdSpend::$platforms[$s->platform] }}</div>
                    <div class="text-xs text-gray-500">{{ $s->campaign_name ?? '—' }} · {{ $s->spend_date->format('d M Y') }}</div>
                </div>
                <div class="text-sm font-bold text-pink-600">৳{{ number_format($s->amount, 0) }}</div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-6">No ad spend recorded.</p>
            @endforelse
        </x-admin.card>
    </div>

</div>
@endsection

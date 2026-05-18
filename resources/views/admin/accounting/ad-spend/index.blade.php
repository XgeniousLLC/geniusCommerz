@extends('admin.layouts.admin')
@section('title', 'Ad Spend')
@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Ad Spend</h1>
            <p class="text-sm text-gray-500">Track advertising costs across platforms</p>
        </div>
        <x-admin.button href="{{ route('admin.accounting.index') }}" variant="outline">← Accounting</x-admin.button>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-admin.card class="p-4">
            <div class="text-xs text-gray-500 mb-1">This Month</div>
            <div class="text-xl font-bold text-pink-600">৳{{ number_format($monthTotal, 0) }}</div>
        </x-admin.card>
        <x-admin.card class="p-4">
            <div class="text-xs text-gray-500 mb-1">All Time</div>
            <div class="text-xl font-bold text-gray-800">৳{{ number_format($allTotal, 0) }}</div>
        </x-admin.card>
        @foreach(\App\Models\AdSpend::$platforms as $key => $label)
            @if(isset($byPlatform[$key]))
            <x-admin.card class="p-4">
                <div class="text-xs text-gray-500 mb-1">{{ $label }}</div>
                <div class="text-lg font-bold text-gray-700">৳{{ number_format($byPlatform[$key], 0) }}</div>
            </x-admin.card>
            @endif
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-admin.card class="lg:col-span-1">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900 text-sm">Record Ad Spend</h3>
            </div>
            <form method="POST" action="{{ route('admin.accounting.ad-spend.store') }}" class="p-5 space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Platform <span class="text-red-500">*</span></label>
                    <select name="platform" required class="w-full rounded-lg border-gray-300 text-sm px-2 py-2">
                        @foreach(\App\Models\AdSpend::$platforms as $key => $label)
                        <option value="{{ $key }}" {{ old('platform') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('platform')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Campaign Name</label>
                    <input type="text" name="campaign_name" value="{{ old('campaign_name') }}" placeholder="Optional"
                        class="w-full rounded-lg border-gray-300 text-sm px-2 py-2" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Amount (৳) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount') }}" min="0" step="0.01" required
                        class="w-full rounded-lg border-gray-300 text-sm px-2 py-2" />
                    @error('amount')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="spend_date" value="{{ old('spend_date', date('Y-m-d')) }}" required
                        class="w-full rounded-lg border-gray-300 text-sm px-2 py-2" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Product (optional)</label>
                    <select name="product_id" class="w-full rounded-lg border-gray-300 text-sm px-2 py-2">
                        <option value="">All products</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm px-2 py-2">{{ old('notes') }}</textarea>
                </div>
                <x-admin.button type="submit" class="w-full justify-center">Save Record</x-admin.button>
            </form>
        </x-admin.card>

        <x-admin.card class="lg:col-span-2">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900 text-sm">Records</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Date</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Platform</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Campaign</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Product</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-600">Amount</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($spends as $s)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-500">{{ $s->spend_date->format('d M Y') }}</td>
                            <td class="px-4 py-2 font-medium text-gray-800">{{ \App\Models\AdSpend::$platforms[$s->platform] }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $s->campaign_name ?? '—' }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $s->product?->name ?? 'All' }}</td>
                            <td class="px-4 py-2 text-right font-bold text-pink-600">৳{{ number_format($s->amount, 0) }}</td>
                            <td class="px-4 py-2 text-right">
                                <form method="POST" action="{{ route('admin.accounting.ad-spend.destroy', $s) }}" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($spends->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">{{ $spends->links() }}</div>
            @endif
        </x-admin.card>
    </div>

</div>
@endsection

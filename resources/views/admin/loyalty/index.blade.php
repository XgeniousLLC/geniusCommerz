@extends('admin.layouts.admin')

@section('title', 'Loyalty Transactions')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.loyalty.settings') }}" class="hover:text-gray-700">Loyalty</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Transactions</li>
</ol>
@endsection

@section('page-header')
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900">Loyalty Transactions</h1>
    <a href="{{ route('admin.loyalty.settings') }}" class="text-sm text-blue-600 hover:underline">← Settings</a>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

    {{-- Transactions table --}}
    <div class="lg:col-span-3">
        <x-admin.card class="mb-5">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <option value="">All Types</option>
                        @foreach(['earned','redeemed','expired','adjusted'] as $t)
                            <option value="{{ $t }}" @selected(request('type') === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <x-admin.button type="submit" variant="secondary">Filter</x-admin.button>
                @if(request()->hasAny(['type','user_id']))
                    <a href="{{ route('admin.loyalty.index') }}" class="text-sm text-gray-500 self-end pb-2 hover:underline">Clear</a>
                @endif
            </form>
        </x-admin.card>

        <x-admin.card>
            @if($transactions->isEmpty())
                <p class="text-center text-gray-500 py-10">No transactions found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                <th class="pb-3 pr-4">Customer</th>
                                <th class="pb-3 pr-4">Type</th>
                                <th class="pb-3 pr-4">Points</th>
                                <th class="pb-3 pr-4">Balance After</th>
                                <th class="pb-3 pr-4">Order</th>
                                <th class="pb-3 pr-4">Description</th>
                                <th class="pb-3">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($transactions as $t)
                            @php
                                $typeStyle = match($t->type) {
                                    'earned'   => 'bg-green-100 text-green-800',
                                    'redeemed' => 'bg-blue-100 text-blue-800',
                                    'expired'  => 'bg-gray-100 text-gray-600',
                                    'adjusted' => 'bg-yellow-100 text-yellow-800',
                                    default    => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 pr-4">
                                    <div class="font-medium text-gray-900">{{ $t->user?->name ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ $t->user?->email }}</div>
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold capitalize {{ $typeStyle }}">
                                        {{ $t->type }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 font-bold {{ $t->points >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                    {{ $t->points >= 0 ? '+' : '' }}{{ number_format($t->points) }}
                                </td>
                                <td class="py-3 pr-4 text-gray-700 font-mono">{{ number_format($t->balance_after) }}</td>
                                <td class="py-3 pr-4">
                                    @if($t->order)
                                        <a href="{{ route('admin.orders.show', $t->order) }}" class="text-blue-600 font-mono text-xs hover:underline">#{{ $t->order->order_number }}</a>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 text-gray-500 text-xs max-w-[160px] truncate">{{ $t->description }}</td>
                                <td class="py-3 text-gray-500 text-xs">{{ $t->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        {{ $transactions->withQueryString()->links() }}
                    </div>
                @endif
            @endif
        </x-admin.card>
    </div>

    {{-- Right: Manual adjust --}}
    <div>
        <x-admin.card>
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Manual Adjustment</h3>
            <form method="POST" action="{{ route('admin.loyalty.adjust') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs text-gray-500 block mb-0.5">User ID</label>
                    <x-admin.input type="number" name="user_id" placeholder="User ID" required />
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-0.5">Points (use - to deduct)</label>
                    <x-admin.input type="number" name="points" placeholder="e.g. 500 or -200" required />
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-0.5">Reason</label>
                    <x-admin.input name="description" placeholder="e.g. Goodwill bonus" required />
                </div>
                <x-admin.button type="submit" class="w-full justify-center text-sm">Apply Adjustment</x-admin.button>
            </form>
        </x-admin.card>
    </div>
</div>
@endsection

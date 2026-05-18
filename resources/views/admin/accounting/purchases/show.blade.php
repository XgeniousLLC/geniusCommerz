@extends('admin.layouts.admin')
@section('title', 'Purchase Order ' . $purchase->reference)
@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ $purchase->reference }}</h1>
            <p class="text-sm text-gray-500">{{ $purchase->supplier_name }} · {{ $purchase->order_date->format('d M Y') }}</p>
        </div>
        <div class="flex gap-2 items-center">
            <span class="px-3 py-1 rounded-full text-sm font-semibold
                {{ ['draft'=>'bg-gray-100 text-gray-600','ordered'=>'bg-blue-100 text-blue-700','partial'=>'bg-yellow-100 text-yellow-700','received'=>'bg-green-100 text-green-700'][$purchase->status] }}">
                {{ ucfirst($purchase->status) }}
            </span>
            <x-admin.button href="{{ route('admin.accounting.purchases.index') }}" variant="outline">← Back</x-admin.button>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <x-admin.card class="p-4 text-center">
            <div class="text-xs text-gray-500 mb-1">Product Cost</div>
            <div class="text-xl font-bold text-gray-900">৳{{ number_format($purchase->totalCost(), 0) }}</div>
        </x-admin.card>
        <x-admin.card class="p-4 text-center">
            <div class="text-xs text-gray-500 mb-1">Shipment Cost</div>
            <div class="text-xl font-bold text-purple-700">৳{{ number_format($purchase->totalShipment(), 0) }}</div>
        </x-admin.card>
        <x-admin.card class="p-4 text-center">
            <div class="text-xs text-gray-500 mb-1">Total Landed Cost</div>
            <div class="text-xl font-bold text-blue-700">৳{{ number_format($purchase->landedCost(), 0) }}</div>
        </x-admin.card>
    </div>

    <x-admin.card>
        <div class="px-5 py-4 border-b border-gray-200 font-semibold text-gray-900 text-sm">Items</div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Product</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600">Variant</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600">Ordered</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600">Unit Cost</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600">Line Total</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-600">Received</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $item->product?->name ?? 'Deleted' }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $item->variant?->label() ?? '—' }}</td>
                        <td class="px-4 py-2 text-right">{{ $item->quantity }}</td>
                        <td class="px-4 py-2 text-right">৳{{ number_format($item->unit_cost, 2) }}</td>
                        <td class="px-4 py-2 text-right font-semibold">৳{{ number_format($item->lineTotal(), 0) }}</td>
                        <td class="px-4 py-2 text-right">
                            <span class="{{ $item->received_qty >= $item->quantity ? 'text-green-600' : 'text-yellow-600' }} font-semibold">
                                {{ $item->received_qty }} / {{ $item->quantity }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>

    @if($purchase->shipments->isNotEmpty())
    <x-admin.card>
        <div class="px-5 py-4 border-b border-gray-200 font-semibold text-gray-900 text-sm">Shipment Costs</div>
        <div class="divide-y divide-gray-100">
            @foreach($purchase->shipments as $s)
            <div class="flex items-center justify-between px-5 py-3">
                <div>
                    <div class="font-medium text-gray-800">{{ $s->description }}</div>
                    <div class="text-xs text-gray-500">{{ $s->shipment_date->format('d M Y') }}</div>
                </div>
                <div class="font-bold text-purple-700">৳{{ number_format($s->amount, 0) }}</div>
            </div>
            @endforeach
        </div>
    </x-admin.card>
    @endif

    @if($purchase->status !== 'received')
    <x-admin.card>
        <div class="px-5 py-4 border-b border-gray-200 font-semibold text-gray-900 text-sm">Update Received Quantities</div>
        <form method="POST" action="{{ route('admin.accounting.purchases.receive', $purchase) }}" class="p-5 space-y-3">
            @csrf @method('PATCH')
            @foreach($purchase->items as $item)
            <div class="flex items-center gap-4">
                <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                <div class="flex-1 text-sm text-gray-800">
                    {{ $item->product?->name }} {{ $item->variant?->label() ? '· '.$item->variant->label() : '' }}
                    <span class="text-xs text-gray-400 ml-1">(ordered: {{ $item->quantity }})</span>
                </div>
                <input type="number" name="items[{{ $loop->index }}][received_qty]" value="{{ $item->received_qty }}"
                    min="0" max="{{ $item->quantity }}"
                    class="w-24 rounded-lg border-gray-300 text-sm px-2 py-1.5 text-right" />
            </div>
            @endforeach
            <x-admin.button type="submit" class="mt-2">Update Receiving</x-admin.button>
        </form>
    </x-admin.card>
    @endif

    <form method="POST" action="{{ route('admin.accounting.purchases.destroy', $purchase) }}" onsubmit="return confirm('Delete this purchase order?')">
        @csrf @method('DELETE')
        <x-admin.button type="submit" variant="outline" class="text-red-600 border-red-200 hover:bg-red-50">Delete Order</x-admin.button>
    </form>
</div>
@endsection

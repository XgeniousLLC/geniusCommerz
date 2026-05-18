@extends('admin.layouts.admin')
@section('title', 'Purchase Orders')
@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900">Purchase Orders</h1>
        <x-admin.button href="{{ route('admin.accounting.purchases.create') }}">+ New Purchase Order</x-admin.button>
    </div>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Reference</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Supplier</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Items</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $po)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono font-semibold text-blue-700">
                            <a href="{{ route('admin.accounting.purchases.show', $po) }}" class="hover:underline">{{ $po->reference }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-800">{{ $po->supplier_name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $po->order_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ $po->items_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ ['draft'=>'bg-gray-100 text-gray-600','ordered'=>'bg-blue-100 text-blue-700','partial'=>'bg-yellow-100 text-yellow-700','received'=>'bg-green-100 text-green-700'][$po->status] }}">
                                {{ ucfirst($po->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.accounting.purchases.show', $po) }}" class="text-blue-600 hover:underline text-xs">View →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No purchase orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">{{ $orders->links() }}</div>
        @endif
    </x-admin.card>
</div>
@endsection

@extends('admin.layouts.admin')

@section('title', 'Refunds')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Refunds</li>
</ol>
@endsection

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Refunds</h1>
    <p class="text-gray-600 mt-1">{{ $refunds->total() }} total requests</p>
</div>
@endsection

@section('content')
<x-admin.card class="mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div class="w-40">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All Statuses</option>
                @foreach(['pending','approved','rejected','processed'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <x-admin.button type="submit" variant="secondary">Filter</x-admin.button>
        @if(request('status'))
            <a href="{{ route('admin.refunds.index') }}" class="text-sm text-gray-500 hover:text-gray-700 self-end pb-2">Clear</a>
        @endif
    </form>
</x-admin.card>

<x-admin.card>
    @if($refunds->isEmpty())
        <p class="text-center text-gray-500 py-10">No refund requests found.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="pb-3 pr-4">Order</th>
                        <th class="pb-3 pr-4">Customer</th>
                        <th class="pb-3 pr-4">Reason</th>
                        <th class="pb-3 pr-4">Amount</th>
                        <th class="pb-3 pr-4">Status</th>
                        <th class="pb-3 pr-4">Requested</th>
                        <th class="pb-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($refunds as $refund)
                    @php
                        $badgeClass = match($refund->status) {
                            'approved'  => 'bg-green-100 text-green-800',
                            'processed' => 'bg-blue-100 text-blue-800',
                            'rejected'  => 'bg-red-100 text-red-700',
                            default     => 'bg-yellow-100 text-yellow-800',
                        };
                        $reasons = \App\Models\Refund::REASONS;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 pr-4">
                            <a href="{{ route('admin.refunds.show', $refund) }}" class="font-mono font-semibold text-blue-600 hover:underline">
                                #{{ $refund->order->order_number }}
                            </a>
                        </td>
                        <td class="py-3 pr-4 text-gray-700">{{ $refund->user->name }}</td>
                        <td class="py-3 pr-4 text-gray-600 capitalize">{{ $reasons[$refund->reason] ?? $refund->reason }}</td>
                        <td class="py-3 pr-4 font-semibold text-gray-900">
                            @if($refund->amount) ৳{{ number_format($refund->amount, 0) }} @else — @endif
                        </td>
                        <td class="py-3 pr-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold capitalize {{ $badgeClass }}">
                                {{ $refund->status }}
                            </span>
                        </td>
                        <td class="py-3 pr-4 text-gray-500">{{ $refund->created_at->format('M d, Y') }}</td>
                        <td class="py-3">
                            <a href="{{ route('admin.refunds.show', $refund) }}" class="text-blue-600 hover:underline text-xs">Review</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($refunds->hasPages())
            <div class="mt-4 pt-4 border-t border-gray-100">
                {{ $refunds->withQueryString()->links() }}
            </div>
        @endif
    @endif
</x-admin.card>
@endsection

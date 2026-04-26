@extends('admin.layouts.admin')

@section('title', 'User – ' . $user->name)

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li><a href="{{ route('admin.users.index') }}" class="hover:text-gray-700">Users</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">{{ $user->name }}</li>
    </ol>
@endsection

@section('page-header')
    <div class="flex items-start justify-between flex-wrap gap-3">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 text-2xl font-bold select-none">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                <p class="text-gray-500 text-sm mt-0.5">
                    {{ $user->email }}{{ $user->phone ? ' · ' . $user->phone : '' }}
                </p>
                <div class="flex items-center gap-2 mt-1">
                    @if($user->is_active)
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                    @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">Inactive</span>
                    @endif
                    <span class="text-xs text-gray-400">Joined {{ $user->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>
        <a href="{{ route('admin.users.edit', $user) }}"
            class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit User
        </a>
    </div>
@endsection

@section('content')
<div class="space-y-6">

{{-- ── KPI strip ── --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
    @foreach([
        ['Total Orders',  $totalOrders,                         'bg-blue-50   text-blue-700'],
        ['Total Spent',   '৳'.number_format($totalSpent,2),    'bg-green-50  text-green-700'],
        ['Avg Order',     '৳'.number_format($avgOrderValue,2), 'bg-indigo-50 text-indigo-700'],
        ['Delivered',     $completedOrders,                     'bg-emerald-50 text-emerald-700'],
        ['Cancelled',     $cancelledOrders,                     'bg-red-50    text-red-600'],
        ['Loyalty Pts',   number_format($loyaltyBalance),       'bg-amber-50  text-amber-700'],
    ] as [$label, $value, $cls])
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-xs text-gray-400 mb-1">{{ $label }}</p>
        <p class="text-xl font-bold {{ explode(' ', $cls)[1] }}">{{ $value }}</p>
    </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-6">

{{-- ── Left sidebar ── --}}
<div class="space-y-5">

    {{-- Contact --}}
    <x-admin.card>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Contact Details</h3>
        <dl class="space-y-2.5 text-sm">
            <div class="flex justify-between gap-2">
                <dt class="text-gray-500 shrink-0">Name</dt>
                <dd class="font-medium text-gray-900 text-right">{{ $user->name }}</dd>
            </div>
            <div class="flex justify-between gap-2">
                <dt class="text-gray-500 shrink-0">Email</dt>
                <dd class="font-medium text-gray-900 text-right break-all">{{ $user->email ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-2">
                <dt class="text-gray-500 shrink-0">Phone</dt>
                <dd class="font-medium text-gray-900">{{ $user->phone ?? '—' }}</dd>
            </div>
            <div class="flex justify-between gap-2">
                <dt class="text-gray-500 shrink-0">Registered</dt>
                <dd class="font-medium text-gray-900">{{ $user->created_at->format('d M Y') }}</dd>
            </div>
            <div class="flex justify-between gap-2">
                <dt class="text-gray-500 shrink-0">Last seen</dt>
                <dd class="text-gray-600">{{ $user->updated_at->diffForHumans() }}</dd>
            </div>
        </dl>
    </x-admin.card>

    {{-- Addresses --}}
    <x-admin.card>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Saved Addresses ({{ $user->addresses->count() }})</h3>
        @forelse($user->addresses as $addr)
        <div class="text-sm border rounded-lg p-3 mb-2 {{ $addr->is_default ? 'border-blue-200 bg-blue-50' : 'border-gray-100' }}">
            @if($addr->is_default)
                <span class="text-xs font-semibold text-blue-600">Default · </span>
            @endif
            <span class="font-medium text-gray-800">{{ $addr->name ?? $user->name }}</span>
            @if(isset($addr->phone))<span class="text-gray-400 text-xs"> · {{ $addr->phone }}</span>@endif
            <p class="text-gray-500 text-xs mt-0.5">
                {{ collect([$addr->address_line ?? null, $addr->city ?? null, $addr->state ?? null])->filter()->implode(', ') }}
            </p>
        </div>
        @empty
        <p class="text-sm text-gray-400">No saved addresses.</p>
        @endforelse
    </x-admin.card>

    {{-- Payment preference --}}
    @if($paymentPreference->isNotEmpty())
    <x-admin.card>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Payment Preferences</h3>
        <ul class="space-y-2">
            @foreach($paymentPreference as $method => $count)
            <li class="flex items-center gap-2 text-sm">
                <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full bg-blue-500"
                         style="width:{{ round(($count / $totalOrders) * 100) }}%"></div>
                </div>
                <span class="text-gray-600 capitalize shrink-0 w-28 truncate">{{ str_replace('_',' ',$method) }}</span>
                <span class="font-semibold text-gray-900 shrink-0">{{ $count }}x</span>
            </li>
            @endforeach
        </ul>
    </x-admin.card>
    @endif

    {{-- Loyalty --}}
    <x-admin.card>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-700">Loyalty Points</h3>
            <span class="text-lg font-bold text-amber-600">{{ number_format($loyaltyBalance) }} pts</span>
        </div>
        @forelse($loyaltyHistory as $pt)
        <div class="flex items-center justify-between text-xs py-1.5 border-b border-gray-50">
            <span class="text-gray-500">{{ $pt->description ?? ($pt->points > 0 ? 'Earned' : 'Redeemed') }}</span>
            <span class="{{ $pt->points > 0 ? 'text-green-600' : 'text-red-500' }} font-medium">
                {{ $pt->points > 0 ? '+' : '' }}{{ $pt->points }}
            </span>
        </div>
        @empty
        <p class="text-xs text-gray-400">No loyalty activity yet.</p>
        @endforelse
    </x-admin.card>

</div>{{-- /left --}}

{{-- ── Main panel ── --}}
<div class="lg:col-span-2 space-y-5">

    {{-- Monthly spend bar chart --}}
    @if($monthlySpend->isNotEmpty())
    <x-admin.card>
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Monthly Spend – Last 12 Months</h3>
        @php $maxSpend = $monthlySpend->max() ?: 1; @endphp
        <div class="flex items-end gap-1 h-28">
            @foreach($monthlySpend as $month => $amount)
            <div class="flex-1 flex flex-col items-center gap-1 group cursor-default"
                 title="{{ $month }}: ৳{{ number_format($amount, 2) }}">
                <div class="w-full rounded-t bg-blue-500 group-hover:bg-blue-600 transition-colors"
                     style="height:{{ max(4, round(($amount / $maxSpend) * 88)) }}px"></div>
                <span class="text-gray-400 text-center leading-none" style="font-size:10px">
                    {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M') }}
                </span>
            </div>
            @endforeach
        </div>
    </x-admin.card>
    @endif

    {{-- Top products --}}
    @if($topProducts->isNotEmpty())
    <x-admin.card>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Most Purchased Products</h3>
        @php $maxQty = $topProducts->max('total_qty') ?: 1; @endphp
        <div class="space-y-2">
            @foreach($topProducts as $item)
            <div class="flex items-center gap-3 text-sm">
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between mb-0.5">
                        <span class="font-medium text-gray-800 truncate">{{ $item->product_name }}</span>
                        <span class="text-gray-500 shrink-0 ml-2">×{{ $item->total_qty }}</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full bg-indigo-500"
                             style="width:{{ round(($item->total_qty / $maxQty) * 100) }}%"></div>
                    </div>
                </div>
                <span class="font-semibold text-gray-900 shrink-0 w-28 text-right">৳{{ number_format($item->total_spent, 2) }}</span>
            </div>
            @endforeach
        </div>
    </x-admin.card>
    @endif

    {{-- Order status summary --}}
    @if($statusCounts->isNotEmpty())
    <x-admin.card>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Order Status Breakdown</h3>
        <div class="flex flex-wrap gap-3">
            @foreach($statusCounts as $status => $cnt)
            <div class="flex items-center gap-2 bg-gray-50 px-3 py-2 rounded-lg text-sm">
                <span class="inline-block w-2 h-2 rounded-full
                    {{ match($status) {
                        'delivered'  => 'bg-green-500',
                        'shipped'    => 'bg-blue-500',
                        'processing' => 'bg-yellow-400',
                        'cancelled'  => 'bg-red-500',
                        default      => 'bg-gray-400'
                    } }}"></span>
                <span class="text-gray-600 capitalize">{{ $status }}</span>
                <span class="font-bold text-gray-900">{{ $cnt }}</span>
            </div>
            @endforeach
        </div>
    </x-admin.card>
    @endif

    {{-- Order history --}}
    <x-admin.card>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-700">Order History</h3>
            <span class="text-xs text-gray-400">{{ $totalOrders }} total</span>
        </div>
        @forelse($orders->take(20) as $order)
        <div class="border border-gray-100 rounded-lg p-3 mb-2 hover:border-gray-200 transition-colors">
            <div class="flex items-start justify-between flex-wrap gap-2">
                <div>
                    <a href="{{ route('admin.orders.show', $order) }}"
                       class="text-sm font-semibold text-blue-600 hover:underline">#{{ $order->order_number }}</a>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $order->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-900">৳{{ number_format($order->total, 2) }}</p>
                    <div class="flex gap-1 mt-0.5 justify-end flex-wrap">
                        <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium
                            {{ match($order->status) {
                                'delivered'  => 'bg-green-100 text-green-700',
                                'shipped'    => 'bg-blue-100 text-blue-700',
                                'processing' => 'bg-yellow-100 text-yellow-700',
                                'cancelled'  => 'bg-red-100 text-red-600',
                                default      => 'bg-gray-100 text-gray-600'
                            } }}">{{ ucfirst($order->status) }}</span>
                        <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium
                            {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-600' }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>
            @if($order->items->isNotEmpty())
            <div class="mt-2 flex flex-wrap gap-1">
                @foreach($order->items->take(3) as $item)
                <span class="text-xs text-gray-500 bg-gray-50 border border-gray-100 px-2 py-0.5 rounded">
                    {{ $item->product_name }} ×{{ $item->quantity }}
                </span>
                @endforeach
                @if($order->items->count() > 3)
                <span class="text-xs text-gray-400">+{{ $order->items->count() - 3 }} more</span>
                @endif
            </div>
            @endif
        </div>
        @empty
        <p class="text-sm text-gray-400 py-4 text-center">No orders yet.</p>
        @endforelse
        @if($orders->count() > 20)
        <p class="text-xs text-gray-400 text-center mt-2">Showing 20 of {{ $orders->count() }} orders.</p>
        @endif
    </x-admin.card>

    {{-- Refunds --}}
    @if($refunds->isNotEmpty())
    <x-admin.card>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Refunds ({{ $refunds->count() }})</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-100">
                <thead>
                    <tr class="text-xs text-gray-400 uppercase">
                        <th class="text-left pb-2 pr-3">Order</th>
                        <th class="text-left pb-2 pr-3">Reason</th>
                        <th class="text-right pb-2 pr-3">Amount</th>
                        <th class="text-right pb-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($refunds as $refund)
                    <tr>
                        <td class="py-2 pr-3">
                            @if($refund->order)
                            <a href="{{ route('admin.orders.show', $refund->order) }}"
                               class="text-blue-600 hover:underline text-xs font-medium">
                                #{{ $refund->order->order_number }}
                            </a>
                            @else <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="py-2 pr-3 text-gray-600 max-w-[180px] truncate">{{ $refund->reason ?? '—' }}</td>
                        <td class="py-2 pr-3 text-right font-medium text-gray-900">৳{{ number_format($refund->amount ?? 0, 2) }}</td>
                        <td class="py-2 text-right">
                            <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium
                                {{ match($refund->status ?? '') {
                                    'approved' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-600',
                                    default    => 'bg-yellow-100 text-yellow-700'
                                } }}">{{ ucfirst($refund->status ?? 'pending') }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>
    @endif

    {{-- Reviews --}}
    @if($reviews->isNotEmpty())
    <x-admin.card>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Product Reviews ({{ $reviews->count() }})</h3>
        <div class="space-y-3">
            @foreach($reviews as $review)
            <div class="border border-gray-100 rounded-lg p-3">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-sm font-medium text-gray-800 truncate">
                        {{ $review->product?->name ?? 'Deleted product' }}
                    </p>
                    <div class="flex gap-0.5 shrink-0 ml-2">
                        @for($i = 1; $i <= 5; $i++)
                        <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                </div>
                @if($review->body)
                <p class="text-xs text-gray-500">{{ Str::limit($review->body, 140) }}</p>
                @endif
                <p class="text-xs text-gray-400 mt-1">{{ $review->created_at->format('d M Y') }}</p>
            </div>
            @endforeach
        </div>
    </x-admin.card>
    @endif

</div>{{-- /main --}}
</div>{{-- /grid --}}
</div>
@endsection

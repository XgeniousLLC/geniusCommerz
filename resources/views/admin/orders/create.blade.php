@extends('admin.layouts.admin')

@section('title', 'Create Order')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.orders.index') }}" class="hover:text-gray-700">Orders</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Create Order</li>
</ol>
@endsection

@section('page-header')
<h1 class="text-2xl font-bold text-gray-900">Create Order</h1>
@endsection

@push('styles')
<style>
    #order-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
    @media (min-width: 1024px) { #order-grid { grid-template-columns: 1fr 340px; } }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('admin.orders.store') }}" id="order-form"
      x-data="orderCreate()" @submit.prevent="submitOrder">
    @csrf

    <div id="order-grid">

        {{-- ── LEFT COLUMN ── --}}
        <div class="space-y-6">

            {{-- Customer --}}
            <x-admin.card>
                <h2 class="text-base font-semibold text-gray-900 mb-4">Customer</h2>

                {{-- Customer search --}}
                <div class="relative mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search existing customer</label>
                    <div style="position:relative">
                        <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#9ca3af;pointer-events:none;flex-shrink:0"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" x-model="customerQuery"
                               @input.debounce.300ms="searchCustomers"
                               @keydown.escape="customerResults = []"
                               placeholder="Search by name, email or phone…"
                               autocomplete="off"
                               style="padding-left:36px;padding-right:36px"
                               class="w-full h-10 rounded-lg border border-gray-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400">
                        <svg x-show="searching" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#3b82f6;flex-shrink:0" class="animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <template x-if="selectedCustomer">
                            <button type="button" @click="clearCustomer"
                                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;padding:0;cursor:pointer;color:#9ca3af">
                                <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </template>
                    </div>

                    {{-- Selected customer pill --}}
                    <template x-if="selectedCustomer">
                        <div class="mt-2 flex items-center gap-3 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="w-7 h-7 rounded-full bg-blue-200 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-blue-900" x-text="selectedCustomer.name"></div>
                                <div class="text-xs text-blue-600 truncate">
                                    <span x-text="selectedCustomer.email"></span>
                                    <template x-if="selectedCustomer.phone">
                                        <span x-text="' · ' + selectedCustomer.phone"></span>
                                    </template>
                                </div>
                            </div>
                            <input type="hidden" name="user_id" :value="selectedCustomer.id">
                        </div>
                    </template>

                    {{-- Search results dropdown --}}
                    <template x-if="customerResults.length > 0">
                        <div class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                            <template x-for="customer in customerResults" :key="customer.id">
                                <button type="button" @click="selectCustomer(customer)"
                                        class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-blue-50 text-left transition-colors border-b border-gray-50 last:border-0">
                                    <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900" x-text="customer.name"></div>
                                        <div class="text-xs text-gray-400 truncate">
                                            <span x-text="customer.email"></span>
                                            <template x-if="customer.phone">
                                                <span x-text="' · ' + customer.phone"></span>
                                            </template>
                                        </div>
                                    </div>
                                </button>
                            </template>
                            <div class="px-4 py-2 border-t border-gray-100">
                                <button type="button" @click="customerResults = []"
                                        class="text-xs text-gray-400 hover:text-gray-600">Enter manually instead</button>
                            </div>
                        </div>
                    </template>

                    <template x-if="customerQuery.length >= 2 && customerResults.length === 0 && !searching && !selectedCustomer">
                        <p class="mt-1.5 text-xs text-gray-400">No customers found — fill in details below.</p>
                    </template>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">Customer Details</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                            <input type="text" name="customer_name" x-model="customerName"
                                   placeholder="Full name" required
                                   class="w-full h-10 rounded-lg border border-gray-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400">
                            @error('customer_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="text" name="customer_phone" x-model="customerPhone"
                                   placeholder="+880 1xxx-xxxxxx"
                                   class="w-full h-10 rounded-lg border border-gray-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="customer_email" x-model="customerEmail"
                                   placeholder="optional@email.com"
                                   class="w-full h-10 rounded-lg border border-gray-300 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400">
                        </div>
                    </div>
                </div>
            </x-admin.card>

            {{-- Order Items --}}
            <x-admin.card>
                <h2 class="text-base font-semibold text-gray-900 mb-4">Order Items</h2>

                <div class="relative mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Add Product</label>
                    <div style="position:relative">
                        <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#9ca3af;pointer-events:none;flex-shrink:0"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts"
                               @keydown.escape="searchResults = []"
                               placeholder="Search by name or SKU…"
                               autocomplete="off"
                               style="padding-left:36px;padding-right:36px"
                               class="w-full h-10 rounded-lg border border-gray-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400">
                        <svg x-show="searchingProducts" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#3b82f6;flex-shrink:0" class="animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </div>

                    <template x-if="searchingProducts">
                        <div class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                            <template x-for="n in [1,2,3]" :key="n">
                                <div class="flex items-center gap-3 px-4 py-2.5 border-b border-gray-50 last:border-0">
                                    <div class="shrink-0 rounded-lg bg-gray-200 animate-pulse" style="width:60px;height:60px"></div>
                                    <div class="flex-1 space-y-2">
                                        <div class="h-3 bg-gray-200 rounded animate-pulse w-3/4"></div>
                                        <div class="h-2.5 bg-gray-100 rounded animate-pulse w-1/3"></div>
                                    </div>
                                    <div class="h-3 bg-gray-200 rounded animate-pulse w-14 shrink-0"></div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="searchResults.length > 0 && !searchingProducts">
                        <div class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                            <template x-for="product in searchResults" :key="product.id">
                                <button type="button" @click="addItem(product)"
                                        class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-blue-50 text-left transition-colors border-b border-gray-50 last:border-0">
                                    <template x-if="product.thumb_url">
                                        <img :src="product.thumb_url" style="width:60px;height:60px;object-fit:cover;flex-shrink:0;border-radius:8px;border:1px solid #e5e7eb">
                                    </template>
                                    <template x-if="!product.thumb_url">
                                        <div style="width:60px;height:60px;flex-shrink:0;border-radius:8px;background:#f3f4f6;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center">
                                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </template>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900 truncate" x-text="product.name"></div>
                                        <div class="text-xs text-gray-400" x-text="product.sku ? 'SKU: ' + product.sku : ''"></div>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-700 shrink-0" x-text="'৳' + product.price.toFixed(2)"></div>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>

                <template x-if="items.length > 0">
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-3 py-2.5 text-center text-xs font-medium text-gray-500 uppercase w-24">Qty</th>
                                    <th class="px-3 py-2.5 text-right text-xs font-medium text-gray-500 uppercase w-28">Price</th>
                                    <th class="px-3 py-2.5 text-right text-xs font-medium text-gray-500 uppercase w-28">Total</th>
                                    <th class="w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(item, index) in items" :key="item._key">
                                    <tr>
                                        <td class="px-3 py-2.5">
                                            <div class="font-medium text-gray-900" x-text="item.product_name"></div>
                                            <div class="text-xs text-gray-400" x-text="item.sku ? 'SKU: ' + item.sku : ''"></div>
                                            <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id">
                                            <input type="hidden" :name="'items[' + index + '][product_name]'" :value="item.product_name">
                                            <input type="hidden" :name="'items[' + index + '][sku]'" :value="item.sku">
                                            <input type="hidden" :name="'items[' + index + '][unit_price]'" :value="item.unit_price">
                                            <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <input type="number" x-model.number="item.quantity" @input="recalc"
                                                   min="1" step="1"
                                                   class="w-20 text-center rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 mx-auto block">
                                        </td>
                                        <td class="px-3 py-2.5">
                                            <input type="number" x-model.number="item.unit_price" @input="recalc"
                                                   min="0" step="0.01"
                                                   class="w-full text-right rounded-lg border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                                        </td>
                                        <td class="px-3 py-2.5 text-right font-medium text-gray-900"
                                            x-text="'৳' + (item.unit_price * item.quantity).toFixed(2)"></td>
                                        <td class="px-2 py-2.5">
                                            <button type="button" @click="removeItem(index)"
                                                    class="text-gray-400 hover:text-red-500 transition-colors p-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </template>

                <template x-if="items.length === 0">
                    <div class="border-2 border-dashed border-gray-200 rounded-xl py-8 text-center text-sm text-gray-400">
                        Search for a product above to add items
                    </div>
                </template>

                @error('items')<p class="text-red-500 text-xs mt-2">{{ $message }}</p>@enderror
            </x-admin.card>

            {{-- Notes --}}
            <x-admin.card>
                <h2 class="text-base font-semibold text-gray-900 mb-4">Notes</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Customer Note</label>
                        <textarea name="notes" rows="2"
                            placeholder="Visible to customer…"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">{{ old('notes') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Admin Note <span class="text-xs font-normal text-gray-400">(internal only)</span></label>
                        <textarea name="admin_note" rows="2"
                            placeholder="e.g. Ordered via WhatsApp, confirmed on call…"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-yellow-50 focus:outline-none focus:ring-2 focus:ring-yellow-200">{{ old('admin_note') }}</textarea>
                    </div>
                </div>
            </x-admin.card>

        </div>

        {{-- ── RIGHT COLUMN ── --}}
        <div class="space-y-6">

            {{-- Order Source --}}
            <x-admin.card>
                <h2 class="text-base font-semibold text-gray-900 mb-4">Order Source</h2>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                    @foreach(\App\Models\Order::SOURCES as $value => $label)
                    <label @click="orderSource = '{{ $value }}'" style="cursor:pointer;display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;border-width:2px;border-style:solid;transition:border-color .15s,background .15s"
                           :style="orderSource === '{{ $value }}'
                               ? 'border-color:#3b82f6;background:#eff6ff'
                               : 'border-color:#e5e7eb;background:#fff'">
                        <input type="radio" name="source" value="{{ $value }}"
                               x-model="orderSource"
                               style="display:none">
                        {{-- Custom radio circle --}}
                        <span style="width:18px;height:18px;border-radius:50%;border-width:2px;border-style:solid;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:border-color .15s"
                              :style="orderSource === '{{ $value }}'
                                  ? 'border-color:#3b82f6'
                                  : 'border-color:#9ca3af'">
                            <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;transition:opacity .15s"
                                  :style="orderSource === '{{ $value }}' ? 'opacity:1' : 'opacity:0'"></span>
                        </span>
                        <span style="font-size:0.875rem;font-weight:500;transition:color .15s"
                              :style="orderSource === '{{ $value }}' ? 'color:#1d4ed8' : 'color:#374151'">
                            {{ $label }}
                        </span>
                    </label>
                    @endforeach
                </div>
                @error('source')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </x-admin.card>

            {{-- Payment --}}
            <x-admin.card>
                <h2 class="text-base font-semibold text-gray-900 mb-4">Payment</h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <x-admin.select name="payment_method">
                            <option value="">— Select —</option>
                            @foreach(['Cash on Delivery','bKash','Nagad','Rocket','Bank Transfer','Card','Other'] as $m)
                                <option value="{{ $m }}" {{ old('payment_method') === $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </x-admin.select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                        <x-admin.select name="payment_status">
                            @foreach(\App\Models\Order::PAYMENT_STATUSES as $s)
                                <option value="{{ $s }}" {{ old('payment_status', 'unpaid') === $s ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $s)) }}
                                </option>
                            @endforeach
                        </x-admin.select>
                    </div>
                </div>
            </x-admin.card>

            {{-- Order Status --}}
            <x-admin.card>
                <h2 class="text-base font-semibold text-gray-900 mb-3">Order Status</h2>
                <x-admin.select name="status">
                    @foreach(\App\Models\Order::STATUSES as $s)
                        <option value="{{ $s }}" {{ old('status', 'pending') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </x-admin.select>
            </x-admin.card>

            {{-- Summary --}}
            <x-admin.card>
                <h2 class="text-base font-semibold text-gray-900 mb-4">Summary</h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Cost (৳)</label>
                        <x-admin.input type="number" name="shipping_cost"
                            value="{{ old('shipping_cost', 0) }}" min="0" step="0.01"
                            x-model.number="shippingCost" @input="recalc" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Discount (৳)</label>
                        <x-admin.input type="number" name="discount_amount"
                            value="{{ old('discount_amount', 0) }}" min="0" step="0.01"
                            x-model.number="discountAmount" @input="recalc" />
                    </div>

                    <div class="pt-3 border-t border-gray-100 space-y-1.5 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span x-text="'৳' + subtotal.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping</span>
                            <span x-text="'৳' + shippingCost.toFixed(2)"></span>
                        </div>
                        <template x-if="discountAmount > 0">
                            <div class="flex justify-between text-green-600">
                                <span>Discount</span>
                                <span x-text="'−৳' + discountAmount.toFixed(2)"></span>
                            </div>
                        </template>
                        <div class="flex justify-between font-bold text-gray-900 text-base pt-1 border-t border-gray-200">
                            <span>Total</span>
                            <span x-text="'৳' + total.toFixed(2)"></span>
                        </div>
                    </div>
                </div>
            </x-admin.card>

            <div class="space-y-2">
                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700
                           text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                    Create Order
                </button>
                <a href="{{ route('admin.orders.index') }}"
                   style="display:flex;align-items:center;justify-content:center;padding:10px 16px;border:1px solid #d1d5db;border-radius:12px;font-size:0.875rem;color:#374151;text-decoration:none;background:#fff;transition:background .15s"
                   onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                    Cancel
                </a>
            </div>

        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function orderCreate() {
    return {
        // customer
        customerQuery:   '',
        customerResults: [],
        selectedCustomer: null,
        searching:       false,
        customerName:    '{{ old('customer_name') }}',
        customerPhone:   '{{ old('customer_phone') }}',
        customerEmail:   '{{ old('customer_email') }}',

        // products
        items:             [],
        searchQuery:       '',
        searchResults:     [],
        searchingProducts: false,

        // source
        orderSource: '{{ old('source', 'whatsapp') }}',

        // totals
        subtotal:       0,
        shippingCost:   {{ old('shipping_cost', 0) }},
        discountAmount: {{ old('discount_amount', 0) }},
        total:          0,
        _keyCounter:    0,

        async searchCustomers() {
            if (this.customerQuery.trim().length < 2) { this.customerResults = []; return; }
            this.searching = true;
            const res = await fetch(`{{ route('admin.orders.customers.search') }}?q=` + encodeURIComponent(this.customerQuery));
            this.customerResults = await res.json();
            this.searching = false;
        },

        selectCustomer(customer) {
            this.selectedCustomer = customer;
            this.customerName     = customer.name;
            this.customerPhone    = customer.phone ?? '';
            this.customerEmail    = customer.email ?? '';
            this.customerQuery    = customer.name;
            this.customerResults  = [];
        },

        clearCustomer() {
            this.selectedCustomer = null;
            this.customerQuery    = '';
            this.customerName     = '';
            this.customerPhone    = '';
            this.customerEmail    = '';
        },

        async searchProducts() {
            if (this.searchQuery.trim().length < 1) { this.searchResults = []; return; }
            this.searchingProducts = true;
            const res = await fetch(`{{ route('admin.orders.products.search') }}?q=` + encodeURIComponent(this.searchQuery));
            this.searchResults = await res.json();
            this.searchingProducts = false;
        },

        addItem(product) {
            const existing = this.items.find(i => i.product_id === product.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.items.push({
                    _key:         ++this._keyCounter,
                    product_id:   product.id,
                    product_name: product.name,
                    sku:          product.sku ?? '',
                    unit_price:   product.price,
                    quantity:     1,
                });
            }
            this.searchQuery   = '';
            this.searchResults = [];
            this.recalc();
        },

        removeItem(index) {
            this.items.splice(index, 1);
            this.recalc();
        },

        recalc() {
            this.subtotal = this.items.reduce((sum, i) => sum + (i.unit_price * i.quantity), 0);
            this.total    = Math.max(0, this.subtotal + this.shippingCost - this.discountAmount);
        },

        submitOrder() {
            if (this.items.length === 0) {
                alert('Please add at least one product.');
                return;
            }
            this.$el.submit();
        },
    };
}
</script>
@endpush

@extends('admin.layouts.admin')
@section('title', 'New Purchase Order')
@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900">New Purchase Order</h1>
        <x-admin.button href="{{ route('admin.accounting.purchases.index') }}" variant="outline">← Back</x-admin.button>
    </div>

    <form method="POST" action="{{ route('admin.accounting.purchases.store') }}" x-data="purchaseForm()">
        @csrf

        <x-admin.card class="mb-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Order Details</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Supplier Name <span class="text-red-500">*</span></label>
                    <input type="text" name="supplier_name" value="{{ old('supplier_name') }}" required
                        class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:border-blue-500 focus:ring-blue-500" />
                    @error('supplier_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Order Date <span class="text-red-500">*</span></label>
                    <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required
                        class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2 focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
                </div>
            </div>
        </x-admin.card>

        <x-admin.card class="mb-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Items</h3>
                <button type="button" @click="addItem()" class="text-xs text-blue-600 hover:underline font-semibold">+ Add Item</button>
            </div>
            @error('items')<p class="text-xs text-red-500 mb-2">{{ $message }}</p>@enderror

            <template x-for="(item, idx) in items" :key="idx">
                <div class="grid grid-cols-12 gap-2 mb-3 items-start">
                    <div class="col-span-5">
                        <label class="block text-xs text-gray-500 mb-1">Product</label>
                        <select :name="`items[${idx}][product_id]`" x-model="item.product_id" @change="loadVariants(item)" required
                            class="w-full rounded-lg border border-gray-300 text-sm px-2 py-2 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select product</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}" data-variants="{{ json_encode($p->variants->map(fn($v)=>['id'=>$v->id,'label'=>$v->label()])->values()) }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs text-gray-500 mb-1">Variant</label>
                        <select :name="`items[${idx}][variant_id]`" x-model="item.variant_id"
                            class="w-full rounded-lg border border-gray-300 text-sm px-2 py-2 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">No variant</option>
                            <template x-for="v in item.variants" :key="v.id">
                                <option :value="v.id" x-text="v.label"></option>
                            </template>
                        </select>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs text-gray-500 mb-1">Qty</label>
                        <input type="number" :name="`items[${idx}][quantity]`" x-model.number="item.quantity" min="1" required
                            class="w-full rounded-lg border border-gray-300 text-sm px-2 py-2 focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Unit Cost (৳)</label>
                        <input type="number" :name="`items[${idx}][unit_cost]`" x-model.number="item.unit_cost" min="0" step="0.01" required
                            class="w-full rounded-lg border border-gray-300 text-sm px-2 py-2 focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                    <div class="col-span-1 pt-6">
                        <button type="button" @click="items.splice(idx,1)" class="text-red-400 hover:text-red-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </template>
        </x-admin.card>

        <x-admin.card class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700">Shipment / Import Costs</h3>
                <button type="button" @click="addShipment()" class="text-xs text-blue-600 hover:underline font-semibold">+ Add Cost</button>
            </div>
            <template x-for="(s, idx) in shipments" :key="idx">
                <div class="grid grid-cols-12 gap-2 mb-3 items-end">
                    <div class="col-span-5">
                        <label class="block text-xs text-gray-500 mb-1">Description</label>
                        <input type="text" :name="`shipments[${idx}][description]`" x-model="s.description" placeholder="e.g. Air freight" required
                            class="w-full rounded-lg border border-gray-300 text-sm px-2 py-2" />
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs text-gray-500 mb-1">Amount (৳)</label>
                        <input type="number" :name="`shipments[${idx}][amount]`" x-model.number="s.amount" min="0" step="0.01" required
                            class="w-full rounded-lg border border-gray-300 text-sm px-2 py-2" />
                    </div>
                    <div class="col-span-3">
                        <label class="block text-xs text-gray-500 mb-1">Date</label>
                        <input type="date" :name="`shipments[${idx}][shipment_date]`" x-model="s.shipment_date" required
                            class="w-full rounded-lg border border-gray-300 text-sm px-2 py-2" />
                    </div>
                    <div class="col-span-1">
                        <button type="button" @click="shipments.splice(idx,1)" class="text-red-400 hover:text-red-600 mb-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            </template>
            <p x-show="shipments.length === 0" class="text-xs text-gray-400">No shipment costs added.</p>
        </x-admin.card>

        <div class="flex gap-3">
            <x-admin.button type="submit">Save Purchase Order</x-admin.button>
            <x-admin.button href="{{ route('admin.accounting.purchases.index') }}" variant="outline">Cancel</x-admin.button>
        </div>
    </form>
</div>

<script>
function purchaseForm() {
    return {
        items: [{ product_id: '', variant_id: '', quantity: 1, unit_cost: 0, variants: [] }],
        shipments: [],
        addItem() { this.items.push({ product_id: '', variant_id: '', quantity: 1, unit_cost: 0, variants: [] }); },
        addShipment() { this.shipments.push({ description: '', amount: 0, shipment_date: '{{ date('Y-m-d') }}' }); },
        loadVariants(item) {
            const opt = document.querySelector(`option[value="${item.product_id}"]`);
            item.variant_id = '';
            item.variants = opt ? JSON.parse(opt.dataset.variants || '[]') : [];
        },
    };
}
</script>
@endsection

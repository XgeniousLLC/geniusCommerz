@extends('admin.layouts.admin')

@section('title', 'New Purchase Order')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">New Purchase Order</h2>
        <div class="sub">Add a new order from a supplier</div>
    </div>
    <a href="{{ route('admin.accounting.purchases.index') }}" class="btn btn-outline">
        <span class="ico" data-ico="arrowLeft" style="width:16px;height:16px"></span>Back
    </a>
</div>

<form method="POST" action="{{ route('admin.accounting.purchases.store') }}" x-data="purchaseForm()" style="max-width:900px">
    @csrf

    <div class="col-gap" style="--gap:18px">

        <div class="card pad">
            <div class="card-head">
                <span class="tile sm t-info"><span class="ico" data-ico="doc" style="width:18px;height:18px"></span></span>
                <div class="ct"><h3>Order Details</h3></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:16px">
                <div class="field">
                    <span class="lbl">Supplier Name <span style="color:var(--danger)">*</span></span>
                    <input class="input" type="text" name="supplier_name" value="{{ old('supplier_name') }}" required>
                    @error('supplier_name')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <span class="lbl">Order Date <span style="color:var(--danger)">*</span></span>
                    <input class="input" type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required>
                </div>
                <div class="field" style="grid-column:1/-1">
                    <span class="lbl">Notes</span>
                    <textarea class="input" name="notes" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="card pad">
            <div class="between" style="margin-bottom:16px">
                <div class="card-head" style="margin:0">
                    <span class="tile sm t-accent"><span class="ico" data-ico="package" style="width:18px;height:18px"></span></span>
                    <div class="ct"><h3>Items</h3></div>
                </div>
                <button type="button" @click="addItem()" class="btn btn-sm btn-outline">
                    <span class="ico" data-ico="plus" style="width:15px;height:15px"></span>Add Item
                </button>
            </div>
            @error('items')<p class="hint" style="color:var(--danger);margin-bottom:10px">{{ $message }}</p>@enderror

            <template x-for="(item, idx) in items" :key="idx">
                <div style="display:grid;grid-template-columns:2fr 1.2fr 80px 120px 40px;gap:10px;margin-bottom:12px;align-items:start">
                    <div class="field" style="margin:0">
                        <span class="lbl">Product</span>
                        <select class="input" :name="`items[${idx}][product_id]`" x-model="item.product_id" @change="loadVariants(item)" required>
                            <option value="">Select product</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}"
                                    data-variants="{{ json_encode($p->variants->map(fn($v)=>['id'=>$v->id,'label'=>$v->label()])->values()) }}">
                                {{ $p->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field" style="margin:0">
                        <span class="lbl">Variant</span>
                        <select class="input" :name="`items[${idx}][variant_id]`" x-model="item.variant_id">
                            <option value="">No variant</option>
                            <template x-for="v in item.variants" :key="v.id">
                                <option :value="v.id" x-text="v.label"></option>
                            </template>
                        </select>
                    </div>
                    <div class="field" style="margin:0">
                        <span class="lbl">Qty</span>
                        <input class="input" type="number" :name="`items[${idx}][quantity]`" x-model.number="item.quantity" min="1" required>
                    </div>
                    <div class="field" style="margin:0">
                        <span class="lbl">Unit Cost (৳)</span>
                        <input class="input" type="number" :name="`items[${idx}][unit_cost]`" x-model.number="item.unit_cost" min="0" step="0.01" required>
                    </div>
                    <div style="padding-top:26px">
                        <button type="button" @click="items.splice(idx,1)" class="icon-btn">
                            <span class="ico" data-ico="x" style="width:16px;height:16px"></span>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <div class="card pad">
            <div class="between" style="margin-bottom:16px">
                <div class="card-head" style="margin:0">
                    <span class="tile sm t-violet"><span class="ico" data-ico="truck" style="width:18px;height:18px"></span></span>
                    <div class="ct"><h3>Shipment / Import Costs</h3></div>
                </div>
                <button type="button" @click="addShipment()" class="btn btn-sm btn-outline">
                    <span class="ico" data-ico="plus" style="width:15px;height:15px"></span>Add Cost
                </button>
            </div>

            <template x-for="(s, idx) in shipments" :key="idx">
                <div style="display:grid;grid-template-columns:2fr 1fr 1.2fr 40px;gap:10px;margin-bottom:12px;align-items:start">
                    <div class="field" style="margin:0">
                        <span class="lbl">Description</span>
                        <input class="input" type="text" :name="`shipments[${idx}][description]`" x-model="s.description" placeholder="e.g. Air freight" required>
                    </div>
                    <div class="field" style="margin:0">
                        <span class="lbl">Amount (৳)</span>
                        <input class="input" type="number" :name="`shipments[${idx}][amount]`" x-model.number="s.amount" min="0" step="0.01" required>
                    </div>
                    <div class="field" style="margin:0">
                        <span class="lbl">Date</span>
                        <input class="input" type="date" :name="`shipments[${idx}][shipment_date]`" x-model="s.shipment_date" required>
                    </div>
                    <div style="padding-top:26px">
                        <button type="button" @click="shipments.splice(idx,1)" class="icon-btn">
                            <span class="ico" data-ico="x" style="width:16px;height:16px"></span>
                        </button>
                    </div>
                </div>
            </template>
            <p x-show="shipments.length === 0" class="faint" style="font-size:13px">No shipment costs added.</p>
        </div>

        <div class="row" style="gap:10px">
            <button type="submit" class="btn btn-primary">Save Purchase Order</button>
            <a href="{{ route('admin.accounting.purchases.index') }}" class="btn btn-outline">Cancel</a>
        </div>

    </div>
</form>

@push('scripts')
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
@endpush

@endsection

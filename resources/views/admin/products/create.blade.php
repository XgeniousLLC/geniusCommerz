@extends('admin.layouts.admin')

@section('title', 'Add Product')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
.ql-toolbar { border-radius: var(--radius-ctl) var(--radius-ctl) 0 0; border-color: var(--border-strong) !important; background: var(--surface-2); }
.ql-container { border-radius: 0 0 var(--radius-ctl) var(--radius-ctl); border-color: var(--border-strong) !important; font-size:14px; }
.ql-editor { min-height: 160px; background: var(--surface); color: var(--text); }
[data-theme="dark"] .ql-toolbar .ql-stroke { stroke: var(--text-muted); }
[data-theme="dark"] .ql-toolbar .ql-fill { fill: var(--text-muted); }
[data-theme="dark"] .ql-picker-label { color: var(--text-muted); }
[data-theme="dark"] .ql-picker-options { background: var(--surface-2); border-color: var(--border); }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('admin.products.store') }}" id="product-form">
@csrf

{{-- Page head --}}
<div class="row" style="gap:14px;margin-bottom:22px;flex-wrap:wrap">
    <a class="icon-btn" href="{{ route('admin.products.index') }}" style="width:40px;height:40px">
        <span class="ico" data-ico="chevLeft"></span>
    </a>
    <div class="grow" style="min-width:180px">
        <div class="breadcrumb">
            <a href="{{ route('admin.products.index') }}">Products</a> / Add
        </div>
        <h2 class="display" style="font-size:24px;letter-spacing:-0.03em">Add Product</h2>
    </div>
    <div class="row" style="gap:10px">
        <button type="submit" name="_action" value="draft" class="btn btn-outline">Save draft</button>
        <button type="submit" name="_action" value="publish" class="btn btn-primary">
            <span class="ico" data-ico="check" style="width:18px;height:18px"></span>Publish
        </button>
    </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,2fr) minmax(0,1fr);gap:18px;align-items:start" class="grid-2">

{{-- LEFT COLUMN --}}
<div class="col-gap">

    {{-- General --}}
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-accent"><span class="ico" data-ico="doc" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>General</h3></div>
        </div>

        <div class="field" style="margin-bottom:14px">
            <span class="lbl">Product name <span class="req">*</span></span>
            <input class="input" type="text" name="name" id="prod-name" value="{{ old('name') }}" required>
            @error('name')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
        </div>

        <div class="field" style="margin-bottom:14px">
            <span class="lbl">Slug <span class="req">*</span></span>
            <input class="input mono" type="text" name="slug" id="prod-slug" value="{{ old('slug') }}" required>
            @error('slug')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
        </div>

        <div class="field" style="margin-bottom:14px">
            <span class="lbl">Short description</span>
            <textarea class="textarea" name="short_description" rows="2">{{ old('short_description') }}</textarea>
        </div>

        <div class="field" x-data="aiDescGen()">
            <div class="between" style="margin-bottom:8px">
                <span class="lbl">Description</span>
                <button type="button" @click="open = !open" class="btn btn-soft btn-sm">
                    <span class="ico" data-ico="spark" style="width:15px;height:15px"></span>Generate with AI
                </button>
            </div>
            <div x-show="open" x-transition style="margin-bottom:12px;border:1px solid var(--border);border-radius:var(--radius-card);padding:16px;background:var(--surface-2)">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                    <div class="field">
                        <span class="lbl">Tone</span>
                        <div class="select-wrap">
                            <select class="select" x-model="tone">
                                <option value="professional">Professional</option>
                                <option value="casual">Casual</option>
                                <option value="enthusiastic">Enthusiastic</option>
                                <option value="minimal">Minimal</option>
                            </select>
                        </div>
                    </div>
                    <div class="field">
                        <span class="lbl">Length</span>
                        <div class="select-wrap">
                            <select class="select" x-model="length">
                                <option value="short">Short</option>
                                <option value="medium">Medium</option>
                                <option value="long">Long</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="field" style="margin-bottom:12px">
                    <span class="lbl">Key attributes <span style="color:var(--text-faint);font-weight:400">(optional)</span></span>
                    <input class="input" type="text" x-model="attributes" placeholder="e.g. waterproof, bluetooth 5.0">
                </div>
                <div class="row" style="gap:8px">
                    <button type="button" @click="generate()" :disabled="loading" class="btn btn-primary btn-sm">
                        <span x-show="!loading">Generate</span>
                        <span x-show="loading">Generating…</span>
                    </button>
                    <span x-show="error" x-text="error" style="font-size:12px;color:var(--danger)"></span>
                </div>
            </div>
            <div id="description-editor" style="border-radius:var(--radius-ctl)"></div>
            <textarea name="description" id="description-input" style="display:none">{{ old('description') }}</textarea>
            @error('description')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
        </div>
    </div>

    {{-- Warranty & Returns --}}
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-teal"><span class="ico" data-ico="shield" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Warranty &amp; Returns</h3></div>
        </div>
        <div class="field" style="margin-bottom:14px">
            <span class="lbl">Warranty</span>
            <input class="input" type="text" name="warranty" value="{{ old('warranty') }}" placeholder="e.g. 1 year manufacturer warranty">
            <span class="faint" style="font-size:12px">Leave blank to hide warranty info on the product page.</span>
        </div>
        <div class="field">
            <span class="lbl">Return policy</span>
            <textarea class="textarea" name="return_policy" rows="3" placeholder="Leave blank to use the global return policy from Settings → Storefront">{{ old('return_policy') }}</textarea>
            <span class="faint" style="font-size:12px">Overrides the global return policy for this product only.</span>
        </div>
    </div>

    {{-- Specifications --}}
    @php $oldSpecs = old('specifications', '[]'); @endphp
    <div class="card pad" x-data="specEditor({{ $oldSpecs }})">
        <div class="card-head">
            <span class="tile sm t-warning"><span class="ico" data-ico="list" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Specifications</h3></div>
            <button type="button" @click="add()" class="link-btn head-action">
                <span class="ico" data-ico="plus" style="width:14px;height:14px"></span>Add row
            </button>
        </div>
        <div class="stack" style="gap:8px">
            <template x-for="(row, i) in rows" :key="i">
                <div class="row">
                    <input type="text" class="input" x-model="row.key" placeholder="e.g. Material" style="flex:1">
                    <input type="text" class="input" x-model="row.value" placeholder="e.g. Cotton 100%" style="flex:1">
                    <button type="button" @click="remove(i)" class="icon-btn danger">
                        <span class="ico" data-ico="x" style="width:15px;height:15px"></span>
                    </button>
                </div>
            </template>
            <p x-show="rows.length === 0" style="text-align:center;padding:16px;color:var(--text-faint);font-size:13.5px;border:1.5px dashed var(--border-strong);border-radius:var(--radius-card)">
                No specifications added yet.
            </p>
        </div>
        <input type="hidden" name="specifications" :value="JSON.stringify(rows.filter(r => r.key.trim()))">
    </div>

    {{-- Pricing / Variants --}}
    <div class="card pad" x-data="variantBuilder()" x-init="init()">
        <div class="card-head">
            <span class="tile sm t-violet"><span class="ico" data-ico="sliders" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Pricing &amp; Variants</h3></div>
            <label class="row head-action" style="gap:8px;cursor:pointer;font-weight:700;font-size:13px">
                <input type="checkbox" class="check" name="has_variants" value="1" id="has-variants"
                    x-model="hasVariants" {{ old('has_variants') ? 'checked' : '' }}>
                Has variants
            </label>
        </div>

        {{-- Simple product pricing --}}
        <div x-show="!hasVariants">
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:14px" class="grid-2">
                <div class="field">
                    <span class="lbl">SKU</span>
                    <input class="input mono" type="text" name="sku" id="prod-sku" value="{{ old('sku') }}">
                </div>
                <div class="field">
                    <span class="lbl">Price <span class="req">*</span></span>
                    <div class="input-prefix">
                        <span>৳</span>
                        <input class="input" type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" style="padding-left:26px">
                    </div>
                    @error('price')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <span class="lbl">Compare at</span>
                    <div class="input-prefix">
                        <span>৳</span>
                        <input class="input" type="number" name="compare_at_price" value="{{ old('compare_at_price') }}" step="0.01" min="0" style="padding-left:26px">
                    </div>
                </div>
                <div class="field">
                    <span class="lbl">Cost price</span>
                    <div class="input-prefix">
                        <span>৳</span>
                        <input class="input" type="number" name="cost_price" value="{{ old('cost_price') }}" step="0.01" min="0" style="padding-left:26px">
                    </div>
                </div>
            </div>
            <div class="row" style="gap:14px;flex-wrap:wrap">
                <div class="field" style="width:120px">
                    <span class="lbl">Weight (kg)</span>
                    <input class="input" type="number" name="weight" value="{{ old('weight') }}" step="0.001" min="0">
                </div>
                <div class="field" style="width:120px">
                    <span class="lbl">Stock qty</span>
                    <input class="input" type="number" name="stock_qty" value="{{ old('stock_qty') }}" min="0">
                </div>
                <label class="row" style="gap:8px;cursor:pointer;padding-top:22px;font-weight:600;font-size:13.5px">
                    <input type="hidden" name="shipping_included" value="0">
                    <input class="check" type="checkbox" name="shipping_included" value="1"
                        {{ old('shipping_included') ? 'checked' : '' }}>
                    Shipping included
                </label>
            </div>
        </div>

        {{-- Variant builder --}}
        <div x-show="hasVariants" x-cloak>
            <div style="margin-bottom:14px">
                <span class="lbl" style="display:block;margin-bottom:8px">Select up to 3 variant axes:</span>
                <div class="row" style="flex-wrap:wrap;gap:10px">
                    @foreach($attributes as $attr)
                    <label class="row" style="gap:6px;cursor:pointer;font-weight:600;font-size:13.5px">
                        <input class="check" type="checkbox" value="{{ $attr->id }}"
                            x-model="selectedAttributes"
                            @change="generateCombinations()"
                            :disabled="!selectedAttributes.includes('{{ $attr->id }}') && !selectedAttributes.includes({{ $attr->id }}) && selectedAttributes.length >= 3">
                        {{ $attr->name }}
                    </label>
                    @endforeach
                </div>
            </div>

            <template x-for="attrId in selectedAttributes" :key="attrId">
                <div style="margin-bottom:12px;padding:14px;background:var(--surface-2);border-radius:var(--radius-card);border:1px solid var(--border)">
                    <div class="row" style="gap:8px;margin-bottom:10px">
                        <span style="font-weight:700;font-size:13.5px" x-text="attrName(attrId)"></span>
                        <input class="input" type="text" style="height:32px;max-width:140px;font-size:13px"
                            :value="newValues[attrId] ?? ''"
                            @input="newValues[attrId] = $event.target.value"
                            @keydown.enter.prevent="quickAddAttrValue(attrId)"
                            placeholder="New value…">
                        <button type="button" @click="quickAddAttrValue(attrId)"
                            class="btn btn-primary btn-sm" style="height:32px"
                            :disabled="addingValue[attrId]">+</button>
                    </div>
                    <div class="row" style="flex-wrap:wrap;gap:8px">
                        <template x-for="val in attrValues(attrId)" :key="val.id">
                            <label class="row" style="gap:5px;cursor:pointer;font-size:13.5px;font-weight:600">
                                <input class="check" type="checkbox" :value="val.id"
                                    x-model="selectedValues[attrId]"
                                    @change="generateCombinations()">
                                <span x-text="val.value"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </template>

            <div x-show="combinations.length > 0">
                <span class="lbl" style="display:block;margin-bottom:8px">
                    <span x-text="combinations.length"></span> variant(s) — fill in pricing:
                </span>
                <div class="table-scroll">
                    <table class="table" style="font-size:13px">
                        <thead>
                            <tr>
                                <th>Variant</th>
                                <th>SKU</th>
                                <th>Price <span class="req">*</span></th>
                                <th>Compare at</th>
                                <th>Cost</th>
                                <th style="text-align:right">Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(combo, idx) in combinations" :key="idx">
                                <tr>
                                    <td style="font-weight:700" x-text="combo.label">
                                        <template x-for="vid in combo.valueIds" :key="vid">
                                            <input type="hidden" :name="`variants[${idx}][attribute_value_ids][]`" :value="vid">
                                        </template>
                                    </td>
                                    <td>
                                        <template x-for="vid in combo.valueIds" :key="vid">
                                            <input type="hidden" :name="`variants[${idx}][attribute_value_ids][]`" :value="vid">
                                        </template>
                                        <input class="input mono" type="text" :name="`variants[${idx}][sku]`" style="width:100px;height:34px;font-size:12px">
                                    </td>
                                    <td><input class="input" type="number" :name="`variants[${idx}][price]`" step="0.01" min="0" required placeholder="0.00" style="width:90px;height:34px"></td>
                                    <td><input class="input" type="number" :name="`variants[${idx}][compare_at_price]`" step="0.01" min="0" placeholder="0.00" style="width:90px;height:34px"></td>
                                    <td><input class="input" type="number" :name="`variants[${idx}][cost_price]`" step="0.01" min="0" placeholder="0.00" style="width:90px;height:34px"></td>
                                    <td style="text-align:right"><input class="input" type="number" :name="`variants[${idx}][stock_qty]`" min="0" placeholder="∞" style="width:70px;height:34px;text-align:right"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- RIGHT COLUMN --}}
<div class="col-gap">

    {{-- Status --}}
    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Status</h3></div></div>
        <div class="field" style="margin-bottom:16px">
            <span class="lbl">Visibility</span>
            <div class="select-wrap">
                <select class="select" name="status">
                    @foreach(['draft' => 'Draft', 'active' => 'Active', 'archived' => 'Archived'] as $v => $l)
                        <option value="{{ $v }}" {{ old('status', 'draft') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <label class="between" style="cursor:pointer" x-data="{ on: {{ old('is_featured') ? 'true' : 'false' }} }">
            <div>
                <div style="font-weight:700;font-size:13.5px">Featured product</div>
                <div class="faint" style="font-size:12px">Show on the homepage</div>
            </div>
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" name="is_featured" value="1" style="display:none" :checked="on" {{ old('is_featured') ? 'checked' : '' }}>
            <button type="button" @click="on = !on" :class="on ? 'toggle on' : 'toggle'" style="flex-shrink:0">
                <span class="knob"></span>
            </button>
        </label>
    </div>

    {{-- Organization --}}
    <div class="card pad" x-data="quickAddBrand()">
        <div class="card-head">
            <div class="ct"><h3>Organization</h3></div>
        </div>

        <div class="field" style="margin-bottom:14px" x-data="quickAddBrand()">
            <div class="between" style="margin-bottom:6px">
                <span class="lbl">Brand</span>
                <button type="button" @click="open = !open" class="link-btn" style="font-size:12px" x-text="open ? 'Cancel' : '+ New brand'"></button>
            </div>
            <div x-show="open" x-collapse style="margin-bottom:8px">
                <div class="row" style="gap:8px">
                    <input class="input" type="text" x-model="name" placeholder="Brand name" @keydown.enter.prevent="save()">
                    <button type="button" @click="save()" :disabled="saving" class="btn btn-soft btn-sm" x-text="saving ? '...' : 'Add'"></button>
                </div>
                <span x-show="error" x-text="error" style="font-size:12px;color:var(--danger)"></span>
            </div>
            <div class="select-wrap">
                <select class="select" name="brand_id" id="brand-select">
                    <option value="">— No brand —</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="field" x-data="quickAddCategory()">
            <div class="between" style="margin-bottom:6px">
                <span class="lbl">Categories</span>
                <button type="button" @click="open = !open" class="link-btn" style="font-size:12px" x-text="open ? 'Cancel' : '+ New category'"></button>
            </div>
            <div x-show="open" x-collapse style="margin-bottom:8px">
                <div class="row" style="gap:8px">
                    <input class="input" type="text" x-model="name" placeholder="Category name" @keydown.enter.prevent="save()">
                    <button type="button" @click="save()" :disabled="saving" class="btn btn-soft btn-sm" x-text="saving ? '...' : 'Add'"></button>
                </div>
                <span x-show="error" x-text="error" style="font-size:12px;color:var(--danger)"></span>
            </div>
            <div id="categories-list" style="max-height:240px;overflow-y:auto;display:flex;flex-direction:column;gap:5px">
                @foreach($categories as $cat)
                <label class="row" style="gap:8px;cursor:pointer;font-weight:600;font-size:13.5px">
                    <input class="check" type="checkbox" name="categories[]" value="{{ $cat->id }}"
                        {{ in_array($cat->id, old('categories', [])) ? 'checked' : '' }}>
                    {{ $cat->name }}
                </label>
                @foreach($cat->children as $child)
                <label class="row" style="gap:8px;cursor:pointer;padding-left:20px;font-size:13px;color:var(--text-muted)">
                    <input class="check" type="checkbox" name="categories[]" value="{{ $child->id }}"
                        {{ in_array($child->id, old('categories', [])) ? 'checked' : '' }}>
                    — {{ $child->name }}
                </label>
                @endforeach
                @endforeach
            </div>
        </div>
    </div>

    {{-- Images --}}
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-info"><span class="ico" data-ico="image" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Images</h3><div class="sub">First image is the thumbnail</div></div>
        </div>
        <x-admin.media-picker name="image_ids" accept="image" :multiple="true" label="Add Images" :value="old('image_ids', [])" />
    </div>

    {{-- Video --}}
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-violet"><span class="ico" data-ico="eye" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Video</h3></div>
        </div>
        <x-admin.media-picker name="video_id" accept="video" label="Add Video" :value="old('video_id')" />
    </div>

    {{-- SEO --}}
    @include('admin.products._seo', ['meta' => null, 'product' => null])

    {{-- Pre-order --}}
    @if(\App\Models\SiteSetting::get('storefront.preorder_enabled'))
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-warning"><span class="ico" data-ico="clock" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Pre-order</h3></div>
        </div>
        <label class="row" style="gap:8px;cursor:pointer;font-weight:600;font-size:13.5px;margin-bottom:14px">
            <input class="check" type="checkbox" name="preorder_enabled" value="1"
                {{ old('preorder_enabled') ? 'checked' : '' }} id="preorder-toggle">
            Enable pre-order for this product
        </label>
        <div id="preorder-fields" class="{{ old('preorder_enabled') ? '' : 'hidden' }} stack" style="gap:12px">
            <div class="field">
                <span class="lbl">Pre-order message <span style="color:var(--text-faint);font-weight:400">(optional)</span></span>
                <input class="input" type="text" name="preorder_message" value="{{ old('preorder_message') }}" placeholder="e.g. Ships in 2-3 weeks">
            </div>
            <div class="field">
                <span class="lbl">Expected date <span style="color:var(--text-faint);font-weight:400">(optional)</span></span>
                <input class="input" type="date" name="preorder_expected_date" value="{{ old('preorder_expected_date') }}">
            </div>
        </div>
    </div>
    @endif

</div>
</div>
</form>

@php
    $attributeJson = json_encode($attributes->map(fn($a) => [
        'id'     => $a->id,
        'name'   => $a->name,
        'values' => $a->values->map(fn($v) => ['id' => $v->id, 'value' => $v->value])->values(),
    ])->values());
@endphp

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
const _csrf = document.querySelector('meta[name="csrf-token"]').content;

function quickAddBrand() {
    return {
        open: false, name: '', saving: false, error: '',
        save() {
            if (!this.name.trim()) return;
            this.saving = true; this.error = '';
            fetch('{{ route("admin.brands.quick-create") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ name: this.name.trim() })
            })
            .then(r => r.json().then(d => r.ok ? d : Promise.reject(d)))
            .then(brand => {
                const sel = document.getElementById('brand-select');
                const opt = new Option(brand.name, brand.id, true, true);
                sel.add(opt);
                this.name = ''; this.open = false;
            })
            .catch(d => { this.error = d.errors?.name?.[0] ?? d.message ?? 'Error'; })
            .finally(() => { this.saving = false; });
        }
    };
}

function quickAddCategory() {
    return {
        open: false, name: '', saving: false, error: '',
        save() {
            if (!this.name.trim()) return;
            this.saving = true; this.error = '';
            fetch('{{ route("admin.categories.quick-create") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ name: this.name.trim() })
            })
            .then(r => r.json().then(d => r.ok ? d : Promise.reject(d)))
            .then(cat => {
                const list = document.getElementById('categories-list');
                const label = document.createElement('label');
                label.style = 'display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:600;font-size:13.5px';
                label.innerHTML = `<input class="check" type="checkbox" name="categories[]" value="${cat.id}" checked>
                    <span>${cat.name}</span>`;
                list.prepend(label);
                this.name = ''; this.open = false;
            })
            .catch(d => { this.error = d.errors?.name?.[0] ?? d.message ?? 'Error'; })
            .finally(() => { this.saving = false; });
        }
    };
}

const descQuill = new Quill('#description-editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ header: [2, 3, 4, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['blockquote', 'link'],
            ['clean'],
        ]
    }
});

const descInitial = document.getElementById('description-input').value;
if (descInitial) descQuill.clipboard.dangerouslyPasteHTML(descInitial);

document.getElementById('product-form').addEventListener('formdata', function (e) {
    e.formData.set('description', descQuill.root.innerHTML === '<p><br></p>' ? '' : descQuill.root.innerHTML);
});

document.getElementById('prod-name').addEventListener('input', function () {
    const slug = this.value.toLowerCase().trim()
        .replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-');
    document.getElementById('prod-slug').value = slug;
    const skuField = document.getElementById('prod-sku');
    if (!skuField._userEdited) {
        skuField.value = this.value.toUpperCase().trim()
            .replace(/[^A-Z0-9]+/g, '-').replace(/^-+|-+$/g, '').substring(0, 20);
    }
});

const skuField = document.getElementById('prod-sku');
skuField.addEventListener('input', function () {
    this._userEdited = this.value !== '';
    const pos = this.selectionStart;
    this.value = this.value.replace(/\s/g, '');
    this.setSelectionRange(pos, pos);
});

function specEditor(initial) {
    return {
        rows: Array.isArray(initial) && initial.length ? initial : [],
        add()  { this.rows.push({ key: '', value: '' }); },
        remove(i) { this.rows.splice(i, 1); },
    };
}

const _attributeDataRaw = {!! $attributeJson !!};

function variantBuilder() {
    return {
        hasVariants: false,
        selectedAttributes: [],
        selectedValues: {},
        combinations: [],
        newValues: {},
        addingValue: {},
        data: [],

        init() {
            this.hasVariants = document.getElementById('has-variants').checked;
            this.data = JSON.parse(JSON.stringify(_attributeDataRaw));
            this.data.forEach(attr => {
                this.selectedValues[attr.id] = [];
                this.newValues[attr.id] = '';
                this.addingValue[attr.id] = false;
            });
        },

        attrName(id) { return this.data.find(a => a.id == id)?.name ?? ''; },
        attrValues(id) { return this.data.find(a => a.id == id)?.values ?? []; },

        async quickAddAttrValue(attrId) {
            const val = (this.newValues[attrId] ?? '').trim();
            if (!val || this.addingValue[attrId]) return;
            const attr = this.data.find(a => a.id == attrId);
            if (attr && attr.values.find(v => v.value.toLowerCase() === val.toLowerCase())) {
                this.newValues[attrId] = ''; return;
            }
            this.addingValue[attrId] = true;
            try {
                const res = await fetch('{{ route("admin.attributes.quick-add-value") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' },
                    body: JSON.stringify({ attribute_id: attrId, value: val }),
                });
                const json = await res.json();
                if (!res.ok) return;
                if (attr && !attr.values.find(v => v.id == json.id)) {
                    attr.values = [...attr.values, { id: json.id, value: json.value }];
                }
                this.newValues[attrId] = '';
            } finally { this.addingValue[attrId] = false; }
        },

        generateCombinations() {
            const axes = this.selectedAttributes
                .map(id => (this.selectedValues[id] ?? []).map(vid => {
                    const attr = this.data.find(a => a.id == id);
                    const val  = attr?.values.find(v => v.id == vid);
                    return { id: vid, label: val?.value ?? vid };
                }))
                .filter(ax => ax.length > 0);
            if (axes.length === 0) { this.combinations = []; return; }
            let result = [[]];
            for (const axis of axes) {
                result = result.flatMap(r => axis.map(v => [...r, v]));
            }
            this.combinations = result.map(combo => ({
                label:    combo.map(v => v.label).join(' / '),
                valueIds: combo.map(v => v.id),
            }));
        },
    };
}

// Draft / Publish buttons set status before submit
document.querySelectorAll('[name="_action"]').forEach(btn => {
    btn.addEventListener('click', function () {
        const status = this.value === 'publish' ? 'active' : 'draft';
        document.querySelector('[name="status"]').value = status;
    });
});

document.getElementById('preorder-toggle')?.addEventListener('change', function () {
    document.getElementById('preorder-fields').classList.toggle('hidden', !this.checked);
});

function aiDescGen() {
    return {
        open: false, loading: false, error: '',
        tone: 'professional', length: 'medium', attributes: '',
        async generate() {
            this.loading = true; this.error = '';
            const name     = document.getElementById('prod-name')?.value || '';
            const category = document.querySelector('[name="category_id"] option:checked')?.text || '';
            const brand    = document.querySelector('[name="brand_id"] option:checked')?.text    || '';
            try {
                const res = await fetch('{{ route('admin.ai.product-description') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf },
                    body: JSON.stringify({ name, category, brand, tone: this.tone, length: this.length, attributes: this.attributes }),
                });
                const json = await res.json();
                if (json.error) { this.error = json.error; return; }
                descQuill.setText('');
                descQuill.clipboard.dangerouslyPasteHTML('<p>' + json.text.replace(/\n\n/g, '</p><p>').replace(/\n/g, '<br>') + '</p>');
                this.open = false;
            } catch (e) { this.error = 'Network error.'; }
            finally { this.loading = false; }
        }
    };
}
</script>
@endpush

@endsection

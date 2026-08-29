@extends('admin.layouts.admin')

@section('title', 'Edit: ' . $product->name)

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
<form method="POST" action="{{ route('admin.products.update', $product) }}" id="product-form">
@csrf @method('PUT')

{{-- Page head --}}
<div class="row" style="gap:14px;margin-bottom:22px;flex-wrap:wrap">
    <a class="icon-btn" href="{{ route('admin.products.index') }}" style="width:40px;height:40px">
        <span class="ico" data-ico="chevLeft"></span>
    </a>
    <div class="grow" style="min-width:180px">
        <div class="breadcrumb">
            <a href="{{ route('admin.products.index') }}">Products</a> / Edit
        </div>
        <h2 class="display" style="font-size:24px;letter-spacing:-0.03em">{{ $product->name }}</h2>
        <div class="faint" style="font-size:12.5px">ID #{{ $product->id }}</div>
    </div>
    <div class="row" style="gap:10px">
        <a href="{{ url('/shop/' . $product->slug) }}" target="_blank" class="btn btn-outline" title="View in storefront">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            View in store
        </a>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <span class="ico" data-ico="check" style="width:18px;height:18px"></span>Save changes
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
            <input class="input" type="text" name="name" value="{{ old('name', $product->name) }}" required>
            @error('name')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
        </div>

        <div class="field" style="margin-bottom:14px">
            <span class="lbl">Slug <span class="req">*</span></span>
            <input class="input mono" type="text" name="slug" value="{{ old('slug', $product->slug) }}" required>
            @error('slug')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
        </div>

        <div class="field" style="margin-bottom:14px">
            <span class="lbl">Short description</span>
            <textarea class="textarea" name="short_description" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
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
            <textarea name="description" id="description-hidden" style="display:none">{{ old('description', $product->description) }}</textarea>
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
            <input class="input" type="text" name="warranty" value="{{ old('warranty', $product->warranty ?? '') }}" placeholder="e.g. 1 year manufacturer warranty">
            <span class="faint" style="font-size:12px">Leave blank to hide warranty info on the product page.</span>
        </div>
        <div class="field">
            <span class="lbl">Return policy</span>
            <textarea class="textarea" name="return_policy" rows="3" placeholder="Leave blank to use the global return policy from Settings → Storefront">{{ old('return_policy', $product->return_policy ?? '') }}</textarea>
            <span class="faint" style="font-size:12px">Overrides the global return policy for this product only.</span>
        </div>
    </div>

    {{-- Specifications --}}
    @php $existingSpecs = old('specifications', json_encode($product->specifications ?? [])); @endphp
    <div class="card pad" x-data="specEditor({{ $existingSpecs }})">
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

    {{-- FAQ --}}
    @php $existingFaqs = old('faqs', json_encode($product->faqs ?? [])); @endphp
    <div class="card pad" x-data="faqEditor({{ $existingFaqs }})">
        <div class="card-head">
            <span class="tile sm t-info"><span class="ico" data-ico="help-circle" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>FAQ</h3><div class="sub">Shown on product page with schema markup</div></div>
            <button type="button" @click="add()" class="link-btn head-action">
                <span class="ico" data-ico="plus" style="width:14px;height:14px"></span>Add question
            </button>
        </div>
        <div class="stack" style="gap:10px">
            <template x-for="(row, i) in rows" :key="i">
                <div style="display:flex;flex-direction:column;gap:6px;padding:12px;border:1px solid var(--border);border-radius:var(--radius-card)">
                    <div class="between">
                        <span class="lbl" style="margin:0">Question</span>
                        <button type="button" @click="remove(i)" class="icon-btn danger" style="width:24px;height:24px">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <input type="text" class="input" x-model="row.question" placeholder="e.g. What material is this made of?">
                    <span class="lbl">Answer</span>
                    <textarea class="input" x-model="row.answer" rows="2" style="resize:vertical" placeholder="Your answer..."></textarea>
                </div>
            </template>
            <p x-show="rows.length === 0" style="text-align:center;padding:16px;color:var(--text-faint);font-size:13.5px;border:1.5px dashed var(--border-strong);border-radius:var(--radius-card)">
                No FAQs added yet.
            </p>
        </div>
        <input type="hidden" name="faqs" :value="JSON.stringify(rows.filter(r => r.question.trim()))">
    </div>

    {{-- Trust Badges (per-product override) --}}
    @php $existingBadges = old('trust_badges', json_encode($product->trust_badges ?? [])); @endphp
    <div class="card pad" x-data="trustBadgeEditor({{ $existingBadges }})">
        <div class="card-head">
            <span class="tile sm t-success"><span class="ico" data-ico="check" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Trust Badges</h3><div class="sub">Override global badges for this product. Leave empty to use global defaults.</div></div>
            <button type="button" @click="add()" class="link-btn head-action" x-show="rows.length < 4">
                <span class="ico" data-ico="plus" style="width:14px;height:14px"></span>Add badge
            </button>
        </div>
        <div class="stack" style="gap:8px">
            <template x-for="(row, i) in rows" :key="i">
                <div class="row" style="gap:8px;align-items:center">
                    <span class="faint" style="font-size:11px;width:18px;text-align:center" x-text="i+1"></span>
                    <input class="input" type="text" x-model="row.title" placeholder="Title e.g. Authentic" style="flex:1">
                    <input class="input" type="text" x-model="row.sub" placeholder="Subtitle e.g. Verified quality" style="flex:1">
                    <button type="button" @click="remove(i)" class="icon-btn danger" style="width:28px;height:28px;flex-shrink:0">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
            <p x-show="rows.length === 0" style="text-align:center;padding:12px;color:var(--text-faint);font-size:13px;border:1.5px dashed var(--border-strong);border-radius:var(--radius-card)">
                Using global trust badges. Add rows to override for this product.
            </p>
        </div>
        <input type="hidden" name="trust_badges" :value="rows.length ? JSON.stringify(rows.filter(r => r.title.trim())) : ''">
    </div>

    {{-- Pricing / Variants --}}
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-violet"><span class="ico" data-ico="sliders" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Pricing &amp; Variants</h3></div>
            <label class="row head-action" style="gap:8px;cursor:pointer;font-weight:700;font-size:13px">
                <input type="checkbox" class="check" name="has_variants" value="1" id="has-variants-edit"
                    {{ old('has_variants', $product->has_variants) ? 'checked' : '' }}
                    onchange="document.getElementById('simple-pricing').style.display=this.checked?'none':'block';document.getElementById('variant-matrix').style.display=this.checked?'block':'none';">
                Has variants
            </label>
        </div>

        {{-- Simple product pricing --}}
        <div id="simple-pricing" style="{{ $product->has_variants ? 'display:none' : '' }}">
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:14px" class="grid-2">
                <div class="field">
                    <span class="lbl">SKU</span>
                    <input class="input mono" type="text" name="sku" id="edit-sku" value="{{ old('sku', $product->sku) }}">
                </div>
                <div class="field">
                    <span class="lbl">Price <span class="req">*</span></span>
                    <div class="input-prefix">
                        <span>{{ currency_symbol() }}</span>
                        <input class="input" type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" style="padding-left:26px">
                    </div>
                    @error('price')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <span class="lbl">Compare at</span>
                    <div class="input-prefix">
                        <span>{{ currency_symbol() }}</span>
                        <input class="input" type="number" name="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price) }}" step="0.01" min="0" style="padding-left:26px">
                    </div>
                </div>
                <div class="field">
                    <span class="lbl">Cost price</span>
                    <div class="input-prefix">
                        <span>{{ currency_symbol() }}</span>
                        <input class="input" type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0" style="padding-left:26px">
                    </div>
                </div>
            </div>
            <div class="row" style="gap:14px;flex-wrap:wrap">
                <div class="field" style="width:120px">
                    <span class="lbl">Weight (kg)</span>
                    <input class="input" type="number" name="weight" value="{{ old('weight', $product->weight) }}" step="0.001" min="0">
                </div>
                <div class="field" style="width:120px">
                    <span class="lbl">Stock qty</span>
                    <input class="input" type="number" name="stock_qty" value="{{ old('stock_qty', $product->stock_qty) }}" min="0">
                </div>
                <label class="row" style="gap:8px;cursor:pointer;padding-top:22px;font-weight:600;font-size:13.5px">
                    <input type="hidden" name="shipping_included" value="0">
                    <input class="check" type="checkbox" name="shipping_included" value="1"
                        {{ old('shipping_included', $product->shipping_included) ? 'checked' : '' }}>
                    Shipping included
                </label>
            </div>
        </div>

        {{-- Variant matrix --}}
        <div id="variant-matrix" style="{{ $product->has_variants ? '' : 'display:none' }}">
            @if($product->variants->isEmpty())
            <div x-data="variantBuilderEdit()" x-init="init()">
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
            @else
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
                            <th style="text-align:center">Active</th>
                            <th>Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($product->variants as $i => $variant)
                        <tr>
                            <td style="font-weight:700">
                                {{ $variant->label() ?: 'Default' }}
                                <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $variant->id }}">
                            </td>
                            <td>
                                <input class="input mono" type="text" name="variants[{{ $i }}][sku]"
                                    value="{{ old("variants.{$i}.sku", $variant->sku) }}"
                                    style="width:110px;height:34px;font-size:12px">
                            </td>
                            <td>
                                <input class="input" type="number" name="variants[{{ $i }}][price]"
                                    value="{{ old("variants.{$i}.price", $variant->price) }}"
                                    step="0.01" min="0" required style="width:100px;height:34px">
                            </td>
                            <td>
                                <input class="input" type="number" name="variants[{{ $i }}][compare_at_price]"
                                    value="{{ old("variants.{$i}.compare_at_price", $variant->compare_at_price) }}"
                                    step="0.01" min="0" style="width:100px;height:34px">
                            </td>
                            <td>
                                <input class="input" type="number" name="variants[{{ $i }}][cost_price]"
                                    value="{{ old("variants.{$i}.cost_price", $variant->cost_price) }}"
                                    step="0.01" min="0" style="width:90px;height:34px">
                            </td>
                            <td style="text-align:right">
                                <input class="input" type="number" name="variants[{{ $i }}][stock_qty]"
                                    value="{{ old("variants.{$i}.stock_qty", $variant->stock_qty) }}"
                                    min="0" placeholder="∞" style="width:70px;height:34px;text-align:right">
                            </td>
                            <td style="text-align:center">
                                <input class="check" type="checkbox" name="variants[{{ $i }}][is_active]" value="1"
                                    {{ old("variants.{$i}.is_active", $variant->is_active) ? 'checked' : '' }}>
                            </td>
                            <td>
                                <x-admin.media-picker
                                    :name="'variants[' . $i . '][image_media_id]'"
                                    accept="image"
                                    label="Pick image"
                                    :value="old('variants.' . $i . '.image_media_id', $variant->image_media_id)" />
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
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
                        <option value="{{ $v }}" {{ old('status', $product->status) === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <label class="between" style="cursor:pointer" x-data="{ on: {{ old('is_featured', $product->is_featured) ? 'true' : 'false' }} }">
            <div>
                <div style="font-weight:700;font-size:13.5px">Featured product</div>
                <div class="faint" style="font-size:12px">Show on the homepage</div>
            </div>
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" name="is_featured" value="1" style="display:none" :checked="on" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
            <button type="button" @click="on = !on" :class="on ? 'toggle on' : 'toggle'" style="flex-shrink:0">
                <span class="knob"></span>
            </button>
        </label>
    </div>

    {{-- Organization --}}
    <div class="card pad">
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
                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
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
            @php $selectedCats = old('categories', $product->categories->pluck('id')->toArray()); @endphp
            <div id="categories-list" style="max-height:240px;overflow-y:auto;display:flex;flex-direction:column;gap:5px">
                @foreach($categories as $cat)
                <label class="row" style="gap:8px;cursor:pointer;font-weight:600;font-size:13.5px">
                    <input class="check" type="checkbox" name="categories[]" value="{{ $cat->id }}"
                        {{ in_array($cat->id, $selectedCats) ? 'checked' : '' }}>
                    {{ $cat->name }}
                </label>
                @foreach($cat->children as $child)
                <label class="row" style="gap:8px;cursor:pointer;padding-left:20px;font-size:13px;color:var(--text-muted)">
                    <input class="check" type="checkbox" name="categories[]" value="{{ $child->id }}"
                        {{ in_array($child->id, $selectedCats) ? 'checked' : '' }}>
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
        <x-admin.media-picker name="image_ids" accept="image" :multiple="true" label="Add Images"
            :value="old('image_ids', $product->images->pluck('id')->toArray())" />
    </div>

    {{-- Video --}}
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-violet"><span class="ico" data-ico="eye" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Video</h3></div>
        </div>
        <x-admin.media-picker name="video_id" accept="video" label="Add Video"
            :value="old('video_id', $product->videos->first()?->id)" />
    </div>

    {{-- SEO --}}
    @include('admin.products._seo', ['meta' => $product->meta, 'product' => $product])

    {{-- Pre-order --}}
    @if(\App\Models\SiteSetting::get('storefront.preorder_enabled'))
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-warning"><span class="ico" data-ico="clock" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Pre-order</h3></div>
        </div>
        <label class="row" style="gap:8px;cursor:pointer;font-weight:600;font-size:13.5px;margin-bottom:14px">
            <input class="check" type="checkbox" name="preorder_enabled" value="1"
                {{ old('preorder_enabled', $product->preorder_enabled) ? 'checked' : '' }} id="preorder-toggle">
            Enable pre-order for this product
        </label>
        <div id="preorder-fields" style="{{ old('preorder_enabled', $product->preorder_enabled) ? '' : 'display:none' }}" class="stack" style="gap:12px">
            <div class="field">
                <span class="lbl">Pre-order message <span style="color:var(--text-faint);font-weight:400">(optional)</span></span>
                <input class="input" type="text" name="preorder_message"
                    value="{{ old('preorder_message', $product->preorder_message) }}"
                    placeholder="e.g. Ships in 2-3 weeks">
            </div>
            <div class="field">
                <span class="lbl">Expected date <span style="color:var(--text-faint);font-weight:400">(optional)</span></span>
                <input class="input" type="date" name="preorder_expected_date"
                    value="{{ old('preorder_expected_date', $product->preorder_expected_date?->format('Y-m-d')) }}">
            </div>
        </div>
    </div>
    @endif

</div>
</div>
</form>

{{-- Content Translations (outside form, reads from live fields via JS) --}}
@php $activeLanguages = \App\Models\Language::where('is_active', true)->get(); @endphp
@if($activeLanguages->isNotEmpty())
@php
$ctTabs = $activeLanguages->mapWithKeys(function($lang) use ($product) {
    $existing = $product->contentTranslations()->where('language_id', $lang->id)->first();
    $fields = $existing?->fields ?? [];
    return [$lang->code => [
        'name'              => $fields['name'] ?? '',
        'short_description' => $fields['short_description'] ?? '',
        'description'       => $fields['description'] ?? '',
    ]];
})->all();
@endphp
<div style="margin-top:18px" x-data="contentTranslator('product', {{ $product->id }})">
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-accent"><span class="ico" data-ico="globe" style="width:18px;height:18px"></span></span>
            <div class="ct">
                <h3>Content Translations</h3>
                <div class="sub">Translate product content per language</div>
            </div>
        </div>

        <div class="row" style="gap:8px;margin-bottom:18px;flex-wrap:wrap">
            @foreach($activeLanguages as $lang)
            <button type="button"
                @click="activeTab = '{{ $lang->code }}'"
                :class="activeTab === '{{ $lang->code }}' ? 'btn btn-primary btn-sm' : 'btn btn-soft btn-sm'">
                {{ $lang->name }}
            </button>
            @endforeach
        </div>

        @foreach($activeLanguages as $lang)
        <div x-show="activeTab === '{{ $lang->code }}'" x-cloak>
            <div class="stack" style="gap:14px">
                <div class="field">
                    <span class="lbl">Name</span>
                    <input class="input" type="text" id="ct_{{ $lang->code }}_name"
                        x-model="tabs['{{ $lang->code }}'].name"
                        placeholder="{{ $product->name }}">
                </div>
                <div class="field">
                    <span class="lbl">Short description</span>
                    <textarea class="textarea" id="ct_{{ $lang->code }}_short_description"
                        x-model="tabs['{{ $lang->code }}'].short_description"
                        rows="2"
                        placeholder="{{ $product->short_description }}"></textarea>
                </div>
                <div class="field">
                    <span class="lbl">Description</span>
                    <textarea class="textarea mono" id="ct_{{ $lang->code }}_description"
                        x-model="tabs['{{ $lang->code }}'].description"
                        rows="5"
                        placeholder="Translated HTML description..."></textarea>
                </div>
                <div class="row" style="gap:10px">
                    <button type="button" @click="aiTranslate('{{ $lang->code }}', {{ $lang->id }})"
                        :disabled="loading === '{{ $lang->code }}'"
                        class="btn btn-soft btn-sm">
                        <span class="ico" data-ico="spark" style="width:15px;height:15px"></span>
                        <span x-text="loading === '{{ $lang->code }}' ? 'Translating...' : 'AI Translate'"></span>
                    </button>
                    <button type="button" @click="saveTranslation('{{ $lang->code }}', {{ $lang->id }})"
                        :disabled="saving === '{{ $lang->code }}'"
                        class="btn btn-primary btn-sm">
                        <span x-text="saving === '{{ $lang->code }}' ? 'Saving...' : 'Save'"></span>
                    </button>
                    <span x-show="saved === '{{ $lang->code }}'" style="font-size:12.5px;color:var(--success);font-weight:700">Saved</span>
                </div>
                <span x-show="errors['{{ $lang->code }}']" x-text="errors['{{ $lang->code }}']"
                    style="font-size:12px;color:var(--danger)"></span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

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

const descInitial = document.getElementById('description-hidden').value;
if (descInitial) descQuill.clipboard.dangerouslyPasteHTML(descInitial);

document.getElementById('product-form').addEventListener('formdata', function (e) {
    e.formData.set('description', descQuill.root.innerHTML === '<p><br></p>' ? '' : descQuill.root.innerHTML);
});

document.getElementById('edit-sku').addEventListener('input', function () {
    const pos = this.selectionStart;
    this.value = this.value.replace(/\s/g, '');
    this.setSelectionRange(pos, pos);
});

document.getElementById('preorder-toggle')?.addEventListener('change', function () {
    document.getElementById('preorder-fields').style.display = this.checked ? '' : 'none';
});

function specEditor(initial) {
    return {
        rows: Array.isArray(initial) && initial.length ? initial : [],
        add()  { this.rows.push({ key: '', value: '' }); },
        remove(i) { this.rows.splice(i, 1); },
    };
}

function faqEditor(initial) {
    return {
        rows: Array.isArray(initial) && initial.length ? initial : [],
        add()  { this.rows.push({ question: '', answer: '' }); },
        remove(i) { this.rows.splice(i, 1); },
    };
}

function trustBadgeEditor(initial) {
    return {
        rows: Array.isArray(initial) && initial.length ? initial : [],
        add()  { if (this.rows.length < 4) this.rows.push({ title: '', sub: '' }); },
        remove(i) { this.rows.splice(i, 1); },
    };
}

const _editAttributeDataRaw = {!! $attributeJson !!};

function variantBuilderEdit() {
    return {
        selectedAttributes: [],
        selectedValues: {},
        combinations: [],
        newValues: {},
        addingValue: {},
        data: [],

        init() {
            this.data = JSON.parse(JSON.stringify(_editAttributeDataRaw));
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

function aiDescGen() {
    return {
        open: false, loading: false, error: '',
        tone: 'professional', length: 'medium', attributes: '',
        async generate() {
            this.loading = true; this.error = '';
            const name     = document.querySelector('[name="name"]')?.value || '';
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

@if($activeLanguages->isNotEmpty())
function contentTranslator(type, id) {
    return {
        activeTab: '{{ $activeLanguages->first()?->code }}',
        loading: null,
        saving: null,
        saved: null,
        errors: {},
        tabs: @json($ctTabs),

        async aiTranslate(code, langId) {
            this.loading = code;
            this.errors[code] = null;
            const original = {
                name:              document.querySelector('[name="name"]')?.value || '',
                short_description: document.querySelector('[name="short_description"]')?.value || '',
                description:       document.getElementById('description-hidden')?.value || '',
            };
            try {
                const res = await fetch('{{ route('admin.ai.translate-content') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': _csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ language_id: langId, translatable_type: type, translatable_id: id, fields: original }),
                });
                const data = await res.json();
                if (data.error) { this.errors[code] = data.error; return; }
                this.tabs[code] = { ...this.tabs[code], ...data.fields };
                this.saved = code;
                setTimeout(() => { if (this.saved === code) this.saved = null; }, 3000);
            } catch (e) {
                this.errors[code] = e.message;
            } finally {
                this.loading = null;
            }
        },

        async saveTranslation(code, langId) {
            this.saving = code;
            this.errors[code] = null;
            try {
                const res = await fetch('{{ route('admin.ai.save-content-translation') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': _csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ language_id: langId, translatable_type: type, translatable_id: id, fields: this.tabs[code] }),
                });
                const data = await res.json();
                if (data.error) { this.errors[code] = data.error; return; }
                this.saved = code;
                setTimeout(() => { if (this.saved === code) this.saved = null; }, 3000);
            } catch (e) {
                this.errors[code] = e.message;
            } finally {
                this.saving = null;
            }
        },
    };
}
@endif
</script>
@endpush

@endsection

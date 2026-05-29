@extends('admin.layouts.admin')

@section('title', 'Add Product')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li><a href="{{ route('admin.products.index') }}" class="hover:text-gray-700">Products</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Add</li>
    </ol>
@endsection

@section('page-header')
    <h1 class="text-2xl font-bold text-gray-900">Add Product</h1>
@endsection

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    .ql-container { font-size: 0.875rem; border-bottom-left-radius: 0.375rem; border-bottom-right-radius: 0.375rem; }
    .ql-toolbar { border-top-left-radius: 0.375rem; border-top-right-radius: 0.375rem; }
    .ql-editor { min-height: 200px; }
    @media (min-width: 1024px) {
        #product-grid { grid-template-columns: 70% 1fr; }
    }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('admin.products.store') }}" id="product-form">
    @csrf
    <div class="grid grid-cols-1 gap-6" id="product-grid">

        {{-- Left: main content --}}
        <div class="space-y-6">

            {{-- Basic info --}}
            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Basic Info</h3>
                <div class="space-y-4">
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                        <x-admin.input type="text" name="name" id="prod-name" value="{{ old('name') }}" required />
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </x-admin.form-group>

                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Slug <span class="text-red-500">*</span></label>
                        <x-admin.input type="text" name="slug" id="prod-slug" value="{{ old('slug') }}" required />
                        @error('slug')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </x-admin.form-group>

                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Short Description</label>
                        <textarea name="short_description" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">{{ old('short_description') }}</textarea>
                    </x-admin.form-group>

                    <x-admin.form-group x-data="aiDescGen()">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <button type="button" @click="open = !open"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-purple-700 border border-purple-200 rounded-lg px-2.5 py-1 hover:bg-purple-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Generate with AI
                            </button>
                        </div>
                        {{-- AI panel --}}
                        <div x-show="open" x-transition class="mb-3 border border-purple-200 rounded-xl p-4 bg-purple-50 space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Tone</label>
                                    <select x-model="tone" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300">
                                        <option value="professional">Professional</option>
                                        <option value="casual">Casual</option>
                                        <option value="enthusiastic">Enthusiastic</option>
                                        <option value="minimal">Minimal</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Length</label>
                                    <select x-model="length" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300">
                                        <option value="short">Short</option>
                                        <option value="medium">Medium</option>
                                        <option value="long">Long</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Key Attributes <span class="font-normal text-gray-400">(optional)</span></label>
                                <input type="text" x-model="attributes" placeholder="e.g. waterproof, bluetooth 5.0, 10h battery"
                                    class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300">
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="generate()" :disabled="loading"
                                    class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 disabled:opacity-60 transition-colors">
                                    <span x-show="!loading">Generate</span>
                                    <span x-show="loading">Generating…</span>
                                </button>
                                <span x-show="error" x-text="error" class="text-xs text-red-600"></span>
                            </div>
                        </div>
                        <div id="description-editor" class="bg-white"></div>
                        <textarea name="description" id="description-input" class="hidden">{{ old('description') }}</textarea>
                        @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </x-admin.form-group>
                </div>
            </x-admin.card>

            {{-- Warranty & Returns --}}
            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Warranty &amp; Returns</h3>
                <div class="space-y-4">
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Warranty</label>
                        <x-admin.input type="text" name="warranty"
                            value="{{ old('warranty') }}"
                            placeholder="e.g. 1 year manufacturer warranty" />
                        <p class="text-xs text-gray-400 mt-1">Leave blank to hide warranty info on the product page.</p>
                    </x-admin.form-group>
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Return Policy</label>
                        <textarea name="return_policy" rows="3"
                            placeholder="Leave blank to use the global return policy from Settings → Storefront"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">{{ old('return_policy') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">Overrides the global return policy for this product only.</p>
                    </x-admin.form-group>
                </div>
            </x-admin.card>

            {{-- Specifications --}}
            @php $oldSpecs = old('specifications', '[]'); @endphp
            <x-admin.card x-data="specEditor({{ $oldSpecs }})">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900">Specifications</h3>
                    <button type="button" @click="add()"
                        class="text-xs font-medium text-blue-600 hover:text-blue-800">+ Add row</button>
                </div>
                <div class="space-y-2">
                    <template x-for="(row, i) in rows" :key="i">
                        <div class="flex items-center gap-2">
                            <input type="text" x-model="row.key" placeholder="e.g. Material"
                                class="flex-1 rounded-md border-gray-300 text-sm h-9 px-3 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                            <input type="text" x-model="row.value" placeholder="e.g. Cotton 100%"
                                class="flex-1 rounded-md border-gray-300 text-sm h-9 px-3 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                            <button type="button" @click="remove(i)"
                                class="shrink-0 w-8 h-8 flex items-center justify-center rounded text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                    <p x-show="rows.length === 0" class="text-sm text-gray-400 text-center py-3 border border-dashed border-gray-200 rounded-md">
                        No specifications added yet.
                    </p>
                </div>
                <input type="hidden" name="specifications" :value="JSON.stringify(rows.filter(r => r.key.trim()))">
            </x-admin.card>

            {{-- Pricing / Variants --}}
            <x-admin.card x-data="variantBuilder()" x-init="init()">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900">Pricing & Variants</h3>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="has_variants" value="1" id="has-variants"
                            x-model="hasVariants"
                            {{ old('has_variants') ? 'checked' : '' }}
                            class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500">
                        <span class="text-sm font-medium text-gray-700">This product has variants</span>
                    </label>
                </div>

                {{-- Simple product pricing --}}
                <div x-show="!hasVariants">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <x-admin.form-group>
                            <label class="flex items-center text-sm font-medium text-gray-700">SKU
                                <x-admin.tooltip text="Stock Keeping Unit — your unique internal identifier for tracking inventory." />
                            </label>
                            <x-admin.input type="text" name="sku" id="prod-sku" value="{{ old('sku') }}" />
                        </x-admin.form-group>
                        <x-admin.form-group>
                            <label class="flex items-center text-sm font-medium text-gray-700">Price
                                <x-admin.tooltip text="The selling price displayed to customers at checkout." />
                            </label>
                            <x-admin.input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" />
                            @error('price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </x-admin.form-group>
                        <x-admin.form-group>
                            <label class="flex items-center text-sm font-medium text-gray-700">Compare At
                                <x-admin.tooltip text="The original price shown as a strikethrough — set higher than Price to display a discount badge." />
                            </label>
                            <x-admin.input type="number" name="compare_at_price" value="{{ old('compare_at_price') }}" step="0.01" min="0" />
                        </x-admin.form-group>
                        <x-admin.form-group>
                            <label class="flex items-center text-sm font-medium text-gray-700">Cost
                                <x-admin.tooltip text="Your cost to source or produce this item. Used internally to calculate profit margins — never shown to customers." />
                            </label>
                            <x-admin.input type="number" name="cost_price" value="{{ old('cost_price') }}" step="0.01" min="0" />
                        </x-admin.form-group>
                    </div>
                    <div class="flex items-start gap-6">
                        <x-admin.form-group class="w-32">
                            <label class="flex items-center text-sm font-medium text-gray-700">Weight (kg)
                                <x-admin.tooltip text="Physical weight of this product in kilograms. Used to calculate shipping rates." />
                            </label>
                            <x-admin.input type="number" name="weight" value="{{ old('weight') }}" step="0.001" min="0" />
                        </x-admin.form-group>
                        <div class="pt-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="shipping_included" value="1"
                                    {{ old('shipping_included') ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500">
                                <span class="text-sm font-medium text-gray-700">Shipping included</span>
                                <x-admin.tooltip text="When checked, the customer will not be charged a delivery fee for this product." />
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Variant builder --}}
                <div x-show="hasVariants" x-cloak>
                    {{-- Step 1: pick attributes --}}
                    <div class="mb-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Select up to 3 variant axes:</p>
                        <div class="flex flex-wrap gap-3">
                            @foreach($attributes as $attr)
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" value="{{ $attr->id }}"
                                    x-model="selectedAttributes"
                                    @change="generateCombinations()"
                                    :disabled="!selectedAttributes.includes('{{ $attr->id }}') && !selectedAttributes.includes({{ $attr->id }}) && selectedAttributes.length >= 3"
                                    class="rounded border-gray-300 text-blue-600">
                                <span class="text-sm text-gray-700">{{ $attr->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Step 2: pick values per attribute --}}
                    <template x-for="attrId in selectedAttributes" :key="attrId">
                        <div class="mb-4 p-3 bg-gray-50 rounded-md">
                            <div class="flex items-center gap-2 mb-2">
                                <p class="text-sm font-semibold text-gray-700" x-text="attrName(attrId)"></p>
                                <input type="text"
                                    :value="newValues[attrId] ?? ''"
                                    @input="newValues[attrId] = $event.target.value"
                                    @keydown.enter.prevent="quickAddAttrValue(attrId)"
                                    placeholder="New value…"
                                    class="h-6 w-32 rounded border-gray-300 text-xs px-2 focus:border-blue-300 focus:ring focus:ring-blue-200" />
                                <button type="button"
                                    @click="quickAddAttrValue(attrId)"
                                    class="h-6 px-2 text-xs rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
                                    :disabled="addingValue[attrId]">+</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="val in attrValues(attrId)" :key="val.id">
                                    <label class="flex items-center space-x-1 cursor-pointer">
                                        <input type="checkbox" :value="val.id"
                                            x-model="selectedValues[attrId]"
                                            @change="generateCombinations()"
                                            class="rounded border-gray-300 text-blue-600">
                                        <span class="text-sm text-gray-700" x-text="val.value"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Step 3: price matrix --}}
                    <div x-show="combinations.length > 0">
                        <p class="text-sm font-medium text-gray-700 mb-2">
                            <span x-text="combinations.length"></span> variant(s) — fill in pricing:
                        </p>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm border border-gray-200 rounded">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">Variant</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">SKU</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">Price *</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">Compare At</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">Cost</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(combo, idx) in combinations" :key="idx">
                                        <tr class="border-t border-gray-200">
                                            <td class="px-3 py-2 font-medium text-gray-800" x-text="combo.label"></td>
                                            <td class="px-3 py-2">
                                                <template x-for="vid in combo.valueIds" :key="vid">
                                                    <input type="hidden"
                                                        :name="`variants[${idx}][attribute_value_ids][]`"
                                                        :value="vid">
                                                </template>
                                                <input type="text" :name="`variants[${idx}][sku]`"
                                                    class="w-24 rounded border-gray-300 text-sm px-2 py-1 focus:border-blue-300 focus:ring focus:ring-blue-200" />
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" :name="`variants[${idx}][price]`"
                                                    step="0.01" min="0" required placeholder="0.00"
                                                    class="w-24 rounded border-gray-300 text-sm px-2 py-1 focus:border-blue-300 focus:ring focus:ring-blue-200" />
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" :name="`variants[${idx}][compare_at_price]`"
                                                    step="0.01" min="0" placeholder="0.00"
                                                    class="w-24 rounded border-gray-300 text-sm px-2 py-1 focus:border-blue-300 focus:ring focus:ring-blue-200" />
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" :name="`variants[${idx}][cost_price]`"
                                                    step="0.01" min="0" placeholder="0.00"
                                                    class="w-24 rounded border-gray-300 text-sm px-2 py-1 focus:border-blue-300 focus:ring focus:ring-blue-200" />
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" :name="`variants[${idx}][stock_qty]`"
                                                    min="0" placeholder="∞"
                                                    class="w-20 rounded border-gray-300 text-sm px-2 py-1 focus:border-blue-300 focus:ring focus:ring-blue-200" />
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </x-admin.card>

        </div>

        {{-- Right sidebar --}}
        <div class="space-y-6">
            {{-- Images --}}
            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Images</h3>
                <x-admin.media-picker
                    name="image_ids"
                    accept="image"
                    :multiple="true"
                    label="Add Images"
                    :value="old('image_ids', [])" />
                <p class="text-xs text-gray-400 mt-2">First image will be used as the product thumbnail.</p>
            </x-admin.card>

            {{-- Video --}}
            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Video</h3>
                <x-admin.media-picker
                    name="video_id"
                    accept="video"
                    label="Add Video"
                    :value="old('video_id')" />
                <p class="text-xs text-gray-400 mt-2">Optional product video shown on the product page.</p>
            </x-admin.card>

            {{-- SEO --}}
            @include('admin.products._seo', ['meta' => null, 'product' => null])

            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Publish</h3>
                <div class="space-y-4">
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <x-admin.select name="status">
                            @foreach(['draft' => 'Draft', 'active' => 'Active', 'archived' => 'Archived'] as $v => $l)
                                <option value="{{ $v }}" {{ old('status', 'draft') === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </x-admin.select>
                    </x-admin.form-group>

                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="is_featured" value="1"
                            {{ old('is_featured') ? 'checked' : '' }}
                            class="rounded border-gray-300 text-yellow-500 shadow-sm focus:ring-yellow-400">
                        <span class="text-sm font-medium text-gray-700">Featured</span>
                    </label>
                </div>
            </x-admin.card>

            <x-admin.card x-data="quickAddBrand()">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-semibold text-gray-900">Brand</h3>
                    <button type="button" @click="open = !open"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                        x-text="open ? 'Cancel' : '+ New brand'"></button>
                </div>
                <div x-show="open" x-collapse class="mb-3">
                    <div class="flex space-x-2">
                        <input type="text" x-model="name" placeholder="Brand name"
                            @keydown.enter.prevent="save()"
                            class="flex-1 rounded-md border-gray-300 text-sm h-9 px-3 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                        <button type="button" @click="save()" :disabled="saving"
                            class="px-3 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50"
                            x-text="saving ? '...' : 'Add'"></button>
                    </div>
                    <p x-show="error" x-text="error" class="text-red-500 text-xs mt-1"></p>
                </div>
                <x-admin.select name="brand_id" id="brand-select">
                    <option value="">— No brand —</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </x-admin.select>
            </x-admin.card>

            <x-admin.card x-data="quickAddCategory()">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-semibold text-gray-900">Categories</h3>
                    <button type="button" @click="open = !open"
                        class="text-xs text-blue-600 hover:text-blue-800 font-medium"
                        x-text="open ? 'Cancel' : '+ New category'"></button>
                </div>
                <div x-show="open" x-collapse class="mb-3">
                    <div class="flex space-x-2">
                        <input type="text" x-model="name" placeholder="Category name"
                            @keydown.enter.prevent="save()"
                            class="flex-1 rounded-md border-gray-300 text-sm h-9 px-3 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                        <button type="button" @click="save()" :disabled="saving"
                            class="px-3 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700 disabled:opacity-50"
                            x-text="saving ? '...' : 'Add'"></button>
                    </div>
                    <p x-show="error" x-text="error" class="text-red-500 text-xs mt-1"></p>
                </div>
                <div id="categories-list" class="space-y-1 max-h-[300px] overflow-y-auto">
                    @foreach($categories as $cat)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                {{ in_array($cat->id, old('categories', [])) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600">
                            <span class="text-sm text-gray-700">{{ $cat->name }}</span>
                        </label>
                        @foreach($cat->children as $child)
                        <label class="flex items-center space-x-2 pl-4">
                            <input type="checkbox" name="categories[]" value="{{ $child->id }}"
                                {{ in_array($child->id, old('categories', [])) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600">
                            <span class="text-sm text-gray-500">— {{ $child->name }}</span>
                        </label>
                        @endforeach
                    @endforeach
                </div>
            </x-admin.card>

            @if(\App\Models\SiteSetting::get('storefront.preorder_enabled'))
            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Pre-order</h3>
                <div class="space-y-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="preorder_enabled" value="1"
                            {{ old('preorder_enabled') ? 'checked' : '' }}
                            id="preorder-toggle"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">Enable pre-order for this product</span>
                    </label>
                    <div id="preorder-fields" class="{{ old('preorder_enabled') ? '' : 'hidden' }} space-y-3">
                        <x-admin.form-group>
                            <label class="block text-sm font-medium text-gray-700">Pre-order Message <span class="font-normal text-gray-400">(optional)</span></label>
                            <x-admin.input type="text" name="preorder_message"
                                value="{{ old('preorder_message') }}"
                                placeholder="e.g. Ships in 2-3 weeks" />
                        </x-admin.form-group>
                        <x-admin.form-group>
                            <label class="block text-sm font-medium text-gray-700">Expected Date <span class="font-normal text-gray-400">(optional)</span></label>
                            <x-admin.input type="date" name="preorder_expected_date"
                                value="{{ old('preorder_expected_date') }}" />
                        </x-admin.form-group>
                    </div>
                </div>
            </x-admin.card>
            @endif

            <div class="flex space-x-3">
                <x-admin.button type="submit" class="flex-1 justify-center">Save Product</x-admin.button>
                <x-admin.button href="{{ route('admin.products.index') }}" variant="outline">Cancel</x-admin.button>
            </div>
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
                label.className = 'flex items-center space-x-2';
                label.innerHTML = `<input type="checkbox" name="categories[]" value="${cat.id}" checked
                    class="rounded border-gray-300 text-blue-600">
                    <span class="text-sm text-gray-700">${cat.name}</span>`;
                list.prepend(label);
                this.name = ''; this.open = false;
            })
            .catch(d => { this.error = d.errors?.name?.[0] ?? d.message ?? 'Error'; })
            .finally(() => { this.saving = false; });
        }
    };
}

// Richtext editor
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

// Slug + SKU auto-generate from name
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

// Lock auto-fill once user manually types in SKU
const skuField = document.getElementById('prod-sku');
skuField.addEventListener('input', function () {
    this._userEdited = this.value !== '';
    // Strip spaces immediately
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

// Variant builder
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

        attrName(id) {
            return this.data.find(a => a.id == id)?.name ?? '';
        },

        attrValues(id) {
            return this.data.find(a => a.id == id)?.values ?? [];
        },

        async quickAddAttrValue(attrId) {
            const val = (this.newValues[attrId] ?? '').trim();
            if (!val || this.addingValue[attrId]) return;
            const attr = this.data.find(a => a.id == attrId);
            if (attr && attr.values.find(v => v.value.toLowerCase() === val.toLowerCase())) {
                this.newValues[attrId] = '';
                return;
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
            } finally {
                this.addingValue[attrId] = false;
            }
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

// Pre-order toggle
document.getElementById('preorder-toggle')?.addEventListener('change', function () {
    document.getElementById('preorder-fields').classList.toggle('hidden', !this.checked);
});

function aiDescGen() {
    return {
        open: false,
        loading: false,
        error: '',
        tone: 'professional',
        length: 'medium',
        attributes: '',

        async generate() {
            this.loading = true;
            this.error   = '';
            const name     = document.getElementById('prod-name')?.value || document.querySelector('[name="name"]')?.value || '';
            const category = document.querySelector('[name="category_id"] option:checked')?.text || '';
            const brand    = document.querySelector('[name="brand_id"] option:checked')?.text    || '';
            try {
                const res = await fetch('{{ route('admin.ai.product-description') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ name, category, brand, tone: this.tone, length: this.length, attributes: this.attributes }),
                });
                const json = await res.json();
                if (json.error) { this.error = json.error; return; }
                descQuill.setText('');
                descQuill.clipboard.dangerouslyPasteHTML('<p>' + json.text.replace(/\n\n/g, '</p><p>').replace(/\n/g, '<br>') + '</p>');
                this.open = false;
            } catch (e) {
                this.error = 'Network error.';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush
@endsection

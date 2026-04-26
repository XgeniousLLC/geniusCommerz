@extends('admin.layouts.admin')

@section('title', 'Edit Product')

@section('breadcrumbs')
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-1">/</span></li>
        <li><a href="{{ route('admin.products.index') }}" class="hover:text-gray-700">Products</a></li>
        <li><span class="mx-1">/</span></li>
        <li class="text-gray-900 font-medium">Edit</li>
    </ol>
@endsection

@section('page-header')
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 truncate" style="max-width:70%">Edit: {{ $product->name }}</h1>
        <span class="text-sm text-gray-500 shrink-0">ID #{{ $product->id }}</span>
    </div>
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
<form method="POST" action="{{ route('admin.products.update', $product) }}" id="product-form">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 gap-6" id="product-grid">

        {{-- Left --}}
        <div class="space-y-6">

            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Basic Info</h3>
                <div class="space-y-4">
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                        <x-admin.input type="text" name="name" value="{{ old('name', $product->name) }}" required />
                        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </x-admin.form-group>

                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Slug <span class="text-red-500">*</span></label>
                        <x-admin.input type="text" name="slug" value="{{ old('slug', $product->slug) }}" required />
                        @error('slug')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </x-admin.form-group>

                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Short Description</label>
                        <textarea name="short_description" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">{{ old('short_description', $product->short_description) }}</textarea>
                    </x-admin.form-group>

                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <div id="description-editor" class="bg-white"></div>
                        <textarea name="description" id="description-input" class="hidden">{{ old('description', $product->description) }}</textarea>
                        @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </x-admin.form-group>
                </div>
            </x-admin.card>

            {{-- Pricing / Variants --}}
            <x-admin.card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900">Pricing & Variants</h3>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="has_variants" value="1"
                            {{ old('has_variants', $product->has_variants) ? 'checked' : '' }}
                            id="has-variants-edit"
                            class="rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500"
                            onchange="document.getElementById('simple-pricing').style.display=this.checked?'none':'block';
                                      document.getElementById('variant-matrix').style.display=this.checked?'block':'none';">
                        <span class="text-sm font-medium text-gray-700">This product has variants</span>
                    </label>
                </div>

                {{-- Simple --}}
                <div id="simple-pricing" style="{{ $product->has_variants ? 'display:none' : '' }}">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <x-admin.form-group>
                            <label class="flex items-center text-sm font-medium text-gray-700">SKU
                                <x-admin.tooltip text="Stock Keeping Unit — your unique internal identifier for tracking inventory." />
                            </label>
                            <x-admin.input type="text" name="sku" id="edit-sku" value="{{ old('sku', $product->sku) }}" />
                        </x-admin.form-group>
                        <x-admin.form-group>
                            <label class="flex items-center text-sm font-medium text-gray-700">Price
                                <x-admin.tooltip text="The selling price displayed to customers at checkout." />
                            </label>
                            <x-admin.input type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" />
                        </x-admin.form-group>
                        <x-admin.form-group>
                            <label class="flex items-center text-sm font-medium text-gray-700">Compare At
                                <x-admin.tooltip text="The original price shown as a strikethrough — set higher than Price to display a discount badge." />
                            </label>
                            <x-admin.input type="number" name="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price) }}" step="0.01" min="0" />
                        </x-admin.form-group>
                        <x-admin.form-group>
                            <label class="flex items-center text-sm font-medium text-gray-700">Cost
                                <x-admin.tooltip text="Your cost to source or produce this item. Used internally to calculate profit margins — never shown to customers." />
                            </label>
                            <x-admin.input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0" />
                        </x-admin.form-group>
                    </div>
                    <div class="flex items-start gap-6 mt-2">
                        <x-admin.form-group class="w-32">
                            <label class="flex items-center text-sm font-medium text-gray-700">Weight (kg)
                                <x-admin.tooltip text="Physical weight of this product in kilograms. Used to calculate shipping rates." />
                            </label>
                            <x-admin.input type="number" name="weight" value="{{ old('weight', $product->weight) }}" step="0.001" min="0" />
                        </x-admin.form-group>
                        <div class="pt-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="shipping_included" value="1"
                                    {{ old('shipping_included', $product->shipping_included) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500">
                                <span class="text-sm font-medium text-gray-700">Shipping included</span>
                                <x-admin.tooltip text="When checked, the customer will not be charged a delivery fee for this product." />
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Variant matrix --}}
                <div id="variant-matrix" style="{{ $product->has_variants ? '' : 'display:none' }}">
                    @if($product->variants->isEmpty())
                        <p class="text-sm text-gray-500">No variants yet. Use the Create form to generate variant combinations.</p>
                    @else
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
                                    <th class="px-3 py-2 text-left font-medium text-gray-600">Active</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->variants as $i => $variant)
                                <tr class="border-t border-gray-200">
                                    <td class="px-3 py-2 font-medium text-gray-800">
                                        {{ $variant->label() ?: 'Default' }}
                                        <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $variant->id }}">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="variants[{{ $i }}][sku]"
                                            value="{{ old("variants.{$i}.sku", $variant->sku) }}"
                                            class="w-28 rounded border-gray-300 text-sm px-2 py-1" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" name="variants[{{ $i }}][price]"
                                            value="{{ old("variants.{$i}.price", $variant->price) }}"
                                            step="0.01" min="0" required
                                            class="w-28 rounded border-gray-300 text-sm px-2 py-1" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" name="variants[{{ $i }}][compare_at_price]"
                                            value="{{ old("variants.{$i}.compare_at_price", $variant->compare_at_price) }}"
                                            step="0.01" min="0"
                                            class="w-28 rounded border-gray-300 text-sm px-2 py-1" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" name="variants[{{ $i }}][cost_price]"
                                            value="{{ old("variants.{$i}.cost_price", $variant->cost_price) }}"
                                            step="0.01" min="0"
                                            class="w-24 rounded border-gray-300 text-sm px-2 py-1" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" name="variants[{{ $i }}][stock_qty]"
                                            value="{{ old("variants.{$i}.stock_qty", $variant->stock_qty) }}"
                                            min="0" placeholder="∞"
                                            class="w-20 rounded border-gray-300 text-sm px-2 py-1" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="checkbox" name="variants[{{ $i }}][is_active]" value="1"
                                            {{ old("variants.{$i}.is_active", $variant->is_active) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-blue-600" />
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
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
                    :value="old('image_ids', $product->images->pluck('id')->toArray())" />
                <p class="text-xs text-gray-400 mt-2">First image will be used as the product thumbnail.</p>
            </x-admin.card>

            {{-- Video --}}
            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Video</h3>
                <x-admin.media-picker
                    name="video_id"
                    accept="video"
                    label="Add Video"
                    :value="old('video_id', $product->videos->first()?->id)" />
                <p class="text-xs text-gray-400 mt-2">Optional product video shown on the product page.</p>
            </x-admin.card>

            {{-- SEO --}}
            @include('admin.products._seo', ['meta' => $product->meta, 'product' => $product])

            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Publish</h3>
                <div class="space-y-4">
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <x-admin.select name="status">
                            @foreach(['draft' => 'Draft', 'active' => 'Active', 'archived' => 'Archived'] as $v => $l)
                                <option value="{{ $v }}" {{ old('status', $product->status) === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </x-admin.select>
                    </x-admin.form-group>

                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="is_featured" value="1"
                            {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}
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
                        <option value="{{ $brand->id }}"
                            {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
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
                @php $selectedCats = old('categories', $product->categories->pluck('id')->toArray()); @endphp
                <div id="categories-list" class="space-y-1 max-h-56 overflow-y-auto">
                    @foreach($categories as $cat)
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                {{ in_array($cat->id, $selectedCats) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600">
                            <span class="text-sm text-gray-700">{{ $cat->name }}</span>
                        </label>
                        @foreach($cat->children as $child)
                        <label class="flex items-center space-x-2 pl-4">
                            <input type="checkbox" name="categories[]" value="{{ $child->id }}"
                                {{ in_array($child->id, $selectedCats) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600">
                            <span class="text-sm text-gray-500">— {{ $child->name }}</span>
                        </label>
                        @endforeach
                    @endforeach
                </div>
            </x-admin.card>

            <x-admin.card>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Warranty &amp; Returns</h3>
                <div class="space-y-4">
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Warranty</label>
                        <x-admin.input type="text" name="warranty"
                            value="{{ old('warranty', $product->warranty ?? '') }}"
                            placeholder="e.g. 1 year manufacturer warranty" />
                        <p class="text-xs text-gray-400 mt-1">Leave blank to hide warranty info on the product page.</p>
                    </x-admin.form-group>
                    <x-admin.form-group>
                        <label class="block text-sm font-medium text-gray-700">Return Policy</label>
                        <textarea name="return_policy" rows="3"
                            placeholder="Leave blank to use the global return policy from Settings → Storefront"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm">{{ old('return_policy', $product->return_policy ?? '') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">Overrides the global return policy for this product only.</p>
                    </x-admin.form-group>
                </div>
            </x-admin.card>

            @php $existingSpecs = old('specifications', json_encode($product->specifications ?? [])); @endphp
            <x-admin.card x-data="specEditor({{ $existingSpecs }})">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900">Specifications</h3>
                    <button type="button" @click="add()"
                        class="text-xs font-medium text-blue-600 hover:text-blue-800">+ Add row</button>
                </div>
                <div class="space-y-2" id="spec-rows">
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

            <div class="flex space-x-3">
                <x-admin.button type="submit" class="flex-1 justify-center">Update Product</x-admin.button>
                <x-admin.button href="{{ route('admin.products.index') }}" variant="outline">Cancel</x-admin.button>
            </div>
        </div>

    </div>
</form>

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

function specEditor(initial) {
    return {
        rows: Array.isArray(initial) && initial.length ? initial : [],
        add()  { this.rows.push({ key: '', value: '' }); },
        remove(i) { this.rows.splice(i, 1); },
    };
}

// Strip spaces from SKU on input
document.getElementById('edit-sku').addEventListener('input', function () {
    const pos = this.selectionStart;
    this.value = this.value.replace(/\s/g, '');
    this.setSelectionRange(pos, pos);
});
</script>
@endpush
@endsection

<x-admin.card>
    <div class="space-y-5" x-data="{
        appliesTo: '{{ old('applies_to', isset($coupon) ? ($coupon->applies_to ?? 'all') : 'all') }}'
    }">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
                <x-admin.input type="text" name="code" value="{{ old('code', $coupon->code ?? '') }}"
                    placeholder="e.g. SUMMER20" class="uppercase" required />
                @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <x-admin.input type="text" name="description" value="{{ old('description', $coupon->description ?? '') }}"
                    placeholder="Internal note…" />
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                <x-admin.select name="type" required>
                    <option value="fixed"   {{ old('type', $coupon->type ?? 'fixed') === 'fixed'   ? 'selected' : '' }}>Fixed amount ($)</option>
                    <option value="percent" {{ old('type', $coupon->type ?? '')      === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                </x-admin.select>
                @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Value <span class="text-red-500">*</span></label>
                <x-admin.input type="number" name="value" value="{{ old('value', $coupon->value ?? '') }}"
                    placeholder="e.g. 20" min="0.01" step="0.01" required />
                @error('value')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Order ($)</label>
                <x-admin.input type="number" name="minimum_order" value="{{ old('minimum_order', $coupon->minimum_order ?? '') }}"
                    placeholder="No minimum" min="0" step="0.01" />
                @error('minimum_order')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Discount ($)</label>
                <x-admin.input type="number" name="maximum_discount" value="{{ old('maximum_discount', $coupon->maximum_discount ?? '') }}"
                    placeholder="No cap" min="0" step="0.01" />
                <p class="text-xs text-gray-400 mt-1">Applies to percent-type coupons</p>
                @error('maximum_discount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Applies To --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Applies To <span class="text-red-500">*</span></label>
            <div class="flex flex-wrap gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="applies_to" value="all"
                        x-model="appliesTo"
                        {{ old('applies_to', $coupon->applies_to ?? 'all') === 'all' ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">All products</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="applies_to" value="specific_products"
                        x-model="appliesTo"
                        {{ old('applies_to', $coupon->applies_to ?? '') === 'specific_products' ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Specific products</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="applies_to" value="specific_categories"
                        x-model="appliesTo"
                        {{ old('applies_to', $coupon->applies_to ?? '') === 'specific_categories' ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Specific categories</span>
                </label>
            </div>
            @error('applies_to')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Specific Products --}}
        <div x-show="appliesTo === 'specific_products'" x-cloak>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Products</label>
            <select name="product_ids[]" multiple
                class="w-full rounded-md border border-gray-300 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[120px]">
                @foreach($products as $product)
                    <option value="{{ $product->id }}"
                        {{ in_array($product->id, old('product_ids', isset($coupon) ? $coupon->products->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">Hold Ctrl / Cmd to select multiple</p>
            @error('product_ids')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Specific Categories --}}
        <div x-show="appliesTo === 'specific_categories'" x-cloak>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Categories</label>
            <select name="category_ids[]" multiple
                class="w-full rounded-md border border-gray-300 text-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[120px]">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ in_array($category->id, old('category_ids', isset($coupon) ? $coupon->categories->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">Hold Ctrl / Cmd to select multiple</p>
            @error('category_ids')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Usage Limit</label>
                <x-admin.input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}"
                    placeholder="Unlimited" min="1" step="1" />
                @error('usage_limit')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Per Customer Limit</label>
                <x-admin.input type="number" name="per_customer_limit" value="{{ old('per_customer_limit', $coupon->per_customer_limit ?? '') }}"
                    placeholder="Unlimited" min="1" step="1" />
                <p class="text-xs text-gray-400 mt-1">Max uses per customer account</p>
                @error('per_customer_limit')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-6">
            <div class="flex items-center gap-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                    {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">Active</label>
            </div>
            <div class="flex items-center gap-3">
                <input type="hidden" name="is_auto_apply" value="0">
                <input type="checkbox" name="is_auto_apply" id="is_auto_apply" value="1"
                    {{ old('is_auto_apply', $coupon->is_auto_apply ?? false) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="is_auto_apply" class="text-sm font-medium text-gray-700">Auto-apply</label>
                <span class="text-xs text-gray-400">Automatically applied at checkout when eligible</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Starts At</label>
                <x-admin.input type="datetime-local" name="starts_at"
                    value="{{ old('starts_at', isset($coupon->starts_at) ? $coupon->starts_at?->format('Y-m-d\TH:i') : '') }}" />
                @error('starts_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expires At</label>
                <x-admin.input type="datetime-local" name="expires_at"
                    value="{{ old('expires_at', isset($coupon->expires_at) ? $coupon->expires_at?->format('Y-m-d\TH:i') : '') }}" />
                @error('expires_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

    </div>
</x-admin.card>

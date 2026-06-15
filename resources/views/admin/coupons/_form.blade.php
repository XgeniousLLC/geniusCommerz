<div class="card pad" x-data="{
    appliesTo: '{{ old('applies_to', isset($coupon) ? ($coupon->applies_to ?? 'all') : 'all') }}'
}">

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
        <div class="field" style="margin:0">
            <span class="lbl">Code <span style="color:var(--danger)">*</span></span>
            <input class="input" type="text" name="code" value="{{ old('code', $coupon->code ?? '') }}"
                placeholder="e.g. SUMMER20" style="text-transform:uppercase" required>
            @error('code')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
        </div>
        <div class="field" style="margin:0">
            <span class="lbl">Description</span>
            <input class="input" type="text" name="description" value="{{ old('description', $coupon->description ?? '') }}"
                placeholder="Internal note…">
            @error('description')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
        <div class="field" style="margin:0">
            <span class="lbl">Type <span style="color:var(--danger)">*</span></span>
            <select class="input" name="type" required>
                <option value="fixed"   {{ old('type', $coupon->type ?? 'fixed') === 'fixed'   ? 'selected' : '' }}>Fixed amount (৳)</option>
                <option value="percent" {{ old('type', $coupon->type ?? '')      === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
            </select>
            @error('type')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
        </div>
        <div class="field" style="margin:0">
            <span class="lbl">Value <span style="color:var(--danger)">*</span></span>
            <input class="input" type="number" name="value" value="{{ old('value', $coupon->value ?? '') }}"
                placeholder="e.g. 20" min="0.01" step="0.01" required>
            @error('value')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
        <div class="field" style="margin:0">
            <span class="lbl">Minimum Order (৳)</span>
            <input class="input" type="number" name="minimum_order" value="{{ old('minimum_order', $coupon->minimum_order ?? '') }}"
                placeholder="No minimum" min="0" step="0.01">
            @error('minimum_order')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
        </div>
        <div class="field" style="margin:0">
            <span class="lbl">Maximum Discount (৳)</span>
            <input class="input" type="number" name="maximum_discount" value="{{ old('maximum_discount', $coupon->maximum_discount ?? '') }}"
                placeholder="No cap" min="0" step="0.01">
            <p class="hint">Applies to percent-type coupons</p>
            @error('maximum_discount')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="field" style="margin-bottom:14px">
        <span class="lbl">Applies To <span style="color:var(--danger)">*</span></span>
        <div class="row" style="gap:20px;margin-top:6px">
            @foreach(['all' => 'All products', 'specific_products' => 'Specific products', 'specific_categories' => 'Specific categories'] as $val => $label)
            <label class="row" style="gap:8px;cursor:pointer;font-size:13.5px">
                <input type="radio" name="applies_to" value="{{ $val }}"
                    x-model="appliesTo"
                    {{ old('applies_to', $coupon->applies_to ?? 'all') === $val ? 'checked' : '' }}>
                {{ $label }}
            </label>
            @endforeach
        </div>
        @error('applies_to')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
    </div>

    <div x-show="appliesTo === 'specific_products'" style="margin-bottom:14px">
        <div class="field" style="margin:0">
            <span class="lbl">Select Products</span>
            <select class="input" name="product_ids[]" multiple style="height:120px">
                @foreach($products as $product)
                    <option value="{{ $product->id }}"
                        {{ in_array($product->id, old('product_ids', isset($coupon) ? $coupon->products->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
            <p class="hint">Hold Ctrl / Cmd to select multiple</p>
            @error('product_ids')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
        </div>
    </div>

    <div x-show="appliesTo === 'specific_categories'" style="margin-bottom:14px">
        <div class="field" style="margin:0">
            <span class="lbl">Select Categories</span>
            <select class="input" name="category_ids[]" multiple style="height:120px">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ in_array($category->id, old('category_ids', isset($coupon) ? $coupon->categories->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <p class="hint">Hold Ctrl / Cmd to select multiple</p>
            @error('category_ids')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
        <div class="field" style="margin:0">
            <span class="lbl">Usage Limit</span>
            <input class="input" type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}"
                placeholder="Unlimited" min="1" step="1">
            @error('usage_limit')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
        </div>
        <div class="field" style="margin:0">
            <span class="lbl">Per Customer Limit</span>
            <input class="input" type="number" name="per_customer_limit" value="{{ old('per_customer_limit', $coupon->per_customer_limit ?? '') }}"
                placeholder="Unlimited" min="1" step="1">
            <p class="hint">Max uses per customer account</p>
            @error('per_customer_limit')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="row" style="gap:24px;margin-bottom:14px">
        <label class="row" style="gap:8px;cursor:pointer;font-size:13.5px">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1"
                {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }}>
            Active
        </label>
        <label class="row" style="gap:8px;cursor:pointer;font-size:13.5px">
            <input type="hidden" name="is_auto_apply" value="0">
            <input type="checkbox" name="is_auto_apply" id="is_auto_apply" value="1"
                {{ old('is_auto_apply', $coupon->is_auto_apply ?? false) ? 'checked' : '' }}>
            Auto-apply
            <span class="faint" style="font-size:12px">Automatically applied at checkout when eligible</span>
        </label>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="field" style="margin:0">
            <span class="lbl">Starts At</span>
            <input class="input" type="datetime-local" name="starts_at"
                value="{{ old('starts_at', isset($coupon->starts_at) ? $coupon->starts_at?->format('Y-m-d\TH:i') : '') }}">
            @error('starts_at')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
        </div>
        <div class="field" style="margin:0">
            <span class="lbl">Expires At</span>
            <input class="input" type="datetime-local" name="expires_at"
                value="{{ old('expires_at', isset($coupon->expires_at) ? $coupon->expires_at?->format('Y-m-d\TH:i') : '') }}">
            @error('expires_at')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
        </div>
    </div>

</div>

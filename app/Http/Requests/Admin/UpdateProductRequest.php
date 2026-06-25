<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name'              => 'required|string|max:191',
            'slug'              => ['required', 'string', 'max:191', 'regex:/^[a-z0-9\-]+$/', Rule::unique('products', 'slug')->ignore($productId)],
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'brand_id'          => 'nullable|exists:brands,id',
            'categories'        => 'nullable|array',
            'categories.*'      => 'exists:categories,id',
            'status'            => 'required|in:draft,active,archived',
            'is_featured'       => 'boolean',
            'has_variants'      => 'boolean',
            'sku'               => ['nullable', 'string', 'max:100', 'regex:/^\S+$/', Rule::unique('products', 'sku')->ignore($productId)],
            'price'             => 'nullable|numeric|min:0',
            'compare_at_price'  => 'nullable|numeric|min:0',
            'cost_price'        => 'nullable|numeric|min:0',
            'weight'            => 'nullable|numeric|min:0',
            'shipping_included' => 'boolean',
            'warranty'          => 'nullable|string|max:255',
            'return_policy'     => 'nullable|string',
            'specifications'    => 'nullable|string',
            'faqs'              => 'nullable|string',
            'trust_badges'      => 'nullable|string',
            // variant matrix rows
            'variants'                   => 'nullable|array',
            'variants.*.id'              => 'nullable|exists:product_variants,id',
            'variants.*.sku'             => 'nullable|string|max:100',
            'variants.*.price'           => 'required_with:variants|numeric|min:0',
            'variants.*.compare_at_price'=> 'nullable|numeric|min:0',
            'variants.*.cost_price'      => 'nullable|numeric|min:0',
            'variants.*.weight'          => 'nullable|numeric|min:0',
            'variants.*.is_active'       => 'boolean',
        ];
    }
}

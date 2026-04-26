<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:191',
            'slug'              => 'required|string|max:191|unique:products,slug|regex:/^[a-z0-9\-]+$/',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'brand_id'          => 'nullable|exists:brands,id',
            'categories'        => 'nullable|array',
            'categories.*'      => 'exists:categories,id',
            'status'            => 'required|in:draft,active,archived',
            'is_featured'       => 'boolean',
            'has_variants'      => 'boolean',
            // simple product fields
            'sku'               => 'nullable|string|max:100|unique:products,sku|regex:/^\S+$/',
            'price'             => 'nullable|numeric|min:0',
            'compare_at_price'  => 'nullable|numeric|min:0',
            'cost_price'        => 'nullable|numeric|min:0',
            'weight'            => 'nullable|numeric|min:0',
            'shipping_included' => 'boolean',
            'warranty'          => 'nullable|string|max:255',
            'return_policy'     => 'nullable|string',
            'specifications'    => 'nullable|string',
            // variants
            'variants'                        => 'nullable|array',
            'variants.*.attribute_value_ids'  => 'required_with:variants|array',
            'variants.*.attribute_value_ids.*'=> 'exists:attribute_values,id',
            'variants.*.sku'                  => 'nullable|string|max:100',
            'variants.*.price'                => 'required_with:variants|numeric|min:0',
            'variants.*.compare_at_price'     => 'nullable|numeric|min:0',
            'variants.*.cost_price'           => 'nullable|numeric|min:0',
            'variants.*.weight'               => 'nullable|numeric|min:0',
            'variants.*.stock_qty'            => 'nullable|integer|min:0',
        ];
    }
}

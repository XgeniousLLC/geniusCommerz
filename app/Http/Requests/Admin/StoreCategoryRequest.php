<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:191',
            'slug'       => 'required|string|max:191|unique:categories,slug|regex:/^[a-z0-9\-]+$/',
            'description'=> 'nullable|string',
            'image_media_id' => 'nullable|integer|exists:media,id',
            'parent_id'  => 'nullable|exists:categories,id',
            'is_active'  => 'boolean',
            'sort_order' => 'integer|min:0',
        ];
    }
}

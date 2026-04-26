<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'slug' => ['nullable', 'string', 'max:191', 'regex:/^[a-z0-9\-]+$/', Rule::unique('brands', 'slug')->ignore($this->route('brand'))],
            'description' => 'nullable|string',
            'logo_media_id' => 'nullable|integer|exists:media,id',
            'website' => 'nullable|url|max:500',
            'is_active' => 'boolean',
        ];
    }
}

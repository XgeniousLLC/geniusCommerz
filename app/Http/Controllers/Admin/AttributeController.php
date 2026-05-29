<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttributeController extends Controller
{
    public function index(): View
    {
        $attributes = Attribute::withCount('values')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.attributes.index', compact('attributes'));
    }

    public function create(): View
    {
        return view('admin.attributes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:191|unique:attributes,name',
            'sort_order' => 'integer|min:0',
            'values' => 'nullable|array',
            'values.*' => 'required|string|max:191',
        ]);

        $attribute = Attribute::create([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        foreach (array_filter($request->input('values', [])) as $i => $value) {
            AttributeValue::create([
                'attribute_id' => $attribute->id,
                'value' => $value,
                'sort_order' => $i,
            ]);
        }

        return redirect()->route('admin.attributes.index')->with('success', 'Attribute created.');
    }

    public function edit(Attribute $attribute): View
    {
        $attribute->load('values');

        return view('admin.attributes.edit', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:191|unique:attributes,name,'.$attribute->id,
            'sort_order' => 'integer|min:0',
            'values' => 'nullable|array',
            'values.*' => 'required|string|max:191',
        ]);

        $attribute->update([
            'name' => $request->input('name'),
            'slug' => Str::slug($request->input('name')),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        // Replace all values
        $attribute->values()->delete();

        foreach (array_filter($request->input('values', [])) as $i => $value) {
            AttributeValue::create([
                'attribute_id' => $attribute->id,
                'value' => $value,
                'sort_order' => $i,
            ]);
        }

        return redirect()->route('admin.attributes.index')->with('success', 'Attribute updated.');
    }

    public function destroy(Attribute $attribute): RedirectResponse
    {
        $attribute->delete();

        return redirect()->route('admin.attributes.index')->with('success', 'Attribute deleted.');
    }

    public function quickAddValue(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value'        => 'required|string|max:191',
        ]);

        $attribute = Attribute::findOrFail($request->integer('attribute_id'));

        $existing = $attribute->values()->where('value', $request->input('value'))->first();
        if ($existing) {
            return response()->json(['id' => $existing->id, 'value' => $existing->value]);
        }

        $maxSort = $attribute->values()->max('sort_order') ?? -1;

        $attrValue = AttributeValue::create([
            'attribute_id' => $attribute->id,
            'value'        => $request->input('value'),
            'sort_order'   => $maxSort + 1,
        ]);

        return response()->json(['id' => $attrValue->id, 'value' => $attrValue->value], 201);
    }
}

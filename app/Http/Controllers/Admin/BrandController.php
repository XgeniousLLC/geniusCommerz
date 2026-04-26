<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Brand::with('logoMedia')->withCount('products')->orderBy('name')->paginate(50);

        return view('admin.brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('admin.brands.create');
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        Brand::create($data);

        return redirect()->route('admin.brands.index')->with('success', 'Brand created.');
    }

    public function quickCreate(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255|unique:brands,name']);

        $brand = Brand::create([
            'name'      => $request->name,
            'slug'      => Str::slug($request->name),
            'is_active' => true,
        ]);

        return response()->json(['id' => $brand->id, 'name' => $brand->name], 201);
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        $brand->update($data);

        return redirect()->route('admin.brands.index')->with('success', 'Brand updated.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->products()->exists()) {
            return back()->with('error', 'Cannot delete a brand that has products.');
        }

        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted.');
    }
}

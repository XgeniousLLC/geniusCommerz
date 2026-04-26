<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::with('parent', 'imageMedia')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(50);

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parents = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('admin.categories.create', compact('parents'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create(array_merge($request->validated(), [
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->input('sort_order', 0),
        ]));

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function quickCreate(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255']);

        $category = Category::create([
            'name'       => $request->name,
            'slug'       => Str::slug($request->name) . '-' . uniqid(),
            'is_active'  => true,
            'sort_order' => 0,
        ]);

        return response()->json(['id' => $category->id, 'name' => $category->name], 201);
    }

    public function edit(Category $category): View
    {
        $parents = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update(array_merge($request->validated(), [
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->input('sort_order', 0),
        ]));

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists()) {
            return back()->with('error', 'Cannot delete a category that has sub-categories.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }
}

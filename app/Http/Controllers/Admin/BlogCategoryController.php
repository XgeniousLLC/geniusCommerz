<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogCategoryController extends Controller
{
    public function index(): View
    {
        $categories = BlogCategory::withCount('blogs')->orderBy('name')->get();
        return view('admin.blog-categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:blog_categories,name',
            'slug' => 'nullable|string|max:100|unique:blog_categories,slug',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        BlogCategory::create($data);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Category created.');
    }

    public function update(Request $request, BlogCategory $blogCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:blog_categories,name,' . $blogCategory->id,
            'slug' => 'nullable|string|max:100|unique:blog_categories,slug,' . $blogCategory->id,
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $blogCategory->update($data);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Category updated.');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->delete();
        return redirect()->route('admin.blog-categories.index')->with('success', 'Category deleted.');
    }

    public function quickCreate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:blog_categories,name',
        ]);

        $data['slug'] = Str::slug($data['name']);

        $category = BlogCategory::create($data);

        return response()->json(['id' => $category->id, 'name' => $category->name]);
    }
}

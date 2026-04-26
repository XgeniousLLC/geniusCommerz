<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        $menus = Menu::withCount('items')->get();
        return view('admin.menus.index', compact('menus'));
    }

    public function create(): View
    {
        $locations = Menu::LOCATIONS;
        $usedLocations = Menu::pluck('location')->toArray();
        return view('admin.menus.create', compact('locations', 'usedLocations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'location' => 'required|string|in:' . implode(',', array_keys(Menu::LOCATIONS)),
        ]);

        $menu = Menu::create($data);
        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu created.');
    }

    public function edit(Menu $menu): View
    {
        $menu->load(['items' => fn($q) => $q->orderBy('sort_order')]);

        $tree      = $this->buildTree($menu->items->toArray(), null);
        $pages      = Page::where('status', 'published')->get(['id', 'title', 'slug']);
        $categories = Category::where('is_active', true)->get(['id', 'name', 'slug']);

        return view('admin.menus.edit', compact('menu', 'tree', 'pages', 'categories'));
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);
        $menu->update($data);
        return back()->with('success', 'Menu name updated.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();
        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted.');
    }

    public function addItem(Request $request, Menu $menu): RedirectResponse
    {
        $data = $request->validate([
            'label'     => 'required|string|max:150',
            'url'       => 'nullable|string|max:500',
            'type'      => 'required|in:custom,page,category',
            'item_id'   => 'nullable|integer',
            'parent_id' => 'nullable|exists:menu_items,id',
            'target'    => 'required|in:_self,_blank',
        ]);

        if ($data['type'] === 'page' && ! empty($data['item_id'])) {
            $page = Page::find($data['item_id']);
            $data['url']   = $page ? '/page/' . $page->slug : $data['url'];
            $data['label'] = $data['label'] ?: ($page?->title ?? 'Page');
        } elseif ($data['type'] === 'category' && ! empty($data['item_id'])) {
            $cat = Category::find($data['item_id']);
            $data['url']   = $cat ? '/shop/c/' . $cat->slug : $data['url'];
            $data['label'] = $data['label'] ?: ($cat?->name ?? 'Category');
        }

        $maxOrder = MenuItem::where('menu_id', $menu->id)
            ->where('parent_id', $data['parent_id'] ?? null)
            ->max('sort_order') ?? -1;

        MenuItem::create([
            'menu_id'    => $menu->id,
            'parent_id'  => $data['parent_id'] ?? null,
            'label'      => $data['label'],
            'url'        => $data['url'],
            'type'       => $data['type'],
            'item_id'    => $data['item_id'] ?? null,
            'target'     => $data['target'],
            'sort_order' => $maxOrder + 1,
            'is_active'  => true,
        ]);

        return back()->with('success', 'Item added.');
    }

    public function updateItem(Request $request, MenuItem $item): RedirectResponse
    {
        $data = $request->validate([
            'label'  => 'required|string|max:150',
            'url'    => 'nullable|string|max:500',
            'target' => 'required|in:_self,_blank',
        ]);
        $item->update($data);
        return back()->with('success', 'Item updated.');
    }

    public function destroyItem(MenuItem $item): RedirectResponse
    {
        // Orphan children by moving them to parent's level
        MenuItem::where('parent_id', $item->id)->update(['parent_id' => $item->parent_id]);
        $item->delete();
        return back()->with('success', 'Item removed.');
    }

    public function reorder(Request $request, Menu $menu): JsonResponse
    {
        $items = $request->validate(['items' => 'required|array'])['items'];
        $this->saveOrder($items, null);
        return response()->json(['ok' => true]);
    }

    private function saveOrder(array $items, ?int $parentId): void
    {
        foreach ($items as $index => $item) {
            MenuItem::where('id', $item['id'])->update([
                'sort_order' => $index,
                'parent_id'  => $parentId,
            ]);
            if (! empty($item['children'])) {
                $this->saveOrder($item['children'], (int) $item['id']);
            }
        }
    }

    private function buildTree(array $items, ?int $parentId): array
    {
        $branch = [];
        foreach ($items as $item) {
            if ($item['parent_id'] === $parentId) {
                $item['children'] = $this->buildTree($items, (int) $item['id']);
                $branch[] = $item;
            }
        }
        return $branch;
    }
}

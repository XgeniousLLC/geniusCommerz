@extends('admin.layouts.admin')

@section('title', 'Edit Menu — ' . $menu->name)

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li><a href="{{ route('admin.menus.index') }}" class="hover:text-gray-700">Menus</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">{{ $menu->name }}</li>
</ol>
@endsection

@section('page-header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $menu->name }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">Drag items to reorder. Drag <strong>into</strong> a parent to nest as a dropdown.</p>
    </div>
    @if($menu->location)
    <span class="text-sm font-medium bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 rounded-full">
        {{ \App\Models\Menu::LOCATIONS[$menu->location] ?? $menu->location }}
    </span>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
const REORDER_URL = "{{ route('admin.menus.reorder', $menu) }}";
const CSRF = "{{ csrf_token() }}";

function getTree(el) {
    const items = [];
    el.querySelectorAll(':scope > .menu-item-row').forEach(row => {
        const id = parseInt(row.dataset.id);
        const childList = row.querySelector('.children-list');
        items.push({ id, children: childList ? getTree(childList) : [] });
    });
    return items;
}

function saveOrder() {
    const root = document.getElementById('menu-tree-root');
    if (!root) return;
    fetch(REORDER_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ items: getTree(root) })
    }).then(r => {
        if (r.ok) showSaved();
    });
}

function showSaved() {
    const el = document.getElementById('save-indicator');
    if (!el) return;
    el.classList.remove('opacity-0');
    clearTimeout(el._t);
    el._t = setTimeout(() => el.classList.add('opacity-0'), 1800);
}

function initSortable(el) {
    new Sortable(el, {
        group: 'menu-items',
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        emptyInsertThreshold: 10,
        fallbackOnBody: true,
        swapThreshold: 0.65,
        filter: '.drop-placeholder',
        onEnd(evt) {
            // Hide drop placeholder in non-empty lists after drop
            document.querySelectorAll('.children-list').forEach(list => {
                const ph = list.querySelector('.drop-placeholder');
                if (ph) {
                    const hasRealItems = list.querySelectorAll('.menu-item-row').length > 0;
                    ph.style.display = hasRealItems ? 'none' : '';
                }
            });
            saveOrder();
        },
    });
    // Recurse into children lists
    el.querySelectorAll('.children-list').forEach(child => initSortable(child));
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('menu-tree-root');
    if (root) initSortable(root);
});

// Called from _item.blade.php "add sub-item" button
function setSubItemParent(id, label) {
    document.querySelectorAll('select[name="parent_id"]').forEach(sel => {
        const opt = [...sel.options].find(o => parseInt(o.value) === id);
        if (opt) sel.value = id;
    });
    // Scroll to and focus the custom link form
    const form = document.getElementById('add-custom-form');
    if (form) {
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        form.querySelector('input[name="label"]')?.focus();
    }
    // Update the parent hint
    const hint = document.getElementById('parent-hint');
    if (hint) hint.textContent = 'Sub-item of: ' + label;
}
</script>

<style>
.sortable-ghost  { opacity: 0.35; background: #EFF6FF !important; border-color: #93C5FD !important; border-radius: 8px; }
.sortable-chosen { box-shadow: 0 4px 16px rgba(0,0,0,.12); }
.sortable-drag   { opacity: 1 !important; }
</style>
@endpush

@section('content')
<div id="save-indicator" class="fixed top-4 right-6 z-50 opacity-0 transition-opacity duration-300 bg-green-600 text-white text-xs font-semibold px-4 py-2 rounded-full shadow-lg pointer-events-none">
    ✓ Order saved
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

    {{-- Left: current tree --}}
    <div class="lg:col-span-2">
        <x-admin.card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-gray-900">Menu Structure</h2>
                <div class="flex items-center gap-3 text-xs text-gray-400">
                    <span class="flex items-center gap-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-blue-300"></span> Dropdown group
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-gray-300"></span> Link
                    </span>
                    <span>Drag to reorder / nest</span>
                </div>
            </div>

            @if(empty($tree))
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h10M4 14h6M4 18h8"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-400">No items yet</p>
                    <p class="text-xs text-gray-300 mt-1">Add items from the panel on the right →</p>
                </div>
            @else
                <ul id="menu-tree-root" class="space-y-1">
                    @foreach($tree as $item)
                        @include('admin.menus._item', ['item' => $item, 'depth' => 0])
                    @endforeach
                </ul>
            @endif
        </x-admin.card>

        {{-- How dropdown nesting works --}}
        <div class="mt-4 p-4 rounded-xl border border-dashed border-gray-200 bg-gray-50 text-xs text-gray-500 space-y-1.5">
            <p class="font-semibold text-gray-600 mb-2">How nested dropdowns work</p>
            <p>① Add a <strong>Dropdown Group</strong> (label only, no URL) — this becomes the parent button in the navbar.</p>
            <p>② <strong>Drag</strong> existing items into it, <em>or</em> use the "Under" selector when adding new items.</p>
            <p>③ Click <strong>＋</strong> on any item to quickly add a sub-item under it.</p>
            <p>④ Assign this menu to <em>Main Navigation</em> in Menu Settings to see it live.</p>
        </div>
    </div>

    {{-- Right: panels --}}
    <div class="space-y-4" id="add-panels">

        {{-- Add Dropdown Group --}}
        <x-admin.card class="border-blue-100 bg-blue-50/30">
            <div class="flex items-center gap-2 mb-3">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/>
                    </svg>
                </span>
                <h3 class="text-sm font-semibold text-blue-900">Add Dropdown Group</h3>
            </div>
            <p class="text-xs text-blue-600/70 mb-3">A label-only parent that opens a dropdown in the navbar. Add links under it after.</p>
            <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="space-y-2">
                @csrf
                <input type="hidden" name="type" value="custom">
                <input type="hidden" name="url" value="">
                <input type="hidden" name="target" value="_self">
                <x-admin.input name="label" placeholder="e.g. Collections, Services…" required />
                <x-admin.button type="submit" class="w-full justify-center text-sm bg-blue-600 hover:bg-blue-700">Create Dropdown Group</x-admin.button>
            </form>
        </x-admin.card>

        {{-- Storefront Pages --}}
        <x-admin.card x-data="{ open: false }">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Storefront Pages</h3>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-3 space-y-1">
                @php
                    $staticPages = [
                        ['label' => 'Home',        'url' => '/'],
                        ['label' => 'Shop',        'url' => '/shop'],
                        ['label' => 'Blog',        'url' => '/blog'],
                        ['label' => 'Track Order', 'url' => '/track'],
                        ['label' => 'Wishlist',    'url' => '/wishlist'],
                        ['label' => 'Loyalty',     'url' => '/loyalty'],
                        ['label' => 'My Account',  'url' => '/account'],
                        ['label' => 'My Orders',   'url' => '/account/orders'],
                        ['label' => 'Contact Us',  'url' => '/contact'],
                    ];
                @endphp
                @foreach($staticPages as $sp)
                <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="type" value="custom">
                    <input type="hidden" name="label" value="{{ $sp['label'] }}">
                    <input type="hidden" name="url" value="{{ $sp['url'] }}">
                    <input type="hidden" name="target" value="_self">
                    <input type="hidden" name="parent_id" class="static-page-parent" value="">
                    <div class="flex-1 flex items-center gap-2 px-2 py-1.5 rounded bg-gray-50 border border-gray-100">
                        <span class="text-sm text-gray-700 flex-1">{{ $sp['label'] }}</span>
                        <span class="text-xs text-gray-400 font-mono">{{ $sp['url'] }}</span>
                    </div>
                    <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium whitespace-nowrap px-2 py-1.5 rounded border border-blue-200 hover:bg-blue-50 transition-colors">+ Add</button>
                </form>
                @endforeach
            </div>
        </x-admin.card>

        {{-- Add Custom Link --}}
        <x-admin.card>
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Add Custom Link</h3>
            <form id="add-custom-form" method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="space-y-2">
                @csrf
                <input type="hidden" name="type" value="custom">
                <div>
                    <label class="text-xs text-gray-500 block mb-0.5">Label</label>
                    <x-admin.input name="label" placeholder="e.g. About Us" required />
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-0.5">URL <span class="text-gray-300">(leave blank for dropdown group)</span></label>
                    <x-admin.input name="url" placeholder="e.g. /about or https://…" />
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs text-gray-500 block mb-0.5">Open in</label>
                        <select name="target" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm">
                            <option value="_self">Same tab</option>
                            <option value="_blank">New tab</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 block mb-0.5">Under (dropdown)</label>
                        <select name="parent_id" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm">
                            <option value="">Top level</option>
                            @foreach($menu->items->whereNull('parent_id') as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p id="parent-hint" class="text-xs text-blue-600 hidden-empty"></p>
                <x-admin.button type="submit" class="w-full justify-center text-sm">Add to Menu</x-admin.button>
            </form>
        </x-admin.card>

        {{-- Add Page --}}
        @if($pages->isNotEmpty())
        <x-admin.card>
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Add Page</h3>
            <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="space-y-2">
                @csrf
                <input type="hidden" name="type" value="page">
                <input type="hidden" name="target" value="_self">
                <select name="item_id" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm" required
                        onchange="this.form.label.value = this.options[this.selectedIndex].text">
                    <option value="">Select a page…</option>
                    @foreach($pages as $page)
                        <option value="{{ $page->id }}">{{ $page->title }}</option>
                    @endforeach
                </select>
                <x-admin.input name="label" placeholder="Label (auto-filled)" />
                <select name="parent_id" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm">
                    <option value="">Top level</option>
                    @foreach($menu->items->whereNull('parent_id') as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->label }}</option>
                    @endforeach
                </select>
                <x-admin.button type="submit" class="w-full justify-center text-sm">Add Page</x-admin.button>
            </form>
        </x-admin.card>
        @endif

        {{-- Add Category --}}
        @if($categories->isNotEmpty())
        <x-admin.card>
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Add Category</h3>
            <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="space-y-2">
                @csrf
                <input type="hidden" name="type" value="category">
                <input type="hidden" name="target" value="_self">
                <select name="item_id" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm" required
                        onchange="this.form.label.value = this.options[this.selectedIndex].text">
                    <option value="">Select a category…</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <x-admin.input name="label" placeholder="Label (auto-filled)" />
                <select name="parent_id" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm">
                    <option value="">Top level</option>
                    @foreach($menu->items->whereNull('parent_id') as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->label }}</option>
                    @endforeach
                </select>
                <x-admin.button type="submit" class="w-full justify-center text-sm">Add Category</x-admin.button>
            </form>
        </x-admin.card>
        @endif

        {{-- Menu Settings --}}
        <x-admin.card>
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Menu Settings</h3>
            <form method="POST" action="{{ route('admin.menus.update', $menu) }}" class="space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="text-xs text-gray-500 block mb-0.5">Menu Name</label>
                    <x-admin.input name="name" value="{{ $menu->name }}" required />
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-0.5">Display Location</label>
                    <select name="location" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm">
                        <option value="">— None —</option>
                        @foreach(\App\Models\Menu::LOCATIONS as $slug => $label)
                            <option value="{{ $slug }}" {{ $menu->location === $slug ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Set to <em>Main Navigation</em> to display in the storefront header.</p>
                </div>
                <x-admin.button type="submit" variant="secondary" class="w-full justify-center text-sm">Save Settings</x-admin.button>
            </form>
        </x-admin.card>

    </div>
</div>
@endsection

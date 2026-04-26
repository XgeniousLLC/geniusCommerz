@extends('admin.layouts.admin')

@section('title', 'Edit Menu')

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
    <h1 class="text-2xl font-bold text-gray-900">{{ $menu->name }}</h1>
    <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
        {{ \App\Models\Menu::LOCATIONS[$menu->location] ?? $menu->location }}
    </span>
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
    const items = getTree(root);
    fetch(REORDER_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ items })
    });
}

function initSortable(el) {
    Sortable.create(el, {
        group: 'menu',
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'opacity-40',
        onEnd: saveOrder,
    });
    el.querySelectorAll(':scope > .menu-item-row .children-list').forEach(child => {
        initSortable(child);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('menu-tree-root');
    if (root) initSortable(root);
});
</script>
@endpush

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: current items tree --}}
    <div class="lg:col-span-2">
        <x-admin.card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-gray-900">Menu Items</h2>
                <p class="text-xs text-gray-500">Drag to reorder · Nest under parents</p>
            </div>

            @if(empty($tree))
                <p class="text-sm text-gray-400 text-center py-8">No items yet. Add items from the panel →</p>
            @else
                <ul id="menu-tree-root" class="space-y-1">
                    @foreach($tree as $item)
                        @include('admin.menus._item', ['item' => $item, 'depth' => 0])
                    @endforeach
                </ul>
            @endif
        </x-admin.card>
    </div>

    {{-- Right: add items + rename --}}
    <div class="space-y-4">

        {{-- Static Pages --}}
        <x-admin.card x-data="{ open: false }">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Storefront Pages</h3>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open?'rotate-180':''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-collapse class="mt-3 space-y-1">
                @php
                    $staticPages = [
                        ['label' => 'Home',          'url' => '/'],
                        ['label' => 'Shop',          'url' => '/shop'],
                        ['label' => 'Blog',          'url' => '/blog'],
                        ['label' => 'Track Order',   'url' => '/track'],
                        ['label' => 'Wishlist',      'url' => '/wishlist'],
                        ['label' => 'Loyalty',       'url' => '/loyalty'],
                        ['label' => 'My Account',    'url' => '/account'],
                        ['label' => 'My Orders',     'url' => '/account/orders'],
                        ['label' => 'Contact Us',    'url' => '/contact'],
                    ];
                @endphp
                @foreach($staticPages as $sp)
                <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="hidden" name="type" value="custom">
                    <input type="hidden" name="label" value="{{ $sp['label'] }}">
                    <input type="hidden" name="url" value="{{ $sp['url'] }}">
                    <input type="hidden" name="target" value="_self">
                    <div class="flex-1 flex items-center gap-2 px-2 py-1.5 rounded bg-gray-50 border border-gray-100">
                        <span class="text-sm text-gray-700 flex-1">{{ $sp['label'] }}</span>
                        <span class="text-xs text-gray-400 font-mono">{{ $sp['url'] }}</span>
                    </div>
                    <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium whitespace-nowrap px-2 py-1.5 rounded border border-blue-200 hover:bg-blue-50 transition-colors">+ Add</button>
                </form>
                @endforeach
            </div>
        </x-admin.card>

        {{-- Add custom link --}}
        <x-admin.card>
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Add Custom Link</h3>
            <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="space-y-2">
                @csrf
                <input type="hidden" name="type" value="custom">
                <div>
                    <label class="text-xs text-gray-500 block mb-0.5">Label</label>
                    <x-admin.input name="label" placeholder="e.g. About Us" required />
                </div>
                <div>
                    <label class="text-xs text-gray-500 block mb-0.5">URL</label>
                    <x-admin.input name="url" placeholder="e.g. /about or https://…" />
                </div>
                <div class="flex gap-2">
                    <div class="flex-1">
                        <label class="text-xs text-gray-500 block mb-0.5">Open in</label>
                        <select name="target" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm">
                            <option value="_self">Same tab</option>
                            <option value="_blank">New tab</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="text-xs text-gray-500 block mb-0.5">Under</label>
                        <select name="parent_id" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm">
                            <option value="">Top level</option>
                            @foreach($menu->items->whereNull('parent_id') as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <x-admin.button type="submit" class="w-full justify-center text-sm">Add to Menu</x-admin.button>
            </form>
        </x-admin.card>

        {{-- Add page --}}
        @if($pages->isNotEmpty())
        <x-admin.card>
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Add Page</h3>
            <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="space-y-2">
                @csrf
                <input type="hidden" name="type" value="page">
                <input type="hidden" name="target" value="_self">
                <select name="item_id" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm" required onchange="this.form.label.value = this.options[this.selectedIndex].text">
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

        {{-- Add category --}}
        @if($categories->isNotEmpty())
        <x-admin.card>
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Add Category</h3>
            <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="space-y-2">
                @csrf
                <input type="hidden" name="type" value="category">
                <input type="hidden" name="target" value="_self">
                <select name="item_id" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm" required onchange="this.form.label.value = this.options[this.selectedIndex].text">
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
                    <p class="text-xs text-gray-400 mt-1">Assign a location to use this menu in the storefront (e.g. Main Navigation).</p>
                </div>
                <x-admin.button type="submit" variant="secondary" class="w-full justify-center text-sm">Save Settings</x-admin.button>
            </form>
        </x-admin.card>
    </div>
</div>
@endsection

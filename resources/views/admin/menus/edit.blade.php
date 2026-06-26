@extends('admin.layouts.admin')
@section('title', 'Edit Menu — ' . $menu->name)

@push('styles')
<style>
[x-cloak] { display: none !important; }
.sortable-ghost  { opacity: 0.35; background: color-mix(in srgb, var(--info) 8%, transparent) !important; border-color: color-mix(in srgb, var(--info) 40%, transparent) !important; border-radius: 8px; }
.sortable-chosen { box-shadow: 0 4px 16px rgba(0,0,0,.12); }
.sortable-drag   { opacity: 1 !important; }
</style>
@endpush

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
    }).then(r => { if (r.ok) showSaved(); });
}

function showSaved() {
    const el = document.getElementById('save-indicator');
    if (!el) return;
    el.style.opacity = '1';
    clearTimeout(el._t);
    el._t = setTimeout(() => { el.style.opacity = '0'; }, 1800);
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
            document.querySelectorAll('.children-list').forEach(list => {
                const ph = list.querySelector('.drop-placeholder');
                if (ph) {
                    ph.style.display = list.querySelectorAll('.menu-item-row').length > 0 ? 'none' : '';
                }
            });
            saveOrder();
        },
    });
    el.querySelectorAll('.children-list').forEach(child => initSortable(child));
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('menu-tree-root');
    if (root) initSortable(root);
});

function setSubItemParent(id, label) {
    document.querySelectorAll('select[name="parent_id"]').forEach(sel => {
        const opt = [...sel.options].find(o => parseInt(o.value) === id);
        if (opt) sel.value = id;
    });
    const form = document.getElementById('add-custom-form');
    if (form) {
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        form.querySelector('input[name="label"]')?.focus();
    }
    const hint = document.getElementById('parent-hint');
    if (hint) { hint.textContent = 'Sub-item of: ' + label; hint.style.display = 'block'; }
}
</script>
@endpush

@section('content')
<div id="save-indicator" style="position:fixed;top:16px;right:24px;z-index:50;opacity:0;transition:opacity .3s;background:var(--success);color:#fff;font-size:12px;font-weight:700;padding:6px 16px;border-radius:999px;box-shadow:0 2px 8px rgba(0,0,0,.18);pointer-events:none">
    Saved
</div>

<div class="page-head">
    <div>
        <h2 class="display">{{ $menu->name }}</h2>
        <div class="faint" style="font-size:13px;margin-top:2px">Drag items to reorder. Drag <strong>into</strong> a parent to nest as dropdown.</div>
    </div>
    <div class="row" style="gap:8px">
        @if($menu->location)
        <span class="pill info">{{ \App\Models\Menu::LOCATIONS[$menu->location] ?? $menu->location }}</span>
        @endif
        <a href="{{ route('admin.menus.index') }}" class="btn btn-outline">
            <span class="ico" data-ico="arrowLeft" style="width:16px;height:16px"></span>Back
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:18px;align-items:start">

    {{-- Left: tree --}}
    <div class="col-gap" style="--gap:14px">
        <div class="card pad">
            <div class="between" style="margin-bottom:16px">
                <h3 style="font-weight:700;font-size:14px">Menu Structure</h3>
                <div class="row" style="gap:12px;font-size:11.5px;color:var(--text-muted)">
                    <span class="row" style="gap:4px">
                        <span style="width:8px;height:8px;border-radius:50%;background:var(--info);display:inline-block"></span>Dropdown
                    </span>
                    <span class="row" style="gap:4px">
                        <span style="width:8px;height:8px;border-radius:50%;background:var(--border);display:inline-block"></span>Link
                    </span>
                    <span>Drag to reorder / nest</span>
                </div>
            </div>

            @if(empty($tree))
                <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 20px;text-align:center">
                    <span class="tile t-info" style="width:52px;height:52px;margin:0 auto 12px">
                        <span class="ico" data-ico="list" style="width:24px;height:24px"></span>
                    </span>
                    <div style="font-size:13.5px;font-weight:600;color:var(--text-muted)">No items yet</div>
                    <div class="faint" style="font-size:12px;margin-top:4px">Add items from the panel on the right</div>
                </div>
            @else
                <ul id="menu-tree-root" style="display:flex;flex-direction:column;gap:4px">
                    @foreach($tree as $item)
                        @include('admin.menus._item', ['item' => $item, 'depth' => 0])
                    @endforeach
                </ul>
            @endif
        </div>

        <div style="padding:14px 16px;border-radius:10px;border:1px dashed var(--border);background:var(--surface-2);font-size:12.5px;color:var(--text-muted)" class="col-gap" style="--gap:6px">
            <div style="font-weight:700;font-size:13px;color:var(--text);margin-bottom:6px">How nested dropdowns work</div>
            <div>① Add a <strong>Dropdown Group</strong> — label only, no URL. This becomes the parent button in the navbar.</div>
            <div>② <strong>Drag</strong> existing items into it, or use the "Under" selector when adding new items.</div>
            <div>③ Click <strong>+</strong> on any item to quickly add a sub-item under it.</div>
            <div>④ Assign this menu to <em>Main Navigation</em> in Menu Settings to see it live.</div>
        </div>
    </div>

    {{-- Right: panels --}}
    <div id="add-panels" class="col-gap" style="--gap:14px">

        {{-- Dropdown Group --}}
        <div class="card pad" style="background:color-mix(in srgb,var(--info) 4%,transparent);border-color:color-mix(in srgb,var(--info) 20%,transparent)">
            <div class="row" style="gap:8px;margin-bottom:10px">
                <span class="tile sm t-info"><span class="ico" data-ico="list" style="width:16px;height:16px"></span></span>
                <h3 style="font-weight:700;font-size:13.5px;color:var(--info)">Add Dropdown Group</h3>
            </div>
            <div class="faint" style="font-size:12px;margin-bottom:10px">Label-only parent that opens a dropdown in the navbar.</div>
            <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" style="display:flex;flex-direction:column;gap:8px">
                @csrf
                <input type="hidden" name="type" value="custom">
                <input type="hidden" name="url" value="">
                <input type="hidden" name="target" value="_self">
                <input class="input" type="text" name="label" placeholder="e.g. Collections, Services…" required>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;background:var(--info);border-color:var(--info)">Create Dropdown Group</button>
            </form>
        </div>

        {{-- Storefront Pages --}}
        <div class="card pad" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="between" style="width:100%;background:none;border:none;cursor:pointer">
                <h3 style="font-weight:700;font-size:13.5px">Storefront Pages</h3>
                <span class="ico" data-ico="chevronDown" style="width:15px;height:15px;color:var(--text-muted);transition:transform .2s" :style="open ? 'transform:rotate(180deg)' : ''"></span>
            </button>
            <div x-show="open" style="margin-top:12px" class="col-gap" style="--gap:6px">
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
                <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" style="display:flex;align-items:center;gap:8px">
                    @csrf
                    <input type="hidden" name="type" value="custom">
                    <input type="hidden" name="label" value="{{ $sp['label'] }}">
                    <input type="hidden" name="url" value="{{ $sp['url'] }}">
                    <input type="hidden" name="target" value="_self">
                    <input type="hidden" name="parent_id" class="static-page-parent" value="">
                    <div style="flex:1;display:flex;align-items:center;justify-content:space-between;padding:6px 10px;border-radius:8px;border:1px solid var(--border);background:var(--surface-2)">
                        <span style="font-size:13px">{{ $sp['label'] }}</span>
                        <span class="faint mono" style="font-size:11px">{{ $sp['url'] }}</span>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline" style="font-size:11.5px;white-space:nowrap">+ Add</button>
                </form>
                @endforeach
            </div>
        </div>

        {{-- Custom Link --}}
        <div class="card pad">
            <h3 style="font-weight:700;font-size:13.5px;margin-bottom:12px">Add Custom Link</h3>
            <form id="add-custom-form" method="POST" action="{{ route('admin.menus.items.store', $menu) }}" style="display:flex;flex-direction:column;gap:10px">
                @csrf
                <input type="hidden" name="type" value="custom">
                <div class="field" style="margin:0">
                    <span class="lbl" style="font-size:11px">Label</span>
                    <input class="input" type="text" name="label" placeholder="e.g. About Us" required style="height:34px">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl" style="font-size:11px">URL <span class="faint">(blank = dropdown group)</span></span>
                    <input class="input" type="text" name="url" placeholder="e.g. /about or https://…" style="height:34px">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl" style="font-size:11px">Open in</span>
                    <select class="input" name="target" style="height:34px">
                        <option value="_self">Same tab</option>
                        <option value="_blank">New tab</option>
                    </select>
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl" style="font-size:11px">Under (dropdown)</span>
                    <select class="input" name="parent_id" style="height:34px">
                        <option value="">Top level</option>
                        @foreach($menu->items->whereNull('parent_id') as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->label }}</option>
                        @endforeach
                    </select>
                </div>
                <p id="parent-hint" class="faint" style="font-size:11.5px;display:none;color:var(--info)"></p>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Add to Menu</button>
            </form>
        </div>

        {{-- Add Page --}}
        @if($pages->isNotEmpty())
        <div class="card pad">
            <h3 style="font-weight:700;font-size:13.5px;margin-bottom:12px">Add Page</h3>
            <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" style="display:flex;flex-direction:column;gap:10px">
                @csrf
                <input type="hidden" name="type" value="page">
                <input type="hidden" name="target" value="_self">
                <select class="input" name="item_id" required                    onchange="this.form.label.value = this.options[this.selectedIndex].text">
                    <option value="">Select a page…</option>
                    @foreach($pages as $page)
                        <option value="{{ $page->id }}">{{ $page->title }}</option>
                    @endforeach
                </select>
                <input class="input" type="text" name="label" placeholder="Label (auto-filled)" style="height:34px">
                <select class="input" name="parent_id" style="height:34px">
                    <option value="">Top level</option>
                    @foreach($menu->items->whereNull('parent_id') as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Add Page</button>
            </form>
        </div>
        @endif

        {{-- Add Category --}}
        @if($categories->isNotEmpty())
        <div class="card pad">
            <h3 style="font-weight:700;font-size:13.5px;margin-bottom:12px">Add Category</h3>
            <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" style="display:flex;flex-direction:column;gap:10px">
                @csrf
                <input type="hidden" name="type" value="category">
                <input type="hidden" name="target" value="_self">
                <select class="input" name="item_id" required                    onchange="this.form.label.value = this.options[this.selectedIndex].text">
                    <option value="">Select a category…</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <input class="input" type="text" name="label" placeholder="Label (auto-filled)" style="height:34px">
                <select class="input" name="parent_id" style="height:34px">
                    <option value="">Top level</option>
                    @foreach($menu->items->whereNull('parent_id') as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Add Category</button>
            </form>
        </div>
        @endif

        {{-- Menu Settings --}}
        <div class="card pad">
            <h3 style="font-weight:700;font-size:13.5px;margin-bottom:12px">Menu Settings</h3>
            <form method="POST" action="{{ route('admin.menus.update', $menu) }}" style="display:flex;flex-direction:column;gap:10px">
                @csrf @method('PUT')
                <div class="field" style="margin:0">
                    <span class="lbl" style="font-size:11px">Menu Name</span>
                    <input class="input" type="text" name="name" value="{{ $menu->name }}" required style="height:34px">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl" style="font-size:11px">Display Location</span>
                    <select class="input" name="location" style="height:34px">
                        <option value="">— None —</option>
                        @foreach(\App\Models\Menu::LOCATIONS as $slug => $label)
                            <option value="{{ $slug }}" {{ $menu->location === $slug ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="hint">Set to <em>Main Navigation</em> to display in the storefront header.</p>
                </div>
                <button type="submit" class="btn btn-outline" style="width:100%;justify-content:center">Save Settings</button>
            </form>
        </div>

    </div>
</div>
@endsection

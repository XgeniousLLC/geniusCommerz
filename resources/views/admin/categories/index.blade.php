@extends('admin.layouts.admin')

@section('title', 'Categories')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Categories</h2>
        <div class="sub">Organize your catalog into shoppable collections</div>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.categories.create') }}">
        <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>Add category
    </a>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr))">
    <div class="card lift stat">
        <span class="tile t-info"><span class="ico" data-ico="tag"></span></span>
        <div><div class="num">{{ $stats['total'] }}</div><div class="lbl">Total categories</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-violet"><span class="ico" data-ico="layers"></span></span>
        <div><div class="num">{{ $stats['top_level'] }}</div><div class="lbl">Top-level</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-success"><span class="ico" data-ico="check"></span></span>
        <div><div class="num">{{ $stats['active'] }}</div><div class="lbl">Active</div></div>
    </div>
</div>

<form method="GET" style="margin-bottom:16px">
    <div class="between">
        <div class="search-field grow" style="max-width:360px">
            <span class="ico" data-ico="search"></span>
            <input class="input" name="search" value="{{ request('search') }}" placeholder="Search categories…">
        </div>
    </div>
</form>

<div class="card" style="padding:8px">
    @forelse($categories as $category)
    <div id="cat-wrap-{{ $category->id }}">
        {{-- Parent row --}}
        <div class="hoverrow row" style="gap:13px;padding:12px 14px;border-radius:12px">
            @if($category->children->isNotEmpty())
            <button type="button" class="icon-btn" style="width:26px;height:26px;background:var(--surface-3)"
                onclick="toggleCatChildren({{ $category->id }}, this)">
                <span class="ico" data-ico="chevRight" style="width:15px;height:15px;transition:transform .2s"></span>
            </button>
            @else
            <span style="width:26px;height:26px;flex-shrink:0"></span>
            @endif

            @if($category->imageMedia)
                <img src="{{ $category->imageMedia->getUrl('thumb') }}" alt="{{ $category->name }}"
                     style="width:40px;height:40px;border-radius:10px;object-fit:cover;flex-shrink:0">
            @else
                <span class="tile t-info"><span class="ico" data-ico="tag" style="width:21px;height:21px"></span></span>
            @endif

            <div class="grow">
                <div class="row" style="gap:8px">
                    <span style="font-weight:700;font-size:14.5px">{{ $category->name }}</span>
                </div>
                <div class="mono faint" style="font-size:12px">/{{ $category->slug }}</div>
            </div>

            <span class="faint" style="font-size:13px;font-weight:600;white-space:nowrap">
                @if($category->children->isNotEmpty())
                    {{ $category->children->count() }} sub ·
                @endif
                <b style="color:var(--text)">{{ $category->products_count }}</b> products
            </span>

            @if($category->is_active)
                <span class="pill sm success"><span class="dot"></span>Visible</span>
            @else
                <span class="pill sm"><span class="dot"></span>Hidden</span>
            @endif

            <div class="row" style="gap:4px">
                <a class="icon-btn" href="{{ route('admin.categories.edit', $category) }}" title="Edit">
                    <span class="ico" data-ico="edit" style="width:16px;height:16px"></span>
                </a>
                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                      onsubmit="return confirm('Delete {{ addslashes($category->name) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="icon-btn danger" title="Delete">
                        <span class="ico" data-ico="trash" style="width:16px;height:16px"></span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Children rows --}}
        @if($category->children->isNotEmpty())
        <div id="cat-children-{{ $category->id }}" style="display:none">
            @foreach($category->children as $child)
            <div class="hoverrow row" style="gap:12px;padding:10px 14px;border-radius:12px;margin-left:52px">
                @if($child->imageMedia)
                    <img src="{{ $child->imageMedia->getUrl('thumb') }}" alt="{{ $child->name }}"
                         style="width:32px;height:32px;border-radius:8px;object-fit:cover;flex-shrink:0">
                @else
                    <span class="tile sm muted"><span class="ico" data-ico="tag" style="width:15px;height:15px"></span></span>
                @endif

                <div class="grow">
                    <div style="font-weight:600;font-size:13.5px">{{ $child->name }}</div>
                    <div class="mono faint" style="font-size:11.5px">/{{ $child->slug }}</div>
                </div>

                <span class="faint" style="font-size:13px;font-weight:600;white-space:nowrap">
                    <b style="color:var(--text)">{{ $child->products_count }}</b> products
                </span>

                @if($child->is_active)
                    <span class="pill sm success"><span class="dot"></span>Visible</span>
                @else
                    <span class="pill sm"><span class="dot"></span>Hidden</span>
                @endif

                <div class="row" style="gap:4px">
                    <a class="icon-btn" href="{{ route('admin.categories.edit', $child) }}" title="Edit">
                        <span class="ico" data-ico="edit" style="width:16px;height:16px"></span>
                    </a>
                    <form method="POST" action="{{ route('admin.categories.destroy', $child) }}"
                          onsubmit="return confirm('Delete {{ addslashes($child->name) }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="icon-btn danger" title="Delete">
                            <span class="ico" data-ico="trash" style="width:16px;height:16px"></span>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @empty
    <div style="padding:40px;text-align:center;color:var(--text-faint)">No categories yet.</div>
    @endforelse
</div>

@if($categories->hasPages())
<div class="between" style="padding:16px 0">
    <span class="faint" style="font-size:13px">
        Showing {{ $categories->firstItem() }}–{{ $categories->lastItem() }} of {{ $categories->total() }}
    </span>
    <div class="row" style="gap:6px">
        @if($categories->onFirstPage())
            <button class="icon-btn" disabled>
                <span class="ico" data-ico="chevLeft" style="width:18px;height:18px"></span>
            </button>
        @else
            <a class="icon-btn" href="{{ $categories->previousPageUrl() }}">
                <span class="ico" data-ico="chevLeft" style="width:18px;height:18px"></span>
            </a>
        @endif

        @foreach($categories->getUrlRange(max(1,$categories->currentPage()-2), min($categories->lastPage(),$categories->currentPage()+2)) as $page => $url)
            @if($page == $categories->currentPage())
                <button class="btn btn-sm" style="background:var(--accent);color:#fff;width:34px;padding:0;height:34px">{{ $page }}</button>
            @else
                <a class="icon-btn" href="{{ $url }}" style="width:34px;height:34px">{{ $page }}</a>
            @endif
        @endforeach

        @if($categories->hasMorePages())
            <a class="icon-btn" href="{{ $categories->nextPageUrl() }}">
                <span class="ico" data-ico="chevRight" style="width:18px;height:18px"></span>
            </a>
        @else
            <button class="icon-btn" disabled>
                <span class="ico" data-ico="chevRight" style="width:18px;height:18px"></span>
            </button>
        @endif
    </div>
</div>
@endif

@push('scripts')
<script>
function toggleCatChildren(id, btn) {
    var el = document.getElementById('cat-children-' + id);
    if (!el) return;
    var open = el.style.display !== 'none';
    el.style.display = open ? 'none' : '';
    var ico = btn.querySelector('.ico');
    if (ico) ico.style.transform = open ? '' : 'rotate(90deg)';
}
</script>
@endpush

@endsection

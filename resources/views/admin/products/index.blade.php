@extends('admin.layouts.admin')

@section('title', 'Products')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Products</h2>
        <div class="sub">
            <b style="color:var(--text)">{{ number_format($productStats['total']) }}</b> products ·
            <b style="color:var(--text)">{{ number_format($productStats['stock']) }}</b> units in stock
        </div>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.products.create') }}">
        <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>Add product
    </a>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr))">
    <div class="card lift stat">
        <span class="tile t-info"><span class="ico" data-ico="package"></span></span>
        <div><div class="num">{{ $productStats['total'] }}</div><div class="lbl">Total products</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-success"><span class="ico" data-ico="check"></span></span>
        <div><div class="num">{{ $productStats['active'] }}</div><div class="lbl">Active</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-warning"><span class="ico" data-ico="clock"></span></span>
        <div><div class="num">{{ $productStats['draft'] }}</div><div class="lbl">Drafts</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-pop"><span class="ico" data-ico="bell"></span></span>
        <div><div class="num">{{ $productStats['low'] }}</div><div class="lbl">Low / out of stock</div></div>
    </div>
</div>

<form method="GET">
    <div class="between wrap" style="margin-bottom:16px;gap:12px">
        <div class="search-field grow" style="max-width:360px">
            <span class="ico" data-ico="search"></span>
            <input class="input" name="search" value="{{ request('search') }}" placeholder="Search products or SKU…">
        </div>
        <div class="row" style="gap:10px">
            <div class="seg sm">
                <button type="button" onclick="location.href='{{ route('admin.products.index', request()->except('status')) }}'"
                        class="{{ !request('status') ? 'active' : '' }}">All</button>
                <button type="button" onclick="location.href='{{ route('admin.products.index', array_merge(request()->except('status'), ['status'=>'active'])) }}'"
                        class="{{ request('status') === 'active' ? 'active' : '' }}">Active</button>
                <button type="button" onclick="location.href='{{ route('admin.products.index', array_merge(request()->except('status'), ['status'=>'draft'])) }}'"
                        class="{{ request('status') === 'draft' ? 'active' : '' }}">Draft</button>
                <button type="button" onclick="location.href='{{ route('admin.products.index', array_merge(request()->except('status'), ['status'=>'archived'])) }}'"
                        class="{{ request('status') === 'archived' ? 'active' : '' }}">Archived</button>
            </div>
            <div class="select-wrap" style="min-width:150px">
                <select class="select" name="brand_id" onchange="this.form.submit()">
                    <option value="">All brands</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="select-wrap" style="min-width:160px">
                <select class="select" name="category_id" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @foreach($cat->children as $child)
                            <option value="{{ $child->id }}" {{ request('category_id') == $child->id ? 'selected' : '' }}>
                                &nbsp;&nbsp;— {{ $child->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            @if(request()->anyFilled(['search','status','brand_id','category_id']))
                <a href="{{ route('admin.products.index') }}" class="btn btn-soft btn-sm">Clear</a>
            @endif
        </div>
    </div>
</form>

<div class="card flush">
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th style="width:34px"></th>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th style="text-align:right">Price</th>
                    <th style="text-align:right">Stock</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                @php
                    $thumb = $product->images->first();
                    $stockQty = $product->has_variants
                        ? $product->variants->sum('stock_qty')
                        : $product->stock_qty;
                    $stockColor = $stockQty === 0 ? 'var(--danger)' : ($stockQty <= 10 ? 'var(--warning)' : '');
                    $statusPill = match($product->status) {
                        'active'   => 'success',
                        'archived' => 'danger',
                        default    => '',
                    };
                    $statusLabel = $product->status === 'draft' ? 'Draft' : ucfirst($product->status);
                @endphp
                <tr class="hoverable" onclick="location.href='{{ route('admin.products.edit', $product) }}'">
                    <td><input class="check" type="checkbox" onclick="event.stopPropagation()"></td>
                    <td>
                        <div class="row" style="gap:11px">
                            @if($thumb)
                                <img class="thumb" style="width:42px;height:42px"
                                     src="{{ $thumb->getUrl('thumb') }}" alt="{{ $thumb->alt ?: $product->name }}">
                            @else
                                <span class="tile muted" style="width:42px;height:42px;border-radius:10px;flex-shrink:0">
                                    <span class="ico" data-ico="image" style="width:20px;height:20px"></span>
                                </span>
                            @endif
                            <div>
                                <div style="font-weight:700;font-size:13.5px">
                                    {{ $product->name }}
                                    @if($product->is_featured)
                                        <span class="ico" data-ico="star" data-stroke="0"
                                              style="width:13px;height:13px;color:var(--pop);vertical-align:-2px"></span>
                                    @endif
                                </div>
                                <div class="mono faint" style="font-size:11.5px">
                                    {{ $product->has_variants ? $product->variants_count.' variant(s)' : ($product->sku ?? '—') }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="muted" style="font-size:13.5px">
                        {{ $product->categories->pluck('name')->join(', ') ?: '—' }}
                    </td>
                    <td class="muted" style="font-size:13.5px">{{ $product->brand?->name ?? '—' }}</td>
                    <td style="text-align:right" class="tnum"><b>{{ $product->displayPrice() }}</b></td>
                    <td style="text-align:right" class="tnum">
                        <b @if($stockColor) style="color:{{ $stockColor }}" @endif>
                            {{ $stockQty !== null ? number_format((int)$stockQty) : '—' }}
                        </b>
                    </td>
                    <td>
                        <span class="pill sm {{ $statusPill }}">
                            <span class="dot"></span>{{ $statusLabel }}
                        </span>
                    </td>
                    <td style="text-align:right">
                        <div class="row" style="gap:4px;justify-content:flex-end" onclick="event.stopPropagation()">
                            <a class="icon-btn" href="{{ route('admin.products.edit', $product) }}" title="Edit">
                                <span class="ico" data-ico="edit" style="width:17px;height:17px"></span>
                            </a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                  onsubmit="return confirm('Delete {{ addslashes($product->name) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="icon-btn danger" title="Delete">
                                    <span class="ico" data-ico="trash" style="width:17px;height:17px"></span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:40px;color:var(--text-faint)">No products found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div class="between" style="padding:16px 22px;border-top:1px solid var(--border)">
        <span class="faint" style="font-size:13px">
            Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }}
        </span>
        <div class="row" style="gap:6px">
            @if($products->onFirstPage())
                <button class="icon-btn" disabled>
                    <span class="ico" data-ico="chevLeft" style="width:18px;height:18px"></span>
                </button>
            @else
                <a class="icon-btn" href="{{ $products->previousPageUrl() }}">
                    <span class="ico" data-ico="chevLeft" style="width:18px;height:18px"></span>
                </a>
            @endif

            @foreach($products->getUrlRange(max(1,$products->currentPage()-2), min($products->lastPage(),$products->currentPage()+2)) as $page => $url)
                @if($page == $products->currentPage())
                    <button class="btn btn-sm" style="background:var(--accent);color:#fff;width:34px;padding:0;height:34px">{{ $page }}</button>
                @else
                    <a class="icon-btn" href="{{ $url }}" style="width:34px;height:34px">{{ $page }}</a>
                @endif
            @endforeach

            @if($products->hasMorePages())
                <a class="icon-btn" href="{{ $products->nextPageUrl() }}">
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
</div>

@endsection

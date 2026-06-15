@extends('admin.layouts.admin')

@section('title', 'Brands')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Brands</h2>
        <div class="sub">Manage the brands you stock and sell</div>
    </div>
    <a class="btn btn-primary" href="{{ route('admin.brands.create') }}">
        <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>Add brand
    </a>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr))">
    <div class="card lift stat">
        <span class="tile t-info"><span class="ico" data-ico="bookmark"></span></span>
        <div><div class="num">{{ $stats['total'] }}</div><div class="lbl">Brands</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-success"><span class="ico" data-ico="check"></span></span>
        <div><div class="num">{{ $stats['active'] }}</div><div class="lbl">Active</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-teal"><span class="ico" data-ico="package"></span></span>
        <div><div class="num">{{ $stats['products'] }}</div><div class="lbl">Products</div></div>
    </div>
</div>

<form method="GET" style="margin-bottom:18px">
    <div class="search-field" style="max-width:360px">
        <span class="ico" data-ico="search"></span>
        <input class="input" name="search" value="{{ request('search') }}" placeholder="Search brands…">
    </div>
</form>

@php
$tileColors = ['t-info','t-pop','t-teal','t-violet','t-warning','t-accent','t-success'];
@endphp

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px">
    @forelse($brands as $i => $brand)
    <div class="card lift" style="padding:20px;display:flex;flex-direction:column;gap:14px">
        <div class="row top" style="gap:13px">
            @if($brand->logoMedia)
                <img src="{{ $brand->logoMedia->getUrl('thumb') }}" alt="{{ $brand->name }}"
                     style="width:48px;height:48px;border-radius:12px;object-fit:contain;background:var(--surface-2);flex-shrink:0">
            @else
                @php $tc = $tileColors[$i % count($tileColors)]; @endphp
                <span class="tile lg {{ $tc }}" style="font-family:var(--font-display);font-weight:800;font-size:22px;flex-shrink:0">
                    {{ strtoupper(substr($brand->name, 0, 1)) }}
                </span>
            @endif
            <div class="grow">
                <div style="font-weight:700;font-size:16px">{{ $brand->name }}</div>
                <div class="mono faint" style="font-size:12px">/{{ $brand->slug }}</div>
            </div>
            <div class="row" style="gap:4px">
                <a class="icon-btn" href="{{ route('admin.brands.edit', $brand) }}" title="Edit">
                    <span class="ico" data-ico="edit" style="width:16px;height:16px"></span>
                </a>
                <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}"
                      onsubmit="return confirm('Delete {{ addslashes($brand->name) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="icon-btn danger" title="Delete">
                        <span class="ico" data-ico="trash" style="width:16px;height:16px"></span>
                    </button>
                </form>
            </div>
        </div>

        <div class="between">
            <span class="faint" style="font-size:13px;font-weight:600">
                <b style="color:var(--text)">{{ $brand->products_count }}</b> products
            </span>
            @if($brand->is_active)
                <span class="pill sm success"><span class="dot"></span>Active</span>
            @else
                <span class="pill sm"><span class="dot"></span>Inactive</span>
            @endif
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1;padding:48px;text-align:center;color:var(--text-faint)">No brands yet.</div>
    @endforelse
</div>

@if($brands->hasPages())
<div class="between" style="padding:16px 0">
    <span class="faint" style="font-size:13px">
        Showing {{ $brands->firstItem() }}–{{ $brands->lastItem() }} of {{ $brands->total() }}
    </span>
    <div class="row" style="gap:6px">
        @if($brands->onFirstPage())
            <button class="icon-btn" disabled>
                <span class="ico" data-ico="chevLeft" style="width:18px;height:18px"></span>
            </button>
        @else
            <a class="icon-btn" href="{{ $brands->previousPageUrl() }}">
                <span class="ico" data-ico="chevLeft" style="width:18px;height:18px"></span>
            </a>
        @endif

        @foreach($brands->getUrlRange(max(1,$brands->currentPage()-2), min($brands->lastPage(),$brands->currentPage()+2)) as $page => $url)
            @if($page == $brands->currentPage())
                <button class="btn btn-sm" style="background:var(--accent);color:#fff;width:34px;padding:0;height:34px">{{ $page }}</button>
            @else
                <a class="icon-btn" href="{{ $url }}" style="width:34px;height:34px">{{ $page }}</a>
            @endif
        @endforeach

        @if($brands->hasMorePages())
            <a class="icon-btn" href="{{ $brands->nextPageUrl() }}">
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

@endsection

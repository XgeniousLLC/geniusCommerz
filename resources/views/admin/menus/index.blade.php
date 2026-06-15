@extends('admin.layouts.admin')
@section('title', 'Menus')
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Navigation Menus</h2>
        <div class="sub">Manage your site's navigation structure</div>
    </div>
    <a href="{{ route('admin.menus.create') }}" class="btn btn-primary">
        <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>New Menu
    </a>
</div>

@if($menus->isEmpty())
<div class="card pad" style="text-align:center;padding:60px 20px">
    <span class="tile t-info" style="width:52px;height:52px;border-radius:16px;margin:0 auto 14px">
        <span class="ico" data-ico="list" style="width:24px;height:24px"></span>
    </span>
    <div style="font-weight:700;font-size:14px;margin-bottom:6px">No menus yet</div>
    <div class="faint" style="font-size:13px;margin-bottom:16px">Create navigation menus to control your storefront's header and footer links.</div>
    <a href="{{ route('admin.menus.create') }}" class="btn btn-primary" style="display:inline-flex">Create first menu</a>
</div>
@else
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px">
    @foreach($menus as $menu)
    @php $locations = \App\Models\Menu::LOCATIONS; @endphp
    <div class="card pad hoverable">
        <div class="between">
            <div>
                <div style="font-weight:700;font-size:14px">{{ $menu->name }}</div>
                <div class="faint" style="font-size:12.5px;margin-top:2px">{{ $locations[$menu->location] ?? $menu->location }}</div>
                <div class="faint" style="font-size:12px;margin-top:4px">{{ $menu->items_count }} items</div>
            </div>
            <div class="row" style="gap:6px">
                <a href="{{ route('admin.menus.edit', $menu) }}" class="icon-btn" title="Edit">
                    <span class="ico" data-ico="edit" style="width:15px;height:15px"></span>
                </a>
                <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}"
                      onsubmit="return confirm('Delete this menu?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="icon-btn">
                        <span class="ico" data-ico="trash" style="width:15px;height:15px"></span>
                    </button>
                </form>
            </div>
        </div>
        <div style="margin-top:12px">
            <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-sm btn-outline" style="width:100%;justify-content:center">Edit Menu Items</a>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection

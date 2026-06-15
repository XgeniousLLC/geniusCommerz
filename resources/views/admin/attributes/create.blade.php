@extends('admin.layouts.admin')

@section('title', 'Add Attribute')

@section('content')
<form method="POST" action="{{ route('admin.attributes.store') }}">
@csrf

<div class="row" style="gap:14px;margin-bottom:22px;flex-wrap:wrap">
    <a class="icon-btn" href="{{ route('admin.attributes.index') }}" style="width:40px;height:40px">
        <span class="ico" data-ico="chevLeft"></span>
    </a>
    <div class="grow" style="min-width:180px">
        <div class="breadcrumb"><a href="{{ route('admin.attributes.index') }}">Attributes</a> / Add</div>
        <h2 class="display" style="font-size:24px;letter-spacing:-0.03em">Add Attribute</h2>
    </div>
    <div class="row" style="gap:10px">
        <a href="{{ route('admin.attributes.index') }}" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <span class="ico" data-ico="check" style="width:18px;height:18px"></span>Save
        </button>
    </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,2fr) minmax(0,1fr);gap:18px;align-items:start" class="grid-2">

<div class="col-gap">
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-violet"><span class="ico" data-ico="sliders" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Values</h3><div class="sub">One per row — e.g. S, M, L, XL</div></div>
            <button type="button" id="add-value" class="link-btn head-action">
                <span class="ico" data-ico="plus" style="width:14px;height:14px"></span>Add value
            </button>
        </div>
        <div id="values-list" class="stack" style="gap:8px">
            @foreach(old('values', ['']) as $val)
            <div class="row value-row">
                <input class="input" type="text" name="values[]" value="{{ $val }}" placeholder="e.g. Small" style="flex:1">
                <button type="button" class="icon-btn danger remove-value">
                    <span class="ico" data-ico="x" style="width:15px;height:15px"></span>
                </button>
            </div>
            @endforeach
        </div>
        @error('values.*')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
    </div>
</div>

<div class="col-gap">
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-accent"><span class="ico" data-ico="gear" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Settings</h3></div>
        </div>

        <div class="field" style="margin-bottom:14px">
            <span class="lbl">Name <span class="req">*</span></span>
            <input class="input" type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Size, Color, Material" required>
            @error('name')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <span class="lbl">Sort order</span>
            <input class="input" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" style="max-width:100px">
        </div>
    </div>
</div>

</div>
</form>

@push('scripts')
<script>
const list = document.getElementById('values-list');

document.getElementById('add-value').addEventListener('click', function () {
    const row = document.createElement('div');
    row.className = 'row value-row';
    row.innerHTML = `<input class="input" type="text" name="values[]" placeholder="e.g. Small" style="flex:1">
        <button type="button" class="icon-btn danger remove-value">
            <span class="ico" data-ico="x" style="width:15px;height:15px"></span>
        </button>`;
    list.appendChild(row);
    if (window.Icons) Icons.render(row);
    row.querySelector('input').focus();
});

list.addEventListener('click', function (e) {
    const btn = e.target.closest('.remove-value');
    if (btn && list.children.length > 1) btn.closest('.value-row').remove();
});
</script>
@endpush

@endsection

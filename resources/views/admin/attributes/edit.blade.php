@extends('admin.layouts.admin')

@section('title', 'Edit: ' . $attribute->name)

@section('content')
<form method="POST" action="{{ route('admin.attributes.update', $attribute) }}">
@csrf @method('PUT')

<div class="row" style="gap:14px;margin-bottom:22px;flex-wrap:wrap">
    <a class="icon-btn" href="{{ route('admin.attributes.index') }}" style="width:40px;height:40px">
        <span class="ico" data-ico="chevLeft"></span>
    </a>
    <div class="grow" style="min-width:180px">
        <div class="breadcrumb"><a href="{{ route('admin.attributes.index') }}">Attributes</a> / Edit</div>
        <h2 class="display" style="font-size:24px;letter-spacing:-0.03em">{{ $attribute->name }}</h2>
    </div>
    <div class="row" style="gap:10px">
        <a href="{{ route('admin.attributes.index') }}" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <span class="ico" data-ico="check" style="width:18px;height:18px"></span>Save changes
        </button>
    </div>
</div>

<div class="card pad" style="margin-bottom:14px;padding:10px 16px;background:var(--warning-soft);border-color:color-mix(in srgb,var(--warning) 30%,transparent)">
    <div class="row" style="gap:10px">
        <span class="tile sm" style="background:var(--warning);color:#fff;flex-shrink:0"><span class="ico" data-ico="bell" style="width:18px;height:18px"></span></span>
        <span style="font-size:13px;color:var(--text)">Saving replaces all values. Existing variants referencing removed values will be affected.</span>
    </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,2fr) minmax(0,1fr);gap:18px;align-items:start" class="grid-2">

<div class="col-gap">
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-violet"><span class="ico" data-ico="sliders" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Values</h3><div class="sub">{{ $attribute->values_count }} values</div></div>
            <button type="button" id="add-value" class="link-btn head-action">
                <span class="ico" data-ico="plus" style="width:14px;height:14px"></span>Add value
            </button>
        </div>
        <div id="values-list" class="stack" style="gap:8px">
            @php $existingValues = old('values', $attribute->values->pluck('value')->toArray()); @endphp
            @foreach($existingValues as $val)
            <div class="row value-row">
                <input class="input" type="text" name="values[]" value="{{ $val }}" style="flex:1">
                <button type="button" class="icon-btn danger remove-value">
                    <span class="ico" data-ico="x" style="width:15px;height:15px"></span>
                </button>
            </div>
            @endforeach
        </div>
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
            <input class="input" type="text" name="name" value="{{ old('name', $attribute->name) }}" required>
            @error('name')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <span class="lbl">Sort order</span>
            <input class="input" type="number" name="sort_order" value="{{ old('sort_order', $attribute->sort_order) }}" min="0" style="max-width:100px">
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
    row.innerHTML = `<input class="input" type="text" name="values[]" placeholder="New value" style="flex:1">
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

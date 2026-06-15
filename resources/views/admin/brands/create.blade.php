@extends('admin.layouts.admin')

@section('title', 'Add Brand')

@section('content')
<form method="POST" action="{{ route('admin.brands.store') }}"
      x-data="{ slug: '{{ old('slug') }}' }">
@csrf

<div class="row" style="gap:14px;margin-bottom:22px;flex-wrap:wrap">
    <a class="icon-btn" href="{{ route('admin.brands.index') }}" style="width:40px;height:40px">
        <span class="ico" data-ico="chevLeft"></span>
    </a>
    <div class="grow" style="min-width:180px">
        <div class="breadcrumb"><a href="{{ route('admin.brands.index') }}">Brands</a> / Add</div>
        <h2 class="display" style="font-size:24px;letter-spacing:-0.03em">Add Brand</h2>
    </div>
    <div class="row" style="gap:10px">
        <a href="{{ route('admin.brands.index') }}" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <span class="ico" data-ico="check" style="width:18px;height:18px"></span>Save brand
        </button>
    </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,2fr) minmax(0,1fr);gap:18px;align-items:start" class="grid-2">

<div class="col-gap">
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-accent"><span class="ico" data-ico="bookmark" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Brand Info</h3></div>
        </div>

        <div class="field" style="margin-bottom:14px">
            <span class="lbl">Name <span class="req">*</span></span>
            <input class="input" type="text" name="name" value="{{ old('name') }}" required
                @input="slug = $event.target.value.toLowerCase().trim().replace(/[^a-z0-9\s\-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-')">
            @error('name')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px" class="grid-2">
            <div class="field">
                <span class="lbl">Slug</span>
                <input class="input mono" type="text" name="slug" x-model="slug" placeholder="auto-generated">
                @error('slug')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <span class="lbl">Website</span>
                <div class="input-prefix">
                    <span style="font-size:11px">https://</span>
                    <input class="input" type="text" name="website" value="{{ old('website') }}" placeholder="brand.com" style="padding-left:52px">
                </div>
                @error('website')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="field">
            <span class="lbl">Description</span>
            <textarea class="textarea" name="description" rows="3">{{ old('description') }}</textarea>
        </div>
    </div>
</div>

<div class="col-gap">
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-info"><span class="ico" data-ico="image" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Logo</h3></div>
        </div>
        <x-admin.media-picker name="logo_media_id" accept="image" label="Add Logo" :value="old('logo_media_id')" />
    </div>

    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Visibility</h3></div></div>
        <label class="between" style="cursor:pointer" x-data="{ on: {{ old('is_active', '1') ? 'true' : 'false' }} }">
            <div>
                <div style="font-weight:700;font-size:13.5px">Active</div>
                <div class="faint" style="font-size:12px">Show in brand filters and pages</div>
            </div>
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" style="display:none" :checked="on" {{ old('is_active', '1') ? 'checked' : '' }}>
            <button type="button" @click="on = !on" :class="on ? 'toggle on' : 'toggle'" style="flex-shrink:0">
                <span class="knob"></span>
            </button>
        </label>
    </div>
</div>

</div>
</form>
@endsection

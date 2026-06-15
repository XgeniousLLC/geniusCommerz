@extends('admin.layouts.admin')

@section('title', 'Edit: ' . $category->name)

@section('content')
<form method="POST" action="{{ route('admin.categories.update', $category) }}">
@csrf @method('PUT')

<div class="row" style="gap:14px;margin-bottom:22px;flex-wrap:wrap">
    <a class="icon-btn" href="{{ route('admin.categories.index') }}" style="width:40px;height:40px">
        <span class="ico" data-ico="chevLeft"></span>
    </a>
    <div class="grow" style="min-width:180px">
        <div class="breadcrumb"><a href="{{ route('admin.categories.index') }}">Categories</a> / Edit</div>
        <h2 class="display" style="font-size:24px;letter-spacing:-0.03em">{{ $category->name }}</h2>
    </div>
    <div class="row" style="gap:10px">
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-primary">
            <span class="ico" data-ico="check" style="width:18px;height:18px"></span>Save changes
        </button>
    </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,2fr) minmax(0,1fr);gap:18px;align-items:start" class="grid-2">

<div class="col-gap">
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-accent"><span class="ico" data-ico="tag" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Category Info</h3></div>
        </div>

        <div class="field" style="margin-bottom:14px">
            <span class="lbl">Name <span class="req">*</span></span>
            <input class="input" type="text" name="name" value="{{ old('name', $category->name) }}" required>
            @error('name')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px" class="grid-2">
            <div class="field">
                <span class="lbl">URL slug <span class="req">*</span></span>
                <div class="input-prefix">
                    <span>/</span>
                    <input class="input mono" type="text" name="slug" value="{{ old('slug', $category->slug) }}" required style="padding-left:18px">
                </div>
                @error('slug')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <span class="lbl">Sort order</span>
                <input class="input" type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0">
            </div>
        </div>

        <div class="field">
            <span class="lbl">Description</span>
            <textarea class="textarea" name="description" rows="3">{{ old('description', $category->description) }}</textarea>
        </div>
    </div>

    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-info"><span class="ico" data-ico="image" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Image</h3></div>
        </div>
        <x-admin.media-picker name="image_media_id" accept="image" label="Add Image"
            :value="old('image_media_id', $category->image_media_id)" />
    </div>
</div>

<div class="col-gap">
    <div class="card pad">
        <div class="card-head"><div class="ct"><h3>Organization</h3></div></div>

        <div class="field" style="margin-bottom:16px">
            <span class="lbl">Parent category</span>
            <div class="select-wrap">
                <select class="select" name="parent_id">
                    <option value="">— Top level —</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}"
                            {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @error('parent_id')<span style="color:var(--danger);font-size:12px">{{ $message }}</span>@enderror
        </div>

        <label class="between" style="cursor:pointer" x-data="{ on: {{ old('is_active', $category->is_active) ? 'true' : 'false' }} }">
            <div>
                <div style="font-weight:700;font-size:13.5px">Visible on storefront</div>
                <div class="faint" style="font-size:12px">Show in category navigation</div>
            </div>
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" style="display:none" :checked="on" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
            <button type="button" @click="on = !on" :class="on ? 'toggle on' : 'toggle'" style="flex-shrink:0">
                <span class="knob"></span>
            </button>
        </label>
    </div>
</div>

</div>
</form>

@endsection

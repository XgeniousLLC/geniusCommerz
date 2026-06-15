@extends('admin.layouts.admin')
@section('title', 'Blog Categories')
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Blog Categories</h2>
        <div class="sub">Manage categories used to organise blog posts</div>
    </div>
    <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline">
        <span class="ico" data-ico="arrowLeft" style="width:16px;height:16px"></span>Blog Posts
    </a>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:18px;align-items:start">

    <div class="card pad">
        <div class="card-head" style="margin-bottom:16px">
            <span class="tile sm t-accent"><span class="ico" data-ico="tag" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Add Category</h3></div>
        </div>
        <form method="POST" action="{{ route('admin.blog-categories.store') }}" class="col-gap" style="--gap:14px">
            @csrf
            <div class="field">
                <span class="lbl">Name <span style="color:var(--danger)">*</span></span>
                <input class="input" type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Style, Tech, Beauty" required>
                @error('name')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <span class="lbl">Slug</span>
                <input class="input" type="text" name="slug" value="{{ old('slug') }}" placeholder="auto-generated">
                @error('slug')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn btn-primary">Add Category</button>
        </form>
    </div>

    <div class="card flush">
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th style="text-align:right">Posts</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                    <tr class="hoverable" x-data="{ editing: false }">
                        <td>
                            <span x-show="!editing" style="font-weight:600;font-size:13.5px">{{ $cat->name }}</span>
                            <form x-show="editing" method="POST"
                                  action="{{ route('admin.blog-categories.update', $cat) }}"
                                  class="row" style="gap:8px">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $cat->name }}" class="input" style="width:160px;height:34px;font-size:13px">
                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                <button type="button" @click="editing=false" class="btn btn-sm btn-outline">Cancel</button>
                            </form>
                        </td>
                        <td class="faint" style="font-size:13px">{{ $cat->slug }}</td>
                        <td style="text-align:right" class="tnum">{{ $cat->blogs_count }}</td>
                        <td style="text-align:right">
                            <div class="row" style="gap:6px;justify-content:flex-end">
                                <button type="button" @click="editing=!editing" class="icon-btn">
                                    <span class="ico" data-ico="edit" style="width:15px;height:15px"></span>
                                </button>
                                <form method="POST" action="{{ route('admin.blog-categories.destroy', $cat) }}"
                                      onsubmit="return confirm('Delete {{ $cat->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-btn">
                                        <span class="ico" data-ico="trash" style="width:15px;height:15px"></span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center;padding:40px 20px">
                            <div class="faint" style="font-size:13.5px">No categories yet.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

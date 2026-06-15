@extends('admin.layouts.admin')
@section('title', 'Pages')
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Pages</h2>
        <div class="sub">Manage static CMS pages</div>
    </div>
    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">
        <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>Create Page
    </a>
</div>

<div class="card flush" style="margin-bottom:14px">
    <form method="GET" style="padding:14px 18px;display:flex;gap:10px;align-items:center;border-bottom:1px solid var(--border)">
        <div class="search-field" style="flex:1;max-width:320px">
            <span class="ico" data-ico="search" style="width:15px;height:15px"></span>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search pages…" class="input" style="border:none;background:none;box-shadow:none;padding:0">
        </div>
        <select class="input" name="status" style="width:140px">
            <option value="">All Status</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
        </select>
        <button type="submit" class="btn btn-sm btn-outline">Filter</button>
        @if(request()->hasAny(['search','status']))
        <a href="{{ route('admin.pages.index') }}" class="btn btn-sm">Clear</a>
        @endif
    </form>
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>Page</th>
                    <th>Status</th>
                    <th>SEO</th>
                    <th>Author</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($pages as $page)
                <tr class="hoverable">
                    <td>
                        <div style="font-weight:700;font-size:13.5px">{{ $page->title }}</div>
                        <div class="faint mono" style="font-size:12px;margin-top:2px">/{{ $page->slug }}</div>
                    </td>
                    <td>
                        <span class="pill sm {{ $page->status === 'published' ? 'success' : 'warning' }}">
                            <span class="dot"></span>{{ ucfirst($page->status) }}
                        </span>
                    </td>
                    <td>
                        @if($page->metaInformation)
                        <span class="pill sm success">Optimized</span>
                        @else
                        <span class="pill sm">Basic</span>
                        @endif
                    </td>
                    <td class="muted" style="font-size:13px">{{ $page->creator->name }}</td>
                    <td class="faint" style="font-size:13px">{{ $page->created_at->format('d M Y') }}</td>
                    <td style="text-align:right">
                        <div class="row" style="gap:6px;justify-content:flex-end">
                            <a href="{{ route('page.show', $page) }}" target="_blank" class="btn btn-sm btn-outline">View</a>
                            <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-primary">Edit</a>
                            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}"
                                  onsubmit="return confirm('Delete this page?')">
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
                    <td colspan="6" style="text-align:center;padding:48px 20px">
                        <div class="faint" style="font-size:13.5px">
                            @if(request()->hasAny(['search','status']))
                                No pages match your filters. <a href="{{ route('admin.pages.index') }}" class="link-btn">Clear filters</a>
                            @else
                                No pages yet. <a href="{{ route('admin.pages.create') }}" class="link-btn">Create the first one</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pages->hasPages())
    <div style="padding:14px 20px;border-top:1px solid var(--border)">{{ $pages->links() }}</div>
    @endif
</div>

@endsection

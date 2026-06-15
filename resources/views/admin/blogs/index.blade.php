@extends('admin.layouts.admin')
@section('title', 'Blog Posts')
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Blog Posts</h2>
        <div class="sub">Manage articles and journal entries</div>
    </div>
    <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">
        <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>New Post
    </a>
</div>

<div class="card flush">
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($blogs as $blog)
                <tr class="hoverable">
                    <td>
                        <div style="font-weight:700;font-size:13.5px">{{ $blog->title }}</div>
                        @if($blog->excerpt)
                        <div class="faint" style="font-size:12px;margin-top:2px">{{ Str::limit($blog->excerpt, 60) }}</div>
                        @endif
                    </td>
                    <td class="muted" style="font-size:13px">{{ $blog->category ?? '—' }}</td>
                    <td class="muted" style="font-size:13px">{{ $blog->author_name }}</td>
                    <td>
                        <span class="pill sm {{ $blog->status === 'published' ? 'success' : '' }}">
                            <span class="dot"></span>{{ ucfirst($blog->status) }}
                        </span>
                    </td>
                    <td class="faint" style="font-size:13px">{{ $blog->published_at?->format('d M Y') ?? '—' }}</td>
                    <td style="text-align:right">
                        <div class="row" style="gap:6px;justify-content:flex-end">
                            @if($blog->status === 'published')
                            <a href="{{ route('blog.show', $blog->slug) }}" target="_blank" class="icon-btn" title="View">
                                <span class="ico" data-ico="external" style="width:15px;height:15px"></span>
                            </a>
                            @endif
                            <a href="{{ route('admin.blogs.edit', $blog) }}" class="icon-btn" title="Edit">
                                <span class="ico" data-ico="edit" style="width:15px;height:15px"></span>
                            </a>
                            <form method="POST" action="{{ route('admin.blogs.destroy', $blog) }}"
                                  onsubmit="return confirm('Delete this post?')">
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
                            No blog posts yet.
                            <a href="{{ route('admin.blogs.create') }}" class="link-btn" style="margin-left:6px">Create the first one</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($blogs->hasPages())
    <div style="padding:14px 20px;border-top:1px solid var(--border)">{{ $blogs->links() }}</div>
    @endif
</div>

@endsection

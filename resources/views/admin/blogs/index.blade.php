@extends('admin.layouts.admin')

@section('title', 'Blog Posts')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Blog Posts</li>
</ol>
@endsection

@section('page-header')
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Blog Posts</h1>
        <p class="text-gray-500 mt-1 text-sm">Manage articles and journal entries.</p>
    </div>
    <a href="{{ route('admin.blogs.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Post
    </a>
</div>
@endsection

@section('content')

<x-admin.card>
    @if($blogs->isEmpty())
    <div class="py-16 text-center text-gray-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <p class="text-sm">No blog posts yet. <a href="{{ route('admin.blogs.create') }}" class="text-blue-600 hover:underline">Create the first one.</a></p>
    </div>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100">
                <th class="text-left py-3 px-4 font-medium text-gray-500">Title</th>
                <th class="text-left py-3 px-4 font-medium text-gray-500 hidden md:table-cell">Category</th>
                <th class="text-left py-3 px-4 font-medium text-gray-500 hidden lg:table-cell">Author</th>
                <th class="text-left py-3 px-4 font-medium text-gray-500">Status</th>
                <th class="text-left py-3 px-4 font-medium text-gray-500 hidden lg:table-cell">Published</th>
                <th class="py-3 px-4"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($blogs as $blog)
            <tr class="hover:bg-gray-50 group">
                <td class="py-3 px-4">
                    <div class="font-medium text-gray-900 line-clamp-1">{{ $blog->title }}</div>
                    @if($blog->excerpt)
                    <div class="text-xs text-gray-400 line-clamp-1 mt-0.5">{{ $blog->excerpt }}</div>
                    @endif
                </td>
                <td class="py-3 px-4 text-gray-500 hidden md:table-cell">{{ $blog->category ?? '—' }}</td>
                <td class="py-3 px-4 text-gray-500 hidden lg:table-cell">{{ $blog->author_name }}</td>
                <td class="py-3 px-4">
                    @if($blog->status === 'published')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Published</span>
                    @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Draft</span>
                    @endif
                </td>
                <td class="py-3 px-4 text-gray-400 text-xs hidden lg:table-cell">
                    {{ $blog->published_at?->format('d M Y') ?? '—' }}
                </td>
                <td class="py-3 px-4 text-right">
                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition">
                        @if($blog->status === 'published')
                        <a href="{{ route('blog.show', $blog->slug) }}" target="_blank"
                           class="p-1.5 text-gray-400 hover:text-blue-600 rounded" title="View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        @endif
                        <a href="{{ route('admin.blogs.edit', $blog) }}"
                           class="p-1.5 text-gray-400 hover:text-blue-600 rounded" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.blogs.destroy', $blog) }}" onsubmit="return confirm('Delete this post?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($blogs->hasPages())
    <div class="mt-4 px-4">{{ $blogs->links() }}</div>
    @endif
    @endif
</x-admin.card>

@endsection

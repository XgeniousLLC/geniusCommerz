@extends('frontend.layout')

@section('title', $activeCategory ? $activeCategory->name . ' — Blog' : 'Blog')
@section('description', $activeCategory ? 'Posts in ' . $activeCategory->name : 'Articles, guides and shopping wisdom.')

@section('content')
<div class="max-w-7xl mx-auto px-4 lg:px-6 py-6 lg:py-10">

    <div class="mb-6">
        @if($activeCategory)
        <div class="flex items-center gap-2 text-sm mb-2" style="color:var(--kb-ink-soft)">
            <a href="{{ route('blog.index') }}" class="hover:underline">Blog</a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
            <span style="color:var(--kb-ink)">{{ $activeCategory->name }}</span>
        </div>
        <h1 class="text-3xl md:text-4xl font-extrabold" style="color:var(--kb-ink)">{{ $activeCategory->name }}</h1>
        @else
        <h1 class="text-3xl md:text-4xl font-extrabold" style="color:var(--kb-ink)">Journal</h1>
        <p class="mt-1" style="color:var(--kb-ink-muted)">Style, gadgets, and shopping wisdom.</p>
        @endif
    </div>

    {{-- Category filter tabs --}}
    @if($categories->isNotEmpty())
    <div class="flex flex-wrap gap-2 mb-8">
        <a href="{{ route('blog.index') }}"
           class="kb-chip text-sm"
           style="{{ !$activeCategory ? 'background:var(--kb-primary);color:#fff' : 'background:#f1f5f9;color:var(--kb-ink-muted)' }}">
            All
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('blog.category', $cat->slug) }}"
           class="kb-chip text-sm"
           style="{{ $activeCategory?->id === $cat->id ? 'background:var(--kb-primary);color:#fff' : 'background:#f1f5f9;color:var(--kb-ink-muted)' }}">
            {{ $cat->name }}
        </a>
        @endforeach
    </div>
    @endif

    @if($featured)
    {{-- Featured post — single <a> wrapper, no nested anchors --}}
    <a href="{{ route('blog.show', $featured->slug) }}"
       class="kb-card overflow-hidden block mb-10 group" style="text-decoration:none">
        <div style="display:grid" class="lg:grid-cols-2">
            <div class="overflow-hidden" style="min-height:220px;aspect-ratio:16/10">
                @if($featured->cover_url)
                <img class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                     src="{{ $featured->cover_url }}" alt="{{ $featured->title }}">
                @else
                <div class="w-full h-full" style="background:var(--kb-primary-50);min-height:220px"></div>
                @endif
            </div>
            <div class="p-6 md:p-8 flex flex-col justify-center">
                @if($featured->category_name)
                @php $catSlug = $featured->blogCategory?->slug; @endphp
                <span onclick="event.preventDefault();event.stopPropagation();window.location='{{ $catSlug ? route('blog.category', $catSlug) : '#' }}'"
                      class="kb-chip kb-chip-new self-start mb-2" style="cursor:pointer">
                    {{ $activeCategory ? 'Latest' : 'Featured' }} · {{ $featured->category_name }}
                </span>
                @endif
                <h2 class="text-2xl md:text-3xl font-extrabold leading-tight" style="color:var(--kb-ink)">
                    {{ $featured->title }}
                </h2>
                @if($featured->excerpt)
                <p class="mt-3" style="color:var(--kb-ink-muted)">{{ $featured->excerpt }}</p>
                @endif
                <div class="flex items-center gap-2 text-xs mt-4" style="color:var(--kb-ink-soft)">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold"
                         style="background:var(--kb-primary)">
                        {{ strtoupper(substr($featured->author_display_name, 0, 1)) }}
                    </div>
                    <span class="font-semibold" style="color:var(--kb-ink-muted)">{{ $featured->author_display_name }}</span>
                    <span>·</span>
                    <span>{{ $featured->published_at?->format('d M Y') }}</span>
                    <span>·</span>
                    <span>{{ $featured->read_time }}</span>
                </div>
                <div class="mt-4">
                    <span class="text-sm font-semibold" style="color:var(--kb-primary)">Read article →</span>
                </div>
            </div>
        </div>
    </a>
    @endif

    @if($posts->isNotEmpty())
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($posts as $post)
        <a href="{{ route('blog.show', $post->slug) }}" class="kb-card overflow-hidden block group" style="text-decoration:none">
            <div class="overflow-hidden" style="aspect-ratio:16/9">
                @if($post->cover_url)
                <img class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                     src="{{ $post->cover_url }}" alt="{{ $post->title }}">
                @else
                <div class="w-full h-full flex items-center justify-center" style="background:var(--kb-primary-50);min-height:160px">
                    <i data-lucide="file-text" class="w-8 h-8" style="color:var(--kb-primary);opacity:.4"></i>
                </div>
                @endif
            </div>
            <div class="p-5">
                @if($post->category_name)
                @php $pCatSlug = $post->blogCategory?->slug; @endphp
                <span onclick="event.preventDefault();event.stopPropagation();window.location='{{ $pCatSlug ? route('blog.category', $pCatSlug) : '#' }}'"
                      class="kb-chip kb-chip-new" style="cursor:pointer">{{ $post->category_name }}</span>
                @endif
                <h3 class="text-lg font-bold mt-2 leading-snug line-clamp-2" style="color:var(--kb-ink)">{{ $post->title }}</h3>
                @if($post->excerpt)
                <p class="text-sm mt-2 line-clamp-2" style="color:var(--kb-ink-muted)">{{ $post->excerpt }}</p>
                @endif
                <div class="flex items-center gap-2 text-xs mt-3" style="color:var(--kb-ink-soft)">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold"
                         style="background:var(--kb-primary)">
                        {{ strtoupper(substr($post->author_display_name, 0, 1)) }}
                    </div>
                    <span style="color:var(--kb-ink-muted)">{{ $post->author_display_name }}</span>
                    <span>·</span>
                    <span>{{ $post->published_at?->format('d M Y') }}</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    @if(!$featured && $posts->isEmpty())
    <div class="py-20 text-center" style="color:var(--kb-ink-soft)">
        <i data-lucide="file-text" class="w-12 h-12 mx-auto mb-4 opacity-30"></i>
        <p class="text-lg font-semibold">
            @if($activeCategory) No posts in "{{ $activeCategory->name }}" yet. @else No posts published yet. @endif
        </p>
        @if($activeCategory)
        <a href="{{ route('blog.index') }}" class="kb-btn kb-btn-ghost kb-btn-md mt-4 inline-flex">
            ← View all posts
        </a>
        @endif
    </div>
    @endif

</div>
@endsection

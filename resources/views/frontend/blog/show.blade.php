@extends('frontend.layout')

@section('content')
<div class="max-w-7xl mx-auto px-4 lg:px-6 py-6 lg:py-10">

    {{-- Breadcrumb --}}
    <nav class="text-xs mb-4 flex items-center gap-1.5" style="color:var(--kb-ink-soft)">
        <a href="/" class="hover:text-slate-900">Home</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <a href="{{ route('blog.index') }}" class="hover:text-slate-900">Blog</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span style="color:var(--kb-ink)">{{ Str::limit($blog->title, 50) }}</span>
    </nav>

    <div class="grid lg:grid-cols-[1fr_280px] gap-10">

        <article>
            @if($blog->category_name && $blog->blogCategory)
            <a href="{{ route('blog.category', $blog->blogCategory->slug) }}"
               class="kb-chip kb-chip-new mb-3 hover:opacity-80" style="text-decoration:none">
                {{ $blog->category_name }}
            </a>
            @elseif($blog->category_name)
            <span class="kb-chip kb-chip-new mb-3">{{ $blog->category_name }}</span>
            @endif
            <h1 class="text-3xl md:text-5xl font-extrabold leading-tight">{{ $blog->title }}</h1>

            <div class="flex flex-wrap items-center gap-3 text-sm mt-4" style="color:var(--kb-ink-soft)">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold"
                         style="background:var(--kb-primary)">
                        {{ strtoupper(substr($blog->author_display_name, 0, 1)) }}
                    </div>
                    <span class="font-semibold" style="color:var(--kb-ink)">{{ $blog->author_display_name }}</span>
                </div>
                <span>·</span>
                <span>{{ $blog->published_at?->format('d M Y') }}</span>
                <span>·</span>
                <span class="flex items-center gap-1">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    {{ $blog->read_time }}
                </span>
            </div>

            @if($blog->cover_url)
            <img class="w-full rounded-2xl mt-6 object-cover kb-shadow-card"
                 style="max-height:480px"
                 src="{{ $blog->cover_url }}" alt="{{ $blog->title }}">
            @endif

            <div id="blog-content" class="mt-7 leading-relaxed space-y-4 text-[1.02rem]" style="color:#334155">
                {!! $blog->content !!}
            </div>

            {{-- FAQ --}}
            @if(!empty($blog->faqs))
            <div class="mt-10">
                <h2 class="text-xl font-bold mb-4" style="color:var(--kb-ink)">Frequently Asked Questions</h2>
                <div class="space-y-3">
                    @foreach($blog->faqs as $faq)
                    <div x-data="{ open: false }" class="kb-card overflow-hidden" style="border-radius:12px">
                        <button type="button" @click="open = !open"
                            class="w-full flex items-center justify-between p-4 text-left"
                            style="background:transparent;border:none;cursor:pointer">
                            <span class="font-semibold text-sm pr-4" style="color:var(--kb-ink)">{{ $faq['question'] }}</span>
                            <svg class="w-4 h-4 flex-shrink-0 transition-transform duration-200"
                                 :class="open ? 'rotate-180' : ''"
                                 style="color:var(--kb-ink-soft)"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-collapse>
                            <div class="px-4 pb-4 text-sm leading-relaxed" style="color:var(--kb-ink-muted)">
                                {{ $faq['answer'] }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Share --}}
            <div class="mt-8 flex items-center gap-3 text-sm">
                <span style="color:var(--kb-ink-soft)">Share:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                   target="_blank" rel="noopener"
                   class="w-9 h-9 rounded-full grid place-items-center hover:opacity-80"
                   style="background:#f1f5f9">
                    <i data-lucide="facebook" class="w-4 h-4"></i>
                </a>
                <button onclick="navigator.clipboard.writeText(window.location.href)"
                        class="w-9 h-9 rounded-full grid place-items-center hover:opacity-80"
                        style="background:#f1f5f9" title="Copy link">
                    <i data-lucide="link" class="w-4 h-4"></i>
                </button>
            </div>
        </article>

        {{-- Right rail --}}
        <aside class="space-y-5">

            {{-- TOC: hidden until JS finds headings --}}
            @if($blog->enable_toc)
            <div class="kb-card p-4" id="toc-box" style="display:none">
                <h3 class="font-semibold text-sm mb-3" style="color:var(--kb-ink)">
                    <i data-lucide="list" class="w-4 h-4 inline-block mr-1" style="vertical-align:-3px"></i>
                    Contents
                </h3>
                <nav id="toc-nav" class="space-y-1 text-sm" style="color:var(--kb-ink-soft)"></nav>
            </div>
            @endif

            {{-- Categories --}}
            @if($categories->isNotEmpty())
            <div class="kb-card p-4">
                <h3 class="font-semibold text-sm mb-3" style="color:var(--kb-ink)">Categories</h3>
                <div class="space-y-1">
                    @foreach($categories as $cat)
                    <a href="{{ route('blog.category', $cat->slug) }}"
                       class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition hover:opacity-80"
                       style="{{ $blog->blog_category_id === $cat->id ? 'background:var(--kb-primary-50);color:var(--kb-primary);font-weight:600' : 'color:var(--kb-ink-muted)' }}">
                        <span>{{ $cat->name }}</span>
                        <span class="text-xs px-1.5 py-0.5 rounded-full" style="background:#f1f5f9;color:var(--kb-ink-soft)">
                            {{ $cat->blogs_count }}
                        </span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

        </aside>

    </div>

    {{-- Related Posts (full-width) --}}
    @if($related->isNotEmpty())
    <div class="mt-14">
        <h2 class="text-xl font-bold mb-6" style="color:var(--kb-ink)">Related Articles</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($related as $rel)
            <a href="{{ route('blog.show', $rel->slug) }}"
               class="kb-card group flex flex-col overflow-hidden kb-shadow-card hover:shadow-lg transition-shadow"
               style="border-radius:16px;text-decoration:none">
                @if($rel->cover_url)
                <img class="w-full object-cover" style="height:180px"
                     src="{{ $rel->cover_url }}" alt="{{ $rel->title }}">
                @else
                <div class="w-full" style="height:180px;background:var(--kb-primary-50)"></div>
                @endif
                <div class="p-4 flex flex-col gap-2 flex-1">
                    @if($rel->category_name)
                    <span class="kb-chip kb-chip-new text-xs self-start">{{ $rel->category_name }}</span>
                    @endif
                    <div class="font-semibold text-sm leading-snug group-hover:underline line-clamp-2"
                         style="color:var(--kb-ink)">{{ $rel->title }}</div>
                    <div class="flex items-center gap-2 text-xs mt-auto pt-2" style="color:var(--kb-ink-soft)">
                        <span>{{ $rel->published_at?->format('d M Y') }}</span>
                        <span>·</span>
                        <span>{{ $rel->read_time }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Comments --}}
    <div class="mt-14" id="comments">

        {{-- Success flash --}}
        @if(session('comment_success'))
        <div class="mb-6 px-4 py-3 rounded-xl text-sm font-medium" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
            {{ session('comment_success') }}
        </div>
        @endif

        {{-- Existing comments --}}
        @if($comments->isNotEmpty())
        <h2 class="text-xl font-bold mb-6" style="color:var(--kb-ink)">
            {{ $comments->count() }} Comment{{ $comments->count() !== 1 ? 's' : '' }}
        </h2>
        <div class="space-y-6 mb-12">
            @foreach($comments as $comment)
            <div class="flex gap-4" id="comment-{{ $comment->id }}">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                     style="background:var(--kb-primary)">
                    {{ strtoupper(substr($comment->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <div class="kb-card p-4" style="border-radius:12px">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="font-semibold text-sm" style="color:var(--kb-ink)">{{ $comment->name }}</span>
                            <span class="text-xs" style="color:var(--kb-ink-soft)">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm leading-relaxed" style="color:var(--kb-ink-muted)">{{ $comment->body }}</p>
                    </div>

                    {{-- Reply --}}
                    @auth
                    <div x-data="{ open: false }" class="mt-2">
                        <button @click="open = !open" type="button"
                                class="text-xs font-medium hover:underline flex items-center gap-1"
                                style="background:none;border:none;cursor:pointer;color:var(--kb-primary)">
                            <i data-lucide="corner-down-right" class="w-3 h-3"></i>
                            Reply
                        </button>
                        <div x-show="open" x-collapse class="mt-3">
                            <form action="{{ route('comments.store') }}" method="POST" class="space-y-3">
                                @csrf
                                <input type="hidden" name="commentable_type" value="blog">
                                <input type="hidden" name="commentable_id" value="{{ $blog->id }}">
                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                <textarea name="body" rows="2" placeholder="Write a reply…" required
                                          class="w-full text-sm px-3 py-2 rounded-lg border outline-none focus:ring-2 resize-none"
                                          style="border-color:#e2e8f0;background:#f8fafc;color:var(--kb-ink)"></textarea>
                                <button type="submit" class="kb-btn kb-btn-primary text-sm px-4 py-1.5">Post reply</button>
                            </form>
                        </div>
                    </div>
                    @else
                    <a href="{{ route('login') }}?redirect={{ urlencode(url()->current().'#comment-'.$comment->id) }}"
                       class="mt-2 text-xs flex items-center gap-1 hover:underline"
                       style="color:var(--kb-ink-soft)">
                        <i data-lucide="corner-down-right" class="w-3 h-3"></i>
                        <span><a href="{{ route('login') }}" class="font-medium hover:underline" style="color:var(--kb-primary)">Sign in</a> to reply</span>
                    </a>
                    @endauth

                    {{-- Nested replies --}}
                    @if($comment->replies->isNotEmpty())
                    <div class="mt-4 space-y-4 pl-4" style="border-left:2px solid var(--kb-border)">
                        @foreach($comment->replies as $reply)
                        <div class="flex gap-3" id="comment-{{ $reply->id }}">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                 style="background:var(--kb-accent)">
                                {{ strtoupper(substr($reply->name, 0, 1)) }}
                            </div>
                            <div class="kb-card p-3 flex-1" style="border-radius:10px">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="font-semibold text-xs" style="color:var(--kb-ink)">{{ $reply->name }}</span>
                                    <span class="text-xs" style="color:var(--kb-ink-soft)">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm leading-relaxed" style="color:var(--kb-ink-muted)">{{ $reply->body }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Comment form / login prompt --}}
        @auth
        <div class="kb-card p-6 lg:p-8" style="border-radius:20px">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold"
                     style="background:var(--kb-primary)">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div>
                    <div class="text-sm font-semibold" style="color:var(--kb-ink)">{{ auth()->user()->name }}</div>
                    <div class="text-xs" style="color:var(--kb-ink-soft)">Commenting as yourself</div>
                </div>
            </div>
            <form action="{{ route('comments.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="commentable_type" value="blog">
                <input type="hidden" name="commentable_id" value="{{ $blog->id }}">
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--kb-ink)">Comment <span style="color:#ef4444">*</span></label>
                    <textarea name="body" rows="5" placeholder="Share your thoughts…" required
                              class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none focus:ring-2 resize-none"
                              style="border-color:#e2e8f0;background:#f8fafc;color:var(--kb-ink)">{{ old('body') }}</textarea>
                </div>
                @if($errors->has('body'))
                <div class="text-xs text-red-600">{{ $errors->first('body') }}</div>
                @endif
                <button type="submit" class="kb-btn kb-btn-primary px-6 py-2.5">Post Comment</button>
            </form>
        </div>
        @else
        <p class="text-sm" style="color:var(--kb-ink-soft)">
            <a href="{{ route('login') }}" class="font-semibold hover:underline" style="color:var(--kb-primary)">Sign in</a>
            or
            <a href="{{ route('register') }}" class="font-semibold hover:underline" style="color:var(--kb-primary)">create an account</a>
            to leave a comment.
        </p>
        @endauth

    </div>

</div>

<?php if (!empty($blog->faqs)): ?>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'FAQPage',
    'mainEntity' => collect($blog->faqs)->map(fn($f) => [
        '@type' => 'Question',
        'name'  => $f['question'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']],
    ])->values()->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
<?php endif; ?>

<?php if ($blog->enable_toc): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var content = document.getElementById('blog-content');
    var tocNav  = document.getElementById('toc-nav');
    if (!content || !tocNav) return;

    var headings = content.querySelectorAll('h2, h3');
    if (!headings.length) return;

    var tocBox = document.getElementById('toc-box');
    if (tocBox) tocBox.style.display = 'block';

    headings.forEach(function(h, idx) {
        if (!h.id) h.id = 'toc-h-' + idx;
        var a = document.createElement('a');
        a.href = '#' + h.id;
        a.textContent = h.textContent.trim();
        a.style.cssText = 'display:block;text-decoration:none;padding:3px 0;line-height:1.5;transition:color .15s;color:inherit;' +
            (h.tagName === 'H3' ? 'padding-left:14px;font-size:0.79rem;opacity:.85;' : 'font-size:0.83rem;font-weight:600;');
        a.addEventListener('mouseover', function() { this.style.color = 'var(--kb-primary)'; });
        a.addEventListener('mouseout',  function() { this.style.color = ''; });
        tocNav.appendChild(a);
    });

    var links = tocNav.querySelectorAll('a');
    window.addEventListener('scroll', function () {
        var current = '';
        headings.forEach(function(h) {
            if (window.scrollY >= h.offsetTop - 120) current = h.id;
        });
        links.forEach(function(a) {
            a.style.color = a.getAttribute('href') === '#' + current ? 'var(--kb-primary)' : '';
        });
    }, { passive: true });
});
</script>
<?php endif; ?>

@endsection

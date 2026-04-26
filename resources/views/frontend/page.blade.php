@extends('frontend.layout')

@section('content')
<div class="max-w-7xl mx-auto px-4 lg:px-6 py-6 lg:py-10">

    {{-- Breadcrumb --}}
    @if($page->show_breadcrumb)
    <nav class="text-xs mb-4 flex items-center gap-1.5" style="color:var(--kb-ink-soft)">
        <a href="/" class="hover:text-slate-900">Home</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <span style="color:var(--kb-ink)">{{ $page->title }}</span>
    </nav>
    @endif

    <div class="grid lg:grid-cols-[1fr_260px] gap-10">

        <article>
            <h1 class="text-3xl md:text-4xl font-extrabold">{{ $page->title }}</h1>
            <p class="text-sm mt-2" style="color:var(--kb-ink-soft)">
                Last updated: {{ $page->updated_at->format('d F Y') }}
            </p>

            <div class="mt-7 leading-relaxed space-y-4 text-[1rem]" style="color:#334155">
                {!! $page->content !!}
            </div>
        </article>

        {{-- TOC sidebar (static pages often have headings) --}}
        <aside>
            <div class="kb-card p-4 sticky top-24">
                <a href="/" class="kb-btn kb-btn-ghost kb-btn-md w-full justify-center mb-3">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Back to home
                </a>
                <hr style="border-color:var(--kb-border);margin:0.5rem 0">
                <p class="text-xs font-semibold mt-2 mb-1" style="color:var(--kb-ink-soft)">Quick links</p>
                <ul class="text-sm space-y-1.5" style="color:var(--kb-ink-muted)">
                    <li><a href="/page/returns-policy" class="hover:underline">Returns policy</a></li>
                    <li><a href="/page/shipping-policy" class="hover:underline">Shipping policy</a></li>
                    <li><a href="/page/privacy-policy" class="hover:underline">Privacy policy</a></li>
                    <li><a href="/page/terms" class="hover:underline">Terms &amp; conditions</a></li>
                    <li><a href="/track" class="hover:underline">Track order</a></li>
                </ul>
            </div>
        </aside>

    </div>

</div>
@endsection

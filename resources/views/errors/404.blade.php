@extends('frontend.layout')

@section('title', 'Page not found')

@section('content')
<div class="max-w-4xl mx-auto px-4 lg:px-6 py-10 lg:py-20 text-center">

    <div class="font-extrabold leading-none select-none" style="font-size:10rem;color:var(--kb-primary);opacity:.12">404</div>
    <h1 class="text-3xl md:text-4xl font-extrabold -mt-10 md:-mt-14">Oops — we lost that page.</h1>
    <p class="mt-3 max-w-md mx-auto" style="color:var(--kb-ink-muted)">
        It might have been moved, removed, or never existed. Try searching, or browse a category below.
    </p>

    <form action="/shop" method="GET" class="mt-6 flex max-w-md mx-auto">
        <input name="q" class="kb-input" style="border-radius:.75rem 0 0 .75rem" placeholder="Search for products…">
        <button type="submit"
                class="kb-btn kb-btn-primary kb-btn-md"
                style="border-radius:0 .75rem .75rem 0;white-space:nowrap">
            <i data-lucide="search" class="w-4 h-4"></i> Search
        </button>
    </form>

    <div class="mt-6">
        <a href="/" class="kb-btn kb-btn-ghost kb-btn-md">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to home
        </a>
    </div>

    <div class="mt-10">
        <h3 class="text-sm font-semibold mb-3" style="color:var(--kb-ink-soft)">Popular categories</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach(['Fashion','Electronics','Home','Beauty','Kids','Sports'] as $cat)
            <a href="/shop?category={{ strtolower($cat) }}"
               class="kb-card p-4 text-sm font-semibold hover:shadow-md transition text-center">
                {{ $cat }}
            </a>
            @endforeach
        </div>
    </div>

</div>
@endsection

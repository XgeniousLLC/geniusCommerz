@extends('frontend.layout')

@section('title', 'Home')

@section('content')

@php
    $siteName   = \App\Models\SiteSetting::get('general.site_name', config('app.name'));
    $siteTagline = \App\Models\SiteSetting::get('general.site_tagline', 'Shop the latest trends');
    $heroTitle  = \App\Models\SiteSetting::get('general.hero_title', $siteTagline);
    $heroSub    = \App\Models\SiteSetting::get('general.hero_subtitle', 'Discover quality products delivered to your door.');
@endphp

{{-- Hero --}}
<section style="background:linear-gradient(135deg,var(--kb-primary) 0%,#2649B8 100%);color:#fff">
    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-16 md:py-24 flex flex-col items-center text-center">
        <span class="kb-chip mb-5" style="background:rgba(255,255,255,.18);color:#fff;font-size:0.8rem">
            Free delivery on orders over ৳999
        </span>
        <h1 class="text-4xl md:text-6xl font-extrabold leading-tight max-w-3xl">
            {{ $heroTitle ?: $siteName }}
        </h1>
        @if($heroSub)
        <p class="mt-4 text-lg md:text-xl max-w-xl" style="color:rgba(255,255,255,.85)">{{ $heroSub }}</p>
        @endif
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="/shop" class="kb-btn kb-btn-accent kb-btn-lg" style="border-radius:12px">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                Shop Now
            </a>
            <a href="/track" class="kb-btn kb-btn-md" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:12px">
                <i data-lucide="package" class="w-4 h-4"></i>
                Track Order
            </a>
        </div>
    </div>
</section>

{{-- Trust badges --}}
<section style="background:#fff;border-bottom:1px solid var(--kb-border)">
    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center text-sm" style="color:var(--kb-ink-muted)">
            @foreach([
                ['truck','Fast Delivery','2-3 business days'],
                ['shield-check','Secure Payment','100% protected'],
                ['refresh-cw','Easy Returns','7-day return policy'],
                ['headphones','24/7 Support','Always here for you'],
            ] as $badge)
            <div class="flex flex-col items-center gap-1.5 py-2">
                <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:var(--kb-primary-50)">
                    <i data-lucide="{{ $badge[0] }}" class="w-5 h-5" style="color:var(--kb-primary)"></i>
                </div>
                <p class="font-semibold text-xs" style="color:var(--kb-ink)">{{ $badge[1] }}</p>
                <p class="text-xs" style="color:var(--kb-ink-soft)">{{ $badge[2] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Shop categories --}}
@if($shopCategories->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 lg:px-6 py-10 lg:py-14">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-extrabold" style="color:var(--kb-ink)">Shop by Category</h2>
        <a href="/shop" class="text-sm font-semibold hover:underline" style="color:var(--kb-primary)">View all →</a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
        @foreach($shopCategories as $cat)
        <a href="/shop?category={{ $cat->slug }}"
           class="kb-card flex flex-col items-center gap-2 p-4 text-center hover:shadow-md transition group"
           style="text-decoration:none">
            <div class="w-14 h-14 rounded-full flex items-center justify-center"
                 style="background:var(--kb-primary-50)">
                <i data-lucide="tag" class="w-6 h-6" style="color:var(--kb-primary)"></i>
            </div>
            <span class="text-sm font-semibold group-hover:text-blue-700" style="color:var(--kb-ink)">{{ $cat->name }}</span>
        </a>
        @endforeach
    </div>
</section>
@endif

{{-- Latest blog posts --}}
@if($latestPosts->isNotEmpty())
<section style="background:#F8FAFF">
    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-10 lg:py-14">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-extrabold" style="color:var(--kb-ink)">From the Blog</h2>
            <a href="{{ route('blog.index') }}" class="text-sm font-semibold hover:underline" style="color:var(--kb-primary)">All posts →</a>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach($latestPosts as $post)
            <a href="{{ route('blog.show', $post->slug) }}" class="kb-card overflow-hidden block group" style="text-decoration:none">
                <div class="overflow-hidden" style="aspect-ratio:16/9">
                    @if($post->cover_url)
                    <img class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                         src="{{ $post->cover_url }}" alt="{{ $post->title }}">
                    @else
                    <div class="w-full h-full flex items-center justify-center" style="background:var(--kb-primary-50);min-height:160px">
                        <i data-lucide="file-text" class="w-10 h-10" style="color:var(--kb-primary);opacity:.4"></i>
                    </div>
                    @endif
                </div>
                <div class="p-5">
                    @if($post->category_name)
                    <span class="kb-chip kb-chip-new">{{ $post->category_name }}</span>
                    @endif
                    <h3 class="text-base font-bold mt-2 leading-snug line-clamp-2" style="color:var(--kb-ink)">{{ $post->title }}</h3>
                    @if($post->excerpt)
                    <p class="text-sm mt-2 line-clamp-2" style="color:var(--kb-ink-muted)">{{ $post->excerpt }}</p>
                    @endif
                    <div class="flex items-center gap-2 text-xs mt-3" style="color:var(--kb-ink-soft)">
                        <span>{{ $post->author_display_name }}</span>
                        <span>·</span>
                        <span>{{ $post->published_at?->format('d M Y') }}</span>
                        <span>·</span>
                        <span>{{ $post->read_time }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Track order CTA --}}
<section class="max-w-7xl mx-auto px-4 lg:px-6 py-10 lg:py-14">
    <div class="kb-card p-8 md:p-12 flex flex-col md:flex-row items-center justify-between gap-6"
         style="background:linear-gradient(135deg,var(--kb-primary-50) 0%,#fff 100%)">
        <div>
            <h2 class="text-2xl font-extrabold mb-2" style="color:var(--kb-ink)">Track Your Order</h2>
            <p style="color:var(--kb-ink-muted)">Enter your order number to see real-time status and delivery updates.</p>
        </div>
        <a href="{{ route('order.track') }}" class="kb-btn kb-btn-primary kb-btn-lg flex-shrink-0" style="border-radius:12px">
            <i data-lucide="search" class="w-5 h-5"></i>
            Track Order
        </a>
    </div>
</section>

@endsection

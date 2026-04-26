<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteName   = \App\Models\SiteSetting::get('general.site_name', config('app.name'));
        $siteTagline = \App\Models\SiteSetting::get('general.site_tagline', '');
    @endphp

    @if(isset($page) && $page->metaInformation)
        <title>{{ $page->metaInformation->meta_title ?: $page->title }} — {{ $siteName }}</title>
        <meta name="description" content="{{ $page->metaInformation->meta_description }}">
        <meta name="keywords"    content="{{ $page->metaInformation->meta_keywords }}">
        <meta name="robots"      content="{{ $page->metaInformation->robots ?: 'index,follow' }}">
        <link rel="canonical"    href="{{ $page->metaInformation->canonical_url ?: url()->current() }}">
        <meta property="og:title"       content="{{ $page->metaInformation->og_title ?: $page->title }}">
        <meta property="og:description" content="{{ $page->metaInformation->og_description ?: $page->metaInformation->meta_description }}">
        <meta property="og:url"         content="{{ url()->current() }}">
        <meta property="og:type"        content="website">
        <meta property="og:site_name"   content="{{ $siteName }}">
    @elseif(isset($blog))
        <title>{{ $blog->metaInformation?->meta_title ?: $blog->title }} — {{ $siteName }}</title>
        <meta name="description" content="{{ $blog->metaInformation?->meta_description }}">
        <meta name="robots"      content="{{ $blog->metaInformation?->robots ?: 'index,follow' }}">
        <link rel="canonical"    href="{{ url()->current() }}">
        <meta property="og:title"       content="{{ $blog->title }}">
        <meta property="og:description" content="{{ $blog->metaInformation?->meta_description ?: $blog->excerpt }}">
        <meta property="og:url"         content="{{ url()->current() }}">
        <meta property="og:type"        content="article">
        @if($blog->cover_image)
        <meta property="og:image"       content="{{ $blog->cover_image }}">
        @endif
        <meta property="og:site_name"   content="{{ $siteName }}">
    @else
        <title>@yield('title', $siteName)</title>
        <meta name="description" content="@yield('description', $siteTagline)">
    @endif

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --kb-primary:     #1F3A8A;
            --kb-primary-600: #2649B8;
            --kb-primary-50:  #EEF2FF;
            --kb-accent:      #F97316;
            --kb-accent-600:  #EA580C;
            --kb-accent-50:   #FFF7ED;
            --kb-ink:         #0F172A;
            --kb-ink-muted:   #475569;
            --kb-ink-soft:    #94A3B8;
            --kb-bg:          #FAFAF9;
            --kb-card:        #FFFFFF;
            --kb-border:      #E2E8F0;
            --kb-success:     #16A34A;
            --kb-danger:      #DC2626;
            --kb-warn:        #D97706;
        }
        html, body { font-family: 'Inter', system-ui, sans-serif; color: var(--kb-ink); background: var(--kb-bg); }
        .bn { font-family: 'Hind Siliguri', 'Inter', system-ui, sans-serif; }
        .kb-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; border-radius: 0.75rem; font-weight: 600; transition: all .15s; }
        .kb-btn-primary { background: var(--kb-primary); color: #fff; }
        .kb-btn-primary:hover { background: var(--kb-primary-600); }
        .kb-btn-accent { background: var(--kb-accent); color: #fff; }
        .kb-btn-accent:hover { background: var(--kb-accent-600); }
        .kb-btn-ghost { background: transparent; color: var(--kb-primary); border: 1px solid var(--kb-border); }
        .kb-btn-ghost:hover { background: var(--kb-primary-50); }
        .kb-btn-md { padding: 0.625rem 1.125rem; font-size: 0.95rem; }
        .kb-btn-lg { padding: 0.875rem 1.4rem; font-size: 1rem; }
        .kb-chip { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.2rem 0.55rem; border-radius: 9999px; font-size: 0.72rem; font-weight: 600; }
        .kb-chip-sale  { background: var(--kb-accent-50);  color: var(--kb-accent-600); }
        .kb-chip-new   { background: var(--kb-primary-50); color: var(--kb-primary); }
        .kb-chip-best  { background: #DCFCE7; color: #166534; }
        .kb-chip-soldout { background: #F1F5F9; color: #475569; }
        .kb-card { background: var(--kb-card); border: 1px solid var(--kb-border); border-radius: 1rem; }
        .kb-input { width: 100%; border: 1px solid var(--kb-border); border-radius: 0.75rem; padding: 0.7rem 0.9rem; font-size: 0.95rem; outline: none; background: #fff; transition: border .15s, box-shadow .15s; }
        .kb-input:focus { border-color: var(--kb-primary); box-shadow: 0 0 0 3px rgba(31,58,138,0.12); }
        .kb-nav-link { color: var(--kb-ink-muted); font-weight: 500; transition: color .15s; text-decoration: none; }
        .kb-nav-link:hover { color: var(--kb-primary); }
        .kb-bar-announce { background: var(--kb-primary); color: #fff; font-size: 0.82rem; padding: 0.5rem 1rem; text-align: center; letter-spacing: 0.02em; }
        .kb-footer { background: #0F172A; color: #CBD5E1; }
        .kb-footer h4 { color: #fff; font-weight: 600; font-size: 0.95rem; margin-bottom: 0.85rem; }
        .kb-footer a { color: #CBD5E1; text-decoration: none; }
        .kb-footer a:hover { color: #fff; }
        .kb-shadow-card { box-shadow: 0 1px 2px rgba(15,23,42,0.04), 0 4px 12px rgba(15,23,42,0.05); }
        :focus-visible { outline: 2px solid var(--kb-primary); outline-offset: 2px; border-radius: 4px; }
    </style>

    @stack('head')
</head>
<body>

{{-- Announce bar --}}
@php $announceBar = \App\Models\SiteSetting::get('general.announce_bar', ''); @endphp
@if($announceBar)
<div class="kb-bar-announce">{{ $announceBar }}</div>
@endif

{{-- Header --}}
@php
    $logoMediaId = \App\Models\SiteSetting::get('general.logo_media_id');
    $logoUrl = $logoMediaId ? \App\Models\Media::find((int)$logoMediaId)?->getUrl('thumb') : null;
    $phone = \App\Models\SiteSetting::get('general.phone', '');
@endphp
<header class="bg-white border-b border-slate-200 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 lg:px-6">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <a href="/" class="flex items-center gap-2" aria-label="{{ $siteName }}">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="h-9 w-auto">
                    @else
                        <span class="text-xl font-extrabold tracking-tight" style="color:var(--kb-primary)">{{ $siteName }}</span>
                    @endif
                </a>
            </div>

            {{-- Desktop nav --}}
            <nav class="hidden lg:flex items-center gap-7 text-sm">
                <a href="/shop" class="kb-nav-link">Shop</a>
                <a href="/blog" class="kb-nav-link {{ request()->is('blog*') ? 'text-slate-900 font-semibold' : '' }}">Blog</a>
                <a href="/track" class="kb-nav-link">Track Order</a>
            </nav>

            {{-- Right icons --}}
            <div class="flex items-center gap-3">
                @auth
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" type="button"
                            class="kb-nav-link flex items-center gap-1.5 text-sm"
                            style="background:none;border:none;cursor:pointer">
                        <i data-lucide="user" class="w-5 h-5"></i>
                        <span class="hidden sm:inline text-xs font-medium">{{ Str::words(auth()->user()->name, 1, '') }}</span>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition
                         class="absolute right-0 mt-2 w-44 kb-card py-1 z-50"
                         style="display:none;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,.12)">
                        <div class="px-4 py-2 text-xs border-b" style="color:var(--kb-ink-soft);border-color:var(--kb-border)">
                            {{ auth()->user()->email }}
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm hover:opacity-70"
                                    style="background:none;border:none;cursor:pointer;color:var(--kb-ink)">
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}" class="kb-nav-link" title="Sign in">
                    <i data-lucide="user" class="w-5 h-5"></i>
                </a>
                @endauth
                <a href="/cart" class="kb-nav-link relative" title="Cart">
                    <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                </a>
            </div>
        </div>

        {{-- Mobile search --}}
        <div class="md:hidden pb-3">
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-3 text-slate-400"></i>
                <input class="kb-input pl-9 text-sm" placeholder="Search products…">
            </div>
        </div>
    </div>
</header>

{{-- Main Content --}}
<main>
    @yield('content')
</main>

{{-- Footer --}}
@php
    $phone   = \App\Models\SiteSetting::get('general.phone', '');
    $email   = \App\Models\SiteSetting::get('general.admin_email', '');
    $address = \App\Models\SiteSetting::get('general.address', '');
@endphp
<footer class="kb-footer mt-20">
    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-12 grid grid-cols-2 md:grid-cols-4 gap-8 text-sm">
        <div class="col-span-2">
            <p class="text-xl font-extrabold text-white mb-3">{{ $siteName }}</p>
            @if($siteTagline)
            <p class="max-w-sm text-slate-400 mb-3">{{ $siteTagline }}</p>
            @endif
            @if($phone || $email || $address)
            <p class="text-slate-400 text-xs leading-relaxed">
                @if($phone)Tel: {{ $phone }}<br>@endif
                @if($email){{ $email }}<br>@endif
                @if($address){{ $address }}@endif
            </p>
            @endif
            <div class="mt-4 flex gap-3">
                <a href="#" class="w-9 h-9 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.1)"><i data-lucide="facebook" class="w-4 h-4"></i></a>
                <a href="#" class="w-9 h-9 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.1)"><i data-lucide="instagram" class="w-4 h-4"></i></a>
                <a href="#" class="w-9 h-9 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.1)"><i data-lucide="youtube" class="w-4 h-4"></i></a>
            </div>
        </div>
        <div>
            <h4>Help</h4>
            <ul class="space-y-2">
                <li><a href="/track">Track order</a></li>
                <li><a href="/page/returns-policy">Returns & refunds</a></li>
                <li><a href="/page/shipping-policy">Shipping policy</a></li>
                <li><a href="/page/contact">Contact us</a></li>
            </ul>
        </div>
        <div>
            <h4>Company</h4>
            <ul class="space-y-2">
                <li><a href="/page/about-us">About us</a></li>
                <li><a href="/blog">Blog</a></li>
                <li><a href="/page/privacy-policy">Privacy</a></li>
                <li><a href="/page/terms">Terms</a></li>
            </ul>
        </div>
        <div class="col-span-2 md:col-span-4 mt-2">
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400">
                <span>We accept:</span>
                @foreach(['bKash','Nagad','Rocket','SSLCOMMERZ','Visa / Mastercard','Cash on Delivery'] as $pm)
                <span class="kb-chip" style="background:#fff;color:#0F172A">{{ $pm }}</span>
                @endforeach
            </div>
        </div>
    </div>
    <div class="border-t py-4 text-center text-xs text-slate-500" style="border-color:rgba(255,255,255,.08)">
        © {{ date('Y') }} {{ $siteName }} · Bangladesh
    </div>
</footer>

<script>
    if (window.lucide) window.lucide.createIcons();
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) window.lucide.createIcons();
    });
</script>

@stack('scripts')
</body>
</html>

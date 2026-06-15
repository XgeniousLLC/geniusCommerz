<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteName   = cache()->remember('site_name', 300, fn () => \App\Models\SiteSetting::get('general.site_name', 'klixbd'));
        $faviconUrl = cache()->remember('site_favicon_url', 300, function () {
            $id = \App\Models\SiteSetting::get('general.favicon_media_id');
            if (!$id) return null;
            $media = \App\Models\Media::find((int) $id);
            return $media?->getUrl();
        });
    @endphp

    <title>@yield('title', 'Dashboard') — {{ $siteName }} Admin</title>

    @if($faviconUrl)
        <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}">
    @endif

    {{-- Persist theme/density before first paint --}}
    <script>(function(){try{var t=JSON.parse(localStorage.getItem('cobalt_theme')||'"light"');var d=JSON.parse(localStorage.getItem('cobalt_density')||'"comfortable"');document.documentElement.setAttribute('data-theme',t);document.documentElement.setAttribute('data-density',d);}catch(e){}})();</script>

    <link rel="stylesheet" href="{{ asset('admin/css/cobalt.css') }}">
    <style>[x-cloak]{display:none!important}</style>

    @stack('styles')

    {{-- Alpine.js collapse plugin (must load before core) --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    {{-- Alpine.js --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Media picker Alpine component — registered before Alpine boots --}}
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('mediaPicker', ({ name, accept = 'image', multiple = false, preItems = [], label = 'Add Image' }) => ({
            isOpen:    false,
            iframeUrl: '',
            selected:  multiple ? null : (preItems[0] ?? null),
            items:     multiple ? preItems : [],
            name,
            accept,
            multiple,
            label,
            pickerId:  name,

            init() {
                window.addEventListener('message', (e) => {
                    if (e.data?.type === 'media-selected' && e.data.pickerId === this.pickerId) {
                        this.handleSelect(e.data.media, e.data.action || 'add');
                    }
                });
            },

            open() {
                const p = new URLSearchParams({ picker: '1', picker_id: this.pickerId });
                if (accept)   p.set('accept', accept);
                if (multiple) p.set('multiple', '1');
                if (multiple && this.items.length) {
                    p.set('selected_ids', this.items.map(i => i.id).join(','));
                }
                this.iframeUrl = `/admin/media?${p}`;
                this.isOpen    = true;
            },

            close() { this.isOpen = false; },

            handleSelect(media, action = 'add') {
                if (!multiple) { this.selected = media; this.close(); return; }
                if (action === 'remove') {
                    this.items = this.items.filter(i => i.id !== media.id);
                } else if (!this.items.find(i => i.id === media.id)) {
                    this.items.push(media);
                }
            },

            removeItem(id) { this.items = this.items.filter(i => i.id !== id); },
        }));
    });
    </script>
</head>
<body>
<div class="app" id="app">
    <div class="scrim"></div>

    <aside class="sidebar" id="sidebar">
        @include('admin.layouts.partials.admin-sidebar')
    </aside>

    <div class="main">
        <header class="topbar" id="topbar">
            @include('admin.layouts.partials.admin-header')
        </header>

        <main class="content">
            <div class="content-inner">

                @if(session('success'))
                    <div class="card pad" style="background:var(--success-soft);border-color:color-mix(in srgb,var(--success) 30%,transparent);margin-bottom:18px">
                        <div class="row" style="gap:10px">
                            <span class="tile sm" style="background:var(--success);color:#fff;flex-shrink:0"><span class="ico" data-ico="check" style="width:18px;height:18px"></span></span>
                            <span style="font-weight:600;font-size:14px;color:var(--success)">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="card pad" style="background:var(--danger-soft);border-color:color-mix(in srgb,var(--danger) 30%,transparent);margin-bottom:18px">
                        <div class="row" style="gap:10px">
                            <span class="tile sm" style="background:var(--danger);color:#fff;flex-shrink:0"><span class="ico" data-ico="x" style="width:18px;height:18px"></span></span>
                            <span style="font-weight:600;font-size:14px;color:var(--danger)">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')

            </div>
        </main>
    </div>
</div>

<div class="toast-host" id="toastHost"></div>

<script src="{{ asset('admin/js/cobalt-icons.js') }}"></script>
<script src="{{ asset('admin/js/cobalt-ui.js') }}"></script>
<script>
(function () {
    var app = document.getElementById('app');

    // Restore sidebar collapsed state
    if (localStorage.getItem('cobalt_collapsed') === '1') app.classList.add('collapsed');

    // Hamburger: desktop = collapse rail, mobile = overlay
    var ham = document.getElementById('hamburger');
    if (ham) ham.addEventListener('click', function () {
        if (window.innerWidth < 900) {
            app.classList.toggle('mobile-open');
        } else {
            app.classList.toggle('collapsed');
            localStorage.setItem('cobalt_collapsed', app.classList.contains('collapsed') ? '1' : '0');
        }
    });

    // Scrim dismisses mobile sidebar
    var scrim = document.querySelector('.scrim');
    if (scrim) scrim.addEventListener('click', function () { app.classList.remove('mobile-open'); });

    // Nav group expand/collapse
    document.querySelectorAll('[data-group-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.nav-group').classList.toggle('open');
        });
    });

    // Theme toggle
    function syncThemeIcon() {
        var dark = document.documentElement.getAttribute('data-theme') === 'dark';
        var el = document.querySelector('#themeToggle .ico');
        if (el) { el.dataset.ico = dark ? 'sun' : 'moon'; el.dataset.rendered = '0'; if (window.Icons) Icons.render(el.parentElement); }
    }
    syncThemeIcon();
    var tt = document.getElementById('themeToggle');
    if (tt) tt.addEventListener('click', function () {
        var dark = document.documentElement.getAttribute('data-theme') === 'dark';
        var next = dark ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('cobalt_theme', JSON.stringify(next));
        syncThemeIcon();
    });
})();
</script>

@stack('scripts')
</body>
</html>

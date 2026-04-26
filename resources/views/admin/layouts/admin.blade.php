<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Shadcn UI CSS Variables -->
    <style>
        :root {
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --muted: 210 40% 98%;
            --muted-foreground: 215.4 16.3% 46.9%;
            --popover: 0 0% 100%;
            --popover-foreground: 222.2 84% 4.9%;
            --card: 0 0% 100%;
            --card-foreground: 222.2 84% 4.9%;
            --border: 214.3 31.8% 91.4%;
            --input: 214.3 31.8% 91.4%;
            --primary: 222.2 47.4% 11.2%;
            --primary-foreground: 210 40% 98%;
            --secondary: 210 40% 96%;
            --secondary-foreground: 222.2 84% 4.9%;
            --accent: 210 40% 96%;
            --accent-foreground: 222.2 84% 4.9%;
            --destructive: 0 84.2% 60.2%;
            --destructive-foreground: 210 40% 98%;
            --ring: 222.2 84% 4.9%;
            --radius: 0.5rem;
        }
    </style>
    
    <!-- Page Specific Styles -->
    @stack('styles')
    
    <!-- Alpine.js collapse plugin (must load before core) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Media picker Alpine component — registered before Alpine boots -->
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
            pickerId:  name, // unique per instance — used to route postMessage back to the right picker

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
                // Ensure modal sits above sidebar regardless of stacking context
                const sidebar = document.getElementById('sidebar');
                if (sidebar) sidebar.style.zIndex = '0';
            },

            close() {
                this.isOpen = false;
                const sidebar = document.getElementById('sidebar');
                if (sidebar) sidebar.style.zIndex = '';
            },

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
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:transform-none lg:static lg:inset-0">
            @include('admin.layouts.partials.admin-sidebar')
        </div>
        
        <!-- Sidebar Overlay (Mobile) -->
        <div id="sidebar-overlay" class="fixed inset-0 z-40 bg-black bg-opacity-50 hidden lg:hidden"></div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                @include('admin.layouts.partials.admin-header')
            </header>
            
            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
                <!-- Breadcrumbs -->
                @hasSection('breadcrumbs')
                    <div class="bg-white border-b border-gray-200 px-6 py-4">
                        <nav class="flex" aria-label="Breadcrumb">
                            @yield('breadcrumbs')
                        </nav>
                    </div>
                @endif
                
                <!-- Page Content -->
                <div class="container mx-auto px-6 py-8">
                    <!-- Page Header -->
                    @hasSection('page-header')
                        <div class="mb-8">
                            @yield('page-header')
                        </div>
                    @endif
                    
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <x-admin.alert type="success" class="mb-6">
                            {{ session('success') }}
                        </x-admin.alert>
                    @endif
                    
                    @if(session('error'))
                        <x-admin.alert type="error" class="mb-6">
                            {{ session('error') }}
                        </x-admin.alert>
                    @endif
                    
                    <!-- Dynamic Content -->
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    
    <!-- Page Specific Scripts -->
    @stack('scripts')
    
    <!-- Sidebar Toggle Script -->
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const toggleBtn = document.getElementById('sidebar-toggle');
            
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('-translate-x-full');
                    overlay.classList.toggle('hidden');
                });
            }
            
            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                });
            }
        });
    </script>
</body>
</html>
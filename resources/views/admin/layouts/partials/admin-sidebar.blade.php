<div class="flex flex-col h-full">
    {{-- Logo --}}
    @php
        $siteName  = cache()->remember('site_name', 300, fn () => \App\Models\SiteSetting::get('general.site_name', 'klixbd'));
        $logoUrl   = cache()->remember('site_logo_url', 300, function () {
            $id = \App\Models\SiteSetting::get('general.logo_media_id');
            if (!$id) return null;
            $media = \App\Models\Media::find((int) $id);
            return $media?->getUrl('thumb');
        });
    @endphp
    <div class="flex items-center justify-center h-16 px-4 bg-blue-600 text-white flex-shrink-0">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="h-9 max-w-full object-contain">
        @else
            <h1 class="text-xl font-bold tracking-wide truncate">{{ $siteName }}</h1>
        @endif
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-1">

        {{-- Dashboard --}}
        <x-admin.sidebar-link
            :href="route('admin.dashboard')"
            :active="request()->routeIs('admin.dashboard')"
            icon="home">
            Dashboard
        </x-admin.sidebar-link>

        {{-- Catalog --}}
        @php $catalogOpen = request()->routeIs('admin.products.*','admin.categories.*','admin.brands.*','admin.attributes.*'); @endphp
        <div x-data="{ open: {{ $catalogOpen ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold uppercase tracking-wider rounded-md
                       {{ $catalogOpen ? 'text-blue-600 bg-blue-50' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}">
                <span>Catalog</span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-1 space-y-0.5 pl-2">
                <x-admin.sidebar-link
                    :href="route('admin.products.index')"
                    :active="request()->routeIs('admin.products.*')"
                    icon="shopping-bag">
                    Products
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.categories.index')"
                    :active="request()->routeIs('admin.categories.*')"
                    icon="tag">
                    Categories
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.brands.index')"
                    :active="request()->routeIs('admin.brands.*')"
                    icon="bookmark">
                    Brands
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.attributes.index')"
                    :active="request()->routeIs('admin.attributes.*')"
                    icon="adjustments">
                    Attributes
                </x-admin.sidebar-link>
            </div>
        </div>

        {{-- Orders --}}
        <x-admin.sidebar-link
            :href="route('admin.orders.index')"
            :active="request()->routeIs('admin.orders.*')"
            icon="shopping-bag">
            Orders
        </x-admin.sidebar-link>

        {{-- Coupons --}}
        <x-admin.sidebar-link
            :href="route('admin.coupons.index')"
            :active="request()->routeIs('admin.coupons.*')"
            icon="ticket">
            Coupons
        </x-admin.sidebar-link>

        {{-- Refunds --}}
        <x-admin.sidebar-link
            :href="route('admin.refunds.index')"
            :active="request()->routeIs('admin.refunds.*')"
            icon="arrow-uturn-left">
            Refunds
        </x-admin.sidebar-link>

        {{-- Menus --}}
        <x-admin.sidebar-link
            :href="route('admin.menus.index')"
            :active="request()->routeIs('admin.menus.*')"
            icon="adjustments">
            Menus
        </x-admin.sidebar-link>

        {{-- Loyalty --}}
        <x-admin.sidebar-link
            :href="route('admin.loyalty.settings')"
            :active="request()->routeIs('admin.loyalty.*')"
            icon="star">
            Loyalty
        </x-admin.sidebar-link>

        {{-- Reports --}}
        <x-admin.sidebar-link
            :href="route('admin.reports.sales')"
            :active="request()->routeIs('admin.reports.*')"
            icon="chart-bar">
            Reports
        </x-admin.sidebar-link>

        {{-- Content --}}
        @php $contentOpen = request()->routeIs('admin.pages.*', 'admin.blogs.*', 'admin.blog-categories.*', 'admin.media.*'); @endphp
        <div x-data="{ open: {{ $contentOpen ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold uppercase tracking-wider rounded-md
                       {{ $contentOpen ? 'text-blue-600 bg-blue-50' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}">
                <span>Content</span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-1 space-y-0.5 pl-2">
                <x-admin.sidebar-link
                    :href="route('admin.blogs.index')"
                    :active="request()->routeIs('admin.blogs.*')"
                    icon="newspaper">
                    Blog Posts
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.blog-categories.index')"
                    :active="request()->routeIs('admin.blog-categories.*')"
                    icon="tag">
                    Blog Categories
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.pages.index')"
                    :active="request()->routeIs('admin.pages.*')"
                    icon="document-text">
                    Pages
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.media.index')"
                    :active="request()->routeIs('admin.media.*')"
                    icon="photograph">
                    Media Library
                </x-admin.sidebar-link>
            </div>
        </div>

        {{-- Users --}}
        @php $usersOpen = request()->routeIs('admin.users.*','admin.admins.*'); @endphp
        <div x-data="{ open: {{ $usersOpen ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold uppercase tracking-wider rounded-md
                       {{ $usersOpen ? 'text-blue-600 bg-blue-50' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}">
                <span>Users</span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-1 space-y-0.5 pl-2">
                <x-admin.sidebar-link
                    :href="route('admin.users.index')"
                    :active="request()->routeIs('admin.users.*')"
                    icon="users">
                    Customers
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.admins.index')"
                    :active="request()->routeIs('admin.admins.*')"
                    icon="shield-check">
                    Admins
                </x-admin.sidebar-link>
            </div>
        </div>

        {{-- Access Control --}}
        @php $accessOpen = request()->routeIs('admin.roles.*'); @endphp
        <div x-data="{ open: {{ $accessOpen ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold uppercase tracking-wider rounded-md
                       {{ $accessOpen ? 'text-blue-600 bg-blue-50' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}">
                <span>Access Control</span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-1 space-y-0.5 pl-2">
                <x-admin.sidebar-link
                    :href="route('admin.roles.index')"
                    :active="request()->routeIs('admin.roles.*')"
                    icon="key">
                    Roles & Permissions
                </x-admin.sidebar-link>
            </div>
        </div>

        {{-- System --}}
        @php $systemOpen = request()->routeIs('admin.settings.*','admin.integrations.*','admin.audit.*','admin.failed-jobs.*') || request()->is('horizon*'); @endphp
        <div x-data="{ open: {{ $systemOpen ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold uppercase tracking-wider rounded-md
                       {{ $systemOpen ? 'text-blue-600 bg-blue-50' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}">
                <span>System</span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-1 space-y-0.5 pl-2">
                <x-admin.sidebar-link
                    :href="route('admin.settings.index')"
                    :active="request()->routeIs('admin.settings.*')"
                    icon="cog">
                    Settings
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.integrations.index')"
                    :active="request()->routeIs('admin.integrations.*')"
                    icon="puzzle">
                    Integrations
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.audit.index')"
                    :active="request()->routeIs('admin.audit.*')"
                    icon="clipboard-list">
                    Audit Log
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.failed-jobs.index')"
                    :active="request()->routeIs('admin.failed-jobs.*')"
                    icon="exclamation-circle">
                    Failed Jobs
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    href="/horizon"
                    :active="request()->is('horizon*')"
                    icon="chart-bar">
                    Horizon
                </x-admin.sidebar-link>
            </div>
        </div>

    </nav>

    {{-- User Info --}}
    @auth('admin')
    <div class="p-4 border-t border-gray-200 flex-shrink-0">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-white text-sm font-medium">
                    {{ strtoupper(substr(auth('admin')->user()->name, 0, 1)) }}
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ auth('admin')->user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth('admin')->user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" title="Sign out"
                    class="text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
    @endauth
</div>

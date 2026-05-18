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

        {{-- Social Proof --}}
        <x-admin.sidebar-link
            :href="route('admin.social-proof.index')"
            :active="request()->routeIs('admin.social-proof.*')"
            icon="trending-up">
            Social Proof
        </x-admin.sidebar-link>

        {{-- Storefront --}}
        @php $storefrontOpen = request()->routeIs('admin.storefront.*'); @endphp
        <div x-data="{ open: {{ $storefrontOpen ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold uppercase tracking-wider rounded-md
                       {{ $storefrontOpen ? 'text-blue-600 bg-blue-50' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}">
                <span>Storefront</span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-1 space-y-0.5 pl-2">
                <x-admin.sidebar-link :href="route('admin.storefront.homepage')" :active="request()->routeIs('admin.storefront.homepage')" icon="home">
                    Homepage
                </x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.storefront.footer')" :active="request()->routeIs('admin.storefront.footer')" icon="document-text">
                    Footer
                </x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.storefront.shop')" :active="request()->routeIs('admin.storefront.shop')" icon="shopping-bag">
                    Shop
                </x-admin.sidebar-link>
            </div>
        </div>

        {{-- Reports --}}
        @php $reportsOpen = request()->routeIs('admin.reports.*'); @endphp
        <div x-data="{ open: {{ $reportsOpen ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold uppercase tracking-wider rounded-md
                       {{ $reportsOpen ? 'text-blue-600 bg-blue-50' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}">
                <span>Reports</span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-1 space-y-0.5 pl-2">
                {{-- Sales & Revenue --}}
                <p class="px-3 pt-2 pb-0.5 text-[10px] font-bold uppercase tracking-widest text-gray-400">Sales</p>
                <x-admin.sidebar-link :href="route('admin.reports.sales')" :active="request()->routeIs('admin.reports.sales')" icon="chart-bar">Sales Overview</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.reports.profit')" :active="request()->routeIs('admin.reports.profit')" icon="chart-bar">Profit Analysis</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.reports.top-products')" :active="request()->routeIs('admin.reports.top-products')" icon="chart-bar">Top Products</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.reports.demand-trends')" :active="request()->routeIs('admin.reports.demand-trends')" icon="chart-bar">Demand Trends</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.reports.category-revenue')" :active="request()->routeIs('admin.reports.category-revenue')" icon="chart-bar">Category & Brand</x-admin.sidebar-link>
                {{-- Inventory --}}
                <p class="px-3 pt-2 pb-0.5 text-[10px] font-bold uppercase tracking-widest text-gray-400">Inventory</p>
                <x-admin.sidebar-link :href="route('admin.reports.inventory')" :active="request()->routeIs('admin.reports.inventory')" icon="chart-bar">Inventory Status</x-admin.sidebar-link>
                {{-- Customers --}}
                <p class="px-3 pt-2 pb-0.5 text-[10px] font-bold uppercase tracking-widest text-gray-400">Customers</p>
                <x-admin.sidebar-link :href="route('admin.reports.customers')" :active="request()->routeIs('admin.reports.customers')" icon="chart-bar">Customer Report</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.reports.customer-ltv')" :active="request()->routeIs('admin.reports.customer-ltv')" icon="chart-bar">Customer LTV</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.reports.geographic')" :active="request()->routeIs('admin.reports.geographic')" icon="chart-bar">Geographic Sales</x-admin.sidebar-link>
                {{-- Orders & Finance --}}
                <p class="px-3 pt-2 pb-0.5 text-[10px] font-bold uppercase tracking-widest text-gray-400">Finance</p>
                <x-admin.sidebar-link :href="route('admin.reports.orders')" :active="request()->routeIs('admin.reports.orders')" icon="chart-bar">Orders Report</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.reports.coupon-usage')" :active="request()->routeIs('admin.reports.coupon-usage')" icon="chart-bar">Coupon Usage</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.reports.refund-analysis')" :active="request()->routeIs('admin.reports.refund-analysis')" icon="chart-bar">Refund Analysis</x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.reports.payment-trends')" :active="request()->routeIs('admin.reports.payment-trends')" icon="chart-bar">Payment Trends</x-admin.sidebar-link>
            </div>
        </div>

        {{-- Accounting --}}
        @php $accountingOpen = request()->routeIs('admin.accounting.*'); @endphp
        <div x-data="{ open: {{ $accountingOpen ? 'true' : 'false' }} }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-2 text-xs font-semibold uppercase tracking-wider rounded-md
                       {{ $accountingOpen ? 'text-blue-600 bg-blue-50' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}">
                <span>Accounting</span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-1 space-y-0.5 pl-2">
                <x-admin.sidebar-link :href="route('admin.accounting.index')" :active="request()->routeIs('admin.accounting.index')" icon="chart-bar">
                    Dashboard
                </x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.accounting.purchases.index')" :active="request()->routeIs('admin.accounting.purchases.*')" icon="shopping-bag">
                    Purchase Orders
                </x-admin.sidebar-link>
                <x-admin.sidebar-link :href="route('admin.accounting.ad-spend.index')" :active="request()->routeIs('admin.accounting.ad-spend.*')" icon="trending-up">
                    Ad Spend
                </x-admin.sidebar-link>
            </div>
        </div>

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
        @php $systemOpen = request()->routeIs('admin.settings.*','admin.integrations.*','admin.audit.*','admin.failed-jobs.*','admin.sitemap.*') || request()->is('horizon*'); @endphp
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
                    :active="request()->routeIs('admin.settings.*') && !in_array(request()->get('tab'), ['tracking','feeds'])"
                    icon="cog">
                    Settings
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.settings.index', ['tab' => 'tracking'])"
                    :active="request()->routeIs('admin.settings.*') && request()->get('tab') === 'tracking'"
                    icon="signal">
                    Tracking
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.settings.index', ['tab' => 'feeds'])"
                    :active="request()->routeIs('admin.settings.*') && request()->get('tab') === 'feeds'"
                    icon="globe-alt">
                    Product Feeds
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.sitemap.index')"
                    :active="request()->routeIs('admin.sitemap.*')"
                    icon="document-text">
                    Sitemap
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.integrations.index')"
                    :active="request()->routeIs('admin.integrations.*') && !request()->routeIs('admin.ai-settings.*')"
                    icon="puzzle">
                    Integrations
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.ai-settings.index')"
                    :active="request()->routeIs('admin.ai-settings.*')"
                    icon="lightning-bolt">
                    AI Settings
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.languages.index')"
                    :active="request()->routeIs('admin.languages.*')"
                    icon="translate">
                    Languages
                </x-admin.sidebar-link>
                <x-admin.sidebar-link
                    :href="route('admin.currencies.index')"
                    :active="request()->routeIs('admin.currencies.*')"
                    icon="currency-dollar">
                    Currencies
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

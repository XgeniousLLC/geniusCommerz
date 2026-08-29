@php
    $siteName = cache()->remember('site_name', 300, fn () => \App\Models\SiteSetting::get('general.site_name', 'geniuscommerz'));
    $logoUrl  = cache()->remember('site_logo_url', 300, function () {
        $id = \App\Models\SiteSetting::get('general.logo_media_id');
        if (!$id) return null;
        $media = \App\Models\Media::find((int) $id);
        return $media?->getUrl('thumb');
    });
@endphp

<div class="sidebar-logo">
    @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" style="height:32px;max-width:140px;object-fit:contain">
    @else
        <span class="logo-mark">{{ strtoupper(substr($siteName, 0, 1)) }}</span>
        <span class="logo-text"><b>{{ $siteName }}</b></span>
    @endif
</div>

<nav class="nav">

    <a class="nav-leaf focusable {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
        <span class="ico" data-ico="dashboard"></span>
        <span>Dashboard</span>
    </a>

    @php $catalogActive = request()->routeIs('admin.products.*','admin.categories.*','admin.brands.*','admin.attributes.*','admin.sourcing.*'); @endphp
    <div class="nav-group {{ $catalogActive ? 'open' : '' }}">
        <button class="nav-grouphead focusable" type="button" data-group-toggle>
            <span class="ico" data-ico="box"></span>
            <span>Catalog</span>
            <span class="ico chev" data-ico="chevDown" style="width:16px;height:16px"></span>
        </button>
        <div class="nav-children"><div><div class="nav-sublist">
            <a class="nav-leaf focusable {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                <span class="ico" data-ico="package"></span><span>Products</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                <span class="ico" data-ico="tag"></span><span>Categories</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}" href="{{ route('admin.brands.index') }}">
                <span class="ico" data-ico="bookmark"></span><span>Brands</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.attributes.*') ? 'active' : '' }}" href="{{ route('admin.attributes.index') }}">
                <span class="ico" data-ico="sliders"></span><span>Attributes</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.sourcing.*') ? 'active' : '' }}" href="{{ route('admin.sourcing.index') }}">
                <span class="ico" data-ico="search"></span><span>Sourcing</span>
            </a>
        </div></div></div>
    </div>

    @php $ordersActive = request()->routeIs('admin.orders.*','admin.coupons.*','admin.refunds.*'); @endphp
    <div class="nav-group {{ $ordersActive ? 'open' : '' }}">
        <button class="nav-grouphead focusable" type="button" data-group-toggle>
            <span class="ico" data-ico="cart"></span>
            <span>Orders</span>
            <span class="ico chev" data-ico="chevDown" style="width:16px;height:16px"></span>
        </button>
        <div class="nav-children"><div><div class="nav-sublist">
            <a class="nav-leaf focusable {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                <span class="ico" data-ico="receipt"></span><span>All Orders</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}" href="{{ route('admin.coupons.index') }}">
                <span class="ico" data-ico="percent"></span><span>Coupons</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.refunds.*') ? 'active' : '' }}" href="{{ route('admin.refunds.index') }}">
                <span class="ico" data-ico="refresh"></span><span>Refunds</span>
            </a>
        </div></div></div>
    </div>

    @php $customersActive = request()->routeIs('admin.users.*','admin.loyalty.*'); @endphp
    <div class="nav-group {{ $customersActive ? 'open' : '' }}">
        <button class="nav-grouphead focusable" type="button" data-group-toggle>
            <span class="ico" data-ico="users"></span>
            <span>Customers</span>
            <span class="ico chev" data-ico="chevDown" style="width:16px;height:16px"></span>
        </button>
        <div class="nav-children"><div><div class="nav-sublist">
            <a class="nav-leaf focusable {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                <span class="ico" data-ico="users"></span><span>All Customers</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.loyalty.*') ? 'active' : '' }}" href="{{ route('admin.loyalty.settings') }}">
                <span class="ico" data-ico="star"></span><span>Loyalty Program</span>
            </a>
        </div></div></div>
    </div>

    @php $storefrontActive = request()->routeIs('admin.storefront.*','admin.menus.*','admin.pages.*','admin.blogs.*','admin.blog-categories.*','admin.media.*'); @endphp
    <div class="nav-group {{ $storefrontActive ? 'open' : '' }}">
        <button class="nav-grouphead focusable" type="button" data-group-toggle>
            <span class="ico" data-ico="store"></span>
            <span>Storefront</span>
            <span class="ico chev" data-ico="chevDown" style="width:16px;height:16px"></span>
        </button>
        <div class="nav-children"><div><div class="nav-sublist">
            <a class="nav-leaf focusable {{ request()->routeIs('admin.storefront.homepage') ? 'active' : '' }}" href="{{ route('admin.storefront.homepage') }}">
                <span class="ico" data-ico="dashboard"></span><span>Homepage</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.storefront.footer') ? 'active' : '' }}" href="{{ route('admin.storefront.footer') }}">
                <span class="ico" data-ico="doc"></span><span>Footer</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.storefront.shop') ? 'active' : '' }}" href="{{ route('admin.storefront.shop') }}">
                <span class="ico" data-ico="package"></span><span>Shop Page</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.menus.*') ? 'active' : '' }}" href="{{ route('admin.menus.index') }}">
                <span class="ico" data-ico="list"></span><span>Navigation</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}">
                <span class="ico" data-ico="doc"></span><span>Pages</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}" href="{{ route('admin.blogs.index') }}">
                <span class="ico" data-ico="edit"></span><span>Blog Posts</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.blog-categories.*') ? 'active' : '' }}" href="{{ route('admin.blog-categories.index') }}">
                <span class="ico" data-ico="tag"></span><span>Blog Categories</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.media.*') ? 'active' : '' }}" href="{{ route('admin.media.index') }}">
                <span class="ico" data-ico="image"></span><span>Media Library</span>
            </a>
        </div></div></div>
    </div>

    @php $reportsActive = request()->routeIs('admin.reports.*'); @endphp
    <div class="nav-group {{ $reportsActive ? 'open' : '' }}">
        <button class="nav-grouphead focusable" type="button" data-group-toggle>
            <span class="ico" data-ico="chart"></span>
            <span>Reports</span>
            <span class="ico chev" data-ico="chevDown" style="width:16px;height:16px"></span>
        </button>
        <div class="nav-children"><div><div class="nav-sublist">
            <a class="nav-leaf focusable {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                <span>Overview</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}" href="{{ route('admin.reports.sales') }}">
                <span>Sales overview</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.reports.profit') ? 'active' : '' }}" href="{{ route('admin.reports.profit') }}">
                <span>Profit analysis</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.reports.top-products') ? 'active' : '' }}" href="{{ route('admin.reports.top-products') }}">
                <span>Top products</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.reports.demand-trends') ? 'active' : '' }}" href="{{ route('admin.reports.demand-trends') }}">
                <span>Demand trends</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.reports.category-revenue') ? 'active' : '' }}" href="{{ route('admin.reports.category-revenue') }}">
                <span>Category revenue</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.reports.inventory') ? 'active' : '' }}" href="{{ route('admin.reports.inventory') }}">
                <span>Inventory</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.reports.customers') ? 'active' : '' }}" href="{{ route('admin.reports.customers') }}">
                <span>Customers</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.reports.orders') ? 'active' : '' }}" href="{{ route('admin.reports.orders') }}">
                <span>Orders report</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.reports.coupon-usage') ? 'active' : '' }}" href="{{ route('admin.reports.coupon-usage') }}">
                <span>Coupon usage</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.reports.refund-analysis') ? 'active' : '' }}" href="{{ route('admin.reports.refund-analysis') }}">
                <span>Refund analysis</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.reports.payment-trends') ? 'active' : '' }}" href="{{ route('admin.reports.payment-trends') }}">
                <span>Payment trends</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.reports.geographic') ? 'active' : '' }}" href="{{ route('admin.reports.geographic') }}">
                <span>Geographic</span>
            </a>
        </div></div></div>
    </div>

    @php $financeActive = request()->routeIs('admin.accounting.*'); @endphp
    <div class="nav-group {{ $financeActive ? 'open' : '' }}">
        <button class="nav-grouphead focusable" type="button" data-group-toggle>
            <span class="ico" data-ico="wallet"></span>
            <span>Finance</span>
            <span class="ico chev" data-ico="chevDown" style="width:16px;height:16px"></span>
        </button>
        <div class="nav-children"><div><div class="nav-sublist">
            <a class="nav-leaf focusable {{ request()->routeIs('admin.accounting.purchases.*') ? 'active' : '' }}" href="{{ route('admin.accounting.purchases.index') }}">
                <span class="ico" data-ico="doc"></span><span>Purchase Orders</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.accounting.ad-spend.*') ? 'active' : '' }}" href="{{ route('admin.accounting.ad-spend.index') }}">
                <span class="ico" data-ico="bolt"></span><span>Ad Spend</span>
            </a>
        </div></div></div>
    </div>

    <a class="nav-leaf focusable {{ request()->routeIs('admin.admins.*','admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.admins.index') }}">
        <span class="ico" data-ico="team"></span>
        <span>Team</span>
    </a>

    <a class="nav-leaf focusable {{ request()->routeIs('admin.settings.*','admin.order-settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
        <span class="ico" data-ico="gear"></span>
        <span>Settings</span>
    </a>

    @php $systemActive = request()->routeIs('admin.sms.*','admin.fraud.*','admin.shipping.*','admin.tax.*','admin.payment-gateways.*','admin.integrations.*','admin.ai-settings.*','admin.languages.*','admin.audit.*','admin.failed-jobs.*','admin.sitemap.*','admin.currencies.*','admin.pixel-logs.*'); @endphp
    <div class="nav-group {{ $systemActive ? 'open' : '' }}">
        <button class="nav-grouphead focusable" type="button" data-group-toggle>
            <span class="ico" data-ico="system"></span>
            <span>System</span>
            <span class="ico chev" data-ico="chevDown" style="width:16px;height:16px"></span>
        </button>
        <div class="nav-children"><div><div class="nav-sublist">
            <a class="nav-leaf focusable {{ request()->routeIs('admin.sms.*') ? 'active' : '' }}" href="{{ route('admin.sms.index') }}">
                <span class="ico" data-ico="message"></span><span>SMS Gateways</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.fraud.*') ? 'active' : '' }}" href="{{ route('admin.fraud.index') }}">
                <span class="ico" data-ico="shield"></span><span>Fraud Checks</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.shipping.*') ? 'active' : '' }}" href="{{ route('admin.shipping.index') }}">
                <span class="ico" data-ico="truck"></span><span>Shipping Zones</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.tax.*') ? 'active' : '' }}" href="{{ route('admin.tax.index') }}">
                <span class="ico" data-ico="receipt"></span><span>Tax Zones</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.payment-gateways.*') ? 'active' : '' }}" href="{{ route('admin.payment-gateways.index') }}">
                <span class="ico" data-ico="card"></span><span>Payment Gateways</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.integrations.*') && !request()->routeIs('admin.ai-settings.*') ? 'active' : '' }}" href="{{ route('admin.integrations.index') }}">
                <span class="ico" data-ico="layers"></span><span>Integrations</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.ai-settings.*') ? 'active' : '' }}" href="{{ route('admin.ai-settings.index') }}">
                <span class="ico" data-ico="spark"></span><span>AI Settings</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.languages.*') ? 'active' : '' }}" href="{{ route('admin.languages.index') }}">
                <span class="ico" data-ico="globe"></span><span>Languages</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.currencies.*') ? 'active' : '' }}" href="{{ route('admin.currencies.index') }}">
                <span class="ico" data-ico="dollar"></span><span>Currencies</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.sitemap.*') ? 'active' : '' }}" href="{{ route('admin.sitemap.index') }}">
                <span class="ico" data-ico="list"></span><span>Sitemap</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.pixel-logs.*') ? 'active' : '' }}" href="{{ route('admin.pixel-logs.index') }}">
                <span class="ico" data-ico="chart"></span><span>Pixel Event Log</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}" href="{{ route('admin.audit.index') }}">
                <span class="ico" data-ico="doc"></span><span>Audit Log</span>
            </a>
            <a class="nav-leaf focusable {{ request()->routeIs('admin.failed-jobs.*') ? 'active' : '' }}" href="{{ route('admin.failed-jobs.index') }}">
                <span class="ico" data-ico="x"></span><span>Failed Jobs</span>
            </a>
        </div></div></div>
    </div>

</nav>

@auth('admin')
<div class="sidebar-user">
    <span class="avatar" style="width:36px;height:36px;background:var(--accent);font-size:12px;font-weight:700;flex-shrink:0">
        {{ strtoupper(substr(auth('admin')->user()->name, 0, 2)) }}
    </span>
    <span class="su-meta grow">
        <b>{{ auth('admin')->user()->name }}</b>
        <span>{{ auth('admin')->user()->email }}</span>
    </span>
    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit" class="su-logout focusable" title="Sign out">
            <span class="ico" data-ico="logout" style="width:18px;height:18px"></span>
        </button>
    </form>
</div>
@endauth

<button class="hamburger focusable" id="hamburger" title="Toggle menu">
    <span class="ico" data-ico="menu"></span>
</button>

<h1>@yield('title', 'Dashboard')</h1>

<div class="topbar-search hide-sm">
    <span class="ico" data-ico="search" style="width:16px;height:16px"></span>
    Search anything…
    <kbd>⌘K</kbd>
</div>

<div class="topbar-actions">
    <a class="visit-btn hide-sm" href="{{ url('/') }}" target="_blank">
        <span class="ico" data-ico="external" style="width:15px;height:15px"></span>
        Visit Store
    </a>

    <button class="icon-btn focusable" id="themeToggle" title="Toggle theme">
        <span class="ico" data-ico="moon" style="width:18px;height:18px"></span>
    </button>

    @auth('admin')
    <div class="menu-wrap">
        <button class="row focusable" data-menu-toggle style="border:none;background:transparent;gap:9px;padding:4px;cursor:pointer">
            <span class="avatar" style="width:36px;height:36px;background:var(--accent);font-size:13px;font-weight:700">
                {{ strtoupper(substr(auth('admin')->user()->name, 0, 2)) }}
            </span>
            <span class="su-meta hide-sm" style="text-align:left;line-height:1.3">
                <b style="color:var(--text);font-size:13.5px;display:block">{{ auth('admin')->user()->name }}</b>
                <span class="faint" style="font-size:11.5px">{{ ucfirst(auth('admin')->user()->role ?? 'Admin') }}</span>
            </span>
        </button>
        <div class="menu" hidden style="min-width:210px">
            <div style="padding:12px 16px;border-bottom:1px solid var(--border)">
                <div style="font-weight:700;font-size:13px;color:var(--text)">{{ auth('admin')->user()->name }}</div>
                <div class="faint" style="font-size:12px;margin-top:2px">{{ auth('admin')->user()->email }}</div>
            </div>
            <a class="menu-item" href="{{ route('admin.profile.edit') }}">
                <span class="ico" data-ico="users" style="width:15px;height:15px"></span>
                Edit Profile
            </a>
            <a class="menu-item" href="{{ route('admin.profile.change-password') }}">
                <span class="ico" data-ico="lock" style="width:15px;height:15px"></span>
                Change Password
            </a>
            <div class="menu-sep"></div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="menu-item danger" style="width:100%;text-align:left">
                    <span class="ico" data-ico="logout" style="width:15px;height:15px"></span>
                    Sign out
                </button>
            </form>
        </div>
    </div>
    @endauth
</div>

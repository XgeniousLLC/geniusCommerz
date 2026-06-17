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
            return \App\Models\Media::find((int) $id)?->getUrl();
        });
        $logoUrl = cache()->remember('site_logo_url', 300, function () {
            $id = \App\Models\SiteSetting::get('general.logo_media_id');
            if (!$id) return null;
            return \App\Models\Media::find((int) $id)?->getUrl();
        });
    @endphp

    <title>Admin Sign In — {{ $siteName }}</title>

    @if($faviconUrl)
        <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}">
    @endif

    <script>(function(){try{var t=JSON.parse(localStorage.getItem('cobalt_theme')||'"light"');document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>

    <link rel="stylesheet" href="{{ asset('admin/css/cobalt.css') }}">

    <style>
        body.auth-body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background:
                radial-gradient(1100px 600px at 12% -10%, color-mix(in srgb,var(--accent) 22%,transparent), transparent 60%),
                radial-gradient(900px 500px at 110% 110%, color-mix(in srgb,var(--pop) 18%,transparent), transparent 55%),
                linear-gradient(135deg, var(--bg-grad-a), var(--bg-grad-b));
        }
        .auth-card {
            width: 100%;
            max-width: 420px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-lg);
            padding: 36px 34px;
        }
        .auth-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            margin-bottom: 28px;
        }
        .auth-logo-tile {
            width: 56px; height: 56px;
            display: grid; place-items: center;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--accent), var(--accent-600));
            color: var(--on-accent);
            font-family: var(--font-display);
            font-weight: 800; font-size: 24px;
            box-shadow: var(--shadow-md);
        }
        .auth-logo-img { max-height: 48px; width: auto; }
        .auth-title { font-family: var(--font-display); font-size: 22px; font-weight: 800; color: var(--text); margin: 0; }
        .auth-sub { font-size: 13px; color: var(--text-muted); margin-top: 4px; }
        .auth-error {
            background: var(--danger-soft);
            border: 1px solid color-mix(in srgb,var(--danger) 35%,transparent);
            color: var(--danger);
            border-radius: var(--radius-ctl);
            padding: 12px 14px; margin-bottom: 18px;
            font-size: 13px;
        }
        .auth-error ul { margin: 0; padding-left: 18px; }
        .auth-foot { text-align: center; margin-top: 22px; font-size: 12px; color: var(--text-faint); }
        .pw-toggle {
            position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
            background: none; border: 0; cursor: pointer; color: var(--text-muted);
            font-size: 12px; font-weight: 600; padding: 4px 6px;
        }
    </style>
</head>
<body class="auth-body">
    <div class="auth-card">
        <div class="auth-brand">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="auth-logo-img">
            @else
                <div class="auth-logo-tile">{{ strtoupper(substr($siteName, 0, 1)) }}</div>
            @endif
            <div style="text-align:center">
                <h1 class="auth-title">Admin Sign In</h1>
                <p class="auth-sub">Access {{ $siteName }} admin panel</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="auth-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" class="col-gap" style="--gap:16px">
            @csrf

            <div class="field">
                <span class="lbl">Email address</span>
                <input class="input" id="email" name="email" type="email" autocomplete="email"
                       value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
            </div>

            <div class="field">
                <span class="lbl">Password</span>
                <div style="position:relative">
                    <input class="input" id="password" name="password" type="password"
                           autocomplete="current-password" placeholder="Enter your password" required
                           style="padding-right:54px">
                    <button type="button" class="pw-toggle"
                            onclick="var p=document.getElementById('password');var s=p.type==='password';p.type=s?'text':'password';this.textContent=s?'Hide':'Show'">Show</button>
                </div>
            </div>

            <label class="row" style="gap:8px;align-items:center;cursor:pointer;font-size:13px;color:var(--text-muted)">
                <input type="checkbox" name="remember" value="1">
                Remember me
            </label>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Sign in</button>
        </form>

        <div class="auth-foot">&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 — Be right back · {{ \App\Models\SiteSetting::get('general.site_name', 'geniusCommerz') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root{--av-ink:#1f1a15;--av-ink-soft:#3a322a;--av-ivory:#f4efe5;--av-paper:#fbf8f1;--av-paper-2:#efe9dc;--av-cognac:#95613a;--av-muted:#756a59;--av-line:rgba(31,26,21,0.14);--av-line-soft:rgba(31,26,21,0.08);--av-display:"Cormorant Garamond",Georgia,serif;--av-sans:"Jost",system-ui,sans-serif;--av-maxw:1280px;--av-gutter:36px}
        @media(max-width:640px){:root{--av-gutter:18px}}
        *{box-sizing:border-box}html,body{margin:0;font-family:var(--av-sans);color:var(--av-ink);background:var(--av-ivory);-webkit-font-smoothing:antialiased}
        body::before{content:"";position:fixed;inset:0;pointer-events:none;opacity:.045;mix-blend-mode:multiply;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");z-index:0}
        .wrap{max-width:var(--av-maxw);margin:0 auto;padding:0 var(--av-gutter);position:relative;z-index:1}
        .eyebrow{font-size:10.5px;font-weight:500;letter-spacing:.34em;text-transform:uppercase;color:var(--av-cognac);font-family:var(--av-sans)}
        .btn-primary{display:inline-flex;align-items:center;justify-content:center;height:50px;padding:0 28px;background:var(--av-ink);color:var(--av-paper);border:1px solid var(--av-ink);font-family:var(--av-sans);font-size:11.5px;font-weight:500;letter-spacing:.2em;text-transform:uppercase;text-decoration:none;border-radius:2px;cursor:pointer}
        .btn-ghost{display:inline-flex;align-items:center;justify-content:center;height:50px;padding:0 28px;background:transparent;color:var(--av-ink);border:1px solid var(--av-line);font-family:var(--av-sans);font-size:11.5px;font-weight:500;letter-spacing:.18em;text-transform:uppercase;text-decoration:none;border-radius:2px;cursor:pointer}
    </style>
</head>
<body>
    @php $siteName = \App\Models\SiteSetting::get('general.site_name', 'geniusCommerz'); @endphp
    <div style="background:var(--av-paper);border-bottom:1px solid var(--av-line-soft);position:relative;z-index:1">
        <div class="wrap" style="height:64px;display:flex;align-items:center;justify-content:space-between">
            <a href="/" style="text-decoration:none"><span style="font-family:var(--av-display);font-weight:500;font-size:19px;letter-spacing:.26em;color:var(--av-ink)">{{ strtoupper($siteName) }}</span></a>
            <span style="font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:var(--av-muted);font-weight:500">Maintenance</span>
        </div>
    </div>
    <div style="position:relative;overflow:hidden;padding:clamp(48px,8vw,80px) 0;text-align:center;z-index:1">
        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;overflow:hidden">
            <span style="font-family:var(--av-display);font-size:clamp(160px,32vw,420px);font-weight:600;color:rgba(31,26,21,.035);letter-spacing:-.04em;user-select:none;white-space:nowrap;font-style:italic;line-height:1">503</span>
        </div>
        <div class="wrap" style="position:relative">
            <div class="eyebrow" style="margin-bottom:14px">Under maintenance</div>
            <h1 style="font-family:var(--av-display);font-size:clamp(30px,4.5vw,48px);font-weight:400;letter-spacing:-.012em;line-height:1.04;margin:0 0 14px;color:var(--av-ink)">We'll be right back.</h1>
            <p style="color:var(--av-muted);font-size:14.5px;line-height:1.7;margin:0 auto 28px;max-width:520px">
                The store is undergoing scheduled maintenance. We'll be back online shortly. Thank you for your patience.
            </p>
            @if(!empty($exception) && $exception->getMessage())
                <p style="margin:0 auto 18px;max-width:520px;font-size:12px;color:var(--av-muted);font-family:ui-monospace,monospace">{{ $exception->getMessage() }}</p>
            @endif
            <div style="display:flex;gap:12;justify-content:center;flex-wrap:wrap">
                <a href="/" class="btn-primary">Back to home</a>
                <a href="mailto:{{ \App\Models\SiteSetting::get('general.admin_email', 'admin@geniuscommerz.com') }}" class="btn-ghost">Contact support</a>
            </div>
            <p style="margin:20px 0 0;font-size:12px;color:var(--av-muted)">Ref: {{ request()->path() }} · {{ now()->format('H:i') }}</p>
        </div>
    </div>
    <footer style="background:var(--av-ink);color:var(--av-paper);position:relative;z-index:1">
        <div class="wrap" style="padding:28px var(--av-gutter);display:flex;justify-content:space-between;flex-wrap:wrap;gap:12;font-size:12px;color:rgba(244,239,229,.42);letter-spacing:.04em;border-top:1px solid rgba(244,239,229,.1)">
            <span>© {{ date('Y') }} {{ $siteName }}. All rights reserved.</span>
            <span>We accept: bKash · Nagad · Rocket · SSLCOMMERZ · Visa · Mastercard · COD</span>
        </div>
    </footer>
</body>
</html>

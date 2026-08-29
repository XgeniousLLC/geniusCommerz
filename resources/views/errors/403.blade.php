<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Access denied · {{ \App\Models\SiteSetting::get('general.site_name', 'geniusCommerz') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root{
            --av-ink:#1f1a15; --av-ink-soft:#3a322a; --av-ivory:#f4efe5; --av-paper:#fbf8f1; --av-paper-2:#efe9dc;
            --av-cognac:#95613a; --av-cognac-deep:#6f4527; --av-gold:#b2904f; --av-muted:#756a59;
            --av-line:rgba(31,26,21,0.14); --av-line-soft:rgba(31,26,21,0.08);
            --av-display:"Cormorant Garamond", Georgia, serif; --av-sans:"Jost", system-ui, sans-serif;
            --av-maxw:1280px; --av-gutter:36px;
        }
        @media(max-width:640px){:root{--av-gutter:18px}}
        *{box-sizing:border-box}
        html,body{margin:0;font-family:var(--av-sans);color:var(--av-ink);background:var(--av-ivory);-webkit-font-smoothing:antialiased}
        ::selection{background:var(--av-cognac);color:#fff}
        body::before{content:"";position:fixed;inset:0;pointer-events:none;opacity:.045;mix-blend-mode:multiply;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");z-index:0}
        .wrap{max-width:var(--av-maxw);margin:0 auto;padding:0 var(--av-gutter);position:relative;z-index:1}
        .eyebrow{font-size:10.5px;font-weight:500;letter-spacing:.34em;text-transform:uppercase;color:var(--av-cognac);font-family:var(--av-sans)}
        .btn-primary{display:inline-flex;align-items:center;justify-content:center;height:50px;padding:0 28px;background:var(--av-ink);color:var(--av-paper);border:1px solid var(--av-ink);font-family:var(--av-sans);font-size:11.5px;font-weight:500;letter-spacing:.2em;text-transform:uppercase;text-decoration:none;border-radius:2px;cursor:pointer;transition:background .2s}
        .btn-primary:hover{background:var(--av-ink-soft);border-color:var(--av-ink-soft)}
        .btn-ghost{display:inline-flex;align-items:center;justify-content:center;height:50px;padding:0 28px;background:transparent;color:var(--av-ink);border:1px solid var(--av-line);font-family:var(--av-sans);font-size:11.5px;font-weight:500;letter-spacing:.18em;text-transform:uppercase;text-decoration:none;border-radius:2px;cursor:pointer;transition:border-color .2s,background .2s}
        .btn-ghost:hover{border-color:var(--av-ink);background:var(--av-paper)}
    </style>
</head>
<body>
    @php $siteName = \App\Models\SiteSetting::get('general.site_name', 'geniusCommerz'); @endphp

    @php $announce = \App\Models\SiteSetting::get('general.announce_bar', ''); @endphp
    @if($announce)
        <div style="background:var(--av-ink);color:var(--av-paper);height:38px;display:flex;align-items:center;justify-content:center;position:relative;z-index:1">
            <span style="font-size:10.5px;letter-spacing:.26em;text-transform:uppercase;opacity:.86">{{ $announce }}</span>
        </div>
    @endif
    <div style="background:var(--av-paper);border-bottom:1px solid var(--av-line-soft);position:relative;z-index:1">
        <div class="wrap" style="height:64px;display:flex;align-items:center;justify-content:space-between">
            <a href="/" style="text-decoration:none;display:inline-flex;align-items:center;gap:10">
                @php $logoId = \App\Models\SiteSetting::get('general.logo_media_id'); $logoUrl = $logoId ? \App\Models\Media::find((int)$logoId)?->getUrl('thumb') : null; @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" style="height:36px;object-fit:contain;display:block">
                @else
                    <span style="font-family:var(--av-display);font-weight:500;font-size:19px;letter-spacing:.26em;color:var(--av-ink)">{{ strtoupper($siteName) }}</span>
                @endif
            </a>
            <a href="/shop" style="font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:var(--av-muted);text-decoration:none;font-weight:500">Shop →</a>
        </div>
    </div>

    <div style="position:relative;overflow:hidden;padding:clamp(48px,8vw,80px) 0 clamp(32px,6vw,48px);text-align:center;z-index:1">
        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;overflow:hidden">
            <span style="font-family:var(--av-display);font-size:clamp(160px,32vw,420px);font-weight:600;color:rgba(31,26,21,.035);letter-spacing:-.04em;user-select:none;white-space:nowrap;font-style:italic;line-height:1">403</span>
        </div>
        <div class="wrap" style="position:relative">
            <div style="width:56px;height:56px;border:1px solid var(--av-line);background:var(--av-paper);display:grid;place-items:center;margin:0 auto 18px;color:var(--av-cognac)">
                <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><circle cx="12" cy="16" r="1"/></svg>
            </div>
            <div class="eyebrow" style="margin-bottom:14px">Access denied</div>
            <h1 style="font-family:var(--av-display);font-size:clamp(30px,4.5vw,48px);font-weight:400;letter-spacing:-.012em;line-height:1.04;margin:0 0 14px;color:var(--av-ink)">You don't have access.</h1>
            <p style="color:var(--av-muted);font-size:14.5px;line-height:1.7;margin:0 auto 28px;max-width:520px">
                This page is restricted. If you were signed in, you may not have permission — or your session may have expired.
                @if(!auth()->check()) <span style="color:var(--av-ink)">Sign in</span> to continue. @endif
            </p>

            <div style="display:flex;gap:12;justify-content:center;flex-wrap:wrap">
                @if(auth()->check())
                    <a href="/" class="btn-primary">Back to home</a>
                    <a href="/shop" class="btn-ghost">Browse collection</a>
                @else
                    <a href="/login" class="btn-primary">Sign in</a>
                    <a href="/" class="btn-ghost">Back to home</a>
                @endif
            </div>

            <p style="margin:28px 0 0;font-size:13px;color:var(--av-muted);line-height:1.6">
                Need access? <a href="/page/contact" style="color:var(--av-ink);text-decoration:underline;text-decoration-color:var(--av-line)">Contact us</a>
                @if(request()->url()) <span style="margin:0 6px;color:var(--av-line)">·</span> <span style="font-size:11px;letter-spacing:.04em">Ref: {{ parse_url(request()->url(), PHP_URL_PATH) }}</span> @endif
            </p>

            @if($exception && $exception->getMessage() && app()->environment('local'))
                <div style="margin:24px auto 0;max-width:560px;text-align:left;padding:14px 16px;background:var(--av-paper);border:1px solid var(--av-line-soft);font-size:12px;color:var(--av-muted);font-family:ui-monospace,monospace;word-break:break-word">
                    {{ $exception->getMessage() }}
                </div>
            @endif
        </div>
    </div>

    <footer style="background:var(--av-ink);color:var(--av-paper);margin-top:clamp(32px,6vw,48px);position:relative;z-index:1">
        <div class="wrap" style="padding:28px var(--av-gutter);display:flex;justify-content:space-between;flex-wrap:wrap;gap:12;font-size:12px;color:rgba(244,239,229,.42);letter-spacing:.04em;border-top:1px solid rgba(244,239,229,.1)">
            <span>© {{ date('Y') }} {{ $siteName }}. All rights reserved.</span>
            <span style="display:flex;gap:12;flex-wrap:wrap"><span>We accept:</span> <span style="letter-spacing:.06em;opacity:.7">bKash · Nagad · Rocket · SSLCOMMERZ · Visa · Mastercard · COD</span></span>
        </div>
    </footer>
</body>
</html>

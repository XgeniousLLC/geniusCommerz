@extends('admin.layouts.admin')
@section('title', 'Sitemap')
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Sitemap</h2>
        <div class="sub">Generate <code style="background:var(--surface-2);padding:1px 6px;border-radius:5px;font-family:monospace;font-size:12px">sitemap.xml</code> for all active products, categories, blog posts, and pages</div>
    </div>
    <form method="POST" action="{{ route('admin.sitemap.generate') }}">
        @csrf
        <button type="submit" class="btn btn-primary">
            <span class="ico" data-ico="refresh" style="width:18px;height:18px"></span>Generate Sitemap
        </button>
    </form>
</div>

<div class="card pad">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-info"><span class="ico" data-ico="list" style="width:18px;height:18px"></span></span>
        <div class="ct">
            <h3>Status</h3>
            <div class="sub">Current sitemap state</div>
        </div>
    </div>

    @if($exists)
    <div class="row" style="gap:12px;padding:14px 18px;border-radius:13px;background:var(--accent-soft);border:1px solid var(--border);margin-bottom:20px">
        <span class="tile sm t-accent"><span class="ico" data-ico="check" style="width:18px;height:18px"></span></span>
        <div>
            <div style="font-size:13.5px;font-weight:600">Sitemap exists</div>
            <div class="faint" style="font-size:12.5px;margin-top:2px">
                Last generated: <strong>{{ $lastUpdate }}</strong> ·
                <a href="{{ url('sitemap.xml') }}" target="_blank" class="link-btn" style="font-size:12.5px">/sitemap.xml</a>
            </div>
        </div>
    </div>
    @else
    <div class="row" style="gap:12px;padding:14px 18px;border-radius:13px;background:color-mix(in srgb,var(--warning) 10%,transparent);border:1px solid color-mix(in srgb,var(--warning) 30%,transparent);margin-bottom:20px">
        <span class="tile sm" style="background:var(--warning);color:#fff"><span class="ico" data-ico="x" style="width:18px;height:18px"></span></span>
        <div style="font-size:13.5px;font-weight:600;color:var(--text)">No sitemap found. Click Generate to create one.</div>
    </div>
    @endif

    <div style="padding-top:16px;border-top:1px solid var(--border)">
        <div style="font-weight:700;font-size:13.5px;margin-bottom:10px">What's included</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px">
            @foreach(['Active products','Root categories','Published blog posts','Published pages','Key static URLs'] as $item)
            <div class="row" style="gap:8px;padding:10px 12px;border-radius:10px;background:var(--surface-2);border:1px solid var(--border)">
                <span class="ico" data-ico="check" style="width:14px;height:14px;color:var(--success);flex-shrink:0"></span>
                <span style="font-size:13px;font-weight:600">{{ $item }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div style="padding-top:16px;margin-top:16px;border-top:1px solid var(--border)">
        <div style="font-weight:700;font-size:13.5px;margin-bottom:8px">Submit to search engines</div>
        <div class="row" style="gap:14px;flex-wrap:wrap">
            <a href="https://search.google.com/search-console" target="_blank" rel="noopener" class="link-btn" style="font-size:13px">
                <span class="ico" data-ico="external" style="width:13px;height:13px"></span>Google Search Console
            </a>
            <a href="https://www.bing.com/webmasters" target="_blank" rel="noopener" class="link-btn" style="font-size:13px">
                <span class="ico" data-ico="external" style="width:13px;height:13px"></span>Bing Webmaster Tools
            </a>
        </div>
        <p class="faint" style="font-size:12px;margin-top:10px">
            Sitemap URL: <code style="background:var(--surface-2);padding:1px 7px;border-radius:5px;font-family:monospace">{{ url('sitemap.xml') }}</code>
        </p>
    </div>
</div>

@endsection

@props(['type' => 'google', 'title' => '', 'description' => '', 'url' => '', 'image' => ''])

<div class="card pad">
    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);margin-bottom:12px">
        {{ ucfirst($type) }} Preview
    </div>

    @if($type === 'google')
        <div class="stack" style="gap:4px">
            <div class="row" style="gap:6px;font-size:13px">
                <span style="color:var(--success)">{{ parse_url($url ?: 'https://example.com', PHP_URL_HOST) ?: 'example.com' }}</span>
                <span class="faint">›</span>
                <span class="faint">{{ Str::limit(parse_url($url ?: 'https://example.com/slug', PHP_URL_PATH) ?: '/slug', 30) }}</span>
            </div>
            <div style="font-size:18px;color:var(--accent);font-weight:500;line-height:1.3">
                {{ $title ?: 'Page Title — Site Name' }}
            </div>
            <div class="muted" style="font-size:13.5px;line-height:1.5">
                {{ $description ?: 'Meta description will appear here in search results.' }}
            </div>
        </div>

    @elseif($type === 'facebook')
        <div style="border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;max-width:400px">
            @if($image)
                <img src="{{ $image }}" alt="" style="width:100%;height:160px;object-fit:cover">
            @else
                <div style="width:100%;height:160px;background:var(--surface-3);display:flex;align-items:center;justify-content:center">
                    <span class="ico faint" data-ico="image" style="width:40px;height:40px"></span>
                </div>
            @endif
            <div style="padding:10px 12px;background:var(--surface-2)">
                <div class="faint" style="font-size:11px;text-transform:uppercase;margin-bottom:3px">{{ strtoupper(parse_url($url ?: 'https://example.com', PHP_URL_HOST) ?: 'EXAMPLE.COM') }}</div>
                <div style="font-weight:700;font-size:13px;margin-bottom:3px">{{ $title ?: 'Page Title' }}</div>
                <div class="faint" style="font-size:12px">{{ Str::limit($description ?: 'Open Graph description.', 100) }}</div>
            </div>
        </div>

    @elseif($type === 'twitter')
        <div style="border:1px solid var(--border);border-radius:12px;overflow:hidden;max-width:400px">
            @if($image)
                <img src="{{ $image }}" alt="" style="width:100%;height:192px;object-fit:cover">
            @else
                <div style="width:100%;height:192px;background:var(--surface-3);display:flex;align-items:center;justify-content:center">
                    <span class="ico faint" data-ico="image" style="width:40px;height:40px"></span>
                </div>
            @endif
            <div style="padding:10px 12px">
                <div style="font-weight:700;font-size:13.5px;margin-bottom:3px">{{ $title ?: 'Page Title' }}</div>
                <div class="muted" style="font-size:13px;margin-bottom:6px">{{ Str::limit($description ?: 'Twitter card description.', 120) }}</div>
                <div class="faint" style="font-size:12px">{{ parse_url($url ?: 'https://example.com', PHP_URL_HOST) ?: 'example.com' }}</div>
            </div>
        </div>
    @endif
</div>

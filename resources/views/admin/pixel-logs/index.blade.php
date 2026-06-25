@extends('admin.layouts.admin')

@section('title', 'Pixel Event Log')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Pixel Event Log</h2>
        <div class="sub">Meta CAPI · TikTok Events API · GA4 Measurement Protocol — logged per order</div>
    </div>
    <form method="POST" action="{{ route('admin.pixel-logs.destroy') }}" onsubmit="return confirm('Clear all pixel event logs?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger">Clear log</button>
    </form>
</div>

{{-- Stats --}}
<div class="stat-grid" style="margin-bottom:24px">
    <div class="stat-card">
        <div class="stat-label">Total events</div>
        <div class="stat-val">{{ $stats['total'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Failed</div>
        <div class="stat-val" style="color:var(--danger)">{{ $stats['failed'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Meta CAPI</div>
        <div class="stat-val">{{ $stats['meta'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">TikTok</div>
        <div class="stat-val">{{ $stats['tiktok'] }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">GA4</div>
        <div class="stat-val">{{ $stats['ga4'] }}</div>
    </div>
</div>

{{-- Filters --}}
<div class="card pad" style="margin-bottom:16px">
    <form method="GET" class="row" style="gap:10px;flex-wrap:wrap;align-items:flex-end">
        <div class="field" style="margin:0;min-width:140px">
            <span class="lbl" style="font-size:12px">Platform</span>
            <select class="input" name="platform" onchange="this.form.submit()">
                <option value="">All platforms</option>
                <option value="meta"   {{ request('platform')==='meta'   ? 'selected':'' }}>Meta CAPI</option>
                <option value="tiktok" {{ request('platform')==='tiktok' ? 'selected':'' }}>TikTok</option>
                <option value="ga4"    {{ request('platform')==='ga4'    ? 'selected':'' }}>GA4</option>
            </select>
        </div>
        <div class="field" style="margin:0;min-width:140px">
            <span class="lbl" style="font-size:12px">Status</span>
            <select class="input" name="status" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="success" {{ request('status')==='success' ? 'selected':'' }}>Success</option>
                <option value="failed"  {{ request('status')==='failed'  ? 'selected':'' }}>Failed</option>
            </select>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card flush">
    @if(count($logs) === 0)
    <div style="padding:32px;text-align:center">
        <p class="faint">No pixel events logged yet. Events are recorded when orders are placed.</p>
    </div>
    @else
    <table class="table">
        <thead>
            <tr>
                <th>Time</th>
                <th>Platform</th>
                <th>Event</th>
                <th>Order</th>
                <th>Status</th>
                <th>HTTP</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $e)
            @php
                $success = $e['success'] ?? false;
                $ts      = isset($e['ts']) ? \Carbon\Carbon::parse($e['ts'])->format('M d, H:i:s') : '—';
                $platform= $e['platform'] ?? '—';
                $pillColor = match($platform) {
                    'meta'   => 't-info',
                    'tiktok' => 't-violet',
                    'ga4'    => 't-pop',
                    default  => 'muted',
                };
            @endphp
            <tr>
                <td class="mono faint" style="font-size:12px;white-space:nowrap">{{ $ts }}</td>
                <td>
                    <span class="pill sm {{ $pillColor }}">{{ strtoupper($platform) }}</span>
                </td>
                <td style="font-size:13px;font-family:monospace">{{ $e['event'] ?? '—' }}</td>
                <td style="font-size:13px">
                    @if(!empty($e['order_number']))
                        <a href="{{ route('admin.orders.show', $e['order_id'] ?? 0) }}" class="link-btn" style="font-size:13px">
                            {{ $e['order_number'] }}
                        </a>
                    @else
                        <span class="faint">—</span>
                    @endif
                </td>
                <td>
                    @if($success)
                        <span class="pill sm success">OK</span>
                    @else
                        <span class="pill sm danger">Failed</span>
                    @endif
                </td>
                <td class="mono faint" style="font-size:12px">{{ $e['http_status'] ?? '—' }}</td>
                <td style="max-width:280px">
                    @if(!empty($e['error']))
                        <span style="font-size:12px;color:var(--danger)" title="{{ $e['error'] }}">
                            {{ \Illuminate\Support\Str::limit($e['error'], 80) }}
                        </span>
                    @elseif(!empty($e['response']))
                        <span x-data="{open:false}" style="font-size:12px">
                            <button @click="open=!open" class="link-btn" style="font-size:12px">
                                <span x-text="open?'Hide':'Show'"></span> response
                            </button>
                            <template x-if="open">
                                <pre style="font-size:11px;background:var(--surface-2);padding:6px 8px;border-radius:6px;margin-top:4px;white-space:pre-wrap;word-break:break-all;max-height:120px;overflow:auto">{{ $e['response'] }}</pre>
                            </template>
                        </span>
                    @else
                        <span class="faint" style="font-size:12px">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if(count($logs) === 200)
    <div style="padding:10px 16px;border-top:1px solid var(--border)">
        <span class="faint" style="font-size:12px">Showing latest 200 entries. Clear log to reset.</span>
    </div>
    @endif
    @endif
</div>

@endsection

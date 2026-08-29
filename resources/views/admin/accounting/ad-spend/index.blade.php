@extends('admin.layouts.admin')

@section('title', 'Ad Spend')

@section('content')
@php
$platformMeta = [
    'meta'    => ['label' => 'Meta Ads',     'icon' => 'users',  'color' => 't-violet'],
    'google'  => ['label' => 'Google Ads',   'icon' => 'globe',  'color' => 't-info'],
    'tiktok'  => ['label' => 'TikTok Ads',   'icon' => 'bolt',   'color' => 't-pop'],
    'youtube' => ['label' => 'YouTube Ads',  'icon' => 'play',   'color' => 't-danger'],
    'other'   => ['label' => 'Other',        'icon' => 'chart',  'color' => 'muted'],
];
@endphp

<div class="page-head">
    <div>
        <h2 class="display">Ad Spend</h2>
        <div class="sub">Marketing spend across every channel</div>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('addSpendModal').style.display='flex'">
        <span class="ico" data-ico="plus" style="width:18px;height:18px"></span>Record Spend
    </button>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr))">
    <div class="card lift stat">
        <span class="tile t-warning"><span class="ico" data-ico="wallet"></span></span>
        <div><div class="num">{{ money($monthTotal, 0) }}</div><div class="lbl">This Month</div></div>
    </div>
    <div class="card lift stat">
        <span class="tile t-info"><span class="ico" data-ico="chart"></span></span>
        <div><div class="num">{{ money($allTotal, 0) }}</div><div class="lbl">All Time</div></div>
    </div>
    @foreach($byPlatform as $platform => $total)
    @php $pm = $platformMeta[$platform] ?? $platformMeta['other']; @endphp
    <div class="card lift stat">
        <span class="tile {{ $pm['color'] }}"><span class="ico" data-ico="{{ $pm['icon'] }}"></span></span>
        <div><div class="num">{{ money($total, 0) }}</div><div class="lbl">{{ $pm['label'] }}</div></div>
    </div>
    @endforeach
</div>

<div class="card flush">
    <div class="between" style="padding:20px 22px 14px;flex-wrap:wrap;gap:12px">
        <div class="card-head" style="margin:0">
            <span class="tile sm t-pop"><span class="ico" data-ico="bolt" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Records</h3><div class="sub">All ad spend entries</div></div>
        </div>
    </div>
    <div class="table-scroll">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Platform</th>
                    <th>Campaign</th>
                    <th>Product</th>
                    <th style="text-align:right">Amount</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($spends as $s)
                @php $pm = $platformMeta[$s->platform] ?? $platformMeta['other']; @endphp
                <tr>
                    <td style="font-size:13px;font-weight:600">{{ $s->spend_date->format('d M Y') }}</td>
                    <td>
                        <div class="row" style="gap:8px">
                            <span class="tile" style="width:22px;height:22px;border-radius:7px" data-color="{{ $s->platform }}">
                                <span class="ico" data-ico="{{ $pm['icon'] }}" style="width:13px;height:13px"></span>
                            </span>
                            <span style="font-weight:600;font-size:13px">{{ $pm['label'] }}</span>
                        </div>
                    </td>
                    <td class="muted" style="font-size:13px">{{ $s->campaign_name ?? '—' }}</td>
                    <td class="faint" style="font-size:13px">{{ $s->product?->name ?? 'All' }}</td>
                    <td style="text-align:right;font-weight:700;font-size:14px;color:var(--warning)">
                        {{ money($s->amount, 0) }}
                    </td>
                    <td style="text-align:right">
                        <form method="POST" action="{{ route('admin.accounting.ad-spend.destroy', $s) }}"
                              onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="icon-btn">
                                <span class="ico" data-ico="trash" style="width:16px;height:16px"></span>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:48px 20px">
                        <div class="faint" style="font-size:13.5px">No ad spend records yet.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($spends->hasPages())
    <div style="padding:14px 20px;border-top:1px solid var(--border)">{{ $spends->links() }}</div>
    @endif
</div>

{{-- Add Spend Modal --}}
<div id="addSpendModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.45);align-items:center;justify-content:center;padding:24px">
    <div class="card pad" style="width:100%;max-width:480px;position:relative">
        <div class="card-head" style="margin-bottom:20px">
            <span class="tile sm t-pop"><span class="ico" data-ico="bolt" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Record Ad Spend</h3></div>
            <button class="icon-btn" style="margin-left:auto" onclick="document.getElementById('addSpendModal').style.display='none'">
                <span class="ico" data-ico="x" style="width:18px;height:18px"></span>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.accounting.ad-spend.store') }}" class="col-gap" style="--gap:14px">
            @csrf
            <div class="field">
                <span class="lbl">Platform <span style="color:var(--danger)">*</span></span>
                <select class="input" name="platform" required>
                    @foreach(\App\Models\AdSpend::$platforms as $key => $label)
                    <option value="{{ $key }}" {{ old('platform') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <span class="lbl">Campaign Name</span>
                <input class="input" type="text" name="campaign_name" value="{{ old('campaign_name') }}" placeholder="Optional">
            </div>
            <div class="row" style="gap:12px">
                <div class="field grow">
                    <span class="lbl">Amount ({{ currency_symbol() }}) <span style="color:var(--danger)">*</span></span>
                    <input class="input" type="number" name="amount" value="{{ old('amount') }}" min="0" step="0.01" required>
                </div>
                <div class="field grow">
                    <span class="lbl">Date <span style="color:var(--danger)">*</span></span>
                    <input class="input" type="date" name="spend_date" value="{{ old('spend_date', date('Y-m-d')) }}" required>
                </div>
            </div>
            <div class="field">
                <span class="lbl">Product (optional)</span>
                <select class="input" name="product_id">
                    <option value="">All products</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <span class="lbl">Notes</span>
                <textarea class="input" name="notes" rows="2">{{ old('notes') }}</textarea>
            </div>
            <div class="row" style="gap:10px;padding-top:4px">
                <button type="submit" class="btn btn-primary">Save Record</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('addSpendModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
<script>document.getElementById('addSpendModal').style.display='flex';</script>
@endif

@endsection

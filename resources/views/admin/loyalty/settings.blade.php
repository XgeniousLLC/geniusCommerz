@extends('admin.layouts.admin')

@section('title', 'Loyalty Program')

@section('content')

<form method="POST" action="{{ route('admin.loyalty.update') }}"
      x-data="{ tiers: {{ json_encode($settings['tiers']) }} }">
@csrf

<div class="page-head">
    <div>
        <h2 class="display">Loyalty Program</h2>
        <div class="sub">Configure points earning, redemption, and tiers</div>
    </div>
    <div class="row" style="gap:16px">
        @if($settings['enabled'])
        <span class="pill success"><span class="dot"></span>Active</span>
        @else
        <span class="pill"><span class="dot"></span>Disabled</span>
        @endif
        <a class="link-btn" href="{{ route('admin.loyalty.index') }}">View transactions <span class="ico" data-ico="arrowRight" style="width:15px;height:15px"></span></a>
    </div>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:20px;align-items:start" class="loy-grid grid-2">

<div class="col-gap">

    {{-- Enable toggle --}}
    <div class="card pad row" style="gap:16px">
        <span class="tile" style="background:var(--surface-3);color:var(--text-faint)">
            <span class="ico" data-ico="star" style="width:23px;height:23px"></span>
        </span>
        <div class="grow">
            <div style="font-weight:700;font-size:16px">Enable Loyalty Program</div>
            <div class="faint" style="font-size:13px;margin-top:2px">Customers earn points when they place orders</div>
        </div>
        <input type="hidden" name="enabled" value="0">
        <label x-data="{ on: {{ $settings['enabled'] ? 'true' : 'false' }} }"
               @click="on = !on" role="button">
            <input type="checkbox" name="enabled" value="1" :checked="on" style="display:none">
            <span :class="on ? 'toggle on' : 'toggle'"><span class="knob"></span></span>
        </label>
    </div>

    {{-- Points rules --}}
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-accent"><span class="ico" data-ico="spark" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Points Rules</h3><div class="sub">How points are earned and redeemed</div></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px" class="grid-2">
            <div class="field">
                <span class="lbl">Points earned per ৳1 spent</span>
                <div class="input-prefix">
                    <input class="input" type="number" name="points_per_taka" value="{{ $settings['points_per_taka'] }}" step="0.01" min="0" required>
                    <span style="left:auto;right:13px">pts / ৳1</span>
                </div>
                <span class="hint">e.g. 0.1 = 1 point per ৳10</span>
            </div>
            <div class="field">
                <span class="lbl">Taka value per point</span>
                <div class="input-prefix">
                    <span>৳</span>
                    <input class="input" type="number" name="taka_per_point" value="{{ $settings['taka_per_point'] }}" step="0.01" min="0" required style="padding-left:28px">
                </div>
                <span class="hint">e.g. 0.5 = 100 pts = ৳50</span>
            </div>
            <div class="field">
                <span class="lbl">Minimum points to redeem</span>
                <input class="input" type="number" name="min_redemption" value="{{ $settings['min_redemption'] }}" min="1" required>
            </div>
            <div class="field">
                <span class="lbl">Max % of order payable by points</span>
                <div class="input-prefix">
                    <input class="input" type="number" name="max_redemption_pct" value="{{ $settings['max_redemption_pct'] }}" min="1" max="100" required>
                    <span style="left:auto;right:13px">%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Tiers --}}
    <div class="card pad">
        <div class="card-head">
            <span class="tile sm t-violet"><span class="ico" data-ico="star" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Membership Tiers</h3><div class="sub">Reward higher spenders with bonus points</div></div>
            <button type="button" class="link-btn head-action"
                @click="tiers.push({name:'New Tier', min:0, max:null, bonus_pct:0})">
                <span class="ico" data-ico="plus" style="width:15px;height:15px"></span>Add tier
            </button>
        </div>

        <div style="display:grid;grid-template-columns:1.4fr 1fr 1fr .9fr 34px;gap:10px;padding:0 2px;margin-bottom:10px">
            <div class="section-label">Name</div>
            <div class="section-label">Min points</div>
            <div class="section-label">Max points</div>
            <div class="section-label">Bonus %</div>
            <div></div>
        </div>

        <template x-for="(tier, i) in tiers" :key="i">
            <div style="display:grid;grid-template-columns:1.4fr 1fr 1fr .9fr 34px;gap:10px;align-items:center;padding:12px;border-radius:12px;background:var(--surface-2);border:1px solid var(--border);margin-bottom:10px">
                <div class="row" style="gap:8px">
                    <span style="width:9px;height:9px;border-radius:99px;background:var(--text-faint);flex-shrink:0"></span>
                    <input class="input" style="padding:8px 11px;font-size:13px" :name="`tiers[${i}][name]`" x-model="tier.name" required>
                </div>
                <input class="input" style="padding:8px 11px;font-size:13px" type="number" :name="`tiers[${i}][min]`" x-model="tier.min" min="0" required>
                <input class="input" style="padding:8px 11px;font-size:13px" type="number" :name="`tiers[${i}][max]`" x-model="tier.max" min="0" placeholder="∞">
                <div class="input-prefix">
                    <input class="input" style="padding:8px 20px 8px 11px;font-size:13px" type="number" :name="`tiers[${i}][bonus_pct]`" x-model="tier.bonus_pct" min="0" max="100" required>
                    <span style="left:auto;right:9px">%</span>
                </div>
                <button type="button" class="icon-btn danger" style="width:34px;height:34px" @click="tiers.splice(i, 1)">
                    <span class="ico" data-ico="x" style="width:16px;height:16px"></span>
                </button>
            </div>
        </template>

        <p class="hint" style="margin-top:4px">Bonus % adds extra points per order for customers in that tier.</p>
    </div>

</div>{{-- /left col --}}

<div class="col-gap loy-side" style="position:sticky;top:86px">

    <div class="card pad">
        <div class="row" style="gap:9px;margin-bottom:16px">
            <span class="ico" data-ico="eye" style="width:17px;height:17px;color:var(--accent)"></span>
            <h3 class="display" style="font-size:15.5px;font-weight:700">Quick Preview</h3>
        </div>
        <div class="between" style="padding:11px 0"><span class="muted" style="font-size:13px">Order ৳1,000</span><span class="tnum" style="font-weight:700;font-size:13.5px">{{ round($settings['points_per_taka'] * 1000) }} pts</span></div>
        <div class="between" style="padding:11px 0;border-top:1px solid var(--border)"><span class="muted" style="font-size:13px">100 points</span><span class="tnum" style="font-weight:700;font-size:13.5px">৳{{ round($settings['taka_per_point'] * 100) }}</span></div>
        <div class="between" style="padding:11px 0;border-top:1px solid var(--border)"><span class="muted" style="font-size:13px">Min to redeem</span><span class="tnum" style="font-weight:700;font-size:13.5px">{{ $settings['min_redemption'] }} pts</span></div>
        <div class="between" style="padding:11px 0;border-top:1px solid var(--border)"><span class="muted" style="font-size:13px">Max per order</span><span class="tnum" style="font-weight:700;font-size:13.5px">{{ $settings['max_redemption_pct'] }}% of total</span></div>
    </div>

    <div class="card pad">
        <div class="row" style="gap:9px;margin-bottom:16px">
            <span class="ico" data-ico="star" style="width:17px;height:17px;color:var(--accent)"></span>
            <h3 class="display" style="font-size:15.5px;font-weight:700">Current Tiers</h3>
        </div>
        <div class="stack" style="gap:11px">
            @foreach($settings['tiers'] as $tier)
            @php
            $tc = match($tier['name']) {
                'Gold'     => 'warning',
                'Platinum' => 'violet',
                default    => '',
            };
            @endphp
            <div class="row" style="gap:10px">
                <span class="pill sm {{ $tc }}">{{ $tier['name'] }}</span>
                <span class="faint tnum" style="font-size:12.5px;font-weight:600;margin-left:auto;white-space:nowrap">
                    {{ number_format($tier['min']) }}{{ $tier['max'] ? '–' . number_format($tier['max']) : '+' }} pts
                </span>
                @if($tier['bonus_pct'] > 0)
                <span class="tnum" style="font-size:12.5px;font-weight:700;color:var(--success)">+{{ $tier['bonus_pct'] }}%</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <button type="submit" class="btn btn-primary" style="width:100%;padding:13px;justify-content:center">
        <span class="ico" data-ico="check" style="width:18px;height:18px"></span>Save settings
    </button>

</div>{{-- /right col --}}
</div>
</form>

@endsection

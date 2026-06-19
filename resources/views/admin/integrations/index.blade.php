@extends('admin.layouts.admin')

@section('title', 'Integrations')

@section('content')
<style>
.intg-card{padding:18px;display:flex;flex-direction:column;gap:13px}
.intg-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px}
</style>

@php
$groups = [
    'AI Providers' => [
        'providers' => ['openai', 'gemini', 'claude', 'deepseek'],
        'icon' => 'spark', 'color' => 't-violet',
        'hint' => 'Only one AI provider can be the default',
    ],
    'Payments' => [
        'providers' => ['bkash', 'nagad', 'sslcommerz'],
        'icon' => 'card', 'color' => 't-teal',
        'hint' => null,
    ],
    'Couriers' => [
        'providers' => ['pathao', 'steadfast', 'redx'],
        'icon' => 'truck', 'color' => 't-warning',
        'hint' => 'Only one courier can be the default',
    ],
    'SMS Gateways' => [
        'providers' => ['bulksmsbd', 'smsbd', 'mram', 'twilio'],
        'icon' => 'message', 'color' => 't-pop',
        'hint' => 'Only one gateway can be the default',
    ],
    'Services' => [
        'providers' => ['fraudbd'],
        'icon' => 'shield', 'color' => 't-info',
        'hint' => null,
    ],
];
$courierProviders = \App\Models\Integration::COURIER_PROVIDERS;
$smsProviders     = \App\Models\Integration::SMS_PROVIDERS;
$aiProviders      = \App\Models\Integration::AI_PROVIDERS;
$activeCount = $integrations->where('is_active', true)->count();
@endphp

<div class="page-head">
    <div>
        <h2 class="display">Integrations</h2>
        <div class="sub">Connect payment, shipping, messaging &amp; other services</div>
    </div>
    <div style="text-align:right">
        <div class="display tnum" style="font-size:20px;font-weight:700">
            {{ $activeCount }} <span class="faint" style="font-size:14px;font-weight:600">/ {{ $integrations->count() }}</span>
        </div>
        <div class="faint" style="font-size:12px;font-weight:600">active</div>
    </div>
</div>

<div class="col-gap" style="--gap:28px">
    @foreach($groups as $groupName => $groupCfg)
    @php $groupIntegrations = $integrations->whereIn('provider', $groupCfg['providers']); @endphp
    @if($groupIntegrations->isNotEmpty())
    <div>
        <div class="between" style="margin-bottom:13px">
            <h3 class="section-label">{{ $groupName }}</h3>
            @if($groupCfg['hint'])
            <span class="faint" style="font-size:12.5px;font-weight:600">
                <span class="ico" data-ico="shield" style="width:13px;height:13px;vertical-align:-2px"></span>
                {{ $groupCfg['hint'] }}
            </span>
            @endif
        </div>
        <div class="intg-grid">
            @foreach($groupIntegrations as $integration)
            @php
            $isDefault     = $integration->is_default;
            $canSetDefault = in_array($integration->provider, $courierProviders)
                          || in_array($integration->provider, $smsProviders)
                          || in_array($integration->provider, $aiProviders);
            $credCount     = count($integration->credentials ?? []);
            $tileClass     = $isDefault ? $groupCfg['color'] : 'muted';
            $envDot        = $integration->environment === 'live' ? 'var(--success)' : 'var(--warning)';
            $envLabel      = ucfirst($integration->environment ?? 'sandbox') . ' mode';
            @endphp
            <div class="card intg-card"
                 style="{{ $isDefault ? 'border:1.5px solid var(--accent);background:var(--accent-soft)' : '' }}">
                <div class="row top" style="gap:12px">
                    <span class="tile {{ $tileClass }}">
                        <span class="ico" data-ico="{{ $groupCfg['icon'] }}" style="width:22px;height:22px"></span>
                    </span>
                    <div class="grow">
                        <div class="row wrap" style="gap:7px">
                            <span style="font-weight:700;font-size:14.5px">{{ $integration->label }}</span>
                            <span class="pill sm {{ $integration->is_active ? 'success' : '' }}">
                                <span class="dot"></span>{{ $integration->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($isDefault)
                            <span class="pill sm accent">Default</span>
                            @endif
                        </div>
                        <div class="faint" style="font-size:12px;margin-top:3px">
                            {{ ucfirst($integration->provider) }}
                        </div>
                    </div>
                    <a href="{{ route('admin.integrations.edit', $integration) }}" class="link-btn">
                        <span class="ico" data-ico="gear" style="width:15px;height:15px"></span>Configure
                    </a>
                </div>

                <div class="faint" style="font-size:12px;font-weight:600;display:flex;align-items:center;gap:8px;padding-top:12px;border-top:1px solid var(--border)">
                    <span class="row" style="gap:5px">
                        <span style="width:6px;height:6px;border-radius:99px;background:{{ $envDot }}"></span>
                        {{ $envLabel }}
                    </span>
                    <span style="color:var(--border-strong)">·</span>
                    <span>{{ $credCount }} {{ $credCount === 1 ? 'credential' : 'credentials' }} saved</span>
                </div>

                @if($canSetDefault)
                @if($isDefault)
                <span style="color:var(--accent);font-weight:700;font-size:13px">
                    <span class="ico" data-ico="check" style="width:15px;height:15px;vertical-align:-3px"></span>
                    Currently default
                </span>
                @elseif($integration->is_active)
                <form method="POST" action="{{ route('admin.integrations.set-default', $integration) }}">
                    @csrf
                    <button type="submit" class="link-btn" style="font-size:13px;font-weight:600">
                        Set as default
                    </button>
                </form>
                @else
                <span class="faint" style="font-size:13px;font-weight:600">Activate to set as default</span>
                @endif
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endforeach
</div>

@endsection

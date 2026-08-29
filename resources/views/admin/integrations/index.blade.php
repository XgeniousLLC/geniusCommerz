@extends('admin.layouts.admin')

@section('title', 'Integrations')

@section('content')
<style>
.intg-card{padding:18px;display:flex;flex-direction:column;gap:13px}
.intg-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px}
</style>

<div class="page-head">
    <div>
        <h2 class="display">Integrations</h2>
        <div class="sub">Connect payment, shipping, messaging &amp; other services</div>
    </div>
    <div style="text-align:right">
        <div class="display tnum" style="font-size:20px;font-weight:700">
            {{ $configured }} <span class="faint" style="font-size:14px;font-weight:600">/ {{ $total }}</span>
        </div>
        <div class="faint" style="font-size:12px;font-weight:600">active</div>
    </div>
</div>

<div class="col-gap" style="--gap:28px">
    @foreach($groups as $groupKey => $group)
    <div>
        <div class="between" style="margin-bottom:13px">
            <h3 class="section-label">{{ $group['label'] }}</h3>
            @if($group['hint'])
            <span class="faint" style="font-size:12.5px;font-weight:600">
                <span class="ico" data-ico="shield" style="width:13px;height:13px;vertical-align:-2px"></span>
                {{ $group['hint'] }}
            </span>
            @endif
        </div>
        <div class="intg-grid">
            @foreach($group['cards'] as $card)
            @php
            $def       = $card['definition'];
            $row       = $card['row'];
            $isDefault = $row->exists && $row->is_default;
            $isActive  = $row->exists && $row->is_active;
            $planned   = ! $def->isImplemented();
            $credCount = $row->exists ? $row->credentialCount() : 0;
            $tileClass = $isDefault ? $group['color'] : 'muted';
            $envDot    = ($row->environment ?? 'sandbox') === 'live' ? 'var(--success)' : 'var(--warning)';
            $envLabel  = ucfirst($row->environment ?? 'sandbox') . ' mode';
            @endphp
            <div class="card intg-card"
                 style="{{ $isDefault ? 'border:1.5px solid var(--accent);background:var(--accent-soft)' : '' }}{{ $planned ? 'opacity:.62' : '' }}">
                <div class="row top" style="gap:12px">
                    <span class="tile {{ $tileClass }}">
                        <span class="ico" data-ico="{{ $group['icon'] }}" style="width:22px;height:22px"></span>
                    </span>
                    <div class="grow">
                        <div class="row wrap" style="gap:7px">
                            <span style="font-weight:700;font-size:14.5px">{{ $def->label }}</span>
                            @if($planned)
                                <span class="pill sm">Coming soon</span>
                            @else
                                <span class="pill sm {{ $isActive ? 'success' : '' }}">
                                    <span class="dot"></span>{{ $isActive ? 'Active' : 'Inactive' }}
                                </span>
                            @endif
                            @if($isDefault)
                            <span class="pill sm accent">Default</span>
                            @endif
                        </div>
                        <div class="faint" style="font-size:12px;margin-top:3px">
                            {{ $def->slug }}
                            @if($def->currencies !== ['*'])
                                · {{ implode(', ', array_slice($def->currencies, 0, 4)) }}
                            @endif
                        </div>
                    </div>
                    @unless($planned)
                    <a href="{{ route('admin.integrations.edit', $def->slug) }}" class="link-btn">
                        <span class="ico" data-ico="gear" style="width:15px;height:15px"></span>Configure
                    </a>
                    @endunless
                </div>

                <div class="faint" style="font-size:12px;font-weight:600;display:flex;align-items:center;gap:8px;padding-top:12px;border-top:1px solid var(--border)">
                    @if($planned)
                        <span>Not available yet</span>
                    @else
                        @if($def->supportsEnvironments())
                        <span class="row" style="gap:5px">
                            <span style="width:6px;height:6px;border-radius:99px;background:{{ $envDot }}"></span>
                            {{ $envLabel }}
                        </span>
                        <span style="color:var(--border-strong)">·</span>
                        @endif
                        <span>{{ $credCount }} {{ $credCount === 1 ? 'credential' : 'credentials' }} saved</span>
                    @endif
                </div>

                @if(! $planned && $row->supportsDefault())
                    @if($isDefault)
                    <span style="color:var(--accent);font-weight:700;font-size:13px">
                        <span class="ico" data-ico="check" style="width:15px;height:15px;vertical-align:-3px"></span>
                        Currently default
                    </span>
                    @elseif($isActive)
                    <form method="POST" action="{{ route('admin.integrations.set-default', $def->slug) }}">
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
    @endforeach
</div>

@endsection

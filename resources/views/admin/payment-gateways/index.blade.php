@extends('admin.layouts.admin')

@section('title', 'Payment Gateways')

@section('content')
<style>
.pg-row{display:flex;align-items:center;gap:14px;padding:16px 18px;border-bottom:1px solid var(--border)}
.pg-row:last-child{border-bottom:0}
.pg-pos{width:34px;text-align:center;font-weight:700;font-size:13px;color:var(--text-muted)}
</style>

<div class="page-head">
    <div>
        <h2 class="display">Payment Gateways</h2>
        <div class="sub">Enable gateways and set the order customers see them in at checkout</div>
    </div>
    <div style="text-align:right">
        <div class="display tnum" style="font-size:20px;font-weight:700">{{ $liveCount }}</div>
        <div class="faint" style="font-size:12px;font-weight:600">enabled</div>
    </div>
</div>

@if(count($offered) === 0)
<div class="row" style="gap:12px;padding:14px 18px;border-radius:14px;background:color-mix(in srgb,var(--warning) 10%,transparent);border:1px solid color-mix(in srgb,var(--warning) 30%,transparent);margin-bottom:22px">
    <span class="tile sm" style="background:var(--warning);color:#fff"><span class="ico" data-ico="shield" style="width:18px;height:18px"></span></span>
    <div style="font-size:13.5px;font-weight:600">
        No gateway can currently take a {{ $baseCurrency }} payment — checkout will fall back to cash on delivery.
    </div>
</div>
@endif

<form method="POST" action="{{ route('admin.payment-gateways.reorder') }}">
    @csrf
    <div class="card flush" style="margin-bottom:16px">
        @foreach($gateways as $i => $gateway)
        @php
            $def       = $gateway['definition'];
            $row       = $gateway['row'];
            $isActive  = $row->exists && $row->is_active;
            $planned   = ! $def->isImplemented();
            $isOffered = in_array($def->slug, $offered, true);
        @endphp
        <div class="pg-row" style="{{ $planned ? 'opacity:.6' : '' }}">
            <span class="pg-pos">{{ $i + 1 }}</span>
            <input type="hidden" name="order[]" value="{{ $def->slug }}">

            <span class="tile {{ $isActive ? 't-teal' : 'muted' }}">
                <span class="ico" data-ico="card" style="width:20px;height:20px"></span>
            </span>

            <div class="grow">
                <div class="row wrap" style="gap:7px;align-items:center">
                    <span style="font-weight:700;font-size:14.5px">{{ $def->label }}</span>
                    @if($planned)
                        <span class="pill sm">Coming soon</span>
                    @else
                        <span class="pill sm {{ $isActive ? 'success' : '' }}">
                            <span class="dot"></span>{{ $isActive ? 'Enabled' : 'Disabled' }}
                        </span>
                    @endif
                    @if($isActive && ! $isOffered)
                        <span class="pill sm warning">Not offered in {{ $baseCurrency }}</span>
                    @endif
                </div>
                <div class="faint" style="font-size:12px;margin-top:3px">
                    @if($def->currencies === ['*'])
                        Any currency
                    @else
                        {{ implode(', ', array_slice($def->currencies, 0, 6)) }}@if(count($def->currencies) > 6) +{{ count($def->currencies) - 6 }} more @endif
                    @endif
                    @foreach($def->capabilities as $capability)
                        · {{ $capability->label() }}
                    @endforeach
                </div>
            </div>

            @unless($planned)
            <a href="{{ route('admin.integrations.edit', $def->slug) }}" class="link-btn">
                <span class="ico" data-ico="gear" style="width:15px;height:15px"></span>Credentials
            </a>
            <button type="submit" form="toggle-{{ $def->slug }}" class="btn btn-outline btn-sm">
                {{ $isActive ? 'Disable' : 'Enable' }}
            </button>
            @endunless
        </div>
        @endforeach
    </div>

    <div class="row" style="gap:10px;justify-content:flex-end">
        <button type="submit" class="btn btn-primary">Save checkout order</button>
    </div>
</form>

{{-- Toggle forms live outside the reorder form so they do not nest --}}
@foreach($gateways as $gateway)
    @if($gateway['definition']->isImplemented())
    <form id="toggle-{{ $gateway['definition']->slug }}" method="POST"
          action="{{ route('admin.payment-gateways.toggle', $gateway['definition']->slug) }}" style="display:none">
        @csrf
    </form>
    @endif
@endforeach
@endsection

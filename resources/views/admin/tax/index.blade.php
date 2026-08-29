@extends('admin.layouts.admin')

@section('title', 'Tax Zones')

@section('content')
<style>
.tz-rate{display:flex;align-items:center;gap:10px;padding:9px 0;border-top:1px solid var(--border)}
.tz-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr .7fr auto;gap:10px;align-items:end}
</style>

<div class="page-head">
    <div>
        <h2 class="display">Tax Zones</h2>
        <div class="sub">Destination-based VAT, GST and sales tax</div>
    </div>
</div>

@unless($taxEnabled)
<div class="row" style="gap:12px;padding:14px 18px;border-radius:14px;background:color-mix(in srgb,var(--warning) 10%,transparent);border:1px solid color-mix(in srgb,var(--warning) 30%,transparent);margin-bottom:22px">
    <span class="tile sm" style="background:var(--warning);color:#fff"><span class="ico" data-ico="shield" style="width:18px;height:18px"></span></span>
    <div style="font-size:13.5px;font-weight:600">Tax calculation is off — orders are recorded with zero tax.</div>
</div>
@endunless

<div class="col-gap">

    <div class="card pad">
        <div class="card-head" style="margin-bottom:14px">
            <span class="tile sm t-accent"><span class="ico" data-ico="gear" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Settings</h3></div>
        </div>
        <form method="POST" action="{{ route('admin.tax.settings') }}">
            @csrf
            <label class="row" style="gap:8px;cursor:pointer;margin-bottom:10px">
                <input type="hidden" name="tax_enabled" value="0">
                <input type="checkbox" name="tax_enabled" value="1" {{ $taxEnabled ? 'checked' : '' }}
                    style="width:16px;height:16px;accent-color:var(--accent)">
                <span style="font-size:13.5px">Calculate tax on orders</span>
            </label>
            <label class="row" style="gap:8px;cursor:pointer;margin-bottom:14px">
                <input type="hidden" name="accounting_prices_include_tax" value="0">
                <input type="checkbox" name="accounting_prices_include_tax" value="1" {{ $inclusive ? 'checked' : '' }}
                    style="width:16px;height:16px;accent-color:var(--accent)">
                <span style="font-size:13.5px">Catalogue prices already include tax</span>
            </label>
            <p class="faint" style="font-size:12px;margin:0 0 14px;max-width:65ch">
                With tax-inclusive prices the customer pays the listed price and the tax is
                the portion of it owed to the tax authority — it is recorded, not added on top.
            </p>
            <button type="submit" class="btn btn-primary btn-sm">Save settings</button>
        </form>
    </div>

    <div class="card pad">
        <div class="card-head" style="margin-bottom:12px">
            <span class="tile sm t-violet"><span class="ico" data-ico="layers" style="width:18px;height:18px"></span></span>
            <div class="ct">
                <h3>Start from a template</h3>
                <div class="sub">Seed a whole region, then adjust individual rates</div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.tax.presets') }}" x-data="{ chosen: '' }">
            @csrf
            <div style="display:grid;grid-template-columns:1fr auto;gap:12px;align-items:end">
                <div class="field" style="margin:0">
                    <span class="lbl">Template</span>
                    <select class="input" name="preset" x-model="chosen" required>
                        <option value="">Choose a template…</option>
                        @foreach($presets as $key => $preset)
                        <option value="{{ $key }}">{{ $preset['label'] }} — {{ count($preset['zones']) }} zones</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" :disabled="!chosen">Apply</button>
            </div>
            @foreach($presets as $key => $preset)
            <p class="faint" style="font-size:12px;margin:10px 0 0" x-show="chosen === '{{ $key }}'" x-cloak>
                {{ $preset['note'] }}
            </p>
            @endforeach
            <p class="faint" style="font-size:12px;margin:10px 0 0">
                Zones you already have are skipped, so a template can be applied again later without duplicating anything.
            </p>
        </form>
    </div>

    <div class="card pad">
        <div class="card-head" style="margin-bottom:14px">
            <span class="tile sm t-teal"><span class="ico" data-ico="plus" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Add a zone</h3><div class="sub">Most specific match wins: postal beats state, state beats country</div></div>
        </div>
        <form method="POST" action="{{ route('admin.tax.store') }}">
            @csrf
            <div class="tz-grid">
                <div class="field" style="margin:0">
                    <span class="lbl">Name</span>
                    <input class="input" name="name" placeholder="UK VAT" required>
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Country</span>
                    <select class="input" name="country" required>
                        @foreach($countries as $c)
                        <option value="{{ $c['code'] }}" {{ $c['code'] === $storeCountry ? 'selected' : '' }}>{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">State <span class="faint" style="font-weight:400">(optional)</span></span>
                    <input class="input" name="state" placeholder="CA">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Postal <span class="faint" style="font-weight:400">(optional)</span></span>
                    <input class="input" name="postal_pattern" placeholder="94%">
                </div>
                <button type="submit" class="btn btn-primary">Add</button>
            </div>
            <input type="hidden" name="is_active" value="1">
        </form>
    </div>

    @forelse($zones as $zone)
    <div class="card pad">
        <div class="between" style="margin-bottom:6px">
            <div>
                <div class="row" style="gap:8px;align-items:center">
                    <strong style="font-size:15px">{{ $zone->name }}</strong>
                    <span class="pill sm {{ $zone->is_active ? 'success' : '' }}">
                        <span class="dot"></span>{{ $zone->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="faint" style="font-size:12.5px;margin-top:3px">{{ $zone->describe() }}</div>
            </div>
            <form method="POST" action="{{ route('admin.tax.destroy', $zone) }}"
                  onsubmit="return confirm('Remove this zone and its rates?')">
                @csrf @method('DELETE')
                <button type="submit" class="link-btn" style="color:var(--danger)">Remove</button>
            </form>
        </div>

        @foreach($zone->rates->sortBy('priority') as $rate)
        <div class="tz-rate">
            <span class="pill sm">{{ $rate->tax_class }}</span>
            <strong style="font-size:13.5px">{{ $rate->name }}</strong>
            <span class="tnum" style="font-weight:700">{{ rtrim(rtrim(number_format($rate->rate, 4), '0'), '.') }}%</span>
            @if($rate->applies_to_shipping)<span class="faint" style="font-size:12px">· incl. shipping</span>@endif
            <form method="POST" action="{{ route('admin.tax.rates.destroy', $rate) }}" style="margin-left:auto">
                @csrf @method('DELETE')
                <button type="submit" class="link-btn" style="font-size:12.5px;color:var(--danger)">Remove</button>
            </form>
        </div>
        @endforeach

        <form method="POST" action="{{ route('admin.tax.rates.store', $zone) }}" style="margin-top:12px">
            @csrf
            <div class="tz-grid">
                <div class="field" style="margin:0">
                    <span class="lbl">Rate name</span>
                    <input class="input" name="name" placeholder="VAT" required>
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Applies to</span>
                    <select class="input" name="tax_class">
                        @foreach($classes as $class)
                        <option value="{{ $class }}">{{ ucfirst($class) }} rated</option>
                        @endforeach
                    </select>
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Percent</span>
                    <input class="input" type="number" step="0.0001" min="0" max="100" name="rate" placeholder="20" required>
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Shipping</span>
                    <label class="row" style="gap:6px;cursor:pointer;height:38px;align-items:center">
                        <input type="hidden" name="applies_to_shipping" value="0">
                        <input type="checkbox" name="applies_to_shipping" value="1" checked
                            style="width:15px;height:15px;accent-color:var(--accent)">
                        <span style="font-size:13px">Taxed</span>
                    </label>
                </div>
                <button type="submit" class="btn btn-outline">Add rate</button>
            </div>
        </form>
    </div>
    @empty
    <div class="card pad">
        <p style="font-size:13.5px;color:var(--text-muted);margin:0">
            No tax zones yet. Orders are recorded with zero tax until you add one.
        </p>
    </div>
    @endforelse
</div>
@endsection

@extends('admin.layouts.admin')

@section('title', 'Shipping Zones')

@section('content')
<style>
.sz-rate{display:flex;align-items:center;gap:10px;padding:9px 0;border-top:1px solid var(--border);flex-wrap:wrap}
.sz-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr .7fr auto;gap:10px;align-items:end}
.sz-rgrid{display:grid;grid-template-columns:repeat(4,1fr) auto;gap:10px;align-items:end}
</style>

<div class="page-head">
    <div>
        <h2 class="display">Shipping Zones</h2>
        <div class="sub">Destination rates by weight and order value</div>
    </div>
</div>

<div class="row" style="gap:12px;padding:14px 18px;border-radius:14px;background:var(--surface-2);border:1px solid var(--border);margin-bottom:22px">
    <span class="tile sm t-info"><span class="ico" data-ico="truck" style="width:18px;height:18px"></span></span>
    <div style="font-size:13px;line-height:1.6">
        A live courier quote is used first where one is configured, then the best matching
        zone rate below, then the flat rate of
        <strong>{{ $currency }} {{ number_format($flatRate, 2) }}</strong> as a fallback.
        Products marked <em>shipping included</em> always ship free.
    </div>
</div>

<div class="col-gap">
    <div class="card pad">
        <div class="card-head" style="margin-bottom:14px">
            <span class="tile sm t-info"><span class="ico" data-ico="truck" style="width:18px;height:18px"></span></span>
            <div class="ct">
                <h3>Ship-from address</h3>
                <div class="sub">Required for live carrier rates{{ $hasCarrier ? '' : ' — no carrier is connected yet' }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.shipping.origin') }}">
            @csrf
            <div class="sz-rgrid" style="margin-bottom:10px">
                <div class="field" style="margin:0">
                    <span class="lbl">Name</span>
                    <input class="input" name="origin_name" value="{{ $origin['name'] }}">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Street</span>
                    <input class="input" name="origin_street" value="{{ $origin['street1'] }}">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">City</span>
                    <input class="input" name="origin_city" value="{{ $origin['city'] }}">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">State</span>
                    <input class="input" name="origin_state" value="{{ $origin['state'] }}">
                </div>
                <span></span>
            </div>
            <div class="sz-rgrid">
                <div class="field" style="margin:0">
                    <span class="lbl">Postal code</span>
                    <input class="input" name="origin_postal" value="{{ $origin['zip'] }}">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Country</span>
                    <select class="input" name="origin_country">
                        @foreach($countries as $c)
                        <option value="{{ $c['code'] }}" {{ $origin['country'] === $c['code'] ? 'selected' : '' }}>{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <span></span><span></span>
                <button type="submit" class="btn btn-outline">Save origin</button>
            </div>
        </form>
    </div>

    <div class="card pad">
        <div class="card-head" style="margin-bottom:12px">
            <span class="tile sm t-violet"><span class="ico" data-ico="layers" style="width:18px;height:18px"></span></span>
            <div class="ct">
                <h3>Start from a template</h3>
                <div class="sub">Seed zones and weight bands, then set your own prices</div>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.shipping.presets') }}" x-data="{ chosen: '' }">
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
        <form method="POST" action="{{ route('admin.shipping.store') }}">
            @csrf
            <div class="sz-grid">
                <div class="field" style="margin:0">
                    <span class="lbl">Name</span>
                    <input class="input" name="name" placeholder="United Kingdom" required>
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
        </form>
    </div>

    @forelse($zones as $zone)
    <div class="card pad">
        <div class="between" style="margin-bottom:6px">
            <div>
                <strong style="font-size:15px">{{ $zone->name }}</strong>
                <div class="faint" style="font-size:12.5px;margin-top:3px">{{ $zone->describe() }}</div>
            </div>
            <form method="POST" action="{{ route('admin.shipping.destroy', $zone) }}"
                  onsubmit="return confirm('Remove this zone and its rates?')">
                @csrf @method('DELETE')
                <button type="submit" class="link-btn" style="color:var(--danger)">Remove</button>
            </form>
        </div>

        @foreach($zone->rates->sortBy('priority') as $rate)
        <div class="sz-rate">
            <strong style="font-size:13.5px">{{ $rate->name }}</strong>
            <span class="tnum" style="font-weight:700">{{ $currency }} {{ number_format($rate->price, 2) }}</span>
            @if((float) $rate->per_kg > 0)
                <span class="faint" style="font-size:12px">+ {{ number_format($rate->per_kg, 2) }}/kg</span>
            @endif
            <span class="pill sm">{{ $rate->describeBand() }}</span>
            @if($rate->free_above !== null)
                <span class="pill sm success">free over {{ number_format($rate->free_above, 2) }}</span>
            @endif
            @if($rate->delivery_estimate)
                <span class="faint" style="font-size:12px">{{ $rate->delivery_estimate }}</span>
            @endif
            <form method="POST" action="{{ route('admin.shipping.rates.destroy', $rate) }}" style="margin-left:auto">
                @csrf @method('DELETE')
                <button type="submit" class="link-btn" style="font-size:12.5px;color:var(--danger)">Remove</button>
            </form>
        </div>
        @endforeach

        <form method="POST" action="{{ route('admin.shipping.rates.store', $zone) }}" style="margin-top:12px">
            @csrf
            <div class="sz-rgrid" style="margin-bottom:10px">
                <div class="field" style="margin:0">
                    <span class="lbl">Rate name</span>
                    <input class="input" name="name" placeholder="Standard" required>
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Price</span>
                    <input class="input" type="number" step="0.01" min="0" name="price" value="0" required>
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Per kg</span>
                    <input class="input" type="number" step="0.01" min="0" name="per_kg" placeholder="0">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Free above</span>
                    <input class="input" type="number" step="0.01" min="0" name="free_above" placeholder="—">
                </div>
                <button type="submit" class="btn btn-outline">Add rate</button>
            </div>
            <div class="sz-rgrid">
                <div class="field" style="margin:0">
                    <span class="lbl">Min kg</span>
                    <input class="input" type="number" step="0.001" min="0" name="min_weight" placeholder="—">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Max kg</span>
                    <input class="input" type="number" step="0.001" min="0" name="max_weight" placeholder="—">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Min order</span>
                    <input class="input" type="number" step="0.01" min="0" name="min_subtotal" placeholder="—">
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Delivery estimate</span>
                    <input class="input" name="delivery_estimate" placeholder="3-5 business days">
                </div>
                <span></span>
            </div>
        </form>
    </div>
    @empty
    <div class="card pad">
        <p style="font-size:13.5px;color:var(--text-muted);margin:0">
            No shipping zones yet — every order falls back to the flat rate.
        </p>
    </div>
    @endforelse
</div>
@endsection

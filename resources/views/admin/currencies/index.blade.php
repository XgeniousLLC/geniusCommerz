@extends('admin.layouts.admin')
@section('title', 'Currencies')
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Currencies</h2>
        <div class="sub">Manage currencies and exchange rates</div>
    </div>

<div class="card pad" style="margin-bottom:18px">
    <div class="card-head" style="margin-bottom:12px">
        <span class="tile sm t-info"><span class="ico" data-ico="refresh" style="width:17px;height:17px"></span></span>
        <div class="ct">
            <h3>Automatic rates</h3>
            <div class="sub">Refreshed hourly; a move over 15% is rejected and flagged rather than applied</div>
        </div>
        <form method="POST" action="{{ route('admin.currencies.refresh') }}" style="margin:0">
            @csrf<button type="submit" class="btn btn-outline btn-sm">Refresh now</button>
        </form>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px">
        @foreach($fxSources as $source)
        @php
            $def = $source['definition']; $row = $source['row'];
            $on  = $row->exists && $row->is_active; $isDefault = $row->exists && $row->is_default;
        @endphp
        <div class="card" style="padding:14px;{{ $isDefault ? 'border:1.5px solid var(--accent);background:var(--accent-soft)' : 'background:var(--surface-2)' }}">
            <div class="row wrap" style="gap:7px;align-items:center;margin-bottom:6px">
                <strong style="font-size:13.5px">{{ $def->label }}</strong>
                <span class="pill sm {{ $on ? 'success' : '' }}"><span class="dot"></span>{{ $on ? 'Enabled' : 'Disabled' }}</span>
                @if($isDefault)<span class="pill sm accent">Default</span>@endif
            </div>
            <div class="faint" style="font-size:12px;margin-bottom:10px">{{ $def->hint }}</div>
            <div class="row" style="gap:10px">
                @if($def->fields)
                <a href="{{ route('admin.integrations.edit', $def->slug) }}" class="link-btn" style="font-size:12.5px">Credentials</a>
                @endif
                @if($on && ! $isDefault)
                <form method="POST" action="{{ route('admin.currencies.fx.default', $def->slug) }}" style="margin:0">
                    @csrf<button type="submit" class="link-btn" style="font-size:12.5px">Make default</button>
                </form>
                @endif
                <form method="POST" action="{{ route('admin.currencies.fx.toggle', $def->slug) }}" style="margin:0 0 0 auto">
                    @csrf<button type="submit" class="link-btn" style="font-size:12.5px">{{ $on ? 'Disable' : 'Enable' }}</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    <p class="faint" style="font-size:12px;margin:12px 0 0">
        Set a currency's source to <strong>Auto</strong> below for it to be refreshed. With no
        source enabled the free keyless provider is used.
    </p>
</div>

</div>

<div style="display:grid;grid-template-columns:340px 1fr;gap:18px;align-items:start">

    <div class="card pad">
        <div class="card-head" style="margin-bottom:16px">
            <span class="tile sm t-teal"><span class="ico" data-ico="dollar" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Add Currency</h3></div>
        </div>
        <form method="POST" action="{{ route('admin.currencies.store') }}" class="col-gap" style="--gap:14px">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 80px;gap:10px">
                <div class="field" style="margin:0">
                    <span class="lbl">Name <span style="color:var(--danger)">*</span></span>
                    <input class="input" type="text" name="name" placeholder="US Dollar" value="{{ old('name') }}" required>
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Symbol</span>
                    <input class="input" type="text" name="symbol" placeholder="$" value="{{ old('symbol') }}" required>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:80px 1fr;gap:10px">
                <div class="field" style="margin:0">
                    <span class="lbl">Code</span>
                    <input class="input" type="text" name="code" placeholder="USD" value="{{ old('code') }}" required style="text-transform:uppercase">
                    @error('code')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
                </div>
                <div class="field" style="margin:0">
                    <span class="lbl">Rate (1 base = ?)</span>
                    <input class="input" type="number" name="rate" placeholder="0.0094" value="{{ old('rate') }}" step="0.000001" min="0.000001" required>
                    @error('rate')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Add Currency</button>
        </form>
    </div>

    <div class="card flush">
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name / Symbol</th>
                        <th>Rate</th>
                        <th>Status</th>
                        <th>Default</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($currencies as $currency)
                    <tr class="hoverable" x-data="{ editing: false }">
                        <td><span class="mono" style="font-weight:700;font-size:13.5px">{{ $currency->code }}</span></td>
                        <td>
                            <span x-show="!editing" style="font-size:13.5px">{{ $currency->symbol }} · {{ $currency->name }}</span>
                            <form x-show="editing" method="POST" action="{{ route('admin.currencies.update', $currency) }}"
                                  class="row" style="gap:8px">
                                @csrf @method('PUT')
                                <input class="input" type="text" name="symbol" value="{{ $currency->symbol }}" style="width:60px;height:34px;font-size:13px">
                                <input class="input" type="text" name="name" value="{{ $currency->name }}" style="width:130px;height:34px;font-size:13px">
                                <input class="input" type="number" name="rate" value="{{ $currency->rate }}" step="0.000001" min="0.000001" style="width:100px;height:34px;font-size:13px">
                                <select class="input" name="rate_source" style="width:110px;height:34px;font-size:13px">
                                    <option value="manual" {{ $currency->rate_source === 'manual' ? 'selected' : '' }}>Manual</option>
                                    <option value="api" {{ $currency->rate_source === 'api' ? 'selected' : '' }}>Auto</option>
                                </select>
                                <label class="row" style="gap:5px;font-size:12.5px;cursor:pointer">
                                    <input type="checkbox" name="is_active" value="1" {{ $currency->is_active ? 'checked' : '' }}>Active
                                </label>
                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                <button type="button" @click="editing=false" class="btn btn-sm btn-outline">Cancel</button>
                            </form>
                        </td>
                        <td class="mono faint" style="font-size:13px">
                            {{ $currency->rate }}
                            @if($currency->rate_source === 'api')
                                <span class="pill sm {{ $currency->isRateStale() ? 'warning' : '' }}" style="margin-left:6px">
                                    {{ $currency->isRateStale() ? 'stale' : 'auto' }}
                                </span>
                            @endif
                            @if($currency->rate_updated_at)
                                <div class="faint" style="font-size:11px">{{ $currency->rate_updated_at->diffForHumans() }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="pill sm {{ $currency->is_active ? 'success' : '' }}">
                                <span class="dot"></span>{{ $currency->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @if($currency->is_default)
                            <span class="pill sm accent">Default</span>
                            @else
                            <form method="POST" action="{{ route('admin.currencies.set-default', $currency) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="link-btn" style="font-size:13px">Set default</button>
                            </form>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <div class="row" style="gap:6px;justify-content:flex-end">
                                <button type="button" @click="editing=!editing" class="icon-btn">
                                    <span class="ico" data-ico="edit" style="width:15px;height:15px"></span>
                                </button>
                                @unless($currency->is_default)
                                <form method="POST" action="{{ route('admin.currencies.destroy', $currency) }}"
                                      onsubmit="return confirm('Delete {{ $currency->code }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-btn">
                                        <span class="ico" data-ico="trash" style="width:15px;height:15px"></span>
                                    </button>
                                </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:40px 20px">
                            <div class="faint" style="font-size:13.5px">No currencies added yet.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:12px 18px;border-top:1px solid var(--border)">
            <p class="faint" style="font-size:12px">Base currency is your store default. Set exchange rates relative to 1 unit of the base currency. Rates applied at display time — orders record the rate they were placed at. Currencies set to <strong>Auto</strong> are refreshed hourly by <code>currency:refresh-rates</code>; a move over 15% is rejected and flagged rather than applied.</p>
        </div>
    </div>

</div>
@endsection

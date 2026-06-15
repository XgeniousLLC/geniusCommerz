@extends('admin.layouts.admin')
@section('title', 'Currencies')
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Currencies</h2>
        <div class="sub">Manage currencies and exchange rates</div>
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
                                <label class="row" style="gap:5px;font-size:12.5px;cursor:pointer">
                                    <input type="checkbox" name="is_active" value="1" {{ $currency->is_active ? 'checked' : '' }}>Active
                                </label>
                                <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                <button type="button" @click="editing=false" class="btn btn-sm btn-outline">Cancel</button>
                            </form>
                        </td>
                        <td class="mono faint" style="font-size:13px">{{ $currency->rate }}</td>
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
            <p class="faint" style="font-size:12px">Base currency is your store default. Set exchange rates relative to 1 unit of the base currency. Rates applied at display time — orders stored in base currency.</p>
        </div>
    </div>

</div>
@endsection

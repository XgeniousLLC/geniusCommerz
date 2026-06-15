@extends('admin.layouts.admin')
@section('title', 'Languages')
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Languages</h2>
        <div class="sub">Manage storefront languages and AI-powered translations</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:18px;align-items:start">

    <div class="card pad">
        <div class="card-head" style="margin-bottom:16px">
            <span class="tile sm t-accent"><span class="ico" data-ico="globe" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Add Language</h3></div>
        </div>
        <form method="POST" action="{{ route('admin.languages.store') }}" class="col-gap" style="--gap:14px">
            @csrf
            <div class="field">
                <span class="lbl">Language Name <span style="color:var(--danger)">*</span></span>
                <input class="input" type="text" name="name" placeholder="e.g. Bengali" value="{{ old('name') }}" required>
                @error('name')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <span class="lbl">Language Code <span style="color:var(--danger)">*</span></span>
                <input class="input" type="text" name="code" placeholder="e.g. bn" value="{{ old('code') }}" required>
                @error('code')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn btn-primary">Add Language</button>
        </form>
    </div>

    <div class="card flush">
        <div class="table-scroll">
            <table class="table">
                <thead>
                    <tr>
                        <th>Language</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th>Default</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($languages as $lang)
                    <tr class="hoverable">
                        <td style="font-weight:600;font-size:13.5px">{{ $lang->name }}</td>
                        <td><span class="mono pill sm" style="font-size:12px">{{ $lang->code }}</span></td>
                        <td>
                            <span class="pill sm {{ $lang->is_active ? 'success' : '' }}">
                                <span class="dot"></span>{{ $lang->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @if($lang->is_default)
                            <span class="pill sm accent">Default</span>
                            @else
                            <form method="POST" action="{{ route('admin.languages.set-default', $lang) }}" style="display:inline">
                                @csrf
                                <button type="submit" class="link-btn" style="font-size:13px">Set as default</button>
                            </form>
                            @endif
                        </td>
                        <td style="text-align:right">
                            <div class="row" style="gap:6px;justify-content:flex-end">
                                <a href="{{ route('admin.languages.edit', $lang) }}" class="btn btn-sm btn-outline">Edit / Translate</a>
                                @unless($lang->is_default)
                                <form method="POST" action="{{ route('admin.languages.destroy', $lang) }}"
                                      onsubmit="return confirm('Delete {{ $lang->name }}?')">
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
                        <td colspan="5" style="text-align:center;padding:40px 20px">
                            <div class="faint" style="font-size:13.5px">No languages added yet.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:12px 18px;border-top:1px solid var(--border)">
            <p class="faint" style="font-size:12px">Storefront detects the visitor's preferred language via a cookie (<code>locale</code>). Add a language then use "Translate All with AI" to auto-generate translations.</p>
        </div>
    </div>

</div>
@endsection

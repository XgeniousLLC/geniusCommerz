@extends('admin.layouts.admin')
@section('title', 'New Menu')
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Create Menu</h2>
    </div>
    <a href="{{ route('admin.menus.index') }}" class="btn btn-outline">
        <span class="ico" data-ico="arrowLeft" style="width:16px;height:16px"></span>Back
    </a>
</div>

<div style="max-width:480px">
    <div class="card pad">
        <form method="POST" action="{{ route('admin.menus.store') }}" class="col-gap" style="--gap:14px">
            @csrf

            <div class="field">
                <span class="lbl">Menu Name <span style="color:var(--danger)">*</span></span>
                <input class="input" type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Main Navigation" required>
                @error('name')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
            </div>

            <div class="field">
                <span class="lbl">Location <span style="color:var(--danger)">*</span></span>
                <select class="input" name="location" required>
                    <option value="">Select a location…</option>
                    @foreach($locations as $key => $label)
                        <option value="{{ $key }}"
                            {{ in_array($key, $usedLocations) ? 'disabled' : '' }}
                            {{ old('location') === $key ? 'selected' : '' }}>
                            {{ $label }}{{ in_array($key, $usedLocations) ? ' (in use)' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('location')<p class="hint" style="color:var(--danger)">{{ $message }}</p>@enderror
            </div>

            <div class="row" style="gap:12px;margin-top:4px">
                <button type="submit" class="btn btn-primary">Create Menu</button>
                <a href="{{ route('admin.menus.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection

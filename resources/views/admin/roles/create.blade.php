@extends('admin.layouts.admin')
@section('title', 'Create Role')

@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Create Role</h2>
        <div class="sub">Define a new role and assign permissions</div>
    </div>
</div>

<form method="POST" action="{{ route('admin.roles.store') }}">
    @csrf
    <div style="display:grid;grid-template-columns:320px 1fr;gap:16px;align-items:start">

        {{-- Role name --}}
        <div class="card pad" style="display:flex;flex-direction:column;gap:16px">
            <div class="card-head" style="padding:0"><div class="ct"><h3>Role Details</h3></div></div>

            <div class="field">
                <label class="lbl">Role Name *</label>
                <input type="text" name="name" class="input" placeholder="e.g. warehouse-staff" value="{{ old('name') }}" required>
                <div class="lbl" style="margin-top:4px;font-weight:400">Use lowercase kebab-case (e.g. store-manager)</div>
                @error('name')<div style="color:var(--danger);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div style="display:flex;gap:8px;padding-top:8px;border-top:1px solid var(--border)">
                <button type="button" class="btn btn-outline" onclick="window.history.back()">Cancel</button>
                <button type="submit" class="btn">Create Role</button>
            </div>
        </div>

        {{-- Permissions --}}
        <div class="card pad">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                <h3 style="font-size:14px;font-weight:700;color:var(--text)">Permissions</h3>
                <button type="button" id="toggle-all" class="btn btn-outline btn-sm">Select all</button>
            </div>

            @error('permissions')
                <div style="color:var(--danger);font-size:12px;margin-bottom:12px">{{ $message }}</div>
            @enderror

            <div style="display:flex;flex-direction:column;gap:20px">
                @foreach($permissionGroups as $module => $permissions)
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                        <span style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted)">{{ ucfirst($module) }}</span>
                        <button type="button" class="toggle-group" data-group="{{ $module }}"
                                style="font-size:12px;color:var(--accent);background:none;border:none;cursor:pointer;padding:0">Select all</button>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;padding:12px;background:var(--surface-2);border-radius:8px">
                        @foreach($permissions as $permission)
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;color:var(--text)">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                   data-group="{{ $module }}" class="perm-checkbox"
                                   {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                            {{ ucfirst(explode(' ', $permission->name)[0]) }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-group').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const group = this.dataset.group;
            const boxes = document.querySelectorAll(`input[data-group="${group}"]`);
            const allChecked = Array.from(boxes).every(b => b.checked);
            boxes.forEach(b => b.checked = !allChecked);
            this.textContent = allChecked ? 'Select all' : 'Deselect all';
        });
    });

    const toggleAll = document.getElementById('toggle-all');
    toggleAll.addEventListener('click', function () {
        const boxes = document.querySelectorAll('.perm-checkbox');
        const allChecked = Array.from(boxes).every(b => b.checked);
        boxes.forEach(b => b.checked = !allChecked);
        this.textContent = allChecked ? 'Select all' : 'Deselect all';
    });
});
</script>
@endpush

@endsection

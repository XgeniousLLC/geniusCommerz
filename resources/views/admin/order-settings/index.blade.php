@extends('admin.layouts.admin')
@section('title', 'Order Settings')
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">Order Settings</h2>
        <div class="sub">Configure order number format, serial counter and invoice defaults</div>
    </div>
</div>

<form method="POST" action="{{ route('admin.order-settings.update') }}" class="col-gap" style="--gap:18px">
@csrf

@php
$fmt = old('settings[order.number_format]', $settings->get('order.number_format')?->value ?? '{year}-{month}-{day}-{serial}');
$presets = [
    '{year}-{month}-{day}-{serial}'       => '{year}-{month}-{day}-{serial}   →  2026-04-26-0001',
    '{year}{month}{day}-{serial}'          => '{year}{month}{day}-{serial}   →  20260426-0001',
    '{prefix}-{year}{month}{day}-{serial}' => '{prefix}-{year}{month}{day}-{serial}   →  KLX-20260426-0001',
    '{prefix}-{serial}'                    => '{prefix}-{serial}   →  KLX-0001',
    '{year}{month}-{serial}'               => '{year}{month}-{serial}   →  202604-0001',
];
$resetVal   = old('settings[order.serial_reset]', $settings->get('order.serial_reset')?->value ?? 'daily');
$lastSerial = $settings->get('order.last_serial')?->value;
$lastScope  = $settings->get('order.last_serial_scope')?->value;
@endphp

<div class="card pad">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-info"><span class="ico" data-ico="hash" style="width:18px;height:18px"></span></span>
        <div class="ct">
            <h3>Order Number Format</h3>
            <div class="sub">Define how order IDs are automatically generated</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;margin-bottom:20px">
        <div class="field">
            <span class="lbl">Format Template</span>
            <select class="input" name="settings[order.number_format]" id="order-format">
                @foreach($presets as $val => $desc)
                    <option value="{{ $val }}" {{ $fmt === $val ? 'selected' : '' }}>{{ $desc }}</option>
                @endforeach
            </select>
            <p class="hint">
                Tokens: <code style="background:var(--surface-2);padding:1px 5px;border-radius:4px;font-family:monospace;font-size:11px">{year}</code>
                <code style="background:var(--surface-2);padding:1px 5px;border-radius:4px;font-family:monospace;font-size:11px">{month}</code>
                <code style="background:var(--surface-2);padding:1px 5px;border-radius:4px;font-family:monospace;font-size:11px">{day}</code>
                <code style="background:var(--surface-2);padding:1px 5px;border-radius:4px;font-family:monospace;font-size:11px">{prefix}</code>
                <code style="background:var(--surface-2);padding:1px 5px;border-radius:4px;font-family:monospace;font-size:11px">{serial}</code>
                — prefix set in General Settings.
            </p>
        </div>
        <div class="field">
            <span class="lbl">Preview</span>
            <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;background:color-mix(in srgb,var(--accent) 10%,transparent);border:1px solid color-mix(in srgb,var(--accent) 30%,transparent);border-radius:10px;min-height:42px">
                <code id="order-preview" class="mono" style="font-size:1.05rem;font-weight:700;color:var(--accent)"></code>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;margin-bottom:20px">
        <div class="field">
            <span class="lbl">Serial Padding Digits</span>
            <input class="input" type="number" name="settings[order.serial_digits]" id="order-digits"
                min="1" max="8"
                value="{{ old('settings[order.serial_digits]', $settings->get('order.serial_digits')?->value ?? 4) }}">
            <p class="hint">4 → <code style="background:var(--surface-2);padding:1px 5px;border-radius:4px;font-family:monospace">0001</code></p>
        </div>
        <div class="field">
            <span class="lbl">Order Number Prefix</span>
            <input class="input" type="text" name="settings[general.order_prefix]"
                value="{{ old('settings[general.order_prefix]', $settings->get('general.order_prefix')?->value ?? 'KLX') }}">
            <p class="hint">Used in <code style="background:var(--surface-2);padding:1px 5px;border-radius:4px;font-family:monospace">{prefix}</code> token.</p>
        </div>
        <div class="field">
            <span class="lbl">Reset Counter</span>
            <select class="input" name="settings[order.serial_reset]">
                <option value="daily"   {{ $resetVal === 'daily'   ? 'selected' : '' }}>Every day — restarts at 0001 each midnight</option>
                <option value="monthly" {{ $resetVal === 'monthly' ? 'selected' : '' }}>Every month</option>
                <option value="yearly"  {{ $resetVal === 'yearly'  ? 'selected' : '' }}>Every year</option>
                <option value="never"   {{ $resetVal === 'never'   ? 'selected' : '' }}>Never — always incrementing</option>
            </select>
        </div>
    </div>

    @if($lastSerial)
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
        <div style="padding:12px 16px;background:var(--surface-2);border:1px solid var(--border);border-radius:10px">
            <div class="faint" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Current Serial</div>
            <div class="mono" style="font-size:1.2rem;font-weight:700">{{ $lastSerial }}</div>
        </div>
        <div style="padding:12px 16px;background:var(--surface-2);border:1px solid var(--border);border-radius:10px">
            <div class="faint" style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Last Reset Scope</div>
            <div class="mono" style="font-size:1.2rem;font-weight:700">{{ $lastScope ?? '—' }}</div>
        </div>
    </div>
    <p class="faint" style="font-size:12px">Counter updates automatically with each new order.</p>
    @endif
</div>

<div class="card pad">
    <div class="card-head" style="margin-bottom:20px">
        <span class="tile sm t-violet"><span class="ico" data-ico="file" style="width:18px;height:18px"></span></span>
        <div class="ct">
            <h3>Invoice / Print Slip</h3>
            <div class="sub">This note is printed at the bottom of every order slip</div>
        </div>
    </div>

    <div class="field">
        <span class="lbl">Additional Invoice Note</span>
        <textarea class="input" name="settings[order.invoice_note]" rows="4"
                  style="height:auto;resize:none"
                  placeholder="e.g. No returns after 7 days. For support call +880 1xxx-xxxxxx. Thank you for shopping with us!">{{ old('settings[order.invoice_note]', $settings->get('order.invoice_note')?->value ?? '') }}</textarea>
        <p class="hint">Shown below the totals on every printed invoice. Leave empty to hide.</p>
    </div>

    <div id="note-preview-wrap" style="display:none;margin-top:14px">
        <div class="faint" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px">Preview</div>
        <div style="background:color-mix(in srgb,var(--warning) 12%,transparent);border:1px solid color-mix(in srgb,var(--warning) 30%,transparent);border-radius:10px;padding:10px 14px;font-size:13px;white-space:pre-wrap" id="note-preview"></div>
    </div>
</div>

<div class="row" style="gap:12px">
    <button type="submit" class="btn btn-primary">Save Order Settings</button>
    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline">Cancel</a>
</div>

</form>
@endsection

@push('scripts')
<script>
(function () {
    const fmtSel   = document.getElementById('order-format');
    const digInput = document.getElementById('order-digits');
    const preview  = document.getElementById('order-preview');
    const noteArea = document.querySelector('textarea[name="settings[order.invoice_note]"]');
    const noteWrap = document.getElementById('note-preview-wrap');
    const notePreview = document.getElementById('note-preview');

    const prefix = @json(\App\Models\SiteSetting::get('general.order_prefix', 'KLX'));

    function pad(n, digits) {
        return String(n).padStart(parseInt(digits) || 4, '0');
    }

    function updatePreview() {
        if (!fmtSel || !preview) return;
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        const serial = pad(1, digInput ? digInput.value : 4);

        preview.textContent = fmtSel.value
            .replace('{year}',   y)
            .replace('{month}',  m)
            .replace('{day}',    d)
            .replace('{prefix}', prefix || '')
            .replace('{serial}', serial);
    }

    function updateNote() {
        if (!noteArea || !noteWrap || !notePreview) return;
        const val = noteArea.value.trim();
        if (val) {
            notePreview.textContent = val;
            noteWrap.style.display = 'block';
        } else {
            noteWrap.style.display = 'none';
        }
    }

    if (fmtSel) fmtSel.addEventListener('change', updatePreview);
    if (digInput) digInput.addEventListener('input', updatePreview);
    if (noteArea) noteArea.addEventListener('input', updateNote);

    updatePreview();
    updateNote();
})();
</script>
@endpush

@extends('admin.layouts.admin')

@section('title', 'Order Settings')

@section('breadcrumbs')
<ol class="flex items-center space-x-2 text-sm text-gray-500">
    <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
    <li><span class="mx-1">/</span></li>
    <li class="text-gray-900 font-medium">Order Settings</li>
</ol>
@endsection

@section('page-header')
<div>
    <h1 class="text-2xl font-bold text-gray-900">Order Settings</h1>
    <p class="text-gray-500 mt-1 text-sm">Configure order number format, serial counter and invoice defaults.</p>
</div>
@endsection

@section('content')

{{-- Shared tab nav --}}
<div style="border-bottom:1px solid #e5e7eb;margin-bottom:1.5rem">
    <nav style="display:flex;gap:0;margin-bottom:-1px">
        @foreach(['general' => 'General', 'meta' => 'SEO / Meta', 'social' => 'Social', 'storefront' => 'Storefront', 'payment' => 'Payment', 'shipping' => 'Shipping', 'legal' => 'Legal'] as $slug => $label)
        <a href="{{ route('admin.settings.index', ['tab' => $slug]) }}"
           style="display:inline-block;padding:10px 18px;font-size:0.875rem;font-weight:500;white-space:nowrap;text-decoration:none;border-bottom:2px solid transparent;color:#6b7280;transition:color .15s">
            {{ $label }}
        </a>
        @endforeach
        <a href="{{ route('admin.order-settings.index') }}"
           style="display:inline-block;padding:10px 18px;font-size:0.875rem;font-weight:500;white-space:nowrap;text-decoration:none;border-bottom:2px solid #3b82f6;color:#2563eb;transition:color .15s">
            Orders
        </a>
    </nav>
</div>

@if(session('success'))
<div class="mb-6 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
    {{ session('success') }}
</div>
@endif

<form method="POST" action="{{ route('admin.order-settings.update') }}" class="space-y-6">
@csrf

{{-- ── Order Number Format ── --}}
<x-admin.card>
    <h2 class="text-base font-semibold text-gray-900 mb-1">Order Number Format</h2>
    <p class="text-sm text-gray-500" style="margin-bottom:1rem">Define how order IDs are automatically generated for every new order.</p>

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

    {{-- Row 1: Format selector (left) + Live preview (right) --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;margin-bottom:20px">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Format Template</label>
            <x-admin.select name="settings[order.number_format]" id="order-format">
                @foreach($presets as $val => $desc)
                    <option value="{{ $val }}" {{ $fmt === $val ? 'selected' : '' }}>{{ $desc }}</option>
                @endforeach
            </x-admin.select>
            <p class="text-xs text-gray-400 mt-1">
                Tokens: <code class="bg-gray-100 px-1 rounded">{year}</code>
                <code class="bg-gray-100 px-1 rounded">{month}</code>
                <code class="bg-gray-100 px-1 rounded">{day}</code>
                <code class="bg-gray-100 px-1 rounded">{prefix}</code>
                <code class="bg-gray-100 px-1 rounded">{serial}</code>
                — prefix is set in General Settings.
            </p>
        </div>

        {{-- Live preview aligned with the selector --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Preview</label>
            <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;min-height:42px">
                <code id="order-preview" style="font-size:1.05rem;font-weight:700;font-family:monospace;color:#1d4ed8"></code>
            </div>
        </div>
    </div>

    {{-- Row 2: Serial digits + Reset counter + Prefix --}}
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Serial Padding Digits</label>
            <x-admin.input type="number" name="settings[order.serial_digits]" id="order-digits"
                min="1" max="8"
                value="{{ old('settings[order.serial_digits]', $settings->get('order.serial_digits')?->value ?? 4) }}" />
            <p class="text-xs text-gray-400 mt-1">4 → <code class="bg-gray-100 px-1 rounded">0001</code> &nbsp; 3 → <code class="bg-gray-100 px-1 rounded">001</code></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Order Number Prefix</label>
            <x-admin.input type="text" name="settings[general.order_prefix]"
                value="{{ old('settings[general.order_prefix]', $settings->get('general.order_prefix')?->value ?? 'KLX') }}" />
            <p class="text-xs text-gray-400 mt-1">Used in <code class="bg-gray-100 px-1 rounded">{prefix}</code> token.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reset Counter</label>
            <x-admin.select name="settings[order.serial_reset]">
                <option value="daily"   {{ $resetVal === 'daily'   ? 'selected' : '' }}>Every day — restarts at 0001 each midnight</option>
                <option value="monthly" {{ $resetVal === 'monthly' ? 'selected' : '' }}>Every month</option>
                <option value="yearly"  {{ $resetVal === 'yearly'  ? 'selected' : '' }}>Every year</option>
                <option value="never"   {{ $resetVal === 'never'   ? 'selected' : '' }}>Never — always incrementing</option>
            </x-admin.select>
        </div>
    </div>

    {{-- Current serial state (read-only) --}}
    @if($lastSerial)
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:8px">
        <div style="display:flex;flex-direction:column;gap:4px;padding:12px 16px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px">
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#9ca3af">Current Serial</span>
            <span style="font-family:monospace;font-size:1.2rem;font-weight:700;color:#111">{{ $lastSerial }}</span>
        </div>
        <div style="display:flex;flex-direction:column;gap:4px;padding:12px 16px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px">
            <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:#9ca3af">Last Reset Scope</span>
            <span style="font-family:monospace;font-size:1.2rem;font-weight:700;color:#111">{{ $lastScope ?? '—' }}</span>
        </div>
    </div>
    <p class="text-xs text-gray-400">Counter updates automatically with each new order.</p>
    @endif
</x-admin.card>

{{-- ── Invoice Settings ── --}}
<x-admin.card>
    <h2 class="text-base font-semibold text-gray-900 mb-1">Invoice / Print Slip</h2>
    <p class="text-sm text-gray-500" style="margin-bottom:1rem">This note is printed at the bottom of every order slip.</p>

    <div class="space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Additional Invoice Note</label>
            <textarea name="settings[order.invoice_note]" rows="4"
                      placeholder="e.g. No returns after 7 days. For support call +880 1xxx-xxxxxx. Thank you for shopping with us!"
                      class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 resize-none">{{ old('settings[order.invoice_note]', $settings->get('order.invoice_note')?->value ?? '') }}</textarea>
            <p class="text-xs text-gray-400 mt-1">Shown below the totals on every printed invoice. Leave empty to hide.</p>
        </div>

        {{-- Live preview of the note box --}}
        <div id="note-preview-wrap" class="hidden">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Preview</p>
            <div class="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-sm text-gray-700 whitespace-pre-wrap" id="note-preview"></div>
        </div>

    </div>
</x-admin.card>

{{-- Save --}}
<div class="flex items-center gap-3">
    <x-admin.button type="submit">Save Order Settings</x-admin.button>
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
            noteWrap.classList.remove('hidden');
        } else {
            noteWrap.classList.add('hidden');
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

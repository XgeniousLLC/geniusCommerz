@extends('admin.layouts.admin')
@section('title', 'Edit Language – ' . $language->name)
@section('content')

<div class="page-head">
    <div>
        <h2 class="display">{{ $language->name }} <span class="mono faint" style="font-size:14px">({{ $language->code }})</span></h2>
        <div class="sub">Manage translations for this language</div>
    </div>
    <button type="button" id="aiTranslateBtn" onclick="translateWithAi()"
        class="btn btn-primary" style="background:var(--violet,#7c3aed);border-color:var(--violet,#7c3aed)">
        <span class="ico" data-ico="bolt" style="width:16px;height:16px"></span>
        <span id="aiTranslateBtnLabel">Translate All with AI</span>
    </button>
</div>

<div id="aiMsg" style="display:none;margin-bottom:14px;padding:10px 14px;border-radius:10px;font-size:13px"></div>

<form method="POST" action="{{ route('admin.languages.update', $language) }}" class="col-gap" style="--gap:18px">
    @csrf @method('PUT')

    <div class="card pad">
        <div class="card-head" style="margin-bottom:16px">
            <span class="tile sm t-accent"><span class="ico" data-ico="settings" style="width:18px;height:18px"></span></span>
            <div class="ct"><h3>Language Settings</h3></div>
        </div>
        <div class="row" style="gap:20px;flex-wrap:wrap;align-items:flex-end">
            <div class="field" style="margin:0;min-width:200px">
                <span class="lbl">Display Name</span>
                <input class="input" type="text" name="name" value="{{ old('name', $language->name) }}" required>
            </div>
            <div class="row" style="gap:20px;padding-bottom:2px">
                <label class="row" style="gap:8px;cursor:pointer;font-size:13.5px">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ $language->is_active ? 'checked' : '' }}>
                    Active
                </label>
                <label class="row" style="gap:8px;cursor:pointer;font-size:13.5px">
                    <input type="hidden" name="is_default" value="0">
                    <input type="checkbox" name="is_default" value="1" {{ $language->is_default ? 'checked' : '' }}>
                    Set as Default
                </label>
            </div>
        </div>
    </div>

    <div class="card flush">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
            <div style="font-weight:700;font-size:14px">String Translations</div>
            <div class="faint" style="font-size:12px">{{ count($strings) }} strings</div>
        </div>
        <div>
            @foreach($strings as $key => $default)
            <div style="padding:10px 18px;border-bottom:1px solid var(--border);display:grid;grid-template-columns:1fr 1fr;gap:14px;align-items:center">
                <div>
                    <div class="mono faint" style="font-size:11px;margin-bottom:2px">{{ $key }}</div>
                    <div style="font-size:13px">{{ $default }}</div>
                </div>
                <div>
                    <input class="input" type="text"
                        id="str_{{ $key }}"
                        name="strings[{{ $key }}]"
                        value="{{ old('strings.' . $key, $translations[$key] ?? '') }}"
                        placeholder="{{ $default }}"
                        style="font-size:13px">
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="row" style="gap:12px">
        <button type="submit" class="btn btn-primary">Save Translations</button>
        <a href="{{ route('admin.languages.index') }}" class="btn btn-outline">Cancel</a>
    </div>
</form>

<script>
async function translateWithAi() {
    const btn   = document.getElementById('aiTranslateBtn');
    const label = document.getElementById('aiTranslateBtnLabel');
    const msg   = document.getElementById('aiMsg');

    btn.disabled = true;
    label.textContent = 'Translating...';
    msg.style.display = 'none';

    try {
        const res  = await fetch('{{ route('admin.languages.translate-ai', $language) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });
        const data = await res.json();

        if (data.error) {
            msg.textContent = data.error;
            msg.style.cssText = 'display:block;padding:10px 14px;border-radius:10px;font-size:13px;background:color-mix(in srgb,var(--danger) 10%,transparent);border:1px solid color-mix(in srgb,var(--danger) 30%,transparent);color:var(--danger)';
        } else {
            msg.textContent = `Successfully translated ${data.count} strings. Reloading...`;
            msg.style.cssText = 'display:block;padding:10px 14px;border-radius:10px;font-size:13px;background:color-mix(in srgb,var(--success) 10%,transparent);border:1px solid color-mix(in srgb,var(--success) 30%,transparent);color:var(--success)';
            setTimeout(() => window.location.reload(), 1200);
        }
    } catch (e) {
        msg.textContent = 'Request failed: ' + e.message;
        msg.style.cssText = 'display:block;padding:10px 14px;border-radius:10px;font-size:13px;background:color-mix(in srgb,var(--danger) 10%,transparent);border:1px solid color-mix(in srgb,var(--danger) 30%,transparent);color:var(--danger)';
    } finally {
        btn.disabled = false;
        label.textContent = 'Translate All with AI';
    }
}
</script>
@endsection

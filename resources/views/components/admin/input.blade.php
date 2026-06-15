@props([
    'type'     => 'text',
    'label'    => null,
    'error'    => null,
    'required' => false,
    'help'     => null,
    'prefix'   => null,
])

@if($label)
<div class="field">
    <label>
        {{ $label }}
        @if($required)<span class="req">*</span>@endif
    </label>
@endif

@if($prefix)
<div class="row" style="gap:0;position:relative">
    <span class="input-prefix">{{ $prefix }}</span>
@endif

<input
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'input' . ($error ? ' error' : '')]) }}
>

@if($prefix)
</div>
@endif

@if($help && !$error)
    <div class="faint" style="font-size:12px;margin-top:4px">{{ $help }}</div>
@endif
@if($error)
    <div style="font-size:12px;font-weight:600;color:var(--danger);margin-top:4px">{{ $error }}</div>
@endif

@if($label)
</div>
@endif

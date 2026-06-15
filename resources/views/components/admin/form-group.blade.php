@props(['label' => null, 'required' => false, 'error' => null, 'help' => null])

<div {{ $attributes->merge(['class' => 'field']) }}>
    @if($label)
        <label>
            {{ $label }}
            @if($required)<span class="req">*</span>@endif
        </label>
    @endif
    {{ $slot }}
    @if($help && !$error)
        <div class="faint" style="font-size:12px;margin-top:4px">{{ $help }}</div>
    @endif
    @if($error)
        <div style="font-size:12px;font-weight:600;color:var(--danger);margin-top:4px">{{ $error }}</div>
    @endif
</div>

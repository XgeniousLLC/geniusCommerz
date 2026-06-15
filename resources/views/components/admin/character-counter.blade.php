@props(['current' => 0, 'optimal' => null, 'max' => null, 'target' => null])

@php
    $currentLength = is_string($current) ? strlen($current) : $current;

    $color = 'var(--text-faint)';
    if ($optimal) {
        [$optMin, $optMax] = is_array($optimal) ? $optimal : [$optimal - 10, $optimal + 10];
        if ($currentLength >= $optMin && $currentLength <= $optMax) {
            $color = 'var(--success)';
        } elseif ($currentLength > 0 && $currentLength <= ($max ?? $optMax + 20)) {
            $color = 'var(--warning)';
        } elseif ($currentLength > ($max ?? $optMax + 20)) {
            $color = 'var(--danger)';
        }
    }
    $percentage = $max ? min(($currentLength / $max) * 100, 100) : 0;
@endphp

<div class="row" style="gap:6px;font-size:12px;font-weight:600;color:{{ $color }}">
    <span>{{ $currentLength }}</span>

    @if($optimal)
        <span class="faint">/</span>
        <span class="faint">
            @if(is_array($optimal)){{ $optimal[0] }}–{{ $optimal[1] }}@else{{ $optimal }}@endif
            optimal
        </span>
    @endif

    @if($max)
        <span class="faint">/</span>
        <span class="faint">{{ $max }} max</span>
        <div style="width:64px;height:5px;background:var(--surface-3);border-radius:99px;overflow:hidden">
            <div style="height:100%;width:{{ $percentage }}%;background:{{ $color }};border-radius:99px;transition:width .2s"></div>
        </div>
    @endif
</div>

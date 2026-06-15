@props(['type' => 'info'])

@php
$styles = [
    'success' => 'background:var(--success-soft);border-color:color-mix(in srgb,var(--success) 30%,transparent)',
    'error'   => 'background:var(--danger-soft);border-color:color-mix(in srgb,var(--danger) 30%,transparent)',
    'warning' => 'background:var(--warning-soft);border-color:color-mix(in srgb,var(--warning) 30%,transparent)',
    'info'    => 'background:var(--info-soft);border-color:color-mix(in srgb,var(--info) 30%,transparent)',
    'danger'  => 'background:var(--danger-soft);border-color:color-mix(in srgb,var(--danger) 30%,transparent)',
];
$tileStyles = [
    'success' => 'background:var(--success);color:#fff',
    'error'   => 'background:var(--danger);color:#fff',
    'warning' => 'background:var(--warning);color:#fff',
    'info'    => 'background:var(--info);color:#fff',
    'danger'  => 'background:var(--danger);color:#fff',
];
$textStyles = [
    'success' => 'color:var(--success)',
    'error'   => 'color:var(--danger)',
    'warning' => 'color:var(--warning)',
    'info'    => 'color:var(--info)',
    'danger'  => 'color:var(--danger)',
];
$icons = [
    'success' => 'check',
    'error'   => 'x',
    'warning' => 'bell',
    'info'    => 'info',
    'danger'  => 'x',
];
$t = $type;
@endphp

<div {{ $attributes }} style="{{ $styles[$t] ?? $styles['info'] }};border-radius:var(--radius-card);border:1px solid;padding:14px 16px">
    <div class="row" style="gap:10px;align-items:flex-start">
        <span class="tile sm" style="{{ $tileStyles[$t] ?? $tileStyles['info'] }};flex-shrink:0">
            <span class="ico" data-ico="{{ $icons[$t] ?? 'info' }}" style="width:18px;height:18px"></span>
        </span>
        <span style="font-weight:600;font-size:14px;{{ $textStyles[$t] ?? $textStyles['info'] }}">{{ $slot }}</span>
    </div>
</div>

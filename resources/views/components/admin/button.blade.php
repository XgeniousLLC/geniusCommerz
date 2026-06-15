@props([
    'variant' => 'primary',
    'size'    => 'md',
    'href'    => null,
    'type'    => 'button',
])

@php
$variantClass = match($variant) {
    'primary'   => 'btn-primary',
    'outline'   => 'btn-outline',
    'secondary' => 'btn-outline',
    'soft'      => 'btn-soft',
    'danger'    => 'btn-outline danger',
    'success'   => 'btn-outline',
    'warning'   => 'btn-outline',
    'ghost'     => 'btn-ghost',
    default     => 'btn-outline',
};
$sizeClass = match($size) {
    'sm', 'small' => 'btn-sm',
    'xs'          => 'btn-xs',
    default       => '',
};
$classes = trim("btn {$variantClass} {$sizeClass}");
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif

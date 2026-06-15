@props(['href', 'active' => false, 'icon' => null])

<a href="{{ $href }}" class="nav-leaf focusable {{ $active ? 'active' : '' }}">
    @if($icon)
        <span class="ico" data-ico="{{ $icon }}"></span>
    @endif
    <span>{{ $slot }}</span>
</a>

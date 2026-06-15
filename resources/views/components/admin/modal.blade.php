@props(['id', 'title' => '', 'size' => 'md'])

@php
$maxWidth = match($size) {
    'sm'  => '480px',
    'lg'  => '720px',
    'xl'  => '900px',
    default => '580px',
};
@endphp

<div id="{{ $id }}" class="modal-scrim" hidden>
    <div class="modal" style="max-width:{{ $maxWidth }}">
        @if($title)
            <div class="between" style="margin-bottom:20px">
                <h3 style="font-size:16px;font-weight:700">{{ $title }}</h3>
                <button class="icon-btn focusable" data-close-modal title="Close">
                    <span class="ico" data-ico="x" style="width:18px;height:18px"></span>
                </button>
            </div>
        @endif
        {{ $slot }}
    </div>
</div>

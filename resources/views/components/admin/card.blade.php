@props(['title' => null, 'padding' => true, 'flush' => false])

<div {{ $attributes->merge(['class' => 'card ' . ($flush ? 'flush' : ($padding ? 'pad' : ''))]) }}>
    @if($title)
        <div class="card-head" style="margin-bottom:16px">
            <h3>{{ $title }}</h3>
        </div>
    @endif
    {{ $slot }}
</div>

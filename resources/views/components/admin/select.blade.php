@props(['options' => [], 'placeholder' => null, 'error' => null])

<div class="select-wrap">
    <select {{ $attributes->merge(['class' => 'select' . ($error ? ' error' : '')]) }}>
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $value => $label)
            <option value="{{ $value }}" {{ (string)$value === (string)old($attributes->get('name'), $attributes->get('value')) ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
        {{ $slot }}
    </select>
</div>

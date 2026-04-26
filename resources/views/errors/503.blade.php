@extends('frontend.layout')

@section('title', 'Under Maintenance')

@section('content')
<div class="max-w-4xl mx-auto px-4 lg:px-6 py-10 lg:py-20 text-center">

    <div class="font-extrabold leading-none select-none" style="font-size:10rem;color:var(--kb-primary);opacity:.12">503</div>
    <h1 class="text-3xl md:text-4xl font-extrabold -mt-10 md:-mt-14">We'll be right back.</h1>
    <p class="mt-3 max-w-md mx-auto" style="color:var(--kb-ink-muted)">
        The store is currently undergoing scheduled maintenance. We'll be back online shortly.
        Thank you for your patience.
    </p>

    @if(!empty($exception) && $exception->getMessage())
    <p class="mt-4 text-xs text-gray-400">{{ $exception->getMessage() }}</p>
    @endif

</div>
@endsection

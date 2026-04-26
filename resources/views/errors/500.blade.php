@extends('frontend.layout')

@section('title', 'Server Error')

@section('content')
<div class="max-w-4xl mx-auto px-4 lg:px-6 py-10 lg:py-20 text-center">

    <div class="font-extrabold leading-none select-none" style="font-size:10rem;color:var(--kb-primary);opacity:.12">500</div>
    <h1 class="text-3xl md:text-4xl font-extrabold -mt-10 md:-mt-14">Something went wrong.</h1>
    <p class="mt-3 max-w-md mx-auto" style="color:var(--kb-ink-muted)">
        Our server ran into an unexpected error. We're already notified and working on a fix.
        Please try again in a few minutes.
    </p>

    <div class="mt-8 flex items-center justify-center gap-3 flex-wrap">
        <a href="/" class="kb-btn kb-btn-primary kb-btn-md">Back to home</a>
        <a href="/shop" class="kb-btn kb-btn-ghost kb-btn-md">Browse products</a>
    </div>

</div>
@endsection

@extends('frontend.layout')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="kb-card p-8" style="border-radius:20px">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-extrabold" style="color:var(--kb-ink)">Create account</h1>
                <p class="text-sm mt-1" style="color:var(--kb-ink-soft)">Join us to leave comments</p>
            </div>

            @if($errors->any())
            <div class="mb-5 px-4 py-3 rounded-xl text-sm" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca">
                <ul class="space-y-0.5">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--kb-ink)">Full name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                           placeholder="Your name"
                           class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none focus:ring-2"
                           style="border-color:#e2e8f0;background:#f8fafc;color:var(--kb-ink)">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--kb-ink)">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="you@example.com"
                           class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none focus:ring-2"
                           style="border-color:#e2e8f0;background:#f8fafc;color:var(--kb-ink)">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--kb-ink)">Password</label>
                    <input type="password" name="password" required
                           placeholder="Min. 8 characters"
                           class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none focus:ring-2"
                           style="border-color:#e2e8f0;background:#f8fafc;color:var(--kb-ink)">
                </div>
                <div>
                    <label class="block text-xs font-semibold mb-1.5" style="color:var(--kb-ink)">Confirm password</label>
                    <input type="password" name="password_confirmation" required
                           placeholder="Repeat password"
                           class="w-full px-4 py-2.5 rounded-xl border text-sm outline-none focus:ring-2"
                           style="border-color:#e2e8f0;background:#f8fafc;color:var(--kb-ink)">
                </div>
                <button type="submit" class="kb-btn kb-btn-primary w-full py-2.5 text-sm font-semibold">
                    Create account
                </button>
            </form>

            <p class="text-center text-sm mt-6" style="color:var(--kb-ink-soft)">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold hover:underline" style="color:var(--kb-primary)">Sign in</a>
            </p>
        </div>
    </div>
</div>
@endsection

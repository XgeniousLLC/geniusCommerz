@extends('admin.layouts.admin')

@section('title', 'Change Password')

@section('content')
<div class="max-w-md mx-auto mt-10">
    <h1 class="text-2xl font-bold mb-6">Set New Password</h1>
    <p class="text-gray-600 mb-6">You must set a new password before continuing.</p>

    <form method="POST" action="{{ route('admin.password.update') }}">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
            <input type="password" name="password" required minlength="8"
                class="w-full border rounded px-3 py-2 @error('password') border-red-500 @enderror">
            @error('password')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input type="password" name="password_confirmation" required
                class="w-full border rounded px-3 py-2">
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
            Set Password
        </button>
    </form>
</div>
@endsection

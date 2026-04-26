<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accept Invitation – {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-lg shadow p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold mb-2">Accept Invitation</h1>
        <p class="text-gray-600 mb-6">Hello <strong>{{ $admin->name }}</strong>, set your password to activate your account.</p>

        <form method="POST" action="{{ route('admin.invite.accept', $token) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
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
                Activate Account
            </button>
        </form>
    </div>
</body>
</html>

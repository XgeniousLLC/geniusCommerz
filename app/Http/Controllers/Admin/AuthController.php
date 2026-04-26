<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (! Auth::guard('admin')->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        $admin = Auth::guard('admin')->user();

        if (! $admin->is_active) {
            Auth::guard('admin')->logout();
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated.',
            ]);
        }

        $admin->update(['last_login_at' => now()]);

        if ($admin->must_reset_password) {
            return redirect()->route('admin.password.change');
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function showChangePassword(): View
    {
        return view('admin.auth.change-password');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = Auth::guard('admin')->user();
        $admin->update([
            'password' => $request->password,
            'must_reset_password' => false,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Password updated successfully.');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}

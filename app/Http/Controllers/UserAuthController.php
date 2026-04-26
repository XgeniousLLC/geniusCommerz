<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UserAuthController extends Controller
{
    public function showLogin(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/');
        }
        $loginMethod = SiteSetting::get('auth.login_method', 'email_password');
        return Inertia::render('auth/Login', ['loginMethod' => $loginMethod]);
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string|max:20']);

        $phone = $request->input('phone');
        $user  = User::where('phone', $phone)->where('is_active', true)->first();

        if (! $user) {
            return response()->json(['message' => 'No account found with this phone number.'], 422);
        }

        $otp = $user->generateOtp();

        try {
            $sms = app(SmsService::class);
            $sms->send($phone, "Your klixbd login OTP is: {$otp}. Valid for 5 minutes.");
        } catch (\Throwable $e) {
            // If SMS fails in non-production, surface the OTP for testing
            if (app()->environment('local', 'testing')) {
                return response()->json(['message' => 'OTP sent (dev: ' . $otp . ')']);
            }
            return response()->json(['message' => 'Failed to send OTP. Please try again.'], 500);
        }

        return response()->json(['message' => 'OTP sent to your phone.']);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'otp'   => 'required|string|size:6',
        ]);

        $user = User::where('phone', $request->input('phone'))->where('is_active', true)->first();

        if (! $user || ! $user->isOtpValid($request->input('otp'))) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        $user->clearOtp();
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['redirect' => session()->pull('url.intended', '/')]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
    }

    public function showRegister(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return Inertia::render('auth/Register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => $data['password'],
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function showForgotPassword(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return Inertia::render('auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)])->withInput();
    }

    public function showResetPassword(Request $request, string $token): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect('/');
        }
        return Inertia::render('auth/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])
                     ->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect('/login')->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)])->withInput();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'role' => 'required|string|in:super-admin,store-manager,operations,inventory-manager,marketing,call-agent,call-supervisor,accountant,content-editor,read-only-analyst',
        ]);

        $token = Str::random(64);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make(Str::random(32)),
            'role' => $request->role,
            'is_active' => false,
            'invite_token' => $token,
            'must_reset_password' => true,
        ]);

        $admin->assignRole($request->role);

        // Send invite email (uses log mailer in dev)
        Mail::send('admin.emails.invite', [
            'admin' => $admin,
            'invitedBy' => Auth::guard('admin')->user(),
            'url' => route('admin.invite.show', $token),
        ], function ($mail) use ($admin) {
            $mail->to($admin->email, $admin->name)
                ->subject('You have been invited to klixbd admin');
        });

        return redirect()->route('admin.admins.index')
            ->with('success', "Invite sent to {$admin->email}.");
    }

    public function show(string $token): View
    {
        $admin = Admin::where('invite_token', $token)->firstOrFail();

        return view('admin.auth.invite-accept', compact('admin', 'token'));
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $admin = Admin::where('invite_token', $token)->firstOrFail();

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin->update([
            'password' => $request->password,
            'is_active' => true,
            'invite_token' => null,
            'must_reset_password' => false,
        ]);

        Auth::guard('admin')->login($admin);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Welcome! Your account is ready.');
    }
}

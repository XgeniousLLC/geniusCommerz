<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(): View
    {
        $integrations = Integration::orderBy('provider')->get();

        return view('admin.integrations.index', compact('integrations'));
    }

    public function aiSettings(): View
    {
        $integrations = Integration::whereIn('provider', Integration::AI_PROVIDERS)
            ->orderByDesc('is_default')
            ->orderBy('provider')
            ->get();

        return view('admin.ai-settings.index', compact('integrations'));
    }

    public function edit(Integration $integration): View
    {
        return view('admin.integrations.edit', compact('integration'));
    }

    public function update(Request $request, Integration $integration): RedirectResponse
    {
        // Strip blanks BEFORE merging — blank password fields must not wipe existing saved values
        $credentials = array_filter(
            $request->input('credentials', []),
            fn ($v) => $v !== null && $v !== '',
        );

        $existing = $integration->credentials ?? [];
        $merged   = array_merge($existing, $credentials);

        $integration->update([
            'credentials' => $merged,
            'is_active'   => $request->boolean('is_active'),
            'environment' => $request->input('environment', 'sandbox'),
            'notes'       => $request->input('notes'),
        ]);

        $redirect = in_array($integration->provider, Integration::AI_PROVIDERS)
            ? route('admin.ai-settings.index')
            : route('admin.integrations.index');

        return redirect($redirect)
            ->with('success', "{$integration->label} credentials saved.");
    }

    public function testSms(Request $request, Integration $integration): RedirectResponse
    {
        $request->validate([
            'phone'   => ['required', 'string'],
            'message' => ['required', 'string', 'max:160'],
        ]);

        if (! in_array($integration->provider, Integration::SMS_PROVIDERS)) {
            return back()->with('error', 'Not an SMS provider.');
        }

        try {
            $sent = app(SmsService::class)->driver($integration->provider)->send(
                $request->input('phone'),
                $request->input('message'),
            );

            return back()->with(
                $sent ? 'success' : 'error',
                $sent ? 'Test SMS sent.' : 'Gateway returned failure — check credentials.',
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'SMS failed: ' . $e->getMessage());
        }
    }

    public function smsBalance(Integration $integration): RedirectResponse
    {
        if (! in_array($integration->provider, Integration::SMS_PROVIDERS)) {
            return back()->with('error', 'Not an SMS provider.');
        }

        try {
            $balance = app(SmsService::class)->driver($integration->provider)->balance();

            return $balance === null
                ? back()->with('info', "{$integration->label} does not report a balance.")
                : back()->with('success', "{$integration->label} balance: {$balance}");
        } catch (\Throwable $e) {
            return back()->with('error', 'Balance check failed: ' . $e->getMessage());
        }
    }

    public function setDefault(Integration $integration): RedirectResponse
    {
        $group = in_array($integration->provider, Integration::COURIER_PROVIDERS)
            ? 'courier'
            : (in_array($integration->provider, Integration::SMS_PROVIDERS)
                ? 'sms'
                : (in_array($integration->provider, Integration::AI_PROVIDERS)
                    ? 'ai'
                    : (in_array($integration->provider, Integration::FRAUD_PROVIDERS) ? 'fraud checker' : null)));

        if (! $group) {
            return back()->with('error', 'This integration does not support default selection.');
        }

        if (! $integration->is_active) {
            return back()->with('error', "Please activate {$integration->label} before setting it as default.");
        }

        $integration->setAsDefault();

        $redirect = $group === 'ai'
            ? route('admin.ai-settings.index')
            : route('admin.integrations.index');

        return redirect($redirect)
            ->with('success', "{$integration->label} is now the default {$group}.");
    }
}

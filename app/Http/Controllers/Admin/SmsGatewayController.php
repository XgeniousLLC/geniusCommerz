<?php

namespace App\Http\Controllers\Admin;

use App\Models\SiteSetting;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsGatewayController extends ProviderGroupController
{
    protected function group(): string
    {
        return 'sms';
    }

    public function index(SmsService $sms): View
    {
        return view('admin.sms.index', [
            'gateways'     => $this->cards(),
            'hasDefault'   => $sms->hasDefault(),
            'storeCountry' => SiteSetting::get('general.store_country', 'BD'),
        ]);
    }

    public function test(Request $request, string $provider, SmsService $sms): RedirectResponse
    {
        $data = $request->validate([
            'phone'   => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:160'],
        ]);

        [$definition] = $this->resolve($provider);

        try {
            // Goes through SmsService so the number is normalised exactly as it would be
            // for a real order, rather than passed through raw.
            $sent = $sms->driver($provider)->send(
                $sms->normalise($data['phone'], SiteSetting::get('general.store_country', 'BD')),
                $data['message'],
            );

            return back()->with(
                $sent ? 'success' : 'error',
                $sent ? "Test SMS sent via {$definition->label}." : "{$definition->label} returned failure — check the credentials.",
            );
        } catch (\Throwable $e) {
            return back()->with('error', "{$definition->label}: ".$e->getMessage());
        }
    }

    public function balance(string $provider, SmsService $sms): RedirectResponse
    {
        [$definition] = $this->resolve($provider);

        try {
            $balance = $sms->driver($provider)->balance();

            return $balance === null
                ? back()->with('info', "{$definition->label} does not report a balance.")
                : back()->with('success', "{$definition->label} balance: {$balance}");
        } catch (\Throwable $e) {
            return back()->with('error', "{$definition->label}: ".$e->getMessage());
        }
    }
}

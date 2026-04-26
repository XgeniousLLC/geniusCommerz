<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const TABS = ['general', 'meta', 'social', 'storefront', 'payment', 'shipping', 'cart', 'legal', 'tracking'];

    public function index(Request $request): View
    {
        $tab = in_array($request->get('tab'), self::TABS) ? $request->get('tab') : 'general';
        $settings = SiteSetting::group($tab)->get()->keyBy('key');

        return view('admin.settings.index', compact('tab', 'settings'));
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        if (! in_array($group, self::TABS)) {
            abort(404);
        }

        // Form fields use name="settings[full.key]" so PHP preserves the dot literally in the key.
        // If we used name="general.site_name", PHP would parse it as $_POST['general']['site_name'].
        $data = $request->input('settings', []);

        foreach ($data as $key => $value) {
            // Skip any unexpected PHP arrays (e.g. from malformed form submissions)
            if (is_array($value) && $key !== 'cart.goals_json') continue;

            // cart.goals_json is a JSON-encoded array — store it normalised as cart.goals
            if ($key === 'cart.goals_json') {
                $decoded = json_decode($value ?: '[]', true);
                $goals   = is_array($decoded) ? array_values($decoded) : [];
                $goalKey = 'cart.goals';
                $existing = SiteSetting::where('key', $goalKey)->first();
                if ($existing) {
                    $existing->value = json_encode($goals);
                    $existing->save();
                } else {
                    SiteSetting::create(['key' => $goalKey, 'value' => json_encode($goals), 'type' => 'text', 'group' => 'cart']);
                }
                continue;
            }

            $value    = ($value === '') ? null : $value;
            $existing = SiteSetting::where('key', $key)->first();

            if ($existing) {
                $existing->value = $value;
                $existing->save();
            } else {
                if ($value === null) continue;

                SiteSetting::create([
                    'key'   => $key,
                    'value' => $value,
                    'type'  => 'text',
                    'group' => $group,
                ]);
            }
        }

        cache()->forget('site_name');
        cache()->forget('site_logo_url');
        cache()->forget('site_favicon_url');
        cache()->forget('site_settings_storefront');
        cache()->forget('site_settings_payment');
        cache()->forget('site_settings_shipping');
        cache()->forget('site_settings_tracking');

        return redirect()->route('admin.settings.index', ['tab' => $group])
            ->with('success', 'Settings saved.');
    }
}

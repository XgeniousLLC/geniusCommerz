<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const TABS = ['general', 'meta', 'social', 'storefront', 'payment', 'shipping', 'legal'];

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

        return redirect()->route('admin.settings.index', ['tab' => $group])
            ->with('success', 'Settings saved.');
    }
}

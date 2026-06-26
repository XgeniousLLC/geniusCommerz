<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Services\CloudStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const TABS = ['general', 'meta', 'social', 'storefront', 'payment', 'shipping', 'cart', 'legal', 'tracking', 'feeds', 'currencies', 'accounting', 'storage', 'notifications'];

    public function index(Request $request): View
    {
        $tab = in_array($request->get('tab'), self::TABS) ? $request->get('tab') : 'general';
        $settings = SiteSetting::group($tab)->get()->keyBy('key');
        $currencies = $tab === 'currencies' ? Currency::orderByDesc('is_default')->orderBy('code')->get() : collect();
        $products   = $tab === 'storefront' ? Product::where('status', 'active')->orderBy('name')->get(['id', 'name']) : collect();
        $pages      = $tab === 'legal' ? \App\Models\Page::orderBy('title')->get(['id', 'title', 'slug']) : collect();

        return view('admin.settings.index', compact('tab', 'settings', 'currencies', 'products', 'pages'));
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
            if (is_array($value) && !in_array($key, ['cart.goals_json', 'storefront.sale_alert_product_ids'])) continue;

            // storefront.sale_alert_product_ids is a checkbox array — store as JSON
            if ($key === 'storefront.sale_alert_product_ids') {
                $ids = is_array($value) ? array_map('intval', $value) : [];
                $existing = SiteSetting::where('key', $key)->first();
                if ($existing) {
                    $existing->value = json_encode($ids);
                    $existing->save();
                } else {
                    SiteSetting::create(['key' => $key, 'value' => json_encode($ids), 'type' => 'text', 'group' => 'storefront']);
                }
                continue;
            }

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

            // Don't overwrite existing secrets with blank (password field left empty)
            if ($value === '' && in_array($key, ['storage.s3_secret', 'storage.r2_secret'])) {
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

        if ($group === 'storage') {
            CloudStorageService::flushCache();
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocialProofController extends Controller
{
    public function index(): View
    {
        $settings = SiteSetting::whereIn('key', [
            'storefront.visitor_counter_enabled',
            'storefront.visitor_counter_min',
            'storefront.visitor_counter_max',
            'storefront.sale_alert_enabled',
            'storefront.sale_alert_interval_min',
            'storefront.sale_alert_interval_max',
            'storefront.sale_alert_product_ids',
        ])->get()->keyBy('key');

        $saleAlertProductIds = json_decode(
            $settings->get('storefront.sale_alert_product_ids')?->value ?? '[]',
            true
        ) ?: [];

        $products = Product::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('admin.social-proof.index', compact('settings', 'saleAlertProductIds', 'products'));
    }

    public function update(Request $request): RedirectResponse
    {
        $keys = [
            'storefront.visitor_counter_enabled',
            'storefront.visitor_counter_min',
            'storefront.visitor_counter_max',
            'storefront.sale_alert_enabled',
            'storefront.sale_alert_interval_min',
            'storefront.sale_alert_interval_max',
        ];

        $data = $request->input('settings', []);

        foreach ($keys as $key) {
            $value = $data[$key] ?? null;
            $value = ($value === '') ? null : $value;

            $existing = SiteSetting::where('key', $key)->first();
            if ($existing) {
                $existing->value = $value;
                $existing->save();
            } elseif ($value !== null) {
                SiteSetting::create(['key' => $key, 'value' => $value, 'type' => 'text', 'group' => 'storefront']);
            }
        }

        // Handle product IDs checkbox array
        $ids      = $request->input('sale_alert_product_ids', []);
        $ids      = is_array($ids) ? array_map('intval', $ids) : [];
        $existing = SiteSetting::where('key', 'storefront.sale_alert_product_ids')->first();
        if ($existing) {
            $existing->value = json_encode($ids);
            $existing->save();
        } else {
            SiteSetting::create([
                'key'   => 'storefront.sale_alert_product_ids',
                'value' => json_encode($ids),
                'type'  => 'text',
                'group' => 'storefront',
            ]);
        }

        return back()->with('success', 'Social proof settings saved.');
    }
}

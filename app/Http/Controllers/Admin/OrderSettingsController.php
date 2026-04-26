<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderSettingsController extends Controller
{
    public function index(): View
    {
        $settings = SiteSetting::where('group', 'orders')
            ->orWhere('key', 'general.order_prefix')
            ->get()
            ->keyBy('key');

        return view('admin.order-settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        // Form uses name="settings[order.xxx]" — PHP preserves the dot inside brackets
        $data = $request->input('settings', []);

        $rules = [
            'settings.order\.number_format' => 'required|string|max:100',
            'settings.order\.serial_digits' => 'required|integer|min:1|max:8',
            'settings.order\.serial_reset'  => 'required|in:daily,monthly,yearly,never',
            'settings.order\.invoice_note'  => 'nullable|string|max:1000',
        ];

        $meta = [
            'order.number_format'  => ['type' => 'text',   'group' => 'orders'],
            'order.serial_digits'  => ['type' => 'number', 'group' => 'orders'],
            'order.serial_reset'   => ['type' => 'text',   'group' => 'orders'],
            'order.invoice_note'   => ['type' => 'text',   'group' => 'orders'],
            'general.order_prefix' => ['type' => 'text',   'group' => 'general'],
        ];

        foreach ($data as $key => $value) {
            if (! array_key_exists($key, $meta)) continue;
            SiteSetting::set($key, $value === '' ? null : $value, $meta[$key], 'orders');
        }

        return redirect()->route('admin.order-settings.index')
            ->with('success', 'Order settings saved.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\SiteSetting;
use App\Support\Countries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShippingZoneController extends Controller
{
    public function index(): View
    {
        return view('admin.shipping.index', [
            'zones'        => ShippingZone::with('rates')->orderBy('country')->orderBy('priority')->get(),
            'countries'    => Countries::options(),
            'storeCountry' => SiteSetting::get('general.store_country', 'BD'),
            'flatRate'     => (float) SiteSetting::get('shipping.flat_rate', 60),
            'currency'     => SiteSetting::get('general.currency', 'BDT'),
            'origin'       => \App\Services\CarrierService::originAddress(),
            'hasCarrier'   => app(\App\Services\CarrierService::class)->hasDefault(),
            'presets'      => \App\Support\ShippingPresets::all(),
        ]);
    }

    /**
     * Seed a region's zones and weight bands in one action.
     *
     * Prices are placeholders in the store currency — the preset saves the structural
     * work, not the pricing decision. Existing zones for the same country are skipped.
     */
    public function applyPreset(Request $request): RedirectResponse
    {
        $preset = \App\Support\ShippingPresets::find((string) $request->input('preset'))
            ?? abort(404, 'Unknown preset.');

        $created = $skipped = 0;

        foreach ($preset['zones'] as $blueprint) {
            if (ShippingZone::where('country', $blueprint['country'])->whereNull('state')->exists()) {
                $skipped++;

                continue;
            }

            $zone = ShippingZone::create([
                'name'      => $blueprint['name'],
                'country'   => $blueprint['country'],
                'priority'  => $blueprint['priority'] ?? 0,
                'is_active' => true,
            ]);

            foreach ($blueprint['rates'] as $rate) {
                $zone->rates()->create($rate + ['is_active' => true]);
            }

            $created++;
        }

        return back()->with(
            'success',
            "{$preset['label']}: {$created} zones added"
                .($skipped ? ", {$skipped} skipped because a zone already existed." : '.')
                .' Adjust the placeholder prices to your own carrier rates.',
        );
    }

    /**
     * The address parcels ship from. A live carrier cannot produce a rate without it,
     * so it is edited here beside the zones rather than buried in general settings.
     */
    public function updateOrigin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'origin_name'    => ['nullable', 'string', 'max:120'],
            'origin_street'  => ['nullable', 'string', 'max:200'],
            'origin_city'    => ['nullable', 'string', 'max:100'],
            'origin_state'   => ['nullable', 'string', 'max:100'],
            'origin_postal'  => ['nullable', 'string', 'max:20'],
            'origin_country' => ['required', 'string', 'size:2', Rule::in(array_keys(Countries::all()))],
        ]);

        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(
                ['key' => "shipping.{$key}"],
                ['value' => (string) $value, 'type' => 'text', 'group' => 'shipping'],
            );
        }

        cache()->forget('site_settings_shipping');

        return back()->with('success', 'Shipping origin updated.');
    }

    public function store(Request $request): RedirectResponse
    {
        ShippingZone::create($request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'country'        => ['required', 'string', 'size:2', Rule::in(array_keys(Countries::all()))],
            'state'          => ['nullable', 'string', 'max:100'],
            'postal_pattern' => ['nullable', 'string', 'max:40'],
            'priority'       => ['nullable', 'integer', 'min:0'],
        ]) + ['is_active' => true]);

        return back()->with('success', 'Shipping zone added.');
    }

    public function destroy(ShippingZone $zone): RedirectResponse
    {
        $zone->delete();

        return back()->with('success', 'Shipping zone removed.');
    }

    public function storeRate(Request $request, ShippingZone $zone): RedirectResponse
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:60'],
            'price'             => ['required', 'numeric', 'min:0'],
            'per_kg'            => ['nullable', 'numeric', 'min:0'],
            'min_weight'        => ['nullable', 'numeric', 'min:0'],
            'max_weight'        => ['nullable', 'numeric', 'min:0', 'gt:min_weight'],
            'min_subtotal'      => ['nullable', 'numeric', 'min:0'],
            'max_subtotal'      => ['nullable', 'numeric', 'min:0', 'gt:min_subtotal'],
            'free_above'        => ['nullable', 'numeric', 'min:0'],
            'delivery_estimate' => ['nullable', 'string', 'max:60'],
            'priority'          => ['nullable', 'integer', 'min:0'],
        ]);

        $zone->rates()->create($data + ['is_active' => true]);

        return back()->with('success', 'Rate added.');
    }

    public function destroyRate(ShippingRate $rate): RedirectResponse
    {
        $rate->delete();

        return back()->with('success', 'Rate removed.');
    }
}

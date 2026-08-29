<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\TaxRate;
use App\Models\TaxZone;
use App\Support\Countries;
use App\Tax\TaxCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaxZoneController extends Controller
{
    public function index(TaxCalculator $tax): View
    {
        return view('admin.tax.index', [
            'zones'        => TaxZone::with('rates')->orderBy('country')->orderBy('priority')->get(),
            'countries'    => Countries::options(),
            'classes'      => TaxRate::CLASSES,
            'taxEnabled'   => $tax->enabled(),
            'inclusive'    => $tax->pricesIncludeTax(),
            'storeCountry' => SiteSetting::get('general.store_country', 'BD'),
            'presets'      => \App\Support\TaxPresets::all(),
        ]);
    }

    /**
     * Seed a whole region's zones in one action.
     *
     * Existing zones for the same country and state are left alone rather than
     * overwritten, so a preset can be re-applied later without duplicating anything or
     * discarding rates the merchant has since tuned.
     */
    public function applyPreset(Request $request): RedirectResponse
    {
        $preset = \App\Support\TaxPresets::find((string) $request->input('preset'))
            ?? abort(404, 'Unknown preset.');

        $created = $skipped = 0;

        foreach ($preset['zones'] as $blueprint) {
            $exists = TaxZone::where('country', $blueprint['country'])
                ->where('state', $blueprint['state'] ?? null)
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            $zone = TaxZone::create([
                'name'      => $blueprint['name'],
                'country'   => $blueprint['country'],
                'state'     => $blueprint['state'] ?? null,
                'priority'  => 0,
                'is_active' => true,
            ]);

            foreach ($blueprint['rates'] as $index => $rate) {
                $zone->rates()->create($rate + ['applies_to_shipping' => true, 'priority' => $index]);
            }

            $created++;
        }

        return back()->with(
            'success',
            "{$preset['label']}: {$created} zones added"
                .($skipped ? ", {$skipped} skipped because a zone already existed." : '.')
                .' Check the rates against your own registrations before going live.',
        );
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        foreach (['tax.enabled', 'accounting.prices_include_tax'] as $key) {
            SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $request->boolean(str_replace('.', '_', $key)) ? '1' : '0',
                    'type'  => 'boolean',
                    'group' => explode('.', $key)[0],
                ],
            );
        }

        cache()->forget('site_settings_accounting');
        cache()->forget('site_settings_tax');

        return back()->with('success', 'Tax settings updated.');
    }

    public function store(Request $request): RedirectResponse
    {
        TaxZone::create($this->validated($request));

        return back()->with('success', 'Tax zone added.');
    }

    public function update(Request $request, TaxZone $zone): RedirectResponse
    {
        $zone->update($this->validated($request));

        return back()->with('success', 'Tax zone updated.');
    }

    public function destroy(TaxZone $zone): RedirectResponse
    {
        $zone->delete();

        return back()->with('success', 'Tax zone removed.');
    }

    public function storeRate(Request $request, TaxZone $zone): RedirectResponse
    {
        $zone->rates()->create($request->validate([
            'name'                => ['required', 'string', 'max:60'],
            'tax_class'           => ['required', Rule::in(TaxRate::CLASSES)],
            'rate'                => ['required', 'numeric', 'min:0', 'max:100'],
            'applies_to_shipping' => ['boolean'],
            'priority'            => ['nullable', 'integer', 'min:0'],
        ]) + ['applies_to_shipping' => $request->boolean('applies_to_shipping')]);

        return back()->with('success', 'Rate added.');
    }

    public function destroyRate(TaxRate $rate): RedirectResponse
    {
        $rate->delete();

        return back()->with('success', 'Rate removed.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'country'        => ['required', 'string', 'size:2', Rule::in(array_keys(Countries::all()))],
            'state'          => ['nullable', 'string', 'max:100'],
            // A SQL-style pattern, so a county can be matched by postal prefix.
            'postal_pattern' => ['nullable', 'string', 'max:40'],
            'priority'       => ['nullable', 'integer', 'min:0'],
            'is_active'      => ['boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}

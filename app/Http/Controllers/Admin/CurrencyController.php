<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    public function index(): View
    {
        $currencies = Currency::orderByDesc('is_default')->orderBy('code')->get();
        $registry = app(\App\Integrations\ProviderRegistry::class);
        $fxRows   = \App\Models\Integration::where('group', 'fx')->get()->keyBy('provider');
        $fxSources = [];
        foreach ($registry->group('fx') as $slug => $definition) {
            $fxSources[] = [
                'definition' => $definition,
                'row'        => $fxRows->get($slug) ?? \App\Models\Integration::forSlug($slug),
            ];
        }

        return view('admin.currencies.index', compact('currencies'))->with('fxSources', $fxSources);
    
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code'   => ['required', 'string', 'max:10', 'unique:currencies,code', 'regex:/^[A-Z0-9]+$/'],
            'symbol' => 'required|string|max:10',
            'name'   => 'required|string|max:60',
            'rate'   => 'required|numeric|min:0.000001',
        ]);

        $data['code'] = strtoupper($data['code']);
        Currency::create($data + ['is_active' => true]);
        return redirect()->route('admin.currencies.index')->with('success', 'Currency added.');
    }

    public function update(Request $request, Currency $currency): RedirectResponse
    {
        $data = $request->validate([
            'symbol'    => 'required|string|max:10',
            'name'      => 'required|string|max:60',
            'rate'        => 'required|numeric|min:0.000001',
            'rate_source' => 'nullable|in:manual,api',
            'is_active'   => 'boolean',
        ]);

        $currency->update([
            'symbol'      => $data['symbol'],
            'name'        => $data['name'],
            'rate'        => $data['rate'],
            'rate_source' => $data['rate_source'] ?? 'manual',
            // A hand-edited rate is a deliberate override, so stamp it as fresh.
            'rate_updated_at' => now(),
            'is_active'   => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.currencies.index')->with('success', "Currency {$currency->code} updated.");
    }

    public function destroy(Currency $currency): RedirectResponse
    {
        if ($currency->is_default) {
            return back()->with('error', 'Cannot delete the default currency.');
        }
        $currency->delete();
        return redirect()->route('admin.currencies.index')->with('success', 'Currency deleted.');
    }

    public function setDefault(Currency $currency): RedirectResponse
    {
        if (! $currency->is_active) {
            return back()->with('error', "Activate {$currency->code} before setting it as default.");
        }
        $currency->setAsDefault();
        return redirect()->route('admin.currencies.index')->with('success', "{$currency->code} is now the default currency.");
    }
}

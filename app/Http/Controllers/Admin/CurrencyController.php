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
        return view('admin.currencies.index', compact('currencies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code'   => 'required|string|max:10|unique:currencies,code|alpha_upper',
            'symbol' => 'required|string|max:10',
            'name'   => 'required|string|max:60',
            'rate'   => 'required|numeric|min:0.000001',
        ]);

        Currency::create($data + ['is_active' => true]);
        return redirect()->route('admin.currencies.index')->with('success', 'Currency added.');
    }

    public function update(Request $request, Currency $currency): RedirectResponse
    {
        $data = $request->validate([
            'symbol'    => 'required|string|max:10',
            'name'      => 'required|string|max:60',
            'rate'      => 'required|numeric|min:0.000001',
            'is_active' => 'boolean',
        ]);

        $currency->update([
            'symbol'    => $data['symbol'],
            'name'      => $data['name'],
            'rate'      => $data['rate'],
            'is_active' => $request->boolean('is_active'),
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

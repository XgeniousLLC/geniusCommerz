<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdSpend;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdSpendController extends Controller
{
    public function index(): View
    {
        $spends   = AdSpend::with('product')->orderByDesc('spend_date')->paginate(30);
        $products = Product::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $monthTotal = AdSpend::whereMonth('spend_date', now()->month)->whereYear('spend_date', now()->year)->sum('amount');
        $allTotal   = AdSpend::sum('amount');
        $byPlatform = AdSpend::selectRaw('platform, SUM(amount) as total')->groupBy('platform')->pluck('total', 'platform');
        return view('admin.accounting.ad-spend.index', compact('spends', 'products', 'monthTotal', 'allTotal', 'byPlatform'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'platform'      => 'required|in:meta,google,tiktok,youtube,other',
            'campaign_name' => 'nullable|string|max:255',
            'amount'        => 'required|numeric|min:0',
            'spend_date'    => 'required|date',
            'product_id'    => 'nullable|integer|exists:products,id',
            'notes'         => 'nullable|string|max:1000',
        ]);
        AdSpend::create($data);
        return redirect()->route('admin.accounting.ad-spend.index')->with('success', 'Ad spend recorded.');
    }

    public function destroy(AdSpend $adSpend): RedirectResponse
    {
        $adSpend->delete();
        return redirect()->route('admin.accounting.ad-spend.index')->with('success', 'Record deleted.');
    }
}

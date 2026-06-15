<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SourcingItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SourcingController extends Controller
{
    public function index(): View
    {
        $items        = SourcingItem::with('product')->orderByDesc('created_at')->get();
        $totalCount   = $items->count();
        $listedCount  = $items->where('status', 'listed')->count();
        $withMargin   = $items->filter(fn ($i) => $i->margin() !== null);
        $avgMargin    = $withMargin->count()
            ? round($withMargin->avg(fn ($i) => $i->margin()), 1)
            : 0;
        $capitalInvested = $items->sum(fn ($i) => $i->cost_price * $i->moq);

        return view('admin.sourcing.index', compact(
            'items', 'totalCount', 'listedCount', 'avgMargin', 'capitalInvested'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_name' => 'required|string|max:255',
            'supplier'     => 'required|string|max:255',
            'cost_price'   => 'required|numeric|min:0',
            'sell_price'   => 'nullable|numeric|min:0',
            'moq'          => 'required|integer|min:1',
            'lead_days'    => 'required|integer|min:0',
            'status'       => 'required|in:sourced,listed',
        ]);

        SourcingItem::create($data);

        return redirect()->route('admin.sourcing.index')->with('success', 'Sourcing item added.');
    }

    public function update(Request $request, SourcingItem $sourcing): RedirectResponse
    {
        $data = $request->validate([
            'sell_price' => 'nullable|numeric|min:0',
            'status'     => 'nullable|in:sourced,listed',
        ]);

        $sourcing->update(array_filter($data, fn ($v) => $v !== null));

        return redirect()->route('admin.sourcing.index')->with('success', 'Item updated.');
    }

    public function destroy(SourcingItem $sourcing): RedirectResponse
    {
        $sourcing->delete();

        return redirect()->route('admin.sourcing.index')->with('success', 'Item deleted.');
    }
}

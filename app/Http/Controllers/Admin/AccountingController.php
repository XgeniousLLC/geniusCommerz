<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdSpend;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseShipment;
use App\Models\SiteSetting;
use Illuminate\View\View;

class AccountingController extends Controller
{
    public function index(): View
    {
        // Summary cards
        $totalPurchaseCost  = PurchaseOrderItem::sum(\DB::raw('unit_cost * quantity'));
        $totalShipmentCost  = PurchaseShipment::sum('amount');
        $totalAdSpend       = AdSpend::sum('amount');
        $totalAdSpendMonth  = AdSpend::whereMonth('spend_date', now()->month)->whereYear('spend_date', now()->year)->sum('amount');

        $recentPurchases = PurchaseOrder::with('items')->orderByDesc('order_date')->limit(5)->get();
        $recentAdSpends  = AdSpend::with('product')->orderByDesc('spend_date')->limit(5)->get();

        // Margin suggestions: products with purchase cost data
        $costByProduct = PurchaseOrderItem::selectRaw('product_id, SUM(unit_cost * quantity) / SUM(quantity) as avg_unit_cost, SUM(quantity) as total_qty')
            ->groupBy('product_id')
            ->with('product:id,name,slug,price')
            ->get();

        // Per-order average shipment per unit
        $avgShipmentPerUnit = 0;
        $totalUnits = PurchaseOrderItem::sum('quantity');
        if ($totalUnits > 0) {
            $avgShipmentPerUnit = $totalShipmentCost / $totalUnits;
        }

        $adSpendPerUnit = (float) (SiteSetting::get('accounting.ad_spend_per_unit', 0) ?? 0);

        $suggestions = $costByProduct->map(function ($row) use ($avgShipmentPerUnit, $adSpendPerUnit) {
            $landedCost      = (float) $row->avg_unit_cost + $avgShipmentPerUnit + $adSpendPerUnit;
            $suggestedPrice  = $landedCost > 0 ? round($landedCost / 0.60, 2) : 0; // 40% gross margin
            $currentPrice    = (float) ($row->product?->price ?? 0);
            $currentMargin   = $currentPrice > 0 && $landedCost > 0
                ? round((1 - $landedCost / $currentPrice) * 100, 1)
                : null;

            return [
                'product_id'      => $row->product_id,
                'product_name'    => $row->product?->name ?? 'Deleted product',
                'avg_unit_cost'   => round((float) $row->avg_unit_cost, 2),
                'shipment_cost'   => round($avgShipmentPerUnit, 2),
                'ad_spend_unit'   => round($adSpendPerUnit, 2),
                'landed_cost'     => round($landedCost, 2),
                'current_price'   => $currentPrice,
                'suggested_price' => $suggestedPrice,
                'current_margin'  => $currentMargin,
                'total_qty'       => (int) $row->total_qty,
            ];
        })->filter(fn ($r) => $r['landed_cost'] > 0)->sortBy('product_name')->values();

        return view('admin.accounting.index', compact(
            'totalPurchaseCost', 'totalShipmentCost', 'totalAdSpend', 'totalAdSpendMonth',
            'recentPurchases', 'recentAdSpends', 'suggestions', 'adSpendPerUnit'
        ));
    }
}

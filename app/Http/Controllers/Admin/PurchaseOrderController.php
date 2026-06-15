<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseShipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = PurchaseOrder::withCount('items')->orderByDesc('order_date');
        if ($request->filled('status') && in_array($request->status, ['draft','ordered','partial','received'])) {
            $query->where('status', $request->status);
        }
        $orders = $query->paginate(20);
        return view('admin.accounting.purchases.index', compact('orders'));
    }

    public function create(): View
    {
        $products = Product::where('status', 'active')
            ->with('variants.variantValues.attributeValue.attribute')
            ->orderBy('name')->get();
        return view('admin.accounting.purchases.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_name'            => 'required|string|max:255',
            'order_date'               => 'required|date',
            'notes'                    => 'nullable|string|max:2000',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|integer|exists:products,id',
            'items.*.variant_id'       => 'nullable|integer|exists:product_variants,id',
            'items.*.quantity'         => 'required|integer|min:1',
            'items.*.unit_cost'        => 'required|numeric|min:0',
            'shipments'                => 'nullable|array',
            'shipments.*.description'  => 'required_with:shipments|string|max:255',
            'shipments.*.amount'       => 'required_with:shipments|numeric|min:0',
            'shipments.*.shipment_date'=> 'required_with:shipments|date',
        ]);

        $order = PurchaseOrder::create([
            'reference'     => PurchaseOrder::nextReference(),
            'supplier_name' => $data['supplier_name'],
            'order_date'    => $data['order_date'],
            'notes'         => $data['notes'] ?? null,
            'status'        => 'ordered',
        ]);

        foreach ($data['items'] as $row) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'product_id'        => $row['product_id'],
                'variant_id'        => $row['variant_id'] ?: null,
                'quantity'          => $row['quantity'],
                'unit_cost'         => $row['unit_cost'],
                'received_qty'      => 0,
            ]);
        }

        foreach ($data['shipments'] ?? [] as $s) {
            PurchaseShipment::create([
                'purchase_order_id' => $order->id,
                'description'       => $s['description'],
                'amount'            => $s['amount'],
                'shipment_date'     => $s['shipment_date'],
            ]);
        }

        return redirect()->route('admin.accounting.purchases.show', $order)->with('success', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchase): View
    {
        $purchase->load(['items.product', 'items.variant', 'shipments']);
        return view('admin.accounting.purchases.show', compact('purchase'));
    }

    public function receive(Request $request, PurchaseOrder $purchase): RedirectResponse
    {
        $data = $request->validate([
            'items'                  => 'required|array',
            'items.*.id'             => 'required|integer',
            'items.*.received_qty'   => 'required|integer|min:0',
        ]);

        foreach ($data['items'] as $row) {
            $item = PurchaseOrderItem::where('id', $row['id'])->where('purchase_order_id', $purchase->id)->first();
            if (! $item) continue;
            $item->update(['received_qty' => min((int) $row['received_qty'], $item->quantity)]);
        }

        // Update status
        $purchase->load('items');
        $allReceived     = $purchase->items->every(fn ($i) => $i->received_qty >= $i->quantity);
        $anyReceived     = $purchase->items->some(fn ($i) => $i->received_qty > 0);
        $purchase->update(['status' => $allReceived ? 'received' : ($anyReceived ? 'partial' : 'ordered')]);

        return redirect()->route('admin.accounting.purchases.show', $purchase)->with('success', 'Receiving updated.');
    }

    public function destroy(PurchaseOrder $purchase): RedirectResponse
    {
        $purchase->delete();
        return redirect()->route('admin.accounting.purchases.index')->with('success', 'Purchase order deleted.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderTrackingController extends Controller
{
    public function show(Request $request): Response
    {
        $order    = null;
        $searched = false;

        if ($request->filled('order_number')) {
            $searched = true;
            $order = Order::with(['items', 'activities'])
                ->where('order_number', strtoupper(trim($request->input('order_number'))))
                ->when($request->filled('phone'), fn($q) =>
                    $q->where('customer_phone', $request->input('phone'))
                )
                ->first();
        }

        return Inertia::render('Track', [
            'order'       => $order ? $this->orderData($order) : null,
            'searched'    => $searched,
            'orderNumber' => $request->input('order_number', ''),
            'phone'       => $request->input('phone', ''),
        ]);
    }

    private function orderData($order): array
    {
        return [
            'id'               => $order->id,
            'order_number'     => $order->order_number,
            'status'           => $order->status,
            'payment_status'   => $order->payment_status,
            'customer_name'    => $order->customer_name,
            'customer_phone'   => $order->customer_phone,
            'customer_email'   => $order->customer_email,
            'shipping_address' => $order->shipping_address,
            'subtotal'         => $order->subtotal,
            'shipping_charge'  => $order->shipping_charge,
            'discount'         => $order->discount ?? 0,
            'total'            => $order->total,
            'tracking_number'  => $order->tracking_number,
            'notes'            => $order->notes,
            'created_at'       => $order->created_at->format('d M Y, h:i A'),
            'items' => $order->items->map(fn($i) => [
                'id'           => $i->id,
                'product_name' => $i->product_name,
                'quantity'     => $i->quantity,
                'unit_price'   => $i->unit_price,
                'total_price'  => $i->total_price,
            ])->all(),
            'activities' => $order->activities->map(fn($a) => [
                'id'          => $a->id,
                'description' => $a->description,
                'created_at'  => $a->created_at->format('d M Y, h:i A'),
            ])->all(),
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\OrderConfirmed;
use App\Services\LoyaltyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(): Response
    {
        $shippingCost = (float) SiteSetting::get('shipping.flat_rate', 60);
        $freeAbove    = (float) SiteSetting::get('shipping.free_above', 0);
        $paymentMethods = [
            'cod'    => 'Cash on Delivery',
            'bkash'  => 'bKash',
            'nagad'  => 'Nagad',
            'rocket' => 'Rocket',
        ];

        return Inertia::render('Checkout', compact('shippingCost', 'freeAbove', 'paymentMethods'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:50',
            'customer_email'   => 'nullable|email|max:255',
            'address'          => 'required|string|max:500',
            'city'             => 'required|string|max:100',
            'notes'            => 'nullable|string|max:1000',
            'payment_method'   => 'required|string|max:50',
            'coupon_code'      => 'nullable|string|max:100',
            'items'            => 'required|array|min:1',
            'items.*.product_id'    => 'required|integer|exists:products,id',
            'items.*.variant_id'    => 'nullable|integer|exists:product_variants,id',
            'items.*.name'          => 'required|string|max:255',
            'items.*.sku'           => 'nullable|string|max:100',
            'items.*.variant_label' => 'nullable|string|max:255',
            'items.*.price'         => 'required|numeric|min:0',
            'items.*.quantity'      => 'required|integer|min:1',
        ]);

        // Verify prices against DB to prevent client-side manipulation
        $items = [];
        $subtotal = 0;
        foreach ($data['items'] as $row) {
            $price = $this->resolvePrice($row);
            $total = $price * (int) $row['quantity'];
            $subtotal += $total;
            $items[] = array_merge($row, ['price' => $price, 'total' => $total]);
        }

        // Resolve coupon
        $coupon = null;
        $discountAmount = 0;
        if (! empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper($data['coupon_code']))->first();
            if ($coupon && $coupon->isValid($subtotal, auth()->id())) {
                $productIds = array_column($data['items'], 'product_id');
                if ($coupon->appliesToProducts($productIds)) {
                    $discountAmount = $coupon->computeDiscount($subtotal);
                }
            }
        }

        // Shipping — free if every product has shipping included
        $productIds    = array_column($data['items'], 'product_id');
        $allIncluded   = Product::whereIn('id', $productIds)->where('shipping_included', false)->doesntExist();
        $shippingCost  = $allIncluded ? 0 : (float) SiteSetting::get('shipping.flat_rate', 60);
        $freeAbove     = (float) SiteSetting::get('shipping.free_above', 0);
        if (! $allIncluded && $freeAbove > 0 && $subtotal >= $freeAbove) {
            $shippingCost = 0;
        }

        $total = max(0, $subtotal + $shippingCost - $discountAmount);

        // Resolve/create user
        $userId = auth()->id();
        if (! $userId && $data['customer_email']) {
            $user = User::firstOrCreate(
                ['email' => $data['customer_email']],
                [
                    'name'      => $data['customer_name'],
                    'phone'     => $data['customer_phone'],
                    'password'  => Str::random(16),
                    'is_active' => true,
                ]
            );
            $userId = $user->id;
        }

        $order = Order::create([
            'order_number'    => Order::generateOrderNumber(),
            'source'          => 'website',
            'user_id'         => $userId,
            'customer_name'   => $data['customer_name'],
            'customer_email'  => $data['customer_email'],
            'customer_phone'  => $data['customer_phone'],
            'shipping_address'=> [
                'address' => $data['address'],
                'city'    => $data['city'],
            ],
            'coupon_id'       => $coupon?->id,
            'coupon_code'     => $coupon?->code,
            'discount_amount' => $discountAmount,
            'subtotal'        => $subtotal,
            'shipping_cost'   => $shippingCost,
            'total'           => $total,
            'payment_method'  => $data['payment_method'],
            'payment_status'  => 'unpaid',
            'status'          => 'pending',
            'notes'           => $data['notes'] ?? null,
        ]);

        foreach ($items as $item) {
            $order->items()->create([
                'product_id'    => $item['product_id'],
                'variant_id'    => $item['variant_id'] ?? null,
                'product_name'  => $item['name'],
                'sku'           => $item['sku'] ?? null,
                'variant_label' => $item['variant_label'] ?? null,
                'unit_price'    => $item['price'],
                'quantity'      => $item['quantity'],
                'total'         => $item['total'],
            ]);
        }

        if ($coupon) {
            $coupon->increment('usage_count');
        }

        $order->logActivity('created', 'Order placed', 'Order placed via website.');

        if ($order->user_id) {
            app(LoyaltyService::class)->earnPoints($order);
        }

        // Send confirmation notification
        if ($order->customer_email) {
            $notifiable = $order->user ?? (new \App\Models\User)->forceFill([
                'name'  => $order->customer_name,
                'email' => $order->customer_email,
            ]);
            try { $notifiable->notify(new OrderConfirmed($order)); } catch (\Throwable) {}
        }

        return redirect()->route('order.confirm', $order->order_number)
            ->with('order_placed', true);
    }

    private function resolvePrice(array $row): float
    {
        if (! empty($row['variant_id'])) {
            $variant = ProductVariant::find($row['variant_id']);
            if ($variant) return (float) $variant->price;
        }

        $product = Product::find($row['product_id']);
        if ($product) return (float) $product->price;

        return (float) $row['price'];
    }
}

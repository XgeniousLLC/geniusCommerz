<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\OrderConfirmed;
use App\Services\CourierService;
use App\Services\LoyaltyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(): Response
    {
        $shippingCost = (float) SiteSetting::get('shipping.flat_rate', 60);
        $freeAbove    = (float) SiteSetting::get('shipping.free_above', 0);

        $allMethods = [
            'cod'        => ['label' => 'Cash on Delivery', 'key' => 'payment.cod_enabled'],
            'bkash'      => ['label' => 'bKash',            'key' => 'payment.bkash_enabled'],
            'nagad'      => ['label' => 'Nagad',            'key' => 'payment.nagad_enabled'],
            'rocket'     => ['label' => 'Rocket',           'key' => 'payment.rocket_enabled'],
            'sslcommerz' => ['label' => 'Card / Net Banking','key' => 'payment.sslcommerz_enabled'],
            'stripe'     => ['label' => 'Credit / Debit Card','key' => 'payment.stripe_enabled'],
        ];

        $paymentMethods = [];
        foreach ($allMethods as $value => $meta) {
            if (SiteSetting::get($meta['key'])) {
                $paymentMethods[$value] = $meta['label'];
            }
        }

        // Always fall back to COD if nothing is enabled
        if (empty($paymentMethods)) {
            $paymentMethods['cod'] = 'Cash on Delivery';
        }

        $loyaltyEnabled = (bool) SiteSetting::get('loyalty.enabled', false);
        $loyaltyBalance = 0;
        $loyaltyTaka    = 0;

        if ($loyaltyEnabled && auth()->check()) {
            $service        = app(LoyaltyService::class);
            $loyaltyBalance = $service->getBalance(auth()->user());
            $loyaltyTaka    = $service->pointsToTaka($loyaltyBalance);
        }

        $courierService         = app(CourierService::class);
        $courierLocationEnabled = $courierService->hasDefault()
            && (bool) SiteSetting::get('shipping.courier_location_charges', false);

        return Inertia::render('Checkout', compact(
            'shippingCost', 'freeAbove', 'paymentMethods',
            'loyaltyEnabled', 'loyaltyBalance', 'loyaltyTaka',
            'courierLocationEnabled'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name'         => 'required|string|max:255',
            'customer_phone'        => 'required|string|max:50',
            'customer_email'        => 'nullable|email|max:255',
            'address'               => 'required|string|max:500',
            'city'                  => 'required|string|max:100',
            'notes'                 => 'nullable|string|max:1000',
            'payment_method'        => 'required|string|max:50',
            'coupon_code'           => 'nullable|string|max:100',
            'loyalty_points_redeem' => 'nullable|integer|min:0',
            'items'                 => 'required|array|min:1',
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

        // Resolve loyalty points redemption
        $loyaltyService      = app(LoyaltyService::class);
        $loyaltyDiscount     = 0;
        $loyaltyPointsUsed   = 0;
        $pointsToRedeem      = (int) ($data['loyalty_points_redeem'] ?? 0);

        if ($pointsToRedeem > 0 && auth()->check()) {
            $user = auth()->user();
            if ($loyaltyService->canRedeem($user, $pointsToRedeem)) {
                $loyaltyDiscount   = $loyaltyService->pointsToTaka($pointsToRedeem);
                $loyaltyPointsUsed = $pointsToRedeem;
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

        $total = max(0, $subtotal + $shippingCost - $discountAmount - $loyaltyDiscount);

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
            'discount_amount' => $discountAmount + $loyaltyDiscount,
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
            $loyaltyService->earnPoints($order);

            // Deduct redeemed points
            if ($loyaltyPointsUsed > 0) {
                $loyaltyService->redeemPoints(
                    User::find($order->user_id),
                    $loyaltyPointsUsed,
                    $order
                );
            }
        }

        // Send confirmation notification
        if ($order->customer_email) {
            $notifiable = $order->user ?? (new \App\Models\User)->forceFill([
                'name'  => $order->customer_name,
                'email' => $order->customer_email,
            ]);
            try { $notifiable->notify(new OrderConfirmed($order)); } catch (\Throwable) {}
        }

        // Server-side tracking
        $order->load('items');
        $this->trackPurchase($order, $request);

        return redirect()->route('order.confirm', $order->order_number)
            ->with('order_placed', true);
    }

    private function trackPurchase(Order $order, Request $request): void
    {
        $pixelId   = SiteSetting::get('tracking.meta_pixel_id');
        $capiToken = SiteSetting::get('tracking.meta_capi_token');
        $ga4MId    = SiteSetting::get('tracking.ga4_measurement_id');
        $ga4Secret = SiteSetting::get('tracking.ga4_api_secret');

        $orderItems = $order->items->map(fn($i) => [
            'id'         => (string) $i->product_id,
            'item_name'  => $i->product_name,
            'quantity'   => $i->quantity,
            'price'      => (float) $i->unit_price,
        ])->all();

        // Meta CAPI — Purchase event
        if ($pixelId && $capiToken) {
            try {
                $userData = [];
                if ($order->customer_email) {
                    $userData['em'] = [hash('sha256', strtolower(trim($order->customer_email)))];
                }
                if ($order->customer_phone) {
                    $userData['ph'] = [hash('sha256', preg_replace('/\D/', '', $order->customer_phone))];
                }

                Http::timeout(5)->post(
                    "https://graph.facebook.com/v18.0/{$pixelId}/events?access_token={$capiToken}",
                    [
                        'data' => [[
                            'event_name'       => 'Purchase',
                            'event_time'       => time(),
                            'event_source_url' => $request->header('referer', config('app.url')),
                            'action_source'    => 'website',
                            'user_data'        => $userData,
                            'custom_data'      => [
                                'currency'    => 'BDT',
                                'value'       => (float) $order->total,
                                'order_id'    => $order->order_number,
                                'contents'    => array_map(fn($i) => [
                                    'id'         => $i['id'],
                                    'quantity'   => $i['quantity'],
                                    'item_price' => $i['price'],
                                ], $orderItems),
                                'content_type' => 'product',
                            ],
                        ]],
                    ]
                );
            } catch (\Throwable) {}
        }

        // TikTok Events API — Purchase event
        (new \App\Services\TikTokService())->sendPurchase(
            [
                'id'       => $order->id,
                'total'    => $order->total,
                'currency' => 'BDT',
                'items'    => $order->items->map(fn($i) => [
                    'product_id'   => $i->product_id,
                    'product_name' => $i->product_name,
                    'quantity'     => $i->quantity,
                    'price'        => (float) $i->unit_price,
                ])->all(),
            ],
            email:     $order->customer_email,
            phone:     $order->customer_phone,
            ip:        $request->ip(),
            userAgent: $request->userAgent(),
        );

        // GA4 Measurement Protocol — purchase event
        if ($ga4MId && $ga4Secret) {
            try {
                Http::timeout(5)->post(
                    "https://www.google-analytics.com/mp/collect?measurement_id={$ga4MId}&api_secret={$ga4Secret}",
                    [
                        'client_id' => $request->cookie('_ga') ?? Str::uuid()->toString(),
                        'events'    => [[
                            'name'   => 'purchase',
                            'params' => [
                                'currency'       => 'BDT',
                                'value'          => (float) $order->total,
                                'transaction_id' => $order->order_number,
                                'shipping'       => (float) $order->shipping_cost,
                                'coupon'         => $order->coupon_code ?? '',
                                'items'          => array_map(fn($i) => [
                                    'item_id'   => $i['id'],
                                    'item_name' => $i['item_name'],
                                    'quantity'  => $i['quantity'],
                                    'price'     => $i['price'],
                                    'currency'  => 'BDT',
                                ], $orderItems),
                            ],
                        ]],
                    ]
                );
            } catch (\Throwable) {}
        }
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

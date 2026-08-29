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
use App\Services\MetaCapiService;
use App\Payments\PaymentStatus;
use App\Rules\Phone;
use App\Services\PaymentService;
use App\Services\PixelLogger;
use App\Services\PriceBook;
use App\Services\ShippingCalculator;
use App\Tax\TaxCalculator;
use App\Services\SmsService;
use App\Support\Countries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function show(Request $request, MetaCapiService $capi): Response
    {
        // InitiateCheckout CAPI — fire async (no cart data on server, just user signal)
        if ($capi->isConfigured()) {
            $userData = $capi->buildUserData($request, ['user_id' => auth()->id()]);
            $result   = $capi->send('InitiateCheckout', [], $userData, $request->url());
            PixelLogger::record(
                platform:     'meta',
                event:        'InitiateCheckout',
                success:      $result['success'],
                httpStatus:   $result['status'] ?? null,
                responseBody: $result['body']   ?? null,
                error:        $result['success'] ? null : ($result['error'] ?? null),
            );
        }

        $shippingCost = (float) SiteSetting::get('shipping.flat_rate', 60);
        $freeAbove    = (float) SiteSetting::get('shipping.free_above', 0);

        // Gateways the merchant has enabled that can charge this currency. Country is
        // not known until the address is entered, so it is applied again at store().
        $priceBook = app(PriceBook::class);
        $currency  = $priceBook->presentmentCurrency($request->cookie('currency'));
        // Pinned so a scheduled refresh cannot change totals mid-visit.
        $fxRate    = $priceBook->sessionRate($currency);

        $paymentMethods = [];
        foreach (app(PaymentService::class)->availableFor($currency) as $slug => $definition) {
            $paymentMethods[$slug] = $definition->label;
        }

        // Nothing enabled yet means the store can still take orders, just not online payment.
        if (empty($paymentMethods)) {
            $paymentMethods['cod'] = 'Cash on Delivery';
        }

        $loyaltyEnabled = (bool) SiteSetting::get('loyalty.enabled', false);
        $loyaltyBalance = 0;
        $loyaltyValue    = 0;

        if ($loyaltyEnabled && auth()->check()) {
            $service        = app(LoyaltyService::class);
            $loyaltyBalance = $service->getBalance(auth()->user());
            $loyaltyValue    = $service->pointsToCurrency($loyaltyBalance);
        }

        $courierService         = app(CourierService::class);
        $courierLocationEnabled = $courierService->hasDefault()
            && (bool) SiteSetting::get('shipping.courier_location_charges', false);

        $prefill = null;
        if (auth()->check()) {
            $user           = auth()->user();
            $defaultAddress = $user->addresses()->where('is_default', true)->first();
            $prefill = [
                'name'           => $user->name ?? '',
                'email'          => $user->email ?? '',
                'phone'          => $user->phone ?? '',
                'country'        => $defaultAddress?->country ?? '',
                'address'        => $defaultAddress?->address ?? '',
                'address_line_2' => $defaultAddress?->address_line_2 ?? '',
                'city'           => $defaultAddress?->city ?? '',
                'state'          => $defaultAddress?->state ?? '',
                'postal_code'    => $defaultAddress?->postal_code ?? '',
            ];
        }

        // Sent per-page rather than shared globally: 213 countries with their subdivisions
        // would bloat the Inertia payload of every page on the site.
        $countries    = Countries::options();
        $storeCountry = SiteSetting::get('general.store_country', 'BD');

        return Inertia::render('Checkout', compact(
            'shippingCost', 'freeAbove', 'paymentMethods',
            'loyaltyEnabled', 'loyaltyBalance', 'loyaltyValue',
            'courierLocationEnabled', 'prefill', 'countries', 'storeCountry', 'fxRate'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name'         => 'required|string|max:255',
            // Validated against the destination country so an unusable number is caught
             // here rather than failing silently at the SMS gateway.
            'customer_phone'        => ['required', 'string', 'max:50', new Phone($request->input('country'))],
            'customer_email'        => 'nullable|email|max:255',
            'country'               => ['required', 'string', 'size:2', Rule::in(array_keys(Countries::all()))],
            'address'               => 'required|string|max:500',
            'address_line_2'        => 'nullable|string|max:255',
            'city'                  => 'required|string|max:100',
            'state'                 => 'nullable|string|max:100',
            'postal_code'           => [
                'nullable', 'string', 'max:20',
                // Several countries (UAE, Hong Kong, Qatar, Panama) have no postal system,
                // so this is required per-country rather than globally.
                Rule::requiredIf(fn () => Countries::requiresPostalCode((string) $request->input('country', ''))),
            ],
            'notes'                 => 'nullable|string|max:1000',
            // Was an unconstrained string, so any value was accepted and persisted.
            'payment_method'        => ['required', 'string', 'max:50', Rule::in($this->availableGateways($request))],
            'coupon_code'           => 'nullable|string|max:100',
            'loyalty_points_redeem' => 'nullable|integer|min:0',
            'fx_rate'               => 'nullable|numeric|min:0',
            'courier_city_id'       => 'nullable|integer',
            'courier_zone_id'       => 'nullable|integer',
            'courier_area_id'       => 'nullable|integer',
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
                $loyaltyDiscount   = $loyaltyService->pointsToCurrency($pointsToRedeem);
                $loyaltyPointsUsed = $pointsToRedeem;
            }
        }

        // Shipping is resolved server-side, never trusted from the request: a live courier
        // quote beats a configured shipping zone, which beats the global flat rate.
        $shippingQuote = app(ShippingCalculator::class)->quote(
            address: [
                'country'     => $data['country'],
                'state'       => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
            ],
            items: $data['items'],
            subtotal: $subtotal,
            courierLocation: [
                'city_id' => $data['courier_city_id'] ?? null,
                'zone_id' => $data['courier_zone_id'] ?? null,
                'area_id' => $data['courier_area_id'] ?? null,
            ],
        );

        $shippingCost   = $shippingQuote['cost'];
        $shippingMethod = $shippingQuote['method'];

        // Store one canonical shape, so SMS, couriers and fraud lookups all agree.
        $data['customer_phone'] = \App\Support\PhoneNumber::toE164($data['customer_phone'], $data['country'])
            ?? $data['customer_phone'];

        $address = [
            'address'        => $data['address'],
            'address_line_2' => $data['address_line_2'] ?? null,
            'city'           => $data['city'],
            'state'          => $data['state'] ?? null,
            'postal_code'    => $data['postal_code'] ?? null,
            'country'        => $data['country'],
        ];

        // Destination-based tax on what is actually paid, so the discount is applied
        // before tax rather than after. With tax-inclusive pricing the figure is the
        // portion already inside the prices, so it is recorded but not added again.
        $tax = app(TaxCalculator::class)->calculate(
            lines: $items,
            address: $address,
            shipping: $shippingCost,
            discount: $discountAmount + $loyaltyDiscount,
        );

        $total = max(0, $subtotal + $shippingCost - $discountAmount - $loyaltyDiscount + $tax->addedToTotal());

        // Totals above are base currency and stay that way, because every admin report
        // sums them. The presentment set records what this customer was actually quoted,
        // converted once here rather than trusting the browser's display conversion.
        $priceBook           = app(PriceBook::class);
        $baseCurrency        = $priceBook->baseCurrency();
        $presentmentCurrency = $priceBook->presentmentCurrency($request->cookie('currency'));
        $rate                = $priceBook->rate($presentmentCurrency);

        // Close the render-to-submit window: if the rate moved since the page was drawn,
        // re-quote rather than charging a total the customer never agreed to.
        if ($priceBook->rateHasDrifted($presentmentCurrency, $data['fx_rate'] ?? null)) {
            session()->forget('fx');

            return back()
                ->withInput()
                ->withErrors(['fx_rate' => 'Exchange rates have been updated. Please review your total and try again.']);
        }
        $presentment         = $priceBook->convertAll([
            'subtotal'        => $subtotal,
            'shipping_cost'   => $shippingCost,
            'tax'             => $tax->total,
            'discount_amount' => $discountAmount + $loyaltyDiscount,
            'total'           => $total,
        ], $presentmentCurrency, $rate);

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
            'shipping_address'=> $address,
            // The storefront collects one address; mirroring it into billing beats
            // leaving the column null, which it always was before.
            'billing_address' => $address,
            'coupon_id'       => $coupon?->id,
            'coupon_code'     => $coupon?->code,
            'discount_amount' => $discountAmount + $loyaltyDiscount,
            'subtotal'        => $subtotal,
            'shipping_cost'   => $shippingCost,
            'shipping_method' => $shippingMethod,
            'tax'                => $tax->total,
            'tax_breakdown'      => $tax->breakdown ?: null,
            'prices_include_tax' => $tax->inclusive,
            'total'           => $total,

            'base_currency'               => $baseCurrency,
            'presentment_currency'        => $presentmentCurrency,
            'exchange_rate'               => $rate,
            'presentment_subtotal'        => $presentment['subtotal'],
            'presentment_shipping_cost'   => $presentment['shipping_cost'],
            'presentment_tax'             => $presentment['tax'],
            'presentment_discount_amount' => $presentment['discount_amount'],
            'presentment_total'           => $presentment['total'],

            'payment_method'  => $data['payment_method'],
            'payment_status'  => 'unpaid',
            'status'          => 'pending',
            'notes'           => $data['notes'] ?? null,
        ]);

        foreach ($items as $index => $item) {
            $order->items()->create([
                'product_id'    => $item['product_id'],
                'variant_id'    => $item['variant_id'] ?? null,
                'product_name'  => $item['name'],
                'sku'           => $item['sku'] ?? null,
                'variant_label' => $item['variant_label'] ?? null,
                'unit_price'    => $item['price'],
                'quantity'      => $item['quantity'],
                'total'         => $item['total'],
                'tax_amount'    => $tax->lineTax[$index] ?? 0,
                'presentment_unit_price' => $priceBook->convert($item['price'], $presentmentCurrency, $rate),
                'presentment_total'      => $priceBook->convert($item['total'], $presentmentCurrency, $rate),
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
            try {
                $notifiable->notify(new OrderConfirmed($order));
            } catch (\Throwable $e) {
                Log::warning('Order confirmation notification failed', ['order' => $order->order_number, 'error' => $e->getMessage()]);
            }
        }

        // Send SMS on order placement
        if ($order->customer_phone && SiteSetting::get('notifications.sms_on_order', '0') === '1') {
            try {
                $sms = app(SmsService::class);
                if ($sms->hasDefault()) {
                    $siteName     = SiteSetting::get('general.site_name', config('app.name'));
                    $currencyCode = $order->presentment_currency ?: $order->base_currency;
                    $default      = "Thank you for your order at {$siteName}!\nYour order #{{order_id}} has been placed successfully.\nTotal: {{amount}} {$currencyCode}.";
                    $template = SiteSetting::get('notifications.sms_template_placed', $default);
                    $message  = SmsService::renderTemplate($template, [
                        'order_id'      => $order->order_number,
                        'amount'        => $order->presentment_total ?: $order->total,
                        'customer_name' => $order->customer_name,
                    ]);
                    $sms->send($order->customer_phone, $message, $order->shipping_address['country'] ?? null);
                }
            } catch (\Throwable $e) {
                Log::warning('Order SMS failed', ['order' => $order->order_number, 'error' => $e->getMessage()]);
            }
        }

        // Server-side tracking
        $order->load('items');
        $this->trackPurchase($order, $request);

        // Hand off to the gateway. Anything that is not an immediate redirect leaves the
        // order exactly as it is — only a verified webhook or server-side verify can mark
        // it paid, never this request.
        $result = app(PaymentService::class)->begin($order, $data['payment_method']);

        if ($result->status === PaymentStatus::Redirect && $result->redirectUrl) {
            return redirect()->away($result->redirectUrl);
        }

        if ($result->status === PaymentStatus::Failed) {
            return redirect()->route('checkout')
                ->with('error', $result->error ?? 'We could not start your payment. Please try again.');
        }

        if ($request->filled('lp_slug')) {
            return redirect()->route('lp.show', ['product' => $request->input('lp_slug')])
                ->with('lp_confirmed_order', $order->order_number);
        }

        return redirect()->route('order.confirm', $order->order_number)
            ->with('order_placed', true);
    }

    /**
     * Slugs a customer may legitimately submit: gateways the merchant enabled that can
     * charge this order's currency and serve its destination country.
     *
     * @return list<string>
     */
    private function availableGateways(Request $request): array
    {
        $currency = app(PriceBook::class)->presentmentCurrency($request->cookie('currency'));
        $country  = $request->input('country');

        $slugs = array_keys(app(PaymentService::class)->availableFor($currency, $country));

        // Mirrors show(): with nothing enabled the store still takes cash-on-delivery orders.
        return $slugs ?: ['cod'];
    }

    private function trackPurchase(Order $order, Request $request): void
    {
        $ga4MId    = SiteSetting::get('tracking.ga4_measurement_id');
        $ga4Secret = SiteSetting::get('tracking.ga4_api_secret');

        // Ad platforms must be told the currency the customer was actually charged in and
        // amounts to match. Hardcoding BDT reports every international sale in the wrong
        // currency and corrupts ROAS on Meta, TikTok and GA4.
        $currency    = $order->presentment_currency ?: $order->base_currency ?: SiteSetting::get('general.currency', 'BDT');
        $orderValue  = (float) ($order->presentment_total ?: $order->total);

        $orderItems = $order->items->map(fn($i) => [
            'id'         => (string) $i->product_id,
            'item_name'  => $i->product_name,
            'quantity'   => $i->quantity,
            'price'      => (float) ($i->presentment_unit_price ?: $i->unit_price),
        ])->all();

        // Fraud gate: skip the ad-platform purchase events when the configured checker
        // flags this customer. It follows whichever checker is set as default, and is
        // skipped entirely when that checker does not serve the destination country —
        // a Bangladeshi courier-history service cannot score a US order, and sending it
        // that customer's phone would be useless and a privacy problem.
        $destination = strtoupper((string) ($order->shipping_address['country'] ?? ''));

        if ($order->customer_phone && $destination !== '') {
            try {
                $checker = app(\App\Services\FraudService::class)->active($destination);

                if ($checker) {
                    $result = $checker->check($order->customer_phone, [
                        'email'   => $order->customer_email,
                        'country' => $destination,
                    ]);

                    $flagged = ! empty($result['reports'])
                        || in_array($result['risk_level'] ?? null, ['high_risk'], true);

                    if ($flagged) {
                        $reason = $checker->name().' flagged this customer as high risk.';

                        foreach ([['meta', 'Purchase'], ['tiktok', 'Purchase'], ['ga4', 'purchase']] as [$platform, $event]) {
                            PixelLogger::record(
                                platform:    $platform,
                                event:       $event,
                                success:     false,
                                orderId:     $order->id,
                                orderNumber: $order->order_number,
                                error:       'Blocked — '.$reason,
                            );
                        }

                        return;
                    }
                }
            } catch (\Throwable) {
                // Fail open: a fraud-check outage must not stop analytics.
            }
        }

        // Meta CAPI — Purchase event
        $capi = app(MetaCapiService::class);
        if ($capi->isConfigured()) {
            $addr     = $order->shipping_address ?? [];
            $userData = $capi->buildUserData($request, [
                'email'   => $order->customer_email,
                'phone'   => $order->customer_phone,
                'name'    => $order->customer_name,
                'city'    => $addr['city'] ?? null,
                'zip'     => $addr['postcode'] ?? null,
                'user_id' => $order->user_id,
            ]);

            $result = $capi->send(
                'Purchase',
                [
                    'currency'     => $currency,
                    'value'        => $orderValue,
                    'order_id'     => $order->order_number,
                    'content_type' => 'product',
                    'contents'     => array_map(fn ($i) => [
                        'id'         => $i['id'],
                        'quantity'   => $i['quantity'],
                        'item_price' => $i['price'],
                    ], $orderItems),
                ],
                $userData,
                $request->header('referer', config('app.url')),
                'purchase_' . $order->order_number,
            );

            PixelLogger::record(
                platform:     'meta',
                event:        'Purchase',
                success:      $result['success'],
                orderId:      $order->id,
                orderNumber:  $order->order_number,
                httpStatus:   $result['status'] ?? null,
                responseBody: $result['body']   ?? null,
                error:        $result['success'] ? null : ($result['error'] ?? null),
            );
        }

        // TikTok Events API — Purchase event
        $ttResult = (new \App\Services\TikTokService())->sendPurchase(
            [
                'id'       => $order->id,
                'total'    => $orderValue,
                'currency' => $currency,
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

        if (($ttResult['error'] ?? null) !== 'TikTok not configured') {
            PixelLogger::record(
                platform:     'tiktok',
                event:        'Purchase',
                success:      $ttResult['success'],
                orderId:      $order->id,
                orderNumber:  $order->order_number,
                httpStatus:   $ttResult['http_status'] ?? null,
                responseBody: $ttResult['body'] ?? null,
                error:        $ttResult['success'] ? null : ($ttResult['error'] ?? null),
            );
        }

        // GA4 Measurement Protocol — purchase event
        if ($ga4MId && $ga4Secret) {
            try {
                $ga4Res = Http::timeout(5)->post(
                    "https://www.google-analytics.com/mp/collect?measurement_id={$ga4MId}&api_secret={$ga4Secret}",
                    [
                        'client_id' => $request->cookie('_ga') ?? Str::uuid()->toString(),
                        'events'    => [[
                            'name'   => 'purchase',
                            'params' => [
                                'currency'       => $currency,
                                'value'          => $orderValue,
                                'transaction_id' => $order->order_number,
                                'shipping'       => (float) $order->shipping_cost,
                                'coupon'         => $order->coupon_code ?? '',
                                'items'          => array_map(fn($i) => [
                                    'item_id'   => $i['id'],
                                    'item_name' => $i['item_name'],
                                    'quantity'  => $i['quantity'],
                                    'price'     => $i['price'],
                                    'currency'  => $currency,
                                ], $orderItems),
                            ],
                        ]],
                    ]
                );

                PixelLogger::record(
                    platform:     'ga4',
                    event:        'purchase',
                    success:      $ga4Res->successful(),
                    orderId:      $order->id,
                    orderNumber:  $order->order_number,
                    httpStatus:   $ga4Res->status(),
                    responseBody: $ga4Res->body(),
                    error:        $ga4Res->successful() ? null : 'HTTP ' . $ga4Res->status(),
                );
            } catch (\Throwable $e) {
                PixelLogger::record(
                    platform:    'ga4',
                    event:       'purchase',
                    success:     false,
                    orderId:     $order->id,
                    orderNumber: $order->order_number,
                    error:       $e->getMessage(),
                );
            }
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

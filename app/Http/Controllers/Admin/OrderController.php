<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\OrderStatusChanged;
use App\Services\CourierService;
use App\Services\LoyaltyService;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with(['user'])->withCount('items');

        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where(function ($builder) use ($q) {
                $builder->where('order_number', 'like', "%{$q}%")
                        ->orWhere('customer_name', 'like', "%{$q}%")
                        ->orWhere('customer_email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        $orders = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function create(): View
    {
        return view('admin.orders.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id'          => 'nullable|exists:users,id',
            'customer_name'    => 'required|string|max:255',
            'customer_email'   => 'nullable|email|max:255',
            'customer_phone'   => 'nullable|string|max:50',
            'source'           => 'required|in:' . implode(',', array_keys(Order::SOURCES)),
            'payment_method'   => 'nullable|string|max:100',
            'payment_status'   => 'required|in:' . implode(',', Order::PAYMENT_STATUSES),
            'status'           => 'required|in:' . implode(',', Order::STATUSES),
            'shipping_cost'    => 'nullable|numeric|min:0',
            'discount_amount'  => 'nullable|numeric|min:0',
            'notes'            => 'nullable|string|max:2000',
            'admin_note'       => 'nullable|string|max:2000',
            'items'            => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.product_name' => 'required|string',
            'items.*.sku'          => 'nullable|string',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.quantity'     => 'required|integer|min:1',
        ]);

        $items        = $request->input('items');
        $subtotal     = collect($items)->sum(fn($i) => $i['unit_price'] * $i['quantity']);
        $shippingCost = (float) ($request->input('shipping_cost') ?? 0);
        $discount     = (float) ($request->input('discount_amount') ?? 0);
        $total        = max(0, $subtotal + $shippingCost - $discount);

        $paid_at = null;
        if ($request->input('payment_status') === 'paid') {
            $paid_at = now();
        }

        $userId = $request->input('user_id');
        if (! $userId) {
            $email = $request->input('customer_email');
            $user  = $email ? User::firstOrCreate(
                ['email' => $email],
                [
                    'name'      => $request->input('customer_name'),
                    'phone'     => $request->input('customer_phone'),
                    'password'  => Str::random(16),
                    'is_active' => true,
                ]
            ) : User::create([
                'name'      => $request->input('customer_name'),
                'phone'     => $request->input('customer_phone'),
                'password'  => Str::random(16),
                'is_active' => true,
            ]);
            $userId = $user->id;
        }

        $order = Order::create([
            'order_number'   => Order::generateOrderNumber(),
            'source'         => $request->input('source'),
            'user_id'        => $userId,
            'customer_name'  => $request->input('customer_name'),
            'customer_email' => $request->input('customer_email'),
            'customer_phone' => $request->input('customer_phone'),
            'payment_method' => $request->input('payment_method'),
            'payment_status' => $request->input('payment_status'),
            'status'         => $request->input('status'),
            'subtotal'       => $subtotal,
            'shipping_cost'  => $shippingCost,
            'discount_amount'=> $discount,
            'total'          => $total,
            'notes'          => $request->input('notes'),
            'admin_note'     => $request->input('admin_note'),
            'paid_at'        => $paid_at,
        ]);

        foreach ($items as $item) {
            $order->items()->create([
                'product_id'   => $item['product_id'],
                'product_name' => $item['product_name'],
                'sku'          => $item['sku'] ?? null,
                'unit_price'   => $item['unit_price'],
                'quantity'     => $item['quantity'],
                'total'        => $item['unit_price'] * $item['quantity'],
            ]);
        }

        $adminId = Auth::guard('admin')->id();
        $order->logActivity('created', 'Order placed',
            'Order created via ' . (Order::SOURCES[$order->source] ?? ucfirst($order->source)) . '.',
            [], $adminId
        );

        if ($request->input('payment_status') === 'paid') {
            $order->logActivity('payment_updated', 'Marked as paid', null, [], $adminId);
        }

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order created.');
    }

    public function customerSearch(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) return response()->json([]);

        $customers = User::where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%");
            })
            ->limit(8)
            ->get(['id','name','email','phone']);

        return response()->json($customers);
    }

    public function productSearch(Request $request): JsonResponse
    {
        $q = $request->input('q', '');
        $products = Product::with('images')
            ->where('status', 'active')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'id'        => $p->id,
                'name'      => $p->name,
                'sku'       => $p->sku,
                'price'     => (float) $p->price,
                'thumb_url' => $p->images->first()?->getUrl('thumb'),
            ]);

        return response()->json($products);
    }

    public function show(Order $order): View
    {
        $order->load(['items.product', 'user', 'coupon', 'activities.admin']);
        return view('admin.orders.show', compact('order'));
    }

    public function printSlip(Order $order): View
    {
        $order->load(['items.product.images', 'user']);
        $site = [
            'name'            => SiteSetting::get('general.site_name', config('app.name')),
            'tagline'         => SiteSetting::get('general.site_tagline', ''),
            'phone'           => SiteSetting::get('general.phone', ''),
            'email'           => SiteSetting::get('general.admin_email', ''),
            'address'         => SiteSetting::get('general.address', ''),
            'currency_symbol' => SiteSetting::get('general.currency_symbol', '৳'),
            'invoice_note'    => SiteSetting::get('order.invoice_note', ''),
        ];
        return view('admin.orders.print', compact('order', 'site'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status'           => 'nullable|in:' . implode(',', Order::STATUSES),
            'payment_status'   => 'nullable|in:' . implode(',', Order::PAYMENT_STATUSES),
            'payment_method'   => 'nullable|string|max:100',
            'tracking_number'  => 'nullable|string|max:255',
            'notes'            => 'nullable|string|max:2000',
            'admin_note'       => 'nullable|string|max:2000',
        ]);

        $data = array_filter($request->only('status', 'payment_status', 'payment_method', 'tracking_number', 'notes', 'admin_note'), fn($v) => $v !== null);

        if (isset($data['payment_status']) && $data['payment_status'] === 'paid' && ! $order->paid_at) {
            $data['paid_at'] = now();
        }

        $adminId = Auth::guard('admin')->id();

        if (isset($data['status']) && $data['status'] !== $order->status) {
            $order->logActivity('status_changed',
                'Status changed to ' . ucfirst($data['status']),
                null,
                ['from' => $order->status, 'to' => $data['status']],
                $adminId
            );
        }

        if (isset($data['payment_status']) && $data['payment_status'] !== $order->payment_status) {
            $order->logActivity('payment_updated',
                'Payment status changed to ' . ucwords(str_replace('_', ' ', $data['payment_status'])),
                null,
                ['from' => $order->payment_status, 'to' => $data['payment_status']],
                $adminId
            );
        }

        if (isset($data['payment_method']) && $data['payment_method'] !== $order->payment_method) {
            $order->logActivity('payment_updated',
                'Payment method changed to ' . ucwords(str_replace('_', ' ', $data['payment_method'])),
                null,
                ['from' => $order->payment_method, 'to' => $data['payment_method']],
                $adminId
            );
        }

        if (isset($data['tracking_number']) && $data['tracking_number'] !== $order->tracking_number) {
            $order->logActivity('tracking_added',
                'Tracking number ' . ($order->tracking_number ? 'updated' : 'added'),
                $data['tracking_number'],
                [], $adminId
            );
        }

        if (isset($data['notes']) && $data['notes'] !== $order->notes) {
            $order->logActivity('note_added', 'Customer note updated', null, [], $adminId);
        }

        $previousStatus = $order->status;
        $order->update($data);

        if (isset($data['status']) && $data['status'] !== $previousStatus) {
            $loyalty = app(LoyaltyService::class);
            if ($data['status'] === 'delivered') {
                $loyalty->earnPoints($order->fresh());
            } elseif ($data['status'] === 'cancelled') {
                $loyalty->reverseEarnedPoints($order->fresh());
            }
        }

        // Notify customer on key status changes
        if (isset($data['status']) && in_array($data['status'], ['shipped', 'delivered', 'cancelled']) && $order->customer_email) {
            $notifiable = $order->user ?? (new \App\Models\User)->forceFill([
                'name'  => $order->customer_name,
                'email' => $order->customer_email,
            ]);
            try { $notifiable->notify(new OrderStatusChanged($order->fresh(), $data['status'])); } catch (\Throwable) {}
        }

        // Send SMS on confirmed / delivered status transitions
        if (isset($data['status']) && $data['status'] !== $previousStatus && $order->customer_phone) {
            $smsToggleKey = match($data['status']) {
                'confirmed' => 'notifications.sms_on_confirmed',
                'delivered' => 'notifications.sms_on_delivered',
                default     => null,
            };
            if ($smsToggleKey && SiteSetting::get($smsToggleKey, '1') === '1') {
                try {
                    $sms = app(SmsService::class);
                    if ($sms->hasDefault()) {
                        $fresh    = $order->fresh();
                        $defaults = [
                            'confirmed' => "Your order #{{order_id}} has been confirmed by KlixBD.\nTotal: {{amount}} BDT.\nWe are now preparing your order for delivery. Thank you for shopping with us!",
                            'delivered' => "Your order #{{order_id}} has been delivered. Thank you for shopping with us!",
                        ];
                        $templateKey = 'notifications.sms_template_' . $data['status'];
                        $template    = SiteSetting::get($templateKey, $defaults[$data['status']]);
                        $message     = SmsService::renderTemplate($template, [
                            'order_id'      => $fresh->order_number,
                            'amount'        => $fresh->total,
                            'customer_name' => $fresh->customer_name,
                        ]);
                        $sms->send($fresh->customer_phone, $message);
                    }
                } catch (\Throwable) {}
            }
        }

        return back()->with('success', 'Order updated.');
    }

    public function updateAddress(Request $request, Order $order): RedirectResponse
    {
        $type = $request->input('type'); // 'shipping_address' or 'billing_address'
        abort_unless(in_array($type, ['shipping_address', 'billing_address']), 422);

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'nullable|string|max:50',
            'address'  => 'required|string|max:500',
            'city'     => 'required|string|max:100',
            'state'    => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country'  => 'nullable|string|max:100',
        ]);

        $label    = $type === 'shipping_address' ? 'Shipping' : 'Billing';
        $adminId  = Auth::guard('admin')->id();
        $previous = $order->$type;

        $order->update([$type => $data]);

        $order->logActivity(
            'address_updated',
            $label . ' address updated by admin',
            null,
            ['from' => $previous, 'to' => $data],
            $adminId
        );

        return back()->with('success', $label . ' address updated.');
    }

    public function dispatchToCourier(Request $request, Order $order, CourierService $courier): RedirectResponse
    {
        $extra = $request->validate([
            'city_id'      => 'nullable|integer',
            'zone_id'      => 'nullable|integer',
            'area_id'      => 'nullable|integer',
            'area'         => 'nullable|string|max:200',
            'item_weight'  => 'nullable|numeric|min:0.1',
            'cod_amount'   => 'nullable|numeric|min:0',
            'notes'        => 'nullable|string|max:500',
        ]);

        try {
            $result = $courier->driver()->createOrder($order, array_filter($extra));

            $order->update([
                'courier_provider' => $courier->driver()->name(),
                'consignment_id'   => $result['consignment_id'],
                'courier_status'   => 'dispatched',
                'courier_data'     => $result['raw'],
                'tracking_number'  => $result['tracking_code'] ?? $result['consignment_id'],
            ]);

            $order->logActivity('courier_dispatched', 'Order dispatched via ' . $courier->driver()->name(), null, $result, Auth::guard('admin')->id());

        } catch (\Throwable $e) {
            return back()->with('error', 'Courier dispatch failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Order dispatched to courier. Consignment: ' . $result['consignment_id']);
    }

    public function refreshCourierStatus(Order $order, CourierService $courier): RedirectResponse
    {
        if (! $order->consignment_id) {
            return back()->with('error', 'No consignment ID found for this order.');
        }

        try {
            $result = $courier->driver()->getStatus($order->consignment_id);
            $order->update([
                'courier_status' => $result['status'],
                'courier_data'   => array_merge($order->courier_data ?? [], ['last_status_check' => $result]),
            ]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Status refresh failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Courier status updated: ' . $result['status']);
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Order deleted.');
    }
}

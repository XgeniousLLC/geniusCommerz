<?php

namespace App\Http\Controllers;

use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\Refund;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserAccountController extends Controller
{
    public function dashboard(): Response
    {
        $user = auth()->user();
        $stats = [
            'total_orders'    => Order::where('user_id', $user->id)->count(),
            'pending_orders'  => Order::where('user_id', $user->id)->whereIn('status', ['pending', 'processing'])->count(),
            'delivered'       => Order::where('user_id', $user->id)->where('status', 'delivered')->count(),
            'total_reviews'   => ProductReview::where('user_id', $user->id)->count(),
            'open_refunds'    => Refund::where('user_id', $user->id)->whereIn('status', ['pending', 'approved'])->count(),
        ];

        $recentOrders = Order::where('user_id', $user->id)
            ->withCount('items')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($o) => $this->formatOrder($o));

        $loyaltyPoints = LoyaltyPoint::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->value('balance_after') ?? 0;

        $defaultAddress = $user->addresses()->where('is_default', true)->first();

        return Inertia::render('Account/Dashboard', compact('stats', 'recentOrders', 'loyaltyPoints', 'defaultAddress'));
    }

    public function orders(Request $request): Response
    {
        $user   = auth()->user();
        $orders = Order::where('user_id', $user->id)
            ->withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Account/Orders', [
            'orders'   => $orders->through(fn ($o) => $this->formatOrder($o)),
            'filters'  => $request->only('status'),
            'statuses' => Order::STATUSES,
        ]);
    }

    public function orderShow(Order $order): Response
    {
        abort_unless($order->user_id === auth()->id(), 403);
        $order->load(['items.product.images', 'activities', 'refunds']);

        $reviewedProductIds = ProductReview::where('user_id', auth()->id())
            ->whereIn('product_id', $order->items->pluck('product_id'))
            ->pluck('product_id')
            ->toArray();

        return Inertia::render('Account/OrderShow', [
            'order'              => $this->formatOrderFull($order),
            'reviewedProductIds' => $reviewedProductIds,
        ]);
    }

    public function reviews(): Response
    {
        $reviews = ProductReview::where('user_id', auth()->id())
            ->with('product.images')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => [
                'id'           => $r->id,
                'rating'       => $r->rating,
                'title'        => $r->title,
                'body'         => $r->body,
                'is_approved'  => $r->is_approved,
                'created_at'   => $r->created_at->format('M d, Y'),
                'product' => [
                    'id'        => $r->product->id,
                    'name'      => $r->product->name,
                    'slug'      => $r->product->slug,
                    'thumb'     => $r->product->images->first()?->getUrl('thumb'),
                ],
            ]);

        return Inertia::render('Account/Reviews', compact('reviews'));
    }

    public function address(): Response
    {
        $user      = auth()->user();
        $addresses = $user->addresses()->orderByDesc('is_default')->orderBy('created_at')->get();

        return Inertia::render('Account/Address', [
            'addresses' => $addresses,
            'profile'   => ['name' => $user->name, 'email' => $user->email, 'phone' => $user->phone],
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'phone'    => 'nullable|string|max:50',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user = auth()->user();
        $user->name  = $data['name'];
        $user->phone = $data['phone'] ?? $user->phone;
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return back()->with('success', 'Profile updated.');
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label'      => 'required|string|max:50',
            'name'       => 'required|string|max:255',
            'phone'      => 'required|string|max:50',
            'address'    => 'required|string|max:500',
            'city'       => 'required|string|max:100',
            'is_default' => 'boolean',
        ]);

        $user = auth()->user();

        if (! empty($data['is_default'])) {
            $user->addresses()->update(['is_default' => false]);
        }

        // Set first address as default automatically
        if ($user->addresses()->count() === 0) {
            $data['is_default'] = true;
        }

        $user->addresses()->create($data);

        return back()->with('success', 'Address saved.');
    }

    public function updateAddress(Request $request, UserAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === auth()->id(), 403);

        $data = $request->validate([
            'label'      => 'required|string|max:50',
            'name'       => 'required|string|max:255',
            'phone'      => 'required|string|max:50',
            'address'    => 'required|string|max:500',
            'city'       => 'required|string|max:100',
            'is_default' => 'boolean',
        ]);

        if (! empty($data['is_default'])) {
            auth()->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($data);

        return back()->with('success', 'Address updated.');
    }

    public function destroyAddress(UserAddress $address): RedirectResponse
    {
        abort_unless($address->user_id === auth()->id(), 403);
        $address->delete();
        return back()->with('success', 'Address removed.');
    }

    public function refunds(): Response
    {
        $refunds = Refund::where('user_id', auth()->id())
            ->with('order')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => [
                'id'           => $r->id,
                'order_number' => $r->order->order_number,
                'reason'       => $r->reason,
                'details'      => $r->details,
                'amount'       => $r->amount,
                'status'       => $r->status,
                'admin_note'   => $r->admin_note,
                'created_at'   => $r->created_at->format('M d, Y'),
                'resolved_at'  => $r->resolved_at?->format('M d, Y'),
            ]);

        // Orders eligible for refund (delivered, no pending refund)
        $eligibleOrders = Order::where('user_id', auth()->id())
            ->where('status', 'delivered')
            ->whereDoesntHave('refunds', fn ($q) => $q->whereIn('status', ['pending', 'approved']))
            ->orderByDesc('created_at')
            ->get(['id', 'order_number', 'total']);

        return Inertia::render('Account/Refunds', [
            'refunds'        => $refunds,
            'eligibleOrders' => $eligibleOrders,
            'reasons'        => Refund::REASONS,
        ]);
    }

    public function storeRefund(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason'   => 'required|string|max:100',
            'details'  => 'nullable|string|max:1000',
        ]);

        $order = Order::findOrFail($data['order_id']);
        abort_unless($order->user_id === auth()->id(), 403);
        abort_unless($order->status === 'delivered', 422);

        // Prevent duplicate pending refunds
        if ($order->refunds()->whereIn('status', ['pending', 'approved'])->exists()) {
            return back()->with('error', 'A refund request is already pending for this order.');
        }

        Refund::create([
            'order_id' => $order->id,
            'user_id'  => auth()->id(),
            'reason'   => $data['reason'],
            'details'  => $data['details'],
            'amount'   => $order->total,
        ]);

        return back()->with('success', 'Refund request submitted. We\'ll review it within 2–3 business days.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function formatOrder(Order $o): array
    {
        return [
            'id'             => $o->id,
            'order_number'   => $o->order_number,
            'status'         => $o->status,
            'payment_status' => $o->payment_status,
            'total'          => $o->total,
            'items_count'    => $o->items_count ?? $o->items->count(),
            'created_at'     => $o->created_at->format('M d, Y'),
        ];
    }

    private function formatOrderFull(Order $o): array
    {
        return [
            'id'               => $o->id,
            'order_number'     => $o->order_number,
            'status'           => $o->status,
            'payment_status'   => $o->payment_status,
            'payment_method'   => $o->payment_method,
            'tracking_number'  => $o->tracking_number,
            'subtotal'         => $o->subtotal,
            'shipping_cost'    => $o->shipping_cost,
            'discount_amount'  => $o->discount_amount,
            'total'            => $o->total,
            'notes'            => $o->notes,
            'created_at'       => $o->created_at->format('M d, Y H:i'),
            'shipping_address' => $o->shipping_address,
            'coupon_code'      => $o->coupon_code,
            'items' => $o->items->map(fn ($i) => [
                'id'            => $i->id,
                'product_id'    => $i->product_id,
                'product_name'  => $i->product_name,
                'variant_label' => $i->variant_label,
                'sku'           => $i->sku,
                'unit_price'    => $i->unit_price,
                'quantity'      => $i->quantity,
                'total'         => $i->total,
                'thumb'         => $i->product?->images->first()?->getUrl('thumb'),
                'slug'          => $i->product?->slug,
            ]),
            'activities' => $o->activities->map(fn ($a) => [
                'id'          => $a->id,
                'title'       => $a->title,
                'description' => $a->description,
                'created_at'  => $a->created_at->format('M d, Y H:i'),
            ]),
            'refunds' => $o->refunds->map(fn ($r) => [
                'id'     => $r->id,
                'status' => $r->status,
                'amount' => $r->amount,
            ]),
        ];
    }
}

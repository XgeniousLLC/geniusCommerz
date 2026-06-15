<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangePasswordRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('email', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $users = $query->withCount('orders')
            ->withSum('orders as orders_total', 'total')
            ->latest()
            ->paginate(20);

        $thisMonth = now()->startOfMonth();
        $stats = [
            'total'      => User::count(),
            'vip'        => User::has('orders', '>=', 5)->count(),
            'returning'  => User::has('orders', '>=', 2)->count(),
            'new'        => User::where('created_at', '>=', $thisMonth)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load(['addresses', 'reviews.product']);

        // Orders with items
        $orders = $user->orders()->with('items')->latest()->get();

        // Summary stats
        $totalOrders   = $orders->count();
        $totalSpent    = $orders->whereIn('status', ['processing','shipped','delivered'])->sum('total');
        $completedOrders = $orders->where('status', 'delivered')->count();
        $cancelledOrders = $orders->where('status', 'cancelled')->count();
        $avgOrderValue = $totalOrders ? round($totalSpent / max($completedOrders, 1), 2) : 0;

        // Refunds
        $refunds = $user->refunds()->with('order')->latest()->get();

        // Loyalty
        $loyaltyBalance  = $user->loyaltyBalance();
        $loyaltyHistory  = $user->loyaltyPoints()->latest()->take(10)->get();

        // Most purchased products
        $topProducts = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.user_id', $user->id)
            ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.total) as total_spent'))
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // Order status distribution
        $statusCounts = $orders->groupBy('status')->map->count();

        // Monthly spend (last 12 months)
        $monthlySpend = $user->orders()
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('SUM(total) as total'))
            ->where('created_at', '>=', now()->subMonths(12))
            ->whereIn('status', ['processing','shipped','delivered'])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Payment method preference
        $paymentPreference = $orders->groupBy('payment_method')->map->count()->sortDesc();

        // Reviews
        $reviews = $user->reviews()->with('product')->latest()->get();

        return view('admin.users.show', compact(
            'user', 'orders', 'refunds', 'reviews',
            'totalOrders', 'totalSpent', 'completedOrders', 'cancelledOrders', 'avgOrderValue',
            'loyaltyBalance', 'loyaltyHistory',
            'topProducts', 'statusCounts', 'monthlySpend', 'paymentPreference',
        ));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function changePassword(ChangePasswordRequest $request, User $user)
    {
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Password changed successfully.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();

        // Stock: sum tracked quantities (null = unlimited, exclude from count)
        $simpleStock  = Product::where('has_variants', false)->whereNotNull('stock_qty')->sum('stock_qty');
        $variantStock = ProductVariant::where('is_active', true)->whereNotNull('stock_qty')->sum('stock_qty');

        $stats = [
            'total_orders'     => Order::count(),
            'orders_24h'       => Order::where('created_at', '>=', $now->copy()->subDay())->count(),
            'orders_7d'        => Order::where('created_at', '>=', $now->copy()->subDays(7))->count(),
            'pending_orders'   => Order::where('status', 'pending')->count(),
            'in_shipment'      => Order::where('status', 'shipped')->count(),
            'total_revenue'    => Order::where('payment_status', 'paid')->sum('total'),
            'total_stock'      => (int) $simpleStock + (int) $variantStock,
            'total_products'   => Product::count(),
            'total_customers'  => User::count(),
            'active_coupons'   => Coupon::where('is_active', true)->count(),
        ];

        $recentOrders = Order::withCount('items')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentOrders'));
    }
}

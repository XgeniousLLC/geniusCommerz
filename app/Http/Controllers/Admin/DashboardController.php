<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
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
        $now  = now();
        $ago7 = $now->copy()->subDays(7);
        $ago30 = $now->copy()->subDays(30);

        $simpleStock  = Product::where('has_variants', false)->whereNotNull('stock_qty')->sum('stock_qty');
        $variantStock = ProductVariant::where('is_active', true)->whereNotNull('stock_qty')->sum('stock_qty');

        $revenue30  = Order::where('payment_status', 'paid')->where('created_at', '>=', $ago30)->sum('total');
        $orders30   = Order::where('created_at', '>=', $ago30)->count();
        $aov        = $orders30 > 0 ? round($revenue30 / $orders30, 2) : 0;
        $newCust30  = User::where('created_at', '>=', $ago30)->count();

        $stats = [
            'total_orders'    => Order::count(),
            'orders_24h'      => Order::where('created_at', '>=', $now->copy()->subDay())->count(),
            'orders_7d'       => Order::where('created_at', '>=', $ago7)->count(),
            'pending_orders'  => Order::where('status', 'pending')->count(),
            'in_shipment'     => Order::where('status', 'shipped')->count(),
            'total_revenue'   => Order::where('payment_status', 'paid')->sum('total'),
            'revenue_30d'     => $revenue30,
            'orders_30d'      => $orders30,
            'aov'             => $aov,
            'new_customers'   => $newCust30,
            'total_stock'     => (int) $simpleStock + (int) $variantStock,
            'total_products'  => Product::count(),
            'total_customers' => User::count(),
            'active_coupons'  => Coupon::where('is_active', true)->count(),
        ];

        $recentOrders = Order::withCount('items')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $topCategories = Category::withCount('products')->orderByDesc('products_count')->limit(4)->get();

        $lowStock = Product::where('has_variants', false)
            ->whereNotNull('stock_qty')
            ->where('stock_qty', '<=', 10)
            ->where('stock_qty', '>', 0)
            ->orderBy('stock_qty')
            ->limit(5)
            ->get(['id', 'name', 'stock_qty']);

        $ordersByStatus = collect(Order::STATUSES)->mapWithKeys(
            fn($s) => [$s => Order::where('status', $s)->count()]
        );

        return view('admin.dashboard', compact('stats', 'recentOrders', 'topCategories', 'lowStock', 'ordersByStatus'));
    }
}

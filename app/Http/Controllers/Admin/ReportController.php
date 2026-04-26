<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Http\StreamedResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function salesOverview(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $baseQuery = fn () => DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled');

        $stats = [
            'total_revenue'     => (float) $baseQuery()->sum('total'),
            'total_orders'      => (int) $baseQuery()->count(),
            'avg_order_value'   => (float) $baseQuery()->avg('total') ?: 0,
            'total_items_sold'  => (int) DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->where('orders.status', '!=', 'cancelled')
                ->sum('order_items.quantity'),
        ];

        $diffDays = $startDate->diffInDays($endDate);
        $groupFormat = $diffDays <= 31 ? '%Y-%m-%d' : '%Y-%W';

        $chartData = DB::table('orders')
            ->selectRaw("DATE_FORMAT(created_at, '{$groupFormat}') as date, SUM(total) as revenue, COUNT(*) as orders")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupByRaw("DATE_FORMAT(created_at, '{$groupFormat}')")
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date'    => $row->date,
                'revenue' => (float) $row->revenue,
                'orders'  => (int) $row->orders,
            ])
            ->toArray();

        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('order_items.product_id, order_items.product_name, SUM(order_items.total) as revenue, SUM(order_items.quantity) as qty_sold')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $revenueByPaymentMethod = DB::table('orders')
            ->selectRaw('COALESCE(payment_method, "unknown") as payment_method, SUM(total) as revenue, COUNT(*) as orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get();

        return view('admin.reports.sales', compact(
            'stats',
            'chartData',
            'topProducts',
            'revenueByPaymentMethod',
            'startDate',
            'endDate',
            'request'
        ));
    }

    public function inventory(Request $request): View
    {
        $filter = $request->input('filter', 'all');
        $sort   = $request->input('sort', 'asc');
        $lowStockThreshold = (int) SiteSetting::get('low_stock_threshold', 10);

        $query = DB::table('products')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.has_variants',
                'products.price',
                'products.stock_qty',
                DB::raw('COALESCE((SELECT SUM(pv.stock_qty) FROM product_variants pv WHERE pv.product_id = products.id AND pv.is_active = 1), products.stock_qty, 0) as effective_stock')
            );

        $query->where(function ($q) use ($filter, $lowStockThreshold) {
            if ($filter === 'out_of_stock') {
                $q->whereRaw('COALESCE((SELECT SUM(pv.stock_qty) FROM product_variants pv WHERE pv.product_id = products.id AND pv.is_active = 1), products.stock_qty, 0) = 0');
            } elseif ($filter === 'low_stock') {
                $q->whereRaw('COALESCE((SELECT SUM(pv.stock_qty) FROM product_variants pv WHERE pv.product_id = products.id AND pv.is_active = 1), products.stock_qty, 0) > 0')
                  ->whereRaw('COALESCE((SELECT SUM(pv.stock_qty) FROM product_variants pv WHERE pv.product_id = products.id AND pv.is_active = 1), products.stock_qty, 0) <= ?', [$lowStockThreshold]);
            }
        });

        $direction = $sort === 'desc' ? 'desc' : 'asc';
        $query->orderByRaw("COALESCE((SELECT SUM(pv.stock_qty) FROM product_variants pv WHERE pv.product_id = products.id AND pv.is_active = 1), products.stock_qty, 0) {$direction}");

        $products = $query->paginate(50)->withQueryString();

        $summaryRow = DB::table('products')
            ->selectRaw('
                COUNT(*) as total_products,
                SUM(CASE WHEN COALESCE((SELECT SUM(pv.stock_qty) FROM product_variants pv WHERE pv.product_id = products.id AND pv.is_active = 1), products.stock_qty, 0) = 0 THEN 1 ELSE 0 END) as out_of_stock_count,
                SUM(CASE WHEN COALESCE((SELECT SUM(pv.stock_qty) FROM product_variants pv WHERE pv.product_id = products.id AND pv.is_active = 1), products.stock_qty, 0) > 0 AND COALESCE((SELECT SUM(pv.stock_qty) FROM product_variants pv WHERE pv.product_id = products.id AND pv.is_active = 1), products.stock_qty, 0) <= ? THEN 1 ELSE 0 END) as low_stock_count,
                SUM(COALESCE((SELECT SUM(pv.stock_qty) FROM product_variants pv WHERE pv.product_id = products.id AND pv.is_active = 1), products.stock_qty, 0) * COALESCE(products.price, 0)) as total_stock_value
            ', [$lowStockThreshold])
            ->first();

        $summary = [
            'total_products'    => (int) $summaryRow->total_products,
            'out_of_stock_count'=> (int) $summaryRow->out_of_stock_count,
            'low_stock_count'   => (int) $summaryRow->low_stock_count,
            'total_stock_value' => (float) $summaryRow->total_stock_value,
            'low_stock_threshold' => $lowStockThreshold,
        ];

        return view('admin.reports.inventory', compact('products', 'summary', 'filter', 'sort'));
    }

    public function customers(Request $request): View
    {
        $totalCustomers = DB::table('users')->count();

        $newThisMonth = DB::table('users')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $repeatCustomers = DB::table('orders')
            ->selectRaw('user_id, COUNT(*) as order_count')
            ->whereNotNull('user_id')
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $topCustomers = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->selectRaw('
                users.id,
                users.name,
                users.email,
                COUNT(orders.id) as orders_count,
                SUM(orders.total) as total_spent,
                MAX(orders.created_at) as last_order_at
            ')
            ->whereNotIn('orders.status', ['cancelled'])
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_spent')
            ->limit(20)
            ->get();

        $stats = [
            'total_customers'   => $totalCustomers,
            'new_this_month'    => $newThisMonth,
            'repeat_customers'  => $repeatCustomers,
        ];

        return view('admin.reports.customers', compact('stats', 'topCustomers'));
    }

    public function ordersReport(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $byStatus = DB::table('orders')
            ->selectRaw('status, COUNT(*) as count, SUM(total) as revenue')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $byPaymentMethod = DB::table('orders')
            ->selectRaw('COALESCE(payment_method, "unknown") as payment_method, COUNT(*) as count, SUM(total) as revenue')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->get();

        $totalOrders      = DB::table('orders')->whereBetween('created_at', [$startDate, $endDate])->count();
        $cancelledOrders  = DB::table('orders')->whereBetween('created_at', [$startDate, $endDate])->where('status', 'cancelled')->count();
        $cancellationRate = $totalOrders > 0 ? round(($cancelledOrders / $totalOrders) * 100, 2) : 0;

        return view('admin.reports.orders', compact(
            'byStatus',
            'byPaymentMethod',
            'cancellationRate',
            'totalOrders',
            'cancelledOrders',
            'startDate',
            'endDate',
            'request'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $type      = $request->input('type', 'sales');
        $dateStart = $request->input('date_start') ? Carbon::parse($request->input('date_start'))->startOfDay() : now()->startOfMonth();
        $dateEnd   = $request->input('date_end')   ? Carbon::parse($request->input('date_end'))->endOfDay()   : now()->endOfDay();

        $filename = "{$type}-report-" . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($type, $dateStart, $dateEnd) {
            $handle = fopen('php://output', 'w');

            match ($type) {
                'sales'     => $this->exportSales($handle, $dateStart, $dateEnd),
                'inventory' => $this->exportInventory($handle),
                'customers' => $this->exportCustomers($handle),
                'orders'    => $this->exportOrders($handle, $dateStart, $dateEnd),
                default     => $this->exportSales($handle, $dateStart, $dateEnd),
            };

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function exportSales($handle, Carbon $dateStart, Carbon $dateEnd): void
    {
        fputcsv($handle, ['Date', 'Order Number', 'Customer Name', 'Customer Email', 'Payment Method', 'Status', 'Subtotal', 'Discount', 'Shipping', 'Tax', 'Total']);

        DB::table('orders')
            ->whereBetween('created_at', [$dateStart, $dateEnd])
            ->orderBy('created_at')
            ->each(function ($order) use ($handle) {
                fputcsv($handle, [
                    $order->created_at,
                    $order->order_number,
                    $order->customer_name,
                    $order->customer_email,
                    $order->payment_method,
                    $order->status,
                    $order->subtotal,
                    $order->discount_amount,
                    $order->shipping_cost,
                    $order->tax,
                    $order->total,
                ]);
            });
    }

    private function exportInventory($handle): void
    {
        fputcsv($handle, ['Product Name', 'SKU', 'Has Variants', 'Stock Qty', 'Price', 'Stock Value']);

        DB::table('products')
            ->selectRaw('
                products.name,
                products.sku,
                products.has_variants,
                COALESCE((SELECT SUM(pv.stock_qty) FROM product_variants pv WHERE pv.product_id = products.id AND pv.is_active = 1), products.stock_qty, 0) as effective_stock,
                products.price
            ')
            ->orderBy('products.name')
            ->each(function ($row) use ($handle) {
                fputcsv($handle, [
                    $row->name,
                    $row->sku,
                    $row->has_variants ? 'Yes' : 'No',
                    $row->effective_stock,
                    $row->price,
                    round($row->effective_stock * $row->price, 2),
                ]);
            });
    }

    private function exportCustomers($handle): void
    {
        fputcsv($handle, ['Name', 'Email', 'Total Orders', 'Total Spent', 'Last Order At']);

        DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->selectRaw('users.name, users.email, COUNT(orders.id) as orders_count, SUM(orders.total) as total_spent, MAX(orders.created_at) as last_order_at')
            ->whereNotIn('orders.status', ['cancelled'])
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_spent')
            ->each(function ($row) use ($handle) {
                fputcsv($handle, [
                    $row->name,
                    $row->email,
                    $row->orders_count,
                    $row->total_spent,
                    $row->last_order_at,
                ]);
            });
    }

    private function exportOrders($handle, Carbon $dateStart, Carbon $dateEnd): void
    {
        fputcsv($handle, ['Order Number', 'Customer Name', 'Customer Email', 'Status', 'Payment Status', 'Payment Method', 'Total', 'Created At']);

        DB::table('orders')
            ->whereBetween('created_at', [$dateStart, $dateEnd])
            ->orderBy('created_at')
            ->each(function ($order) use ($handle) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->customer_name,
                    $order->customer_email,
                    $order->status,
                    $order->payment_status,
                    $order->payment_method,
                    $order->total,
                    $order->created_at,
                ]);
            });
    }

    public function profitAnalysis(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $revenue = (float) DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $cogs = (float) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'cancelled')
            ->whereNotNull('products.cost_price')
            ->selectRaw('SUM(order_items.quantity * products.cost_price) as cogs')
            ->value('cogs') ?: 0;

        $discount = (float) DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->sum('discount_amount');

        $shipping = (float) DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->sum('shipping_cost');

        $grossProfit = $revenue - $cogs;
        $netProfit   = $grossProfit + $shipping - $discount;
        $margin      = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0;

        $byMonth = DB::table('orders')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as revenue, SUM(discount_amount) as discount, SUM(shipping_cost) as shipping")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupByRaw("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month')
            ->get();

        $topMargin = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'cancelled')
            ->whereNotNull('products.cost_price')
            ->selectRaw('
                order_items.product_name,
                SUM(order_items.total) as revenue,
                SUM(order_items.quantity * products.cost_price) as cost,
                SUM(order_items.total) - SUM(order_items.quantity * products.cost_price) as profit,
                ROUND(((SUM(order_items.total) - SUM(order_items.quantity * products.cost_price)) / NULLIF(SUM(order_items.total), 0)) * 100, 2) as margin_pct
            ')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('profit')
            ->limit(15)
            ->get();

        return view('admin.reports.profit', compact(
            'revenue', 'cogs', 'discount', 'shipping', 'grossProfit', 'netProfit', 'margin',
            'byMonth', 'topMargin', 'startDate', 'endDate', 'request'
        ));
    }

    public function topProducts(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $sortBy = $request->input('sort', 'revenue');

        $query = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('
                order_items.product_id,
                order_items.product_name,
                products.sku,
                SUM(order_items.quantity) as qty_sold,
                SUM(order_items.total) as revenue,
                AVG(order_items.unit_price) as avg_price,
                COUNT(DISTINCT order_items.order_id) as order_count,
                SUM(CASE WHEN products.cost_price IS NOT NULL
                    THEN order_items.total - (order_items.quantity * products.cost_price)
                    ELSE NULL END) as profit,
                ROUND(AVG(CASE WHEN products.cost_price IS NOT NULL AND products.price > 0
                    THEN ((products.price - products.cost_price) / products.price) * 100
                    ELSE NULL END), 2) as margin_pct
            ')
            ->groupBy('order_items.product_id', 'order_items.product_name', 'products.sku');

        $query->orderByDesc($sortBy === 'qty' ? 'qty_sold' : ($sortBy === 'orders' ? 'order_count' : 'revenue'));

        $products = $query->limit(50)->get();

        $totalRevenue = (float) DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        return view('admin.reports.top-products', compact(
            'products', 'totalRevenue', 'sortBy', 'startDate', 'endDate', 'request'
        ));
    }

    public function demandTrends(Request $request): View
    {
        $year = (int) $request->input('year', now()->year);

        $monthly = DB::table('orders')
            ->selectRaw("MONTH(created_at) as month, COUNT(*) as orders, SUM(total) as revenue, AVG(total) as avg_order")
            ->whereYear('created_at', $year)
            ->where('status', '!=', 'cancelled')
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get()
            ->keyBy('month');

        $hourly = DB::table('orders')
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as orders')
            ->whereYear('created_at', $year)
            ->where('status', '!=', 'cancelled')
            ->groupByRaw('HOUR(created_at)')
            ->orderByRaw('HOUR(created_at)')
            ->get()
            ->keyBy('hour');

        $dayOfWeek = DB::table('orders')
            ->selectRaw('DAYOFWEEK(created_at) as dow, COUNT(*) as orders, SUM(total) as revenue')
            ->whereYear('created_at', $year)
            ->where('status', '!=', 'cancelled')
            ->groupByRaw('DAYOFWEEK(created_at)')
            ->orderByRaw('DAYOFWEEK(created_at)')
            ->get();

        $yearRange = range(max(now()->year - 4, 2020), now()->year);

        $yoy = DB::table('orders')
            ->selectRaw("YEAR(created_at) as year, MONTH(created_at) as month, SUM(total) as revenue")
            ->whereIn(DB::raw('YEAR(created_at)'), array_slice($yearRange, -3))
            ->where('status', '!=', 'cancelled')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get();

        return view('admin.reports.demand-trends', compact(
            'monthly', 'hourly', 'dayOfWeek', 'year', 'yearRange', 'yoy', 'request'
        ));
    }

    public function categoryRevenue(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $byCategory = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('category_product', 'category_product.product_id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'category_product.category_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('categories.name as category, SUM(order_items.total) as revenue, SUM(order_items.quantity) as qty_sold, COUNT(DISTINCT order_items.order_id) as order_count')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        $byBrand = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('brands', 'brands.id', '=', 'products.brand_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('brands.name as brand, SUM(order_items.total) as revenue, SUM(order_items.quantity) as qty_sold, COUNT(DISTINCT order_items.order_id) as order_count')
            ->groupBy('brands.id', 'brands.name')
            ->orderByDesc('revenue')
            ->get();

        $totalRevenue = (float) DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        return view('admin.reports.category-revenue', compact(
            'byCategory', 'byBrand', 'totalRevenue', 'startDate', 'endDate', 'request'
        ));
    }

    public function couponUsage(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $coupons = DB::table('orders')
            ->whereNotNull('coupon_code')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('coupon_code, COUNT(*) as times_used, SUM(discount_amount) as total_discount, SUM(total) as revenue_after_discount, AVG(total) as avg_order')
            ->groupBy('coupon_code')
            ->orderByDesc('times_used')
            ->get();

        $stats = [
            'total_coupon_orders' => (int) DB::table('orders')->whereNotNull('coupon_code')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_discount_given' => (float) DB::table('orders')->whereNotNull('coupon_code')->whereBetween('created_at', [$startDate, $endDate])->sum('discount_amount'),
            'unique_coupons_used' => $coupons->count(),
            'all_orders' => (int) DB::table('orders')->whereBetween('created_at', [$startDate, $endDate])->where('status', '!=', 'cancelled')->count(),
        ];

        return view('admin.reports.coupon-usage', compact('coupons', 'stats', 'startDate', 'endDate', 'request'));
    }

    public function refundAnalysis(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $byReason = DB::table('refunds')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('reason, COUNT(*) as count, SUM(COALESCE(amount, 0)) as total_amount, AVG(CASE WHEN resolved_at IS NOT NULL THEN TIMESTAMPDIFF(HOUR, created_at, resolved_at) ELSE NULL END) as avg_resolution_hours')
            ->groupBy('reason')
            ->orderByDesc('count')
            ->get();

        $byStatus = DB::table('refunds')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count, SUM(COALESCE(amount, 0)) as total_amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $stats = [
            'total_requests' => (int) DB::table('refunds')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_refunded' => (float) DB::table('refunds')->whereBetween('created_at', [$startDate, $endDate])->where('status', 'processed')->sum('amount'),
            'pending' => (int) ($byStatus['pending']->count ?? 0),
            'approved' => (int) ($byStatus['approved']->count ?? 0),
            'rejected' => (int) ($byStatus['rejected']->count ?? 0),
            'processed' => (int) ($byStatus['processed']->count ?? 0),
            'total_orders' => (int) DB::table('orders')->whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        $recent = DB::table('refunds')
            ->join('orders', 'orders.id', '=', 'refunds.order_id')
            ->whereBetween('refunds.created_at', [$startDate, $endDate])
            ->selectRaw('refunds.*, orders.order_number, orders.customer_name')
            ->orderByDesc('refunds.created_at')
            ->limit(20)
            ->get();

        return view('admin.reports.refund-analysis', compact('byReason', 'byStatus', 'stats', 'recent', 'startDate', 'endDate', 'request'));
    }

    public function paymentTrends(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $byMethod = DB::table('orders')
            ->selectRaw('COALESCE(payment_method, "unknown") as method, COUNT(*) as orders, SUM(total) as revenue, AVG(total) as avg_order')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupBy('payment_method')
            ->orderByDesc('orders')
            ->get();

        $diffDays = $startDate->diffInDays($endDate);
        $groupFormat = $diffDays <= 60 ? '%Y-%m-%d' : '%Y-%m';

        $overTime = DB::table('orders')
            ->selectRaw("DATE_FORMAT(created_at, '{$groupFormat}') as period, COALESCE(payment_method, 'unknown') as method, COUNT(*) as orders")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->groupByRaw("DATE_FORMAT(created_at, '{$groupFormat}'), payment_method")
            ->orderBy('period')
            ->get()
            ->groupBy('method');

        $paymentStatus = DB::table('orders')
            ->selectRaw('payment_status, COUNT(*) as count, SUM(total) as revenue')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('payment_status')
            ->get();

        return view('admin.reports.payment-trends', compact('byMethod', 'overTime', 'paymentStatus', 'startDate', 'endDate', 'request'));
    }

    public function geographic(Request $request): View
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);

        $byCity = DB::table('orders')
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(shipping_address, '$.city')) as city, COUNT(*) as orders, SUM(total) as revenue, AVG(total) as avg_order")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('shipping_address')
            ->groupByRaw("JSON_UNQUOTE(JSON_EXTRACT(shipping_address, '$.city'))")
            ->havingRaw("city IS NOT NULL AND city != 'null' AND city != ''")
            ->orderByDesc('revenue')
            ->limit(30)
            ->get();

        $byState = DB::table('orders')
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(shipping_address, '$.state')) as state, COUNT(*) as orders, SUM(total) as revenue")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('shipping_address')
            ->groupByRaw("JSON_UNQUOTE(JSON_EXTRACT(shipping_address, '$.state'))")
            ->havingRaw("state IS NOT NULL AND state != 'null' AND state != ''")
            ->orderByDesc('revenue')
            ->limit(20)
            ->get();

        $totalRevenue = (float) DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        return view('admin.reports.geographic', compact('byCity', 'byState', 'totalRevenue', 'startDate', 'endDate', 'request'));
    }

    public function customerLtv(Request $request): View
    {
        $topLtv = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->whereNotNull('orders.user_id')
            ->whereNotIn('orders.status', ['cancelled'])
            ->selectRaw('
                users.id,
                users.name,
                users.email,
                users.created_at as joined_at,
                COUNT(orders.id) as order_count,
                SUM(orders.total) as ltv,
                AVG(orders.total) as avg_order,
                MIN(orders.created_at) as first_order,
                MAX(orders.created_at) as last_order,
                DATEDIFF(MAX(orders.created_at), MIN(orders.created_at)) as days_active
            ')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.created_at')
            ->orderByDesc('ltv')
            ->limit(50)
            ->get();

        $repeatRate = DB::table('orders')
            ->whereNotNull('user_id')
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw('user_id, COUNT(*) as order_count')
            ->groupBy('user_id')
            ->get();

        $repeatCount = $repeatRate->filter(fn ($r) => $r->order_count > 1)->count();
        $totalBuyers = $repeatRate->count();

        $cohorts = DB::table('users')
            ->selectRaw("DATE_FORMAT(users.created_at, '%Y-%m') as cohort, COUNT(DISTINCT users.id) as new_customers, COUNT(DISTINCT orders.user_id) as buyers, SUM(orders.total) as revenue")
            ->leftJoin('orders', function ($join) {
                $join->on('orders.user_id', '=', 'users.id')->whereNotIn('orders.status', ['cancelled']);
            })
            ->whereYear('users.created_at', '>=', now()->year - 1)
            ->groupByRaw("DATE_FORMAT(users.created_at, '%Y-%m')")
            ->orderByRaw("DATE_FORMAT(users.created_at, '%Y-%m') DESC")
            ->limit(12)
            ->get();

        $stats = [
            'total_buyers'    => $totalBuyers,
            'repeat_buyers'   => $repeatCount,
            'repeat_rate'     => $totalBuyers > 0 ? round(($repeatCount / $totalBuyers) * 100, 1) : 0,
            'avg_ltv'         => $topLtv->avg('ltv') ?? 0,
            'avg_order_count' => $repeatRate->avg('order_count') ?? 0,
        ];

        return view('admin.reports.customer-ltv', compact('topLtv', 'cohorts', 'stats', 'request'));
    }

    private function resolveDateRange(Request $request): array
    {
        $period = $request->input('period', 'month');

        if ($period === 'custom' || ($request->filled('start_date') && $request->filled('end_date'))) {
            $start = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
            $end   = Carbon::parse($request->input('end_date', now()))->endOfDay();
            return [$start, $end];
        }

        return match ($period) {
            'today'  => [now()->startOfDay(), now()->endOfDay()],
            'week'   => [now()->startOfWeek(), now()->endOfWeek()],
            'year'   => [now()->startOfYear(), now()->endOfYear()],
            default  => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}

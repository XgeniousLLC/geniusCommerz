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

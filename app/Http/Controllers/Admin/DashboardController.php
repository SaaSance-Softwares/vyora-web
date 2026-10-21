<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\Review;
use App\Models\Sku;
use App\Models\ThemeSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $yesterdayStart = $now->copy()->subDay()->startOfDay();
        $yesterdayEnd = $now->copy()->subDay()->endOfDay();
        $monthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Today vs Yesterday Pulse
        $todayValidOrders = Order::where('created_at', '>=', $todayStart)
            ->whereNotIn('status', ['cancelled', 'failed']);
        $todayRevenue = (float) (clone $todayValidOrders)->sum('total_amount');
        $todayOrdersCount = (clone $todayValidOrders)->count();

        $yesterdayRevenue = (float) Order::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->sum('total_amount');

        // This Month vs Last Month Pulse
        $monthValidOrders = Order::where('created_at', '>=', $monthStart)
            ->whereNotIn('status', ['cancelled', 'failed']);
        $monthRevenue = (float) (clone $monthValidOrders)->sum('total_amount');
        $monthOrdersCount = (clone $monthValidOrders)->count();
        $monthDiscounts = (float) (clone $monthValidOrders)->sum('discount_amount');
        $monthAov = $monthOrdersCount > 0 ? ($monthRevenue / $monthOrdersCount) : 0;

        $lastMonthRevenue = (float) Order::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->sum('total_amount');

        $monthGrowthPct = $lastMonthRevenue > 0
            ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : ($monthRevenue > 0 ? 100.0 : null);

        // Operational Action Counters
        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $processingOrdersCount = Order::where('status', 'processing')->count();
        $agingOrdersCount = Order::whereIn('status', ['pending', 'processing'])
            ->where('created_at', '<=', $now->copy()->subHours(48))
            ->count();

        $pendingRmaCount = ReturnRequest::where('status', 'pending')->count();
        $activeRmaCount = ReturnRequest::whereIn('status', ['pending', 'approved', 'received'])->count();
        $pendingReviewsCount = Review::where('is_approved', false)->count();

        $outOfStockSkus = Sku::where('stock', '<=', 0)->count();
        $lowStockSkus = Sku::where('stock', '>', 0)->where('stock', '<=', 5)->count();

        $stats = [
            'today_revenue' => $todayRevenue,
            'today_orders' => $todayOrdersCount,
            'yesterday_revenue' => $yesterdayRevenue,
            'month_revenue' => $monthRevenue,
            'month_orders' => $monthOrdersCount,
            'month_discounts' => $monthDiscounts,
            'month_aov' => $monthAov,
            'month_growth_pct' => $monthGrowthPct,

            'total_orders' => Order::count(),
            'pending_orders' => $pendingOrdersCount,
            'processing_orders' => $processingOrdersCount,
            'orders_to_ship' => $pendingOrdersCount + $processingOrdersCount,
            'aging_orders' => $agingOrdersCount,
            'total_products' => Product::where('is_active', true)->count(),
            'revenue' => (float) Order::whereNotIn('status', ['cancelled', 'failed'])->sum('total_amount'),
            'return_requests' => $pendingRmaCount,
            'active_rma_count' => $activeRmaCount,
            'pending_reviews' => $pendingReviewsCount,
            'cancel_requests' => Order::where('status', 'cancel_requested')->count(),
            'pending_payments' => (float) Order::where('balance_due', '>', 0)->where('status', '!=', 'cancelled')->sum('balance_due'),
            'out_of_stock' => $outOfStockSkus,
            'low_stock' => $lowStockSkus,
            'cancelled_amount' => (float) Order::where('status', 'cancelled')->sum('total_amount'),

            // Pipeline counts
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'returned_orders' => Order::whereIn('status', ['returned', 'refunded', 'exchanged'])->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
        ];

        $recent_orders = Order::with(['user', 'shippingAddress'])->latest()->take(7)->get();

        // Top 5 Low-Stock Active SKUs that need restocking
        $low_stock_items = DB::table('skus')
            ->join('products', 'skus.product_id', '=', 'products.id')
            ->leftJoin('colors', 'skus.color_id', '=', 'colors.id')
            ->leftJoin('sizes', 'skus.size_id', '=', 'sizes.id')
            ->where('products.is_active', true)
            ->where('skus.stock', '<=', 5)
            ->select(
                'skus.id',
                'skus.product_id',
                'products.name as product_name',
                'skus.code as sku_code',
                'colors.name as color_name',
                'sizes.name as size_name',
                'skus.stock'
            )
            ->orderBy('skus.stock', 'asc')
            ->limit(5)
            ->get();

        $currencySymbol = ThemeSetting::where('key', 'currency_symbol')->value('value') ?? '₹';

        return view('admin.dashboard', compact('stats', 'recent_orders', 'low_stock_items', 'currencySymbol'));
    }

    public function chartsData(Request $request)
    {
        $range = $request->query('range', 'this_month');
        $now = Carbon::now();

        switch ($range) {
            case 'this_week':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                $groupBy = 'date';
                break;
            case 'this_month':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $groupBy = 'date';
                break;
            case 'this_quarter':
                $startDate = $now->copy()->startOfQuarter();
                $endDate = $now->copy()->endOfQuarter();
                $groupBy = 'month';
                break;
            case 'this_year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                $groupBy = 'month';
                break;
            case 'previous_week':
                $startDate = $now->copy()->subWeek()->startOfWeek();
                $endDate = $now->copy()->subWeek()->endOfWeek();
                $groupBy = 'date';
                break;
            case 'previous_month':
                $startDate = $now->copy()->subMonth()->startOfMonth();
                $endDate = $now->copy()->subMonth()->endOfMonth();
                $groupBy = 'date';
                break;
            case 'previous_quarter':
                $startDate = $now->copy()->subQuarter()->startOfQuarter();
                $endDate = $now->copy()->subQuarter()->endOfQuarter();
                $groupBy = 'month';
                break;
            case 'previous_year':
                $startDate = $now->copy()->subYear()->startOfYear();
                $endDate = $now->copy()->subYear()->endOfYear();
                $groupBy = 'month';
                break;
            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $groupBy = 'date';
        }

        // Sales Data
        $salesQuery = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->select([
                DB::raw($groupBy === 'month' ? "DATE_FORMAT(created_at, '%Y-%m') as label" : "DATE(created_at) as label"),
                DB::raw('COUNT(id) as count'),
                DB::raw('SUM(total_amount) as amount'),
            ])
            ->groupBy('label')
            ->orderBy('label');

        $salesResults = $salesQuery->get()->keyBy('label');

        $labels = [];
        $counts = [];
        $amounts = [];

        // Generate full date range to ensure zero fill
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $label = $groupBy === 'month' ? $current->format('Y-m') : $current->format('Y-m-d');

            if (! in_array($label, $labels)) {
                $labels[] = $label;

                $data = $salesResults->get($label);
                $counts[] = $data ? (int) $data->count : 0;
                $amounts[] = $data ? (float) $data->amount : 0;
            }

            $groupBy === 'month' ? $current->addMonth() : $current->addDay();
        }

        // Shipping Data
        $shippingStats = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(id) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $shippingData = [
            'pending' => $shippingStats['pending'] ?? 0,
            'packed' => ($shippingStats['processing'] ?? 0),
            'shipped' => $shippingStats['shipped'] ?? 0,
            'delivered' => $shippingStats['delivered'] ?? 0,
            'cancelled' => $shippingStats['cancelled'] ?? 0,
            'returned' => ($shippingStats['returned'] ?? 0) + ($shippingStats['exchanged'] ?? 0) + ($shippingStats['refunded'] ?? 0),
        ];

        $shippingTotal = array_sum($shippingData);

        return response()->json([
            'sales' => [
                'labels' => $labels,
                'counts' => $counts,
                'amounts' => $amounts,
            ],
            'shipping' => [
                'data' => array_values($shippingData),
                'total' => $shippingTotal,
            ],
        ]);
    }
}

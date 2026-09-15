<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PosAnalysisController extends Controller
{
    public function index(Request $request)
    {
        // Filters
        $startDateInput = $request->input('start_date', $request->input('date', Carbon::today()->toDateString()));
        $endDateInput = $request->input('end_date', $startDateInput);
        
        $locationId = $request->input('pos_location_id', 'all');

        $start = Carbon::parse($startDateInput)->startOfDay();
        $end = Carbon::parse($endDateInput)->endOfDay();

        // Base Query for POS Orders
        $query = Order::whereNotNull('pos_location_id')
                      ->whereBetween('created_at', [$start, $end])
                      // Only count paid/completed type orders
                      ->whereNotIn('status', ['cancelled', 'failed']);

        if ($locationId !== 'all') {
            $query->where('pos_location_id', $locationId);
        }

        // Metrics
        $totalSales = (clone $query)->sum('total_amount');
        $totalOrders = (clone $query)->count();
        $uniqueCustomers = (clone $query)->distinct('customer_phone')->count('customer_phone');
        
        $aov = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
        $aovPerCustomer = $uniqueCustomers > 0 ? $totalSales / $uniqueCustomers : 0;

        // Fetch Location Names mapped by ID
        $locations = DB::table('pos_locations')->get();
        $locationMap = $locations->pluck('name', 'id');

        // Store Breakdown
        $storeStats = (clone $query)
            ->selectRaw('pos_location_id, COUNT(id) as orders_count, SUM(total_amount) as total_sales, COUNT(DISTINCT customer_phone) as unique_customers')
            ->groupBy('pos_location_id')
            ->get()
            ->map(function ($stat) use ($locationMap) {
                $stat->store_name = $locationMap[$stat->pos_location_id] ?? 'Unknown Store';
                $stat->aov = $stat->orders_count > 0 ? $stat->total_sales / $stat->orders_count : 0;
                return $stat;
            })
            ->sortByDesc('total_sales');

        // Variant Performance
        // Join orders, order_items, skus, colors, sizes
        $variantQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('pos_locations', 'orders.pos_location_id', '=', 'pos_locations.id')
            ->leftJoin('skus', 'order_items.sku_id', '=', 'skus.id')
            ->leftJoin('colors', 'skus.color_id', '=', 'colors.id')
            ->leftJoin('sizes', 'skus.size_id', '=', 'sizes.id')
            ->whereNotNull('orders.pos_location_id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereNotIn('orders.status', ['cancelled', 'failed']);

        if ($locationId !== 'all') {
            $variantQuery->where('orders.pos_location_id', $locationId);
        }

        $variantStats = $variantQuery
            ->selectRaw('
                pos_locations.name as store_name,
                order_items.product_name,
                colors.name as color_name,
                sizes.name as size_name,
                SUM(order_items.quantity - COALESCE(order_items.returned_quantity, 0)) as net_qty_sold,
                SUM(order_items.total) as total_revenue
            ')
            ->groupBy('pos_locations.name', 'order_items.product_name', 'colors.name', 'sizes.name')
            ->orderByDesc('net_qty_sold')
            ->limit(50)
            ->get();

        return view('admin.pos-analysis.index', compact(
            'startDateInput',
            'endDateInput',
            'locationId',
            'totalSales',
            'totalOrders',
            'aov',
            'aovPerCustomer',
            'storeStats',
            'variantStats',
            'locations'
        ));
    }
}

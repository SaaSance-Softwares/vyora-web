<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_products' => Product::count(),
            'revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'return_requests' => Order::where('status', 'return_requested')->count(),
            'cancel_requests' => Order::where('status', 'cancel_requested')->count(),
            'pending_payments' => Order::where('balance_due', '>', 0)->where('status', '!=', 'cancelled')->sum('balance_due'),
            'out_of_stock' => \App\Models\Sku::where('stock', 0)->count(),
            'cancelled_amount' => Order::where('status', 'cancelled')->sum('total_amount'),
            
            // Third row stats
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'returned_orders' => Order::whereIn('status', ['returned', 'refunded'])->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
        ];

        $recent_orders = Order::with('user')->latest()->take(5)->get();
        $currencySymbol = \App\Models\ThemeSetting::where('key', 'currency_symbol')->value('value') ?? '₹';

        return view('admin.dashboard', compact('stats', 'recent_orders', 'currencySymbol'));
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
            ->where('status', '!=', 'cancelled')
            ->select([
                DB::raw($groupBy === 'month' ? "DATE_FORMAT(created_at, '%Y-%m') as label" : "DATE(created_at) as label"),
                DB::raw('COUNT(id) as count'),
                DB::raw('SUM(total_amount) as amount')
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
            
            // Avoid duplicate month labels
            if (!in_array($label, $labels)) {
                $labels[] = $label;
                
                $data = $salesResults->get($label);
                $counts[] = $data ? (int)$data->count : 0;
                $amounts[] = $data ? (float)$data->amount : 0;
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
            ]
        ]);
    }
}

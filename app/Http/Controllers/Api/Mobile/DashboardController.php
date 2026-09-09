<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Define completed status. E.g. anything not cancelled/failed.
        $totalSales = Order::whereNotIn('status', ['cancelled', 'refunded'])->sum('total_amount');
        
        $totalOrders = Order::count();
        
        $codOrders = Order::where('payment_method', 'cod')->count();
        $paidOrders = Order::where('payment_method', '!=', 'cod')->count();

        // 7 Day Chart Data
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subDays($i)->format('Y-m-d');
            $daySales = Order::whereNotIn('status', ['cancelled', 'refunded'])
                ->whereDate('created_at', $date)
                ->sum('total_amount');
            $dayOrders = Order::whereDate('created_at', $date)->count();
            
            $chartData[] = [
                'date' => \Carbon\Carbon::parse($date)->format('M d'),
                'sales' => (float)$daySales,
                'orders' => $dayOrders,
            ];
        }

        return response()->json([
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'cod_orders' => $codOrders,
            'paid_orders' => $paidOrders,
            'chart_data' => $chartData,
        ]);
    }

    public function subscription()
    {
        try {
            $apiKeyRow = \App\Models\ThemeSetting::where('group', 'integration.saasance')->where('key', 'api_key')->first();
            $apiKey = $apiKeyRow && $apiKeyRow->value ? \Illuminate\Support\Facades\Crypt::decryptString($apiKeyRow->value) : null;
        } catch (\Exception $e) {
            $apiKey = null;
            \Illuminate\Support\Facades\Log::error('API Key Decrypt Error: ' . $e->getMessage());
        }
        
        if (!$apiKey) {
            return response()->json([
                'status' => 'inactive',
                'plan_type' => 'Not Connected',
                'expires_at' => null,
            ]);
        }
        
        $saasanceUrl = env('SAASANCE_RELAY_URL', 'https://saasance.com/api/sso/push-relay');
        $parsedUrl = parse_url($saasanceUrl);
        
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $subscriptionUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $port . '/api/sso/store-subscription';
        
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'X-SaaSance-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(5)->get($subscriptionUrl);
            
            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mobile App Subscription Check Failed: ' . $e->getMessage());
        }
        
        return response()->json([
            'status' => 'unknown',
            'plan_type' => 'Unknown',
            'expires_at' => null,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserOrderActionController extends Controller
{
    private function getShippingSettings()
    {
        $setting = ThemeSetting::where('key', 'shipping_rules')->first();
        return $setting ? json_decode($setting->value, true) : null;
    }

    private function calculateFee($order, $action)
    {
        $settings = $this->getShippingSettings();
        $method = strtolower($order->payment_method) === 'cod' ? 'cod' : 'prepaid';
        
        $feePercent = 0;
        if ($settings && isset($settings[$method])) {
            $feePercent = (float) ($settings[$method][$action . '_fee'] ?? 0);
        }

        // Calculate fee as percentage of the base order value (items total, excluding shipping/COD fees)
        $baseOrderValue = $order->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        return round(($feePercent / 100) * $baseOrderValue, 2);
    }

    public function cancel(Request $request, $uuid)
    {
        $order = Order::where('uuid', $uuid)->where('user_id', Auth::id())->firstOrFail();

        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json(['success' => false, 'message' => 'Order cannot be cancelled at this stage.'], 400);
        }

        $fee = $this->calculateFee($order, 'cancel');

        $order->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.',
            'fee_applied' => $fee,
        ]);
    }

    public function returnOrder(Request $request, $uuid)
    {
        $order = Order::with('items.sku.product')->where('uuid', $uuid)->where('user_id', Auth::id())->firstOrFail();

        if ($order->status !== 'delivered') {
            return response()->json(['success' => false, 'message' => 'Only delivered orders can be returned.'], 400);
        }

        $hasNonReturnable = $order->items->some(function ($item) {
            return optional(optional(optional($item)->sku)->product)->is_returnable === 0 || optional(optional(optional($item)->sku)->product)->is_returnable === false;
        });

        if ($hasNonReturnable) {
            return response()->json(['success' => false, 'message' => 'This order contains non-returnable items and cannot be returned.'], 400);
        }

        $fee = $this->calculateFee($order, 'return');

        $order->update([
            'status' => 'return_requested',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Return requested successfully.',
            'fee_applied' => $fee,
        ]);
    }

    public function exchange(Request $request, $uuid)
    {
        $order = Order::where('uuid', $uuid)->where('user_id', Auth::id())->firstOrFail();

        if ($order->status !== 'delivered') {
            return response()->json(['success' => false, 'message' => 'Only delivered orders can be exchanged.'], 400);
        }

        $fee = $this->calculateFee($order, 'exchange');

        $order->update([
            'status' => 'exchange_requested',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Exchange requested successfully.',
            'fee_applied' => $fee,
        ]);
    }
}

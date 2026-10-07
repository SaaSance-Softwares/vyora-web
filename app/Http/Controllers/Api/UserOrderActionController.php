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

    private function calculateFee($order, $action, $requestedItems = null)
    {
        $settings = $this->getShippingSettings();
        $method = strtolower($order->payment_method) === 'cod' ? 'cod' : 'prepaid';
        
        $flatFee = 0;
        if ($settings && isset($settings[$method])) {
            $flatFee = (float) ($settings[$method][$action . '_fee'] ?? 0);
        }

        $baseOrderValue = 0;
        if ($requestedItems && is_array($requestedItems) && count($requestedItems) > 0) {
            foreach ($requestedItems as $reqItem) {
                $orderItem = $order->items->where('id', $reqItem['id'])->first();
                if ($orderItem) {
                    $baseOrderValue += $orderItem->price * $reqItem['quantity'];
                }
            }
        } else {
            $baseOrderValue = $order->items->sum(function ($item) {
                return $item->price * ($item->quantity - ($item->returned_quantity ?? 0));
            });
        }

        return round(min($flatFee, $baseOrderValue), 2);
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

        $requestedItems = $request->input('items');
        $fee = $this->calculateFee($order, 'return', $requestedItems);

        $notes = $order->notes ? $order->notes . "\n\n" : "";
        $notes .= "--- Return Requested on " . now()->format('Y-m-d H:i:s') . " ---\n";
        
        if (is_array($requestedItems) && count($requestedItems) > 0) {
            $notes .= "User wants to return specific items:\n";
            foreach ($requestedItems as $reqItem) {
                $orderItem = $order->items->where('id', $reqItem['id'])->first();
                if ($orderItem) {
                    $notes .= "- " . $reqItem['quantity'] . "x " . $orderItem->product_name . ($orderItem->variant_name ? ' (' . $orderItem->variant_name . ')' : '') . "\n";
                }
            }
        } else {
            $notes .= "User wants to return the entire order.\n";
        }
        
        $notes .= "\n* Estimated Return Fee: ₹" . number_format($fee, 2) . "\n";

        $order->update([
            'status' => 'return_requested',
            'notes' => $notes,
            'return_request_data' => [
                'type' => 'return',
                'fee' => $fee,
                'items' => $requestedItems,
                'requested_at' => now()->toDateTimeString(),
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Return requested successfully.',
            'fee_applied' => $fee,
        ]);
    }

    public function exchange(Request $request, $uuid)
    {
        $order = Order::with('items')->where('uuid', $uuid)->where('user_id', Auth::id())->firstOrFail();

        if ($order->status !== 'delivered') {
            return response()->json(['success' => false, 'message' => 'Only delivered orders can be exchanged.'], 400);
        }

        $requestedItems = $request->input('items');
        $fee = $this->calculateFee($order, 'exchange', $requestedItems);

        $notes = $order->notes ? $order->notes . "\n\n" : "";
        $notes .= "--- Exchange Requested on " . now()->format('Y-m-d H:i:s') . " ---\n";
        
        if (is_array($requestedItems) && count($requestedItems) > 0) {
            $notes .= "User wants to exchange specific items:\n";
            foreach ($requestedItems as $reqItem) {
                $orderItem = $order->items->where('id', $reqItem['id'])->first();
                if ($orderItem) {
                    $notes .= "- " . $reqItem['quantity'] . "x " . $orderItem->product_name . ($orderItem->variant_name ? ' (' . $orderItem->variant_name . ')' : '') . "\n";
                }
            }
        } else {
            $notes .= "User wants to exchange the entire order.\n";
        }
        
        $notes .= "\n* Estimated Exchange Fee: ₹" . number_format($fee, 2) . "\n";

        $order->update([
            'status' => 'exchange_requested',
            'notes' => $notes,
            'return_request_data' => [
                'type' => 'exchange',
                'fee' => $fee,
                'items' => $requestedItems,
                'requested_at' => now()->toDateTimeString(),
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Exchange requested successfully.',
            'fee_applied' => $fee,
        ]);
    }
}

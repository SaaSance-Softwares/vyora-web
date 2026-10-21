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
        
        $feePercent = 0;
        if ($settings && isset($settings[$method])) {
            $feePercent = (float) ($settings[$method][$action . '_fee'] ?? 0);
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

        $requestedItems = $request->input('items');
        $fee = $this->calculateFee($order, 'return', $requestedItems);

        $rma = \App\Models\ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'type' => 'return',
            'status' => 'pending',
            'fee_deducted' => $fee,
            'reason' => $request->input('reason', ''),
            'comments' => $request->input('comments', ''),
        ]);

        if (is_array($requestedItems)) {
            foreach ($requestedItems as $reqItem) {
                \App\Models\ReturnRequestItem::create([
                    'return_request_id' => $rma->id,
                    'order_item_id' => $reqItem['id'],
                    'quantity' => $reqItem['quantity'],
                ]);
            }
        }

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

        $rma = \App\Models\ReturnRequest::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'type' => 'exchange',
            'status' => 'pending',
            'payment_status' => $fee > 0 ? 'pending' : null,
            'fee_deducted' => $fee,
            'reason' => $request->input('reason', ''),
            'comments' => $request->input('comments', ''),
        ]);

        if (is_array($requestedItems)) {
            foreach ($requestedItems as $reqItem) {
                \App\Models\ReturnRequestItem::create([
                    'return_request_id' => $rma->id,
                    'order_item_id' => $reqItem['id'],
                    'quantity' => $reqItem['quantity'],
                    'exchange_variant_id' => $reqItem['exchange_variant_id'] ?? null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $fee > 0 ? 'Exchange requested. Payment required.' : 'Exchange requested successfully.',
            'fee_applied' => $fee,
            'requires_payment' => $fee > 0,
            'rma_id' => $rma->id
        ]);
    }

    public function cancelReturnRequest(Request $request, $id)
    {
        $rma = \App\Models\ReturnRequest::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($rma->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Only pending requests can be cancelled.'], 400);
        }

        $rma->delete();

        return response()->json([
            'success' => true,
            'message' => 'Request cancelled successfully.',
        ]);
    }
}

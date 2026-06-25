<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleQikink(Request $request)
    {
        Log::info('Qikink Webhook Received:', $request->all());

        // According to common webhook structures for order updates:
        $orderNumber = $request->input('order_number') ?? $request->input('order_id');
        $status = $request->input('status');

        if ($orderNumber && $status) {
            $order = Order::where('order_number', $orderNumber)->first();
            if ($order) {
                // Map Qikink status to local status if necessary
                $mappedStatus = strtolower($status);
                // Assume status mapped correctly or handled dynamically
                $order->status = $mappedStatus;
                $order->save();
            }
        }

        return response()->json(['success' => true]);
    }

    public function handleShiprocket(Request $request)
    {
        // Verify x-api-key if set
        $tokenRow = \App\Models\ThemeSetting::where('group', 'integration.shiprocket')
            ->where('key', 'shiprocket_webhook_token')->first();
            
        if ($tokenRow) {
            $expectedToken = \Illuminate\Support\Facades\Crypt::decryptString($tokenRow->value);
            if ($request->header('x-api-key') !== $expectedToken) {
                Log::warning('Shiprocket Webhook rejected: Invalid x-api-key');
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        Log::info('Shiprocket Webhook Received:', $request->all());

        $awb = $request->input('awb');
        $courierName = $request->input('courier_name');
        $status = $request->input('current_status');
        $srOrderId = $request->input('order_id');
        $channelOrderId = $request->input('channel_order_id');

        $order = null;

        if ($srOrderId) {
            $order = Order::where('shiprocket_order_id', $srOrderId)->first();
        }
        
        if (!$order && $channelOrderId) {
            // channel_order_id is usually "ORD-XYZ-12345" based on our creation logic
            $baseOrderNumber = explode('-', $channelOrderId)[0] . '-' . explode('-', $channelOrderId)[1];
            $order = Order::where('order_number', $baseOrderNumber)->first();
        }

        if ($order) {
            $updateData = [];
            
            if ($awb && !$order->tracking_number) {
                $updateData['tracking_number'] = $awb;
                $updateData['courier_partner'] = $courierName;
                $updateData['tracking_url'] = "https://shiprocket.co/tracking/" . $awb;
            }

            if ($status) {
                $statusUpper = strtoupper($status);
                if (in_array($statusUpper, ['SHIPPED', 'IN TRANSIT', 'OUT FOR DELIVERY']) && $order->status !== 'shipped') {
                    $updateData['status'] = 'shipped';
                    if (!$order->shipped_at) $updateData['shipped_at'] = now();
                } elseif ($statusUpper === 'DELIVERED' && $order->status !== 'delivered') {
                    $updateData['status'] = 'delivered';
                    if (!$order->delivered_at) $updateData['delivered_at'] = now();
                } elseif ($statusUpper === 'CANCELED' || $statusUpper === 'CANCELLED') {
                    $updateData['status'] = 'cancelled';
                }
            }

            if (!empty($updateData)) {
                $order->update($updateData);

                // If status changed to shipped, we might want to notify the user.
                // OrderController has fireShippedNotification, but here we can just rely on the webhook
                // triggering a simple event or just let it update silently since tracking is already sent.
                // We'll leave it simple for now.
            }
        }

        return response()->json(['success' => true]);
    }
}

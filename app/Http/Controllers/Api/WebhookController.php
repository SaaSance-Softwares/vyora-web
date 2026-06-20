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
}

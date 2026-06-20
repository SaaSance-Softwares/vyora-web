<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderShippedNotification;
use App\Services\TwilioSmsService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /* ------------------------------------------------------------------ */
    /*  Index — Orders List */
    /* ------------------------------------------------------------------ */

    public function index(Request $request)
    {
        $query = Order::with(['shippingAddress', 'items'])
            ->withCount('items');

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('shippingAddress', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // Payment method filter
        if ($paymentMethod = $request->input('payment_method')) {
            $query->where('payment_method', 'like', "%{$paymentMethod}%");
        }

        // Date range
        if ($from = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $orders = $query->latest()->paginate(25)->withQueryString();

        // Stats for filter tabs
        $stats = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        $stats['all'] = array_sum($stats);

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    /* ------------------------------------------------------------------ */
    /*  Show — Order Detail */
    /* ------------------------------------------------------------------ */

    public function show(Order $order)
    {
        $order->load([
            'items.product.images',
            'items.sku.attributeValues.attribute',
            'shippingAddress',
            'billingAddress',
            'user',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    /* ------------------------------------------------------------------ */
    /*  Update Status */
    /* ------------------------------------------------------------------ */

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
            'courier_partner' => 'nullable|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|url|max:2048',
            'notes' => 'nullable|string',
        ]);

        $previousStatus = $order->status;

        $updateData = ['status' => $validated['status']];

        // Save tracking fields when provided
        if (array_key_exists('courier_partner', $validated)) {
            $updateData['courier_partner'] = $validated['courier_partner'];
        }
        if (array_key_exists('tracking_number', $validated)) {
            $updateData['tracking_number'] = $validated['tracking_number'];
        }
        if (array_key_exists('tracking_url', $validated)) {
            $updateData['tracking_url'] = $validated['tracking_url'];
        }
        if (array_key_exists('notes', $validated)) {
            $updateData['notes'] = $validated['notes'];
        }

        // Set timestamps
        if ($validated['status'] === 'shipped' && $previousStatus !== 'shipped') {
            $updateData['shipped_at'] = now();
        }
        if ($validated['status'] === 'delivered' && $previousStatus !== 'delivered') {
            $updateData['delivered_at'] = now();
        }

        $order->update($updateData);

        // Fire shipped notification
        if ($validated['status'] === 'shipped' && $previousStatus !== 'shipped') {
            $this->fireShippedNotification($order);
        }

        // Fire cancelled SMS and WhatsApp
        if ($validated['status'] === 'cancelled' && $previousStatus !== 'cancelled') {
            app(TwilioSmsService::class)->sendEventSms('cancelled', $order);
            app(WhatsAppService::class)->sendEventWhatsApp('cancelled', $order);
        }

        return back()->with('success', 'Order updated successfully.');
    }

    /* ------------------------------------------------------------------ */
    /*  Update Tracking (dedicated endpoint) */
    /* ------------------------------------------------------------------ */

    public function updateTracking(Request $request, Order $order)
    {
        $validated = $request->validate([
            'courier_partner' => 'nullable|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|url|max:2048',
        ]);

        $order->update($validated);

        // If already shipped, re-send notification with new tracking info
        if ($order->status === 'shipped') {
            $this->fireShippedNotification($order->fresh());
        }

        return back()->with('success', 'Tracking information updated. Customer notified.');
    }

    /* ------------------------------------------------------------------ */
    /*  Private helpers */
    /* ------------------------------------------------------------------ */

    private function fireShippedNotification(Order $order): void
    {
        try {
            $order->load(['shippingAddress', 'items']);

            // Email to the customer (via user account or shipping address email)
            $notifiable = $order->user;

            if (! $notifiable && $order->shippingAddress?->email) {
                // Create an anonymous notifiable with an email
                $notifiable = new AnonymousNotifiable;
                $notifiable->route('mail', $order->shippingAddress->email);
                $notifiable->notify(new OrderShippedNotification($order));
            } elseif ($notifiable) {
                $notifiable->notify(new OrderShippedNotification($order));
            }

            // SMS + WhatsApp stubs (log only — wire to real provider later)
            // OrderShippedNotification::sendSmsStub($order);
            // OrderShippedNotification::sendWhatsAppStub($order);

            // Dispatch Twilio SMS and WhatsApp
            app(TwilioSmsService::class)->sendEventSms('shipped', $order);
            app(WhatsAppService::class)->sendEventWhatsApp('shipped', $order);

        } catch (\Exception $e) {
            Log::error('Failed to send OrderShippedNotification', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

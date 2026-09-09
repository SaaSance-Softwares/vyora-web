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
            'orderStatus',
        ]);

        $isQikinkEnabled = \App\Models\ThemeSetting::where('group', 'integration.qikink')->where('key', 'enabled')->value('value') === '1';
        
        $statusesQuery = \App\Models\OrderStatus::orderBy('sort_order');
        if (!$isQikinkEnabled) {
            $statusesQuery->where(function($q) {
                $q->whereNull('fulfillment_type')
                  ->orWhere('fulfillment_type', '!=', 'QikInk')
                  ->orWhere('is_system', true);
            });
        }
        $orderStatuses = $statusesQuery->get();

        return view('admin.orders.show', compact('order', 'orderStatuses'));
    }

    /* ------------------------------------------------------------------ */
    /*  Retry Qikink Sync */
    /* ------------------------------------------------------------------ */

    public function retryQikink(Order $order)
    {
        $order->update([
            'qikink_sync_attempts' => 0,
            'qikink_sync_error' => null,
        ]);

        return redirect()->back()->with('success', 'Order queued for Qikink sync. It will be processed within the next minute.');
    }

    /* ------------------------------------------------------------------ */
    /*  Update Status */
    /* ------------------------------------------------------------------ */

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->strictValidate([
            'order_status_id' => 'required|exists:order_statuses,id',
            'courier_partner' => 'nullable|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|string|url|max:2048',
            'notes' => 'nullable|string|max:5000',
        ]);

        $previousStatusId = $order->order_status_id;
        $newStatusId = $validated['order_status_id'];
        
        $newStatus = \App\Models\OrderStatus::with(['smsTemplate', 'emailTemplate', 'whatsappTemplate'])->find($newStatusId);
        $statusName = $newStatus->name;
        $statusNameLower = strtolower($statusName);

        $updateData = [
            'order_status_id' => $newStatusId,
            'status' => strtolower($statusName) // fallback
        ];

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
        $previousStatusNameLower = strtolower($order->orderStatus?->name ?? $order->status);
        if ($statusNameLower === 'shipped' && $previousStatusNameLower !== 'shipped') {
            $updateData['shipped_at'] = now();
        }
        if ($statusNameLower === 'delivered' && $previousStatusNameLower !== 'delivered') {
            $updateData['delivered_at'] = now();
        }

        $order->update($updateData);

        // Fire notifications for any status change
        if ($newStatusId !== $previousStatusId) {
            
            // Handle dynamic Email Template
            if ($newStatus->emailTemplate) {
                try {
                    $email = $order->user?->email ?? $order->shippingAddress?->email;
                    if ($email) {
                        \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\DynamicOrderStatusEmail($order, $newStatus->emailTemplate));
                    }
                } catch (\Exception $e) {
                    \Log::error("Failed to send Dynamic Order Status Email ({$statusName}): ".$e->getMessage());
                }
            } else {
                // Fallback to legacy hardcoded email if we want, or just do nothing.
                // It's better to just do nothing if no template is attached.
                if (in_array($statusNameLower, ['shipped', 'cancelled', 'delivered', 'returned'])) {
                     try {
                        $email = $order->user?->email ?? $order->shippingAddress?->email;
                        if ($email) {
                            \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\OrderStatusEmail($order, $statusNameLower));
                        }
                    } catch (\Exception $e) {
                        \Log::error("Failed to send Legacy Order Status Email ({$statusNameLower}): ".$e->getMessage());
                    }
                }
            }

            // Handle dynamic SMS Template
            if ($newStatus->smsTemplate) {
                try {
                    app(\App\Services\TwilioSmsService::class)->sendDynamicSms($order, $newStatus->smsTemplate);
                } catch (\Exception $e) {
                    \Log::error("Failed to send Dynamic Order SMS ({$statusName}): ".$e->getMessage());
                }
            }

            // Handle dynamic WhatsApp Template
            if ($newStatus->whatsappTemplate) {
                try {
                    app(\App\Services\WhatsAppService::class)->sendDynamicWhatsApp($order, $newStatus->whatsappTemplate);
                } catch (\Exception $e) {
                    \Log::error("Failed to send Dynamic Order WhatsApp ({$statusName}): ".$e->getMessage());
                }
            }
            
            // Standard Push Notification
            try {
                if ($order->user) {
                    $statusTitles = [
                        'processing' => 'Order Processing ⏳',
                        'shipped' => 'Order Shipped 🚚',
                        'delivered' => 'Order Delivered 🎉',
                        'cancelled' => 'Order Cancelled ❌',
                    ];
                    
                    app(\App\Services\PushNotificationService::class)->sendToUser(
                        $order->user,
                        $statusTitles[$statusNameLower] ?? "Order Update: {$statusName}",
                        "Your order #{$order->id} is now {$statusName}.",
                        ['type' => 'order', 'id' => $order->id]
                    );
                }
            } catch (\Exception $e) {
                \Log::error("Failed to send Order Push ({$statusNameLower}): ".$e->getMessage());
            }
        }
        return back()->with('success', 'Order status updated successfully.');
    }

    /* ------------------------------------------------------------------ */
    /*  Update Payment Status */
    /* ------------------------------------------------------------------ */

    public function updatePaymentStatus(Request $request, Order $order)
    {
        $validated = $request->strictValidate([
            'payment_status' => 'required|string|in:pending,paid,failed',
            'payment_received_via' => 'nullable|string|max:255',
            'payment_received_by' => 'nullable|string|max:255',
        ]);

        $updateData = [
            'payment_status' => $validated['payment_status'],
            'payment_received_via' => $validated['payment_received_via'],
            'payment_received_by' => $validated['payment_received_by'],
        ];

        // If marked as paid, we might want to update amount_paid and balance_due
        if ($validated['payment_status'] === 'paid' && $order->payment_status !== 'paid') {
            $updateData['amount_paid'] = $order->total_amount;
            $updateData['balance_due'] = 0;
        } elseif ($validated['payment_status'] !== 'paid') {
            // Reset if marked back to pending or failed (optional logic)
            $updateData['amount_paid'] = 0;
            $updateData['balance_due'] = $order->total_amount;
        }

        $order->update($updateData);

        return back()->with('success', 'Payment status updated successfully.');
    }

    /* ------------------------------------------------------------------ */
    /*  Update Tracking (dedicated endpoint) */
    /* ------------------------------------------------------------------ */

    public function updateTracking(Request $request, Order $order)
    {
        $validated = $request->strictValidate([
            'courier_partner' => 'nullable|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
            'tracking_url' => 'nullable|string|url|max:2048',
        ]);

        $order->update($validated);

        // If already shipped, re-send notification with new tracking info
        if ($order->status === 'shipped') {
            $this->fireShippedNotification($order->fresh());
        }

        return back()->with('success', 'Tracking information updated. Customer notified.');
    }

    /* ------------------------------------------------------------------ */
    /*  Shiprocket Integration */
    /* ------------------------------------------------------------------ */

    public function sendToShiprocket(Order $order, \App\Services\ShiprocketService $shiprocket)
    {
        if ($order->shiprocket_order_id) {
            return back()->with('error', 'Order is already sent to Shiprocket.');
        }

        try {
            $response = $shiprocket->createOrder($order);

            if ($response['success']) {
                return back()->with('success', $response['message']);
            }

            return back()->with('error', $response['message']);
        } catch (\Exception $e) {
            return back()->with('error', 'Shiprocket error: ' . $e->getMessage());
        }
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

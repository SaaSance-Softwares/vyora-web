<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GiftCardTemplate;
use App\Models\Order;
use App\Models\ThemeSetting;
use App\Services\TwilioSmsService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Razorpay\Api\Api;

class PaymentController extends Controller
{
    /**
     * Build a Razorpay API client using credentials stored in the DB.
     * Falls back to .env values so existing setups keep working.
     */
    private function razorpay(): Api
    {
        $rows = ThemeSetting::where('group', 'integration.razorpay')->get()->keyBy('key');
        $keyId = $this->decrypt($rows->get('key_id')?->value) ?: env('RAZORPAY_KEY');
        $keySecret = $this->decrypt($rows->get('key_secret')?->value) ?: env('RAZORPAY_SECRET');

        return new Api($keyId, $keySecret);
    }

    private function razorpayKeyId(): string
    {
        $row = ThemeSetting::where('group', 'integration.razorpay')->where('key', 'key_id')->first();

        return $this->decrypt($row?->value) ?: env('RAZORPAY_KEY', '');
    }

    private function decrypt(?string $val): string
    {
        if (! $val) {
            return '';
        }
        try {
            return Crypt::decryptString($val);
        } catch (\Exception) {
            return $val;
        }
    }

    public function initiate(Request $request)
    {
        $rows = ThemeSetting::where('group', 'integration.razorpay')->get()->keyBy('key');
        $isEnabled = ($rows->get('enabled')?->value ?? '0') === '1';

        if (! $isEnabled) {
            return response()->json(['success' => false, 'message' => 'Razorpay payment is currently disabled.'], 422);
        }

        $request->strictValidate([
            'type' => 'nullable|string|in:order,gift_card',
            'template_id' => 'nullable|integer|exists:gift_card_templates,id',
            'order_uuid' => 'nullable|string|uuid|max:255|exists:orders,uuid',
        ]);

        $storeName = ThemeSetting::where('key', 'store_name')->first()?->value ?? 'Dope Style';
        $type = $request->input('type', 'order');

        // ── Gift Card Purchase ─────────────────────────────────────────────
        if ($type === 'gift_card') {
            $template = GiftCardTemplate::find($request->template_id);

            if (! $template->is_active) {
                return response()->json(['success' => false, 'message' => 'This gift card is no longer available.'], 422);
            }

            $razorpayOrder = $this->razorpay()->order->create([
                'receipt' => 'gc-'.Str::random(8),
                'amount' => (int) ($template->amount * 100),
                'currency' => 'INR',
                'payment_capture' => 1,
            ]);

            return response()->json([
                'order_id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'],
                'key' => $this->razorpayKeyId(),
                'name' => $storeName,
                'description' => "Gift Card – ₹{$template->amount}",
            ]);
        }

        // ── Regular Order Payment ──────────────────────────────────────────
        $order = Order::where('uuid', $request->order_uuid)->firstOrFail();

        $payableAmount = $order->total_amount;
        if ($order->payment_method === 'COD' && $order->upfront_amount > 0) {
            $payableAmount = $order->upfront_amount;
        }

        $razorpayOrder = $this->razorpay()->order->create([
            'receipt' => $order->order_number,
            'amount' => (int) ($payableAmount * 100),
            'currency' => 'INR',
            'payment_capture' => 1,
        ]);

        return response()->json([
            'order_id' => $razorpayOrder['id'],
            'amount' => $razorpayOrder['amount'],
            'key' => $this->razorpayKeyId(),
            'name' => $storeName,
            'description' => 'Order #'.$order->order_number,
            'prefill' => [
                'name' => $order->shippingAddress?->name,
                'email' => $order->shippingAddress?->email,
                'contact' => $order->shippingAddress?->phone,
            ],
        ]);
    }

    public function verify(Request $request)
    {
        $type = $request->input('type', 'order');

        $request->strictValidate([
            'type' => 'nullable|string|in:order,gift_card',
            'template_id' => 'nullable|integer|exists:gift_card_templates,id',
            'order_uuid' => 'nullable|string|uuid|max:255|exists:orders,uuid',
            'razorpay_payment_id' => 'required|string|max:255',
            'razorpay_order_id' => 'required|string|max:255',
            'razorpay_signature' => 'required|string|max:255',
        ]);

        $attributes = [
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature,
        ];

        try {
            $this->razorpay()->utility->verifyPaymentSignature($attributes);
        } catch (\Exception $e) {
            if ($order && $order->payment_status === 'pending') {
                $order->update(['payment_status' => 'failed', 'status' => 'cancelled']);
            }
            return response()->json(['success' => false, 'message' => 'Payment verification failed: '.$e->getMessage()], 400);
        }

        // ── Gift Card: signature verified — activation is done separately via /activate ──
        if ($type === 'gift_card') {
            return response()->json(['success' => true, 'message' => 'Payment verified.']);
        }

        // ── Regular Order ──────────────────────────────────────────────────

        $order = Order::where('uuid', $request->order_uuid)->firstOrFail();
        
        if ($order->payment_method === 'COD' && $order->upfront_amount > 0) {
            $order->update([
                'payment_status' => 'partially_paid',
                'status' => 'processing',
                'amount_paid' => $order->upfront_amount,
                'balance_due' => max(0, $order->total_amount - $order->upfront_amount),
                'transaction_id' => $request->razorpay_payment_id,
            ]);
        } else {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
                'amount_paid' => $order->total_amount,
                'balance_due' => 0,
                'transaction_id' => $request->razorpay_payment_id,
                'payment_method' => 'Razorpay',
            ]);
        }

        try {
            $smsTemplate = \App\Models\SmsTemplate::where('name', 'Order Placed')->first();
            if ($smsTemplate) {
                app(\App\Services\TwilioSmsService::class)->sendDynamicSms($order, $smsTemplate);
            } else {
                app(\App\Services\TwilioSmsService::class)->sendEventSms('confirmed', $order);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send Twilio SMS confirmed event: '.$e->getMessage());
        }

        try {
            app(WhatsAppService::class)->sendEventWhatsApp('confirmed', $order);
        } catch (\Exception $e) {
            \Log::error('Failed to send WhatsApp confirmed event: '.$e->getMessage());
        }

        try {
            if ($order->user) {
                app(\App\Services\PushNotificationService::class)->sendToUser(
                    $order->user,
                    'Order Confirmed 🎉',
                    "Your order #{$order->order_number} has been placed successfully.",
                    ['type' => 'order', 'id' => $order->id]
                );
            }
            
            // Notify all admins about the new prepaid order
            $admins = \App\Models\User::where('role', 'administrator')->get();
            foreach ($admins as $admin) {
                app(\App\Services\PushNotificationService::class)->sendToUser(
                    $admin,
                    'New Prepaid Order Received! 💰',
                    "A new prepaid order #{$order->order_number} has been placed.",
                    ['type' => 'admin_order', 'id' => $order->id]
                );
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send push notification: '.$e->getMessage());
        }

        try {
            $email = $order->user?->email ?? $order->shippingAddress?->email;
            if ($email) {
                $emailTemplate = \App\Models\EmailTemplate::where('name', 'Order Placed')->first();
                if ($emailTemplate) {
                    \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\DynamicOrderStatusEmail($order, $emailTemplate));
                } else {
                    \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\OrderStatusEmail($order, 'confirmed'));
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send Order Confirmed Email: '.$e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Payment verified successfully.']);
    }

    public function failed(Request $request)
    {
        $order = Order::where('uuid', $request->order_uuid)->first();
        if ($order && $order->payment_status === 'pending') {
            $order->update([
                'payment_status' => 'failed',
                'status' => 'cancelled'
            ]);
        }
        return response()->json(['success' => true]);
    }
}

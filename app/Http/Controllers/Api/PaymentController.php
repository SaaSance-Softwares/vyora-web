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

        $storeName = ThemeSetting::where('key', 'store_name')->first()?->value ?? 'Dope Style';
        $type = $request->input('type', 'order');

        // ── Gift Card Purchase ─────────────────────────────────────────────
        if ($type === 'gift_card') {
            $request->validate([
                'template_id' => 'required|exists:gift_card_templates,id',
            ]);

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
        $request->validate([
            'order_uuid' => 'required|exists:orders,uuid',
        ]);

        $order = Order::where('uuid', $request->order_uuid)->firstOrFail();

        $razorpayOrder = $this->razorpay()->order->create([
            'receipt' => $order->order_number,
            'amount' => (int) ($order->total_amount * 100),
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

        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required',
            'razorpay_signature' => 'required',
        ]);

        $attributes = [
            'razorpay_order_id' => $request->razorpay_order_id,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature' => $request->razorpay_signature,
        ];

        try {
            $this->razorpay()->utility->verifyPaymentSignature($attributes);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Payment verification failed: '.$e->getMessage()], 400);
        }

        // ── Gift Card: signature verified — activation is done separately via /activate ──
        if ($type === 'gift_card') {
            return response()->json(['success' => true, 'message' => 'Payment verified.']);
        }

        // ── Regular Order ──────────────────────────────────────────────────
        $request->validate([
            'order_uuid' => 'required|exists:orders,uuid',
        ]);

        $order = Order::where('uuid', $request->order_uuid)->firstOrFail();
        $order->update([
            'payment_status' => 'paid',
            'status' => 'processing',
            'transaction_id' => $request->razorpay_payment_id,
            'payment_method' => 'Razorpay',
        ]);

        app(TwilioSmsService::class)->sendEventSms('confirmed', $order);
        app(WhatsAppService::class)->sendEventWhatsApp('confirmed', $order);

        return response()->json(['success' => true, 'message' => 'Payment verified successfully.']);
    }
}

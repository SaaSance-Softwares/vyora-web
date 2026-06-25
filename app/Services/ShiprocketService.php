<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShiprocketService
{
    private $baseUrl = 'https://apiv2.shiprocket.in/v1/external';

    public function getAuthToken()
    {
        // Cache token for 9 days (Shiprocket tokens expire in 10 days)
        return Cache::remember('shiprocket_auth_token', now()->addDays(9), function () {
            $settings = ThemeSetting::where('group', 'integration.shiprocket')->get()->keyBy('key');

            if (!isset($settings['shiprocket_email']) || !isset($settings['shiprocket_password'])) {
                throw new \Exception("Shiprocket credentials are not configured.");
            }

            try {
                $email = Crypt::decryptString($settings['shiprocket_email']->value);
                $password = Crypt::decryptString($settings['shiprocket_password']->value);
            } catch (\Exception $e) {
                throw new \Exception("Invalid Shiprocket credentials.");
            }

            $response = Http::post("{$this->baseUrl}/auth/login", [
                'email' => $email,
                'password' => $password,
            ]);

            if ($response->successful() && $response->json('token')) {
                return $response->json('token');
            }

            Log::error('Shiprocket Auth Failed', ['response' => $response->body()]);
            throw new \Exception("Failed to authenticate with Shiprocket: " . $response->body());
        });
    }

    public function createOrder(Order $order)
    {
        $token = $this->getAuthToken();

        $order->loadMissing(['items', 'shippingAddress', 'billingAddress', 'user']);

        $shipping = $order->shippingAddress;
        $billing = $order->billingAddress ?? $order->shippingAddress;

        // Map items
        $orderItems = $order->items->map(function ($item) {
            return [
                'name' => $item->product_name,
                'sku' => $item->product_id . '-' . ($item->variant_id ?? 'base'),
                'units' => $item->quantity,
                'selling_price' => $item->price,
                'discount' => 0,
                'tax' => 0,
                'hsn' => ''
            ];
        })->toArray();

        // Calculate total dimensions and weight (defaults to 0.5kg if not available)
        $weight = 0.5;
        $length = 10;
        $breadth = 10;
        $height = 10;

        $payload = [
            'order_id' => $order->order_number . '-' . time(), // Shiprocket requires unique order id if retried
            'order_date' => $order->created_at->format('Y-m-d H:i'),
            'pickup_location' => 'Primary', // Must match the location name in Shiprocket dashboard
            'channel_id' => '',
            'comment' => $order->notes ?? '',
            
            // Billing info
            'billing_customer_name' => $billing->name,
            'billing_last_name' => '',
            'billing_address' => $billing->address_line1,
            'billing_address_2' => $billing->address_line2 ?? '',
            'billing_city' => $billing->city,
            'billing_pincode' => $billing->zip_code,
            'billing_state' => $billing->state,
            'billing_country' => $billing->country ?? 'India',
            'billing_email' => $billing->email ?? $order->user->email,
            'billing_phone' => $billing->phone,

            // Shipping info
            'shipping_is_billing' => true,
            'shipping_customer_name' => $shipping->name,
            'shipping_last_name' => '',
            'shipping_address' => $shipping->address_line1,
            'shipping_address_2' => $shipping->address_line2 ?? '',
            'shipping_city' => $shipping->city,
            'shipping_pincode' => $shipping->zip_code,
            'shipping_country' => $shipping->country ?? 'India',
            'shipping_state' => $shipping->state,
            'shipping_email' => $shipping->email ?? $order->user->email,
            'shipping_phone' => $shipping->phone,

            'order_items' => $orderItems,

            'payment_method' => $order->payment_method === 'COD' ? 'COD' : 'Prepaid',
            'shipping_charges' => $order->shipping_amount,
            'giftwrap_charges' => 0,
            'transaction_charges' => 0,
            'total_discount' => $order->discount_amount,
            
            'sub_total' => $order->total_amount,
            
            'length' => $length,
            'breadth' => $breadth,
            'height' => $height,
            'weight' => $weight,
        ];

        $response = Http::withToken($token)->post("{$this->baseUrl}/orders/create/ad-hoc", $payload);

        if ($response->successful()) {
            $data = $response->json();
            
            // Save Shiprocket IDs to our DB
            $order->update([
                'shiprocket_order_id' => $data['order_id'] ?? null,
                'shiprocket_shipment_id' => $data['shipment_id'] ?? null,
            ]);

            return [
                'success' => true,
                'data' => $data,
                'message' => 'Order created in Shiprocket successfully.'
            ];
        }

        Log::error('Shiprocket Create Order Failed', ['payload' => $payload, 'response' => $response->body()]);
        return [
            'success' => false,
            'message' => $response->json('message') ?? 'Failed to create order in Shiprocket.'
        ];
    }
}

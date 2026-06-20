<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class QikinkOrderService
{
    public function processOrder(Order $order)
    {
        try {
            // Get settings
            $settings = ThemeSetting::where('group', 'integration.qikink')->get()->keyBy('key');
            if (($settings->get('enabled')->value ?? '0') !== '1') {
                return false;
            }

            // Check if any item in the order uses Qikink
            $order->load(['items.product', 'items.sku', 'shippingAddress']);

            $qikinkItems = $order->items->filter(function ($item) {
                return $item->product && $item->product->use_qikink;
            });

            if ($qikinkItems->isEmpty()) {
                return false;
            }

            $clientId     = $this->maybeDecrypt($settings->get('client_id')?->value);
            $clientSecret = $this->maybeDecrypt($settings->get('client_secret')?->value);
            $mode         = $settings->get('mode')?->value ?? 'test';

            if (!$clientId || !$clientSecret) {
                Log::warning('Qikink is enabled but credentials are not fully configured.');
                return false;
            }

            $baseUrl = $mode === 'live' ? 'https://api.qikink.com' : 'https://sandbox.qikink.com';

            // 1. Get Token
            $tokenResponse = Http::asForm()->post("$baseUrl/api/token", [
                'ClientId'      => $clientId,
                'client_secret' => $clientSecret,
            ]);

            if (!$tokenResponse->successful() || !$tokenResponse->json('Accesstoken')) {
                Log::error('Qikink Authentication Failed', ['response' => $tokenResponse->body()]);
                return false;
            }

            $accessToken = $tokenResponse->json('Accesstoken');

            // 2. Prepare Line Items
            // IMPORTANT: We use product_sku (the QikInk-specific SKU stored in our skus table),
            // NOT our internal 'code' (e.g. v-9huk1S...) which is unknown to QikInk.
            $lineItems = [];
            foreach ($qikinkItems as $item) {
                $sku = $item->sku;

                // Our internal 'code' IS the QikInk product SKU (used with search_from_my_products)
                // design_sku is stored separately for design-level identification
                $qikinkSku = $sku->code;

                if (!$qikinkSku) {
                    Log::error('Qikink: SKU code is empty — cannot submit order', [
                        'order_id'   => $order->id,
                        'sku_id'     => $sku->id ?? 'N/A',
                        'product_id' => $item->product_id,
                    ]);
                    return false;
                }

                $lineItems[] = [
                    'search_from_my_products' => 1,
                    'sku'                     => $qikinkSku,
                    'quantity'                => (int) $item->quantity,
                    'price'                   => (float) $item->price,
                ];
            }

            // 3. Validate Shipping Address
            $address = $order->shippingAddress;
            if (!$address) {
                Log::error('Qikink: Order has no shipping address.', ['order_id' => $order->id]);
                return false;
            }

            // Safe parsing of customer name
            $nameParts = explode(' ', trim($address->name ?? 'Customer'));
            $firstName  = array_shift($nameParts) ?: 'Customer';
            $lastName   = count($nameParts) > 0 ? implode(' ', $nameParts) : '.';

            // 4. Build Payload
            // QikInk rejects hyphens and special characters in order_number
            $payload = [
                'order_number'      => preg_replace('/[^a-zA-Z0-9]/', '', $order->order_number),
                'qikink_shipping'   => 1,
                'gateway'           => strtolower($order->payment_method) === 'cod' ? 'COD' : 'Prepaid',
                'total_order_value' => (float) $order->total_amount,
                'line_items'        => $lineItems,
                'shipping_address'  => [
                    'first_name'   => $firstName,
                    'last_name'    => $lastName,
                    'address1'     => $address->address_line1 ?? '',
                    'address2'     => $address->address_line2 ?? '',
                    'phone'        => $address->phone ?? '0000000000',
                    'email'        => $address->email ?? 'customer@example.com',
                    'city'         => $address->city ?? '',
                    'zip'          => $address->zip_code ?? '',
                    'province'     => $address->state ?? '',
                    'country_code' => 'IN',
                ],
            ];

            Log::info('Qikink: Submitting order payload', ['order_id' => $order->id, 'payload' => $payload]);

            // 5. Submit Order to QikInk
            $orderResponse = Http::withHeaders([
                'Accesstoken' => $accessToken,
                'ClientId'    => $clientId,
            ])->post("$baseUrl/api/order/create", $payload);

            if (!$orderResponse->successful()) {
                Log::error('Qikink Order Creation Failed', [
                    'order_id' => $order->id,
                    'status'   => $orderResponse->status(),
                    'response' => $orderResponse->body(),
                    'payload'  => $payload,
                ]);
                return false;
            }

            Log::info('Qikink Order Created Successfully', [
                'order_id' => $order->id,
                'response' => $orderResponse->json(),
            ]);
            return true;

        } catch (\Exception $e) {
            Log::error('Exception in QikinkOrderService', [
                'order_id' => $order->id ?? null,
                'message'  => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    private function maybeDecrypt(?string $value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception) {
            return $value;
        }
    }
}

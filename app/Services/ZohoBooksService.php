<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZohoBooksService
{
    private $clientId;
    private $clientSecret;
    private $refreshToken;
    private $dataCenter;
    private $organizationId;
    private $syncType;
    private $inventorySync;

    public function __construct()
    {
        $group = 'integration.zoho-books';
        $rows = ThemeSetting::where('group', $group)->get()->keyBy('key');

        $this->clientId = $this->maybeDecrypt($rows->get('client_id')?->value);
        $this->clientSecret = $this->maybeDecrypt($rows->get('client_secret')?->value);
        $this->refreshToken = $this->maybeDecrypt($rows->get('refresh_token')?->value);
        $this->dataCenter = $rows->get('data_center')?->value ?? '.com';
        $this->organizationId = $this->maybeDecrypt($rows->get('organization_id')?->value);
        $this->syncType = $rows->get('sync_type')?->value ?? 'invoice';
        $this->inventorySync = $rows->get('inventory_sync')?->value ?? '1-way';
    }

    private function maybeDecrypt(?string $value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function isConfigured(): bool
    {
        return $this->clientId && $this->clientSecret && $this->refreshToken;
    }

    public function getAccessToken(): ?string
    {
        if (!$this->isConfigured()) throw new \Exception('Zoho configuration is missing (Check Client ID, Secret, and Auth).');

        try {
            return \Illuminate\Support\Facades\Cache::remember('zoho_books_access_token', 3000, function () {
                $response = Http::asForm()->post("https://accounts.zoho{$this->dataCenter}/oauth/v2/token", [
                    'refresh_token' => $this->refreshToken,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'refresh_token',
                ]);

                $data = $response->json();
                
                if (!isset($data['access_token'])) {
                    throw new \Exception('Token Error: ' . $response->body());
                }
                
                return $data['access_token'];
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Cache::forget('zoho_books_access_token');
            Log::error('Zoho Books getAccessToken error: ' . $e->getMessage());
            throw new \Exception('Unable to get Zoho Access Token: ' . $e->getMessage());
        }
    }

    private function apiRequest($method, $endpoint, $data = [])
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) throw new \Exception('Unable to get Zoho Access Token');

        $url = "https://www.zohoapis{$this->dataCenter}/books/v3/{$endpoint}";
        
        $request = Http::withToken($accessToken);

        if ($this->organizationId) {
            $request->withHeaders([
                'X-com-zoho-books-organizationid' => $this->organizationId
            ]);
        }
        
        $response = $method === 'GET' 
            ? $request->get($url, $data)
            : $request->$method($url, $data);

        // Auto-recover from Multiple Organizations error (6024)
        if (!$response->successful()) {
            $errorData = $response->json();
            if (isset($errorData['code']) && $errorData['code'] == 6024 && isset($errorData['error_info'])) {
                $orgs = $errorData['error_info'];
                foreach ($orgs as $org) {
                    if (!empty($org['is_default_org'])) {
                        $this->organizationId = $org['organization_id'];
                        ThemeSetting::updateOrCreate(
                            ['group' => 'integration.zoho-books', 'key' => 'organization_id'],
                            ['value' => Crypt::encryptString($this->organizationId), 'type' => 'string']
                        );
                        
                        // Retry request with the header
                        $request->withHeaders([
                            'X-com-zoho-books-organizationid' => $this->organizationId
                        ]);
                        
                        $response = $method === 'GET' 
                            ? $request->get($url, $data)
                            : $request->$method($url, $data);
                        break;
                    }
                }
            }
        }

        if (!$response->successful()) {
            throw new \Exception('Zoho API Error: ' . $response->body());
        }

        return $response->json();
    }

    public function getTaxes()
    {
        return $this->apiRequest('GET', 'settings/taxes');
    }

    public function createOrUpdateContact(Order $order)
    {
        $email = $order->user ? $order->user->email : ($order->shippingAddress->email ?? 'guest@vyora.com');
        $name = $order->user ? $order->user->name : ($order->shippingAddress->first_name . ' ' . $order->shippingAddress->last_name);
        
        // Search if contact exists
        $search = $this->apiRequest('GET', 'contacts', ['email' => $email]);
        
        if (isset($search['contacts']) && count($search['contacts']) > 0) {
            return $search['contacts'][0]['contact_id'];
        }

        // Create new contact
        $payload = [
            'contact_name' => $name,
            'company_name' => $name,
            'contact_type' => 'customer',
            'contact_persons' => [
                [
                    'first_name' => $order->shippingAddress->first_name ?? $name,
                    'last_name' => $order->shippingAddress->last_name ?? '',
                    'email' => $email,
                    'phone' => $order->shippingAddress->phone ?? '',
                    'is_primary_contact' => true
                ]
            ],
            'shipping_address' => [
                'attention' => $name,
                'address' => $order->shippingAddress->address1 ?? '',
                'street2' => $order->shippingAddress->address2 ?? '',
                'city' => $order->shippingAddress->city ?? '',
                'state' => $order->shippingAddress->province ?? '',
                'zip' => $order->shippingAddress->zip ?? '',
                'country' => $order->shippingAddress->country ?? '',
                'phone' => $order->shippingAddress->phone ?? ''
            ]
        ];

        $response = $this->apiRequest('POST', 'contacts', $payload);
        return $response['contact']['contact_id'];
    }

    public function createOrUpdateItem($orderItem)
    {
        $sku = $orderItem->variant ? $orderItem->variant->sku : ($orderItem->product->sku ?? 'SKU-'.$orderItem->product_id);
        $name = $orderItem->product_name . ($orderItem->variant_name ? ' - ' . $orderItem->variant_name : '');
        $price = $orderItem->price;
        $hsnCode = $orderItem->product->hsn_code ?? '';

        // Search if item exists
        $search = $this->apiRequest('GET', 'items', ['sku' => $sku]);

        if (isset($search['items']) && count($search['items']) > 0) {
            return $search['items'][0]['item_id'];
        }

        // Create item
        $payload = [
            'name' => $name,
            'rate' => $price,
            'sku' => $sku,
            'hsn_or_sac' => $hsnCode,
        ];

        $response = $this->apiRequest('POST', 'items', $payload);
        return $response['item']['item_id'];
    }

    public function matchTaxId($taxRate, $taxes)
    {
        if (!isset($taxes['taxes'])) return null;
        
        foreach ($taxes['taxes'] as $tax) {
            if ((float)$tax['tax_percentage'] == (float)$taxRate) {
                return $tax['tax_id'];
            }
        }
        return null;
    }

    public function pushOrder(Order $order)
    {
        if (!$this->isConfigured()) return ['success' => false, 'error' => 'Not configured'];

        try {
            $contactId = $this->createOrUpdateContact($order);
            $taxes = $this->getTaxes();

            // Check inclusive/exclusive setting
            $taxInclusionRow = ThemeSetting::where('group', 'tax_shipping')->where('key', 'tax_inclusion')->first();
            $isInclusive = ($taxInclusionRow?->value ?? 'exclude') === 'include';

            $lineItems = [];
            $taxBreakdown = json_decode($order->tax_breakdown, true);
            
            // Find general tax rate from breakdown, assuming a flat rate for simplicity or fallback
            $generalTaxRate = 0;
            if (is_array($taxBreakdown) && isset($taxBreakdown[0]['rate'])) {
                $generalTaxRate = $taxBreakdown[0]['rate'];
            }

            $taxId = $this->matchTaxId($generalTaxRate, $taxes);

            foreach ($order->items as $item) {
                $itemId = $this->createOrUpdateItem($item);
                
                $lineItems[] = [
                    'item_id' => $itemId,
                    'rate' => $item->price,
                    'quantity' => $item->quantity,
                    'tax_id' => $taxId,
                ];
            }

            // Add shipping if any
            if ($order->shipping_amount > 0) {
                $shippingTaxRateRow = ThemeSetting::where('group', 'tax_shipping')->where('key', 'shipping_tax_rate')->first();
                $shippingTaxRate = $shippingTaxRateRow?->value ?? '18';
                $shippingTaxId = $this->matchTaxId($shippingTaxRate, $taxes);
                
                $lineItems[] = [
                    'name' => 'Shipping Charges',
                    'rate' => $order->shipping_amount,
                    'quantity' => 1,
                    'tax_id' => $shippingTaxId,
                ];
            }

            $payload = [
                'customer_id' => $contactId,
                'line_items' => $lineItems,
                'is_inclusive_tax' => $isInclusive,
                'is_discount_before_tax' => true,
                'discount' => $order->discount_amount,
                'discount_type' => 'entity_level',
                'reference_number' => $order->order_number,
            ];

            $endpoint = $this->syncType === 'invoice' ? 'invoices' : 'salesorders';
            
            if ($this->syncType === 'invoice') {
                $payload['invoice_number'] = $order->order_number;
            } else {
                $payload['salesorder_number'] = $order->order_number;
            }

            // Duplicate Check: See if this order was already pushed
            $search = $this->apiRequest('GET', $endpoint, ['reference_number' => $order->order_number]);
            $dataKey = $this->syncType === 'invoice' ? 'invoices' : 'salesorders';
            $idKey = $this->syncType === 'invoice' ? 'invoice_id' : 'salesorder_id';
            
            if (isset($search[$dataKey]) && count($search[$dataKey]) > 0) {
                // Already exists, don't duplicate
                Log::info("Zoho Books: Transaction already exists for order {$order->order_number}, skipping to prevent duplicates.");
                return ['success' => true, 'id' => $search[$dataKey][0][$idKey]];
            }

            $response = $this->apiRequest('POST', $endpoint, $payload);
            
            $dataKeySingle = $this->syncType === 'invoice' ? 'invoice' : 'salesorder';
            return ['success' => true, 'id' => $response[$dataKeySingle][$idKey]];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

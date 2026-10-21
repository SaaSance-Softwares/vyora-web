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
        $address = $order->shippingAddress;

        $name = trim((string) ($order->user?->name ?: ($order->customer_name ?: ($address?->name ?: ''))));
        if ($name === '') {
            $name = ($order->source === 'pos') ? 'POS Walk-in Customer' : ('Guest Customer ' . $order->order_number);
        }

        $email = trim((string) ($order->user?->email ?: ($address?->email ?: '')));
        if ($email === '' || str_ends_with($email, '@vyorapos.local')) {
            $email = ($order->source === 'pos') ? 'pos-walkin@dopestyle.in' : 'guest@dopestyle.in';
        }

        $phone = trim((string) ($order->user?->phone ?: ($order->customer_phone ?: ($address?->phone ?: ''))));

        // 1. Search if contact exists by email (only for real customer emails, not shared fallback emails unless walk-in)
        if (!in_array($email, ['guest@dopestyle.in', 'pos-walkin@dopestyle.in'], true) || $name === 'POS Walk-in Customer') {
            $search = $this->apiRequest('GET', 'contacts', ['email' => $email]);
            if (!empty($search['contacts'])) {
                return $search['contacts'][0]['contact_id'];
            }
        }

        // 2. Search if contact exists by exact contact_name (Zoho requires unique contact_name)
        $searchByName = $this->apiRequest('GET', 'contacts', ['contact_name' => $name]);
        if (!empty($searchByName['contacts'])) {
            return $searchByName['contacts'][0]['contact_id'];
        }

        $nameParts = explode(' ', $name, 2);
        $firstName = $nameParts[0] ?: 'Customer';
        $lastName = $nameParts[1] ?? '';

        // 3. Create new contact using correct Address column names
        $payload = [
            'contact_name' => $name,
            'company_name' => $name,
            'contact_type' => 'customer',
            'contact_persons' => [
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $phone,
                    'is_primary_contact' => true,
                ]
            ],
            'shipping_address' => [
                'attention' => $name,
                'address' => substr((string) ($address?->address_line1 ?: ($order->posLocation?->address ?? '')), 0, 99),
                'street2' => substr((string) ($address?->address_line2 ?: ($order->posLocation?->address_line_2 ?? '')), 0, 99),
                'city' => $address?->city ?: ($order->posLocation?->city ?: (($order->source === 'pos') ? 'Bengaluru' : '')),
                'state' => $address?->state ?: ($order->posLocation?->state ?: $this->resolveBusinessRegisteredState($order)),
                'zip' => $address?->zip_code ?: ($order->posLocation?->pincode ?? ''),
                'country' => $address?->country ?: ($order->posLocation?->country ?: 'India'),
                'phone' => $phone,
            ]
        ];

        try {
            $response = $this->apiRequest('POST', 'contacts', $payload);
            return $response['contact']['contact_id'];
        } catch (\Exception $e) {
            // If contact_name already exists under slightly different spacing/casing, make it unique with order_number
            $payload['contact_name'] = $name . ' (' . $order->order_number . ')';
            $payload['company_name'] = $payload['contact_name'];
            $response = $this->apiRequest('POST', 'contacts', $payload);
            return $response['contact']['contact_id'];
        }
    }

    public function createOrUpdateItem($orderItem, $taxId = null)
    {
        $skuModel = $orderItem->sku;
        $product = $orderItem->product ?? $skuModel?->product;

        $skuCode = trim((string) ($skuModel?->code ?? ''));
        if ($skuCode === '') {
            $skuCode = 'SKU-' . ($orderItem->sku_id ?: ($orderItem->product_id ?: $orderItem->id));
        }

        $baseName = trim((string) ($orderItem->product_name ?: ($product?->name ?: 'Item')));
        $variantSuffix = trim((string) ($orderItem->variant_name ?? ''));
        if ($variantSuffix === '' && $skuModel) {
            $attrs = array_filter([$skuModel->size?->name, $skuModel->color?->name]);
            if (!empty($attrs)) {
                $variantSuffix = implode(' - ', $attrs);
            }
        }

        $fullName = ($variantSuffix !== '' && !str_contains($baseName, $variantSuffix))
            ? ($baseName . ' - ' . $variantSuffix)
            : $baseName;

        $price = (float) $orderItem->price;
        $hsnCode = $product?->productType?->hsn_code ?? ($product?->hsn_code ?? '');

        // 1. Search if item exists by SKU code
        $search = $this->apiRequest('GET', 'items', ['sku' => $skuCode]);
        if (!empty($search['items'])) {
            return $search['items'][0]['item_id'];
        }

        // 2. Search if item exists by full name (prevents Code 1001: "Item already exists")
        $searchByName = $this->apiRequest('GET', 'items', ['name' => $fullName]);
        if (!empty($searchByName['items'])) {
            return $searchByName['items'][0]['item_id'];
        }

        // 3. Also check base product name if different
        if ($fullName !== $baseName) {
            $searchByBase = $this->apiRequest('GET', 'items', ['name' => $baseName]);
            if (!empty($searchByBase['items'])) {
                return $searchByBase['items'][0]['item_id'];
            }
        }

        // 4. Create item
        $payload = [
            'name' => $fullName,
            'rate' => $price,
            'sku' => $skuCode,
            'hsn_or_sac' => $hsnCode,
        ];

        if ($taxId) {
            $payload['tax_id'] = $taxId;
        }

        try {
            $response = $this->apiRequest('POST', 'items', $payload);
            return $response['item']['item_id'];
        } catch (\Exception $e) {
            // If Zoho still reports Code 1001 (name collision), disambiguate with the SKU code
            if (str_contains($e->getMessage(), '1001') || str_contains(strtolower($e->getMessage()), 'already exists')) {
                $payload['name'] = $fullName . ' (' . $skuCode . ')';
                $searchRetry = $this->apiRequest('GET', 'items', ['name' => $payload['name']]);
                if (!empty($searchRetry['items'])) {
                    return $searchRetry['items'][0]['item_id'];
                }
                $response = $this->apiRequest('POST', 'items', $payload);
                return $response['item']['item_id'];
            }
            throw $e;
        }
    }

    /**
     * Match a tax percentage against Zoho Books taxes, respecting intra-state (CGST+SGST group) vs inter-state (IGST).
     */
    public function matchTaxId($taxRate, $taxes, string $spec = 'intra')
    {
        if ((float) $taxRate <= 0 || empty($taxes['taxes']) || !is_array($taxes['taxes'])) {
            return null;
        }

        $targetRate = (float) $taxRate;

        // 1. Try matching exact specification ('inter' = IGST, 'intra' = CGST+SGST tax_group)
        if ($spec === 'inter') {
            foreach ($taxes['taxes'] as $tax) {
                if ((float) ($tax['tax_percentage'] ?? 0) == $targetRate) {
                    $taxSpec = strtolower((string) ($tax['tax_specification'] ?? ''));
                    $specificType = strtolower((string) ($tax['tax_specific_type'] ?? ''));
                    $taxName = strtolower((string) ($tax['tax_name'] ?? ''));
                    if ($taxSpec === 'inter' || $specificType === 'igst' || str_contains($taxName, 'igst')) {
                        return $tax['tax_id'];
                    }
                }
            }
        } else {
            // Prefer tax_group (CGST + SGST combined) for intra-state
            foreach ($taxes['taxes'] as $tax) {
                if ((float) ($tax['tax_percentage'] ?? 0) == $targetRate) {
                    $taxSpec = strtolower((string) ($tax['tax_specification'] ?? ''));
                    $taxType = strtolower((string) ($tax['tax_type'] ?? ''));
                    $taxName = strtolower((string) ($tax['tax_name'] ?? ''));
                    if (($taxSpec === 'intra' || !str_contains($taxName, 'igst')) && $taxType === 'tax_group') {
                        return $tax['tax_id'];
                    }
                }
            }
            foreach ($taxes['taxes'] as $tax) {
                if ((float) ($tax['tax_percentage'] ?? 0) == $targetRate) {
                    $taxSpec = strtolower((string) ($tax['tax_specification'] ?? ''));
                    $taxName = strtolower((string) ($tax['tax_name'] ?? ''));
                    if ($taxSpec === 'intra' || !str_contains($taxName, 'igst')) {
                        return $tax['tax_id'];
                    }
                }
            }
        }

        // 2. Fallback to any tax with matching percentage
        foreach ($taxes['taxes'] as $tax) {
            if ((float) ($tax['tax_percentage'] ?? 0) == $targetRate) {
                return $tax['tax_id'];
            }
        }

        return null;
    }

    /**
     * Normalize an Indian state name or 2-letter abbreviation to a canonical lowercase string.
     */
    protected function normalizeStateName(?string $state): string
    {
        $s = strtolower(trim((string) $state));
        if ($s === '') {
            return '';
        }

        $aliases = [
            'ka' => 'karnataka',
            'mh' => 'maharashtra',
            'dl' => 'delhi',
            'new delhi' => 'delhi',
            'nct of delhi' => 'delhi',
            'tn' => 'tamil nadu',
            'tamilnadu' => 'tamil nadu',
            'kl' => 'kerala',
            'tg' => 'telangana',
            'ts' => 'telangana',
            'ap' => 'andhra pradesh',
            'ga' => 'goa',
            'gj' => 'gujarat',
            'rj' => 'rajasthan',
            'up' => 'uttar pradesh',
            'wb' => 'west bengal',
            'hr' => 'haryana',
            'pb' => 'punjab',
            'mp' => 'madhya pradesh',
            'cg' => 'chhattisgarh',
            'ct' => 'chhattisgarh',
            'or' => 'odisha',
            'od' => 'odisha',
            'br' => 'bihar',
            'jh' => 'jharkhand',
            'as' => 'assam',
            'uk' => 'uttarakhand',
            'ut' => 'uttarakhand',
            'hp' => 'himachal pradesh',
            'jk' => 'jammu and kashmir',
            'ch' => 'chandigarh',
            'py' => 'puducherry',
            'pondicherry' => 'puducherry',
        ];

        return $aliases[$s] ?? $s;
    }

    /**
     * Resolve the Registered Business State for GST:
     * - For POS orders: uses the POS Market's gst_registered_state (or its GST number prefix), falling back to Online Store settings.
     * - For Online orders: uses Online Store General Settings gst_registered_state (or tax_id GST prefix), falling back to Karnataka.
     */
    protected function resolveBusinessRegisteredState(Order $order): string
    {
        if ($order->source === 'pos' && $order->posLocation) {
            if (!empty($order->posLocation->gst_registered_state)) {
                return trim((string) $order->posLocation->gst_registered_state);
            }
            if (!empty($order->posLocation->gst_number)) {
                $fromPosGst = \App\Http\Controllers\Admin\GeneralSettingsController::detectStateFromGstin($order->posLocation->gst_number);
                if ($fromPosGst) {
                    return $fromPosGst;
                }
            }
        }

        $generalRows = ThemeSetting::where('group', 'general')
            ->whereIn('key', ['gst_registered_state', 'tax_id'])
            ->pluck('value', 'key');

        $onlineGstState = trim((string) ($generalRows->get('gst_registered_state') ?? ''));
        if ($onlineGstState !== '') {
            return $onlineGstState;
        }

        $onlineTaxId = trim((string) ($generalRows->get('tax_id') ?? ''));
        if ($onlineTaxId !== '') {
            $detected = \App\Http\Controllers\Admin\GeneralSettingsController::detectStateFromGstin($onlineTaxId);
            if ($detected) {
                return $detected;
            }
        }

        return 'Karnataka';
    }

    /**
     * Determine whether an order is intra-state (CGST+SGST) or inter-state (IGST)
     * by comparing the customer/market state against the Registered Business State for GST.
     */
    protected function resolveOrderTaxSpecification(Order $order): string
    {
        $businessState = $this->normalizeStateName($this->resolveBusinessRegisteredState($order));
        if ($businessState === '') {
            $businessState = 'karnataka';
        }

        // For POS orders: compare POS Market physical location state (if set) with Registered Business State
        if ($order->source === 'pos') {
            $marketState = $this->normalizeStateName($order->posLocation?->state ?? '');
            if ($marketState !== '' && $marketState !== $businessState && !str_contains($marketState, $businessState)) {
                return 'inter';
            }
            return 'intra';
        }

        // For Online orders: compare Customer Shipping Address state with Registered Business State
        $address = $order->shippingAddress;
        $customerState = $this->normalizeStateName($address?->state ?? '');
        $zip = trim((string) ($address?->zip_code ?? ''));

        if ($customerState !== '') {
            if ($customerState === $businessState || str_contains($customerState, $businessState) || str_contains($businessState, $customerState)) {
                return 'intra';
            }
            return 'inter';
        }

        if ($zip !== '' && $businessState === 'karnataka') {
            if (preg_match('/^(56|57|58|59)/', $zip)) {
                return 'intra';
            }
            return 'inter';
        }

        return 'intra';
    }

    public function pushOrder(Order $order)
    {
        if (!$this->isConfigured()) return ['success' => false, 'error' => 'Not configured'];

        try {
            $order->loadMissing(['items.sku.size', 'items.sku.color', 'items.sku.product.productType', 'items.product.productType', 'user', 'shippingAddress', 'posLocation']);

            $endpoint = $this->syncType === 'invoice' ? 'invoices' : 'salesorders';
            $numberKey = $this->syncType === 'invoice' ? 'invoice_number' : 'salesorder_number';
            $dataKey = $this->syncType === 'invoice' ? 'invoices' : 'salesorders';
            $idKey = $this->syncType === 'invoice' ? 'invoice_id' : 'salesorder_id';
            $dataKeySingle = $this->syncType === 'invoice' ? 'invoice' : 'salesorder';

            // Duplicate Check BEFORE creating items/invoice: check both invoice_number/salesorder_number and reference_number
            $searchByNum = $this->apiRequest('GET', $endpoint, [$numberKey => $order->order_number]);
            if (!empty($searchByNum[$dataKey])) {
                Log::info("Zoho Books: Transaction {$order->order_number} already exists by {$numberKey}, linking existing ID.");
                return ['success' => true, 'id' => $searchByNum[$dataKey][0][$idKey]];
            }

            $searchByRef = $this->apiRequest('GET', $endpoint, ['reference_number' => $order->order_number]);
            if (!empty($searchByRef[$dataKey])) {
                Log::info("Zoho Books: Transaction already exists for order {$order->order_number}, skipping to prevent duplicates.");
                return ['success' => true, 'id' => $searchByRef[$dataKey][0][$idKey]];
            }

            $contactId = $this->createOrUpdateContact($order);
            $taxes = $this->getTaxes();
            $taxSpec = $this->resolveOrderTaxSpecification($order);

            // Load tax settings from ThemeSetting
            $taxRows = ThemeSetting::where('group', 'tax_shipping')->get()->keyBy('key');
            $isTaxEnabled = ($taxRows->get('is_tax_enabled')?->value ?? '1') === '1';
            // POS prices are always tax-inclusive; online orders follow store tax_inclusion setting
            $isInclusive = ($order->source === 'pos')
                ? true
                : (($taxRows->get('tax_inclusion')?->value ?? 'exclude') === 'include');

            $configuredTaxes = json_decode($taxRows->get('taxes')?->value ?? '[{"id":"t1","name":"GST 5%","rate":5},{"id":"t2","name":"GST 18%","rate":18}]', true) ?: [];
            $taxClassMap = [];
            $defaultTaxRate = 0;
            foreach ($configuredTaxes as $t) {
                if (isset($t['id'], $t['rate'])) {
                    $r = (float) $t['rate'];
                    $taxClassMap[$t['id']] = $r;
                    if ($defaultTaxRate <= 0 && $r > 0) {
                        $defaultTaxRate = $r;
                    }
                }
            }

            // Also inspect order's tax_breakdown (supports both {"5": 42.86} associative map and [{"rate": 5}] indexed array)
            $taxBreakdown = is_string($order->tax_breakdown) ? json_decode($order->tax_breakdown, true) : $order->tax_breakdown;
            $generalTaxRate = 0;
            if (is_array($taxBreakdown)) {
                if (isset($taxBreakdown[0]['rate'])) {
                    $generalTaxRate = (float) $taxBreakdown[0]['rate'];
                } else {
                    foreach ($taxBreakdown as $rateKey => $taxAmt) {
                        if (is_numeric($rateKey) && (float) $rateKey > 0) {
                            $generalTaxRate = (float) $rateKey;
                            break;
                        }
                    }
                }
            }

            $lineItems = [];
            $lineItemRates = [];

            foreach ($order->items as $idx => $item) {
                $product = $item->product ?? $item->sku?->product;
                $itemTaxRate = 0;

                if ($isTaxEnabled) {
                    if ($product && !empty($product->tax_class) && isset($taxClassMap[$product->tax_class])) {
                        $itemTaxRate = $taxClassMap[$product->tax_class];
                    } elseif ($generalTaxRate > 0) {
                        $itemTaxRate = $generalTaxRate;
                    } elseif ($defaultTaxRate > 0) {
                        $itemTaxRate = $defaultTaxRate;
                    }
                }

                $intraItemTaxId = $this->matchTaxId($itemTaxRate, $taxes, 'intra');
                $lineTaxId = $this->matchTaxId($itemTaxRate, $taxes, $taxSpec);

                $itemId = $this->createOrUpdateItem($item, $intraItemTaxId);

                $lineItem = [
                    'item_id' => $itemId,
                    'rate' => (float) $item->price,
                    'quantity' => (int) $item->quantity,
                ];
                if ($lineTaxId) {
                    $lineItem['tax_id'] = $lineTaxId;
                }
                $lineItems[$idx] = $lineItem;
                $lineItemRates[$idx] = $itemTaxRate;
            }

            // Add shipping if any
            if ($order->shipping_amount > 0) {
                $shippingTaxRate = (float) ($taxRows->get('shipping_tax_rate')?->value ?? 18);
                $shippingTaxId = $this->matchTaxId($shippingTaxRate, $taxes, $taxSpec);

                $shippingIdx = count($lineItems);
                $shippingLine = [
                    'name' => 'Shipping Charges',
                    'rate' => (float) $order->shipping_amount,
                    'quantity' => 1,
                ];
                if ($shippingTaxId) {
                    $shippingLine['tax_id'] = $shippingTaxId;
                }
                $lineItems[$shippingIdx] = $shippingLine;
                $lineItemRates[$shippingIdx] = $shippingTaxRate;
            }

            $payload = [
                'customer_id' => $contactId,
                'line_items' => array_values($lineItems),
                'is_inclusive_tax' => $isInclusive,
                'is_discount_before_tax' => true,
                'discount' => (float) ($order->discount_amount ?? 0),
                'discount_type' => 'entity_level',
                'reference_number' => $order->order_number,
                $numberKey => $order->order_number,
            ];

            try {
                $response = $this->apiRequest('POST', $endpoint, $payload);
                return ['success' => true, 'id' => $response[$dataKeySingle][$idKey]];
            } catch (\Exception $postEx) {
                $errMsg = $postEx->getMessage();

                // Auto-recover if Zoho says invoice/salesorder already exists (Code 1001)
                if (str_contains($errMsg, '1001') || str_contains(strtolower($errMsg), 'already exists')) {
                    $searchFallback = $this->apiRequest('GET', $endpoint, ['search_text' => $order->order_number]);
                    $foundId = $searchFallback[$dataKey][0][$idKey] ?? 'synced';
                    Log::info("Zoho Books: Recovered existing {$endpoint} for {$order->order_number} ({$foundId}).");
                    return ['success' => true, 'id' => $foundId];
                }

                // Auto-recover if Zoho reports interstate (IGST) vs intrastate (CGST/SGST) mismatch (Code 3032)
                if (str_contains($errMsg, '3032') || str_contains(strtolower($errMsg), 'interstate') || str_contains(strtolower($errMsg), 'intrastate') || str_contains($errMsg, 'IGST')) {
                    $altSpec = ($taxSpec === 'intra') ? 'inter' : 'intra';
                    foreach ($lineItems as $idx => &$li) {
                        $rate = $lineItemRates[$idx] ?? 0;
                        $altTaxId = $this->matchTaxId($rate, $taxes, $altSpec);
                        if ($altTaxId) {
                            $li['tax_id'] = $altTaxId;
                        }
                    }
                    unset($li);
                    $payload['line_items'] = array_values($lineItems);

                    $retryResponse = $this->apiRequest('POST', $endpoint, $payload);
                    return ['success' => true, 'id' => $retryResponse[$dataKeySingle][$idKey]];
                }

                throw $postEx;
            }

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

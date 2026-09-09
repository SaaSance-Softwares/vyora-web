<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ThemeSetting;
use App\Models\User;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private $accessToken;

    private $phoneId;

    private $wabaId;

    private $enabled;

    private $apiVersion = 'v19.0';

    private $apiUrl = 'https://graph.facebook.com';

    public function __construct()
    {
        $this->enabled = ThemeSetting::where('group', 'integration.whatsapp')->where('key', 'enabled')->value('value') === '1';

        $rawToken = ThemeSetting::where('group', 'integration.whatsapp')->where('key', 'whatsapp_access_token')->value('value');
        $rawPhone = ThemeSetting::where('group', 'integration.whatsapp')->where('key', 'whatsapp_phone_id')->value('value');
        $rawWaba = ThemeSetting::where('group', 'integration.whatsapp')->where('key', 'whatsapp_business_account_id')->value('value');

        $this->accessToken = $rawToken ? Crypt::decryptString($rawToken) : null;
        $this->phoneId = $rawPhone ? Crypt::decryptString($rawPhone) : null;
        $this->wabaId = $rawWaba ? Crypt::decryptString($rawWaba) : null;
    }

    public function isEnabled(): bool
    {
        return $this->enabled && ! empty($this->accessToken) && ! empty($this->phoneId);
    }

    /**
     * Get a configured template name for a specific event
     */
    public function getTemplateName(string $event): ?string
    {
        $key = "whatsapp_template_{$event}";
        $val = ThemeSetting::where('group', 'integration.whatsapp')->where('key', $key)->value('value');

        return $val ? Crypt::decryptString($val) : null;
    }

    /**
     * Send an automated template message
     */
    public function sendTemplate(string $to, string $templateName, string $languageCode = 'en_US', array $components = [])
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $to = $this->formatPhoneNumber($to);

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
            ],
        ];

        if (! empty($components)) {
            $payload['template']['components'] = $components;
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->apiVersion}/{$this->phoneId}/messages", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $messageId = $data['messages'][0]['id'] ?? null;
                
                // Resolve template text with components before logging
                $resolvedBody = $templateName;
                $template = \App\Models\WhatsappTemplate::where('name', $templateName)->first();
                if ($template) {
                    $templateComponents = $template->components ?? [];
                    $templateText = '';
                    foreach ($templateComponents as $component) {
                        if (in_array($component['type'] ?? '', ['HEADER', 'BODY', 'FOOTER']) && !empty($component['text'])) {
                            $compText = $component['text'];
                            $compType = strtolower($component['type']);
                            
                            // Replace variables in this specific component
                            if (!empty($components)) {
                                foreach ($components as $comp) {
                                    if (($comp['type'] ?? '') === $compType && !empty($comp['parameters'])) {
                                        foreach ($comp['parameters'] as $index => $param) {
                                            if (($param['type'] ?? '') === 'text' && isset($param['text'])) {
                                                $placeholder = '{{' . ($index + 1) . '}}';
                                                $compText = str_replace($placeholder, $param['text'], $compText);
                                            }
                                        }
                                    }
                                }
                            }
                            $templateText .= $compText . "\n\n";
                        }
                    }
                    if (trim($templateText)) {
                        $resolvedBody = trim($templateText);
                    }
                }

                $this->logMessage($to, $resolvedBody, 'template', $messageId);

                return true;
            }

            Log::error('WhatsApp Template Error: '.$response->body());

            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp API Exception: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Fetch all templates from Meta for the configured WABA ID
     */
    public function getTemplatesFromMeta()
    {
        if (! $this->enabled || empty($this->accessToken) || empty($this->wabaId)) {
            return ['error' => 'Integration disabled or missing WABA ID / Access Token.'];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->get("{$this->apiUrl}/{$this->apiVersion}/{$this->wabaId}/message_templates");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('WhatsApp Get Templates Error: '.$response->body());

            return ['error' => 'Failed to fetch templates from Meta.'];
        } catch (\Exception $e) {
            Log::error('WhatsApp API Exception: '.$e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create a new template on Meta
     */
    public function createTemplateToMeta(array $data)
    {
        if (! $this->enabled || empty($this->accessToken) || empty($this->wabaId)) {
            return ['error' => 'Integration disabled or missing WABA ID / Access Token.'];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->apiVersion}/{$this->wabaId}/message_templates", $data);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('WhatsApp Create Template Error: '.$response->body());
            $errorData = $response->json();
            $msg = $errorData['error']['error_user_msg'] ?? $errorData['error']['message'] ?? 'Failed to create template on Meta.';

            return ['error' => $msg];
        } catch (\Exception $e) {
            Log::error('WhatsApp API Exception: '.$e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Edit an existing template on Meta
     */
    public function editTemplateToMeta(string $templateId, array $data)
    {
        if (! $this->enabled || empty($this->accessToken) || empty($this->wabaId)) {
            return ['error' => 'Integration disabled or missing WABA ID / Access Token.'];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->apiVersion}/{$templateId}", $data);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('WhatsApp Edit Template Error: '.$response->body());
            $errorData = $response->json();
            $msg = $errorData['error']['error_user_msg'] ?? $errorData['error']['message'] ?? 'Failed to edit template on Meta.';

            return ['error' => $msg];
        } catch (\Exception $e) {
            Log::error('WhatsApp API Exception: '.$e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Delete a template from Meta
     */
    public function deleteTemplateFromMeta(string $name)
    {
        if (! $this->enabled || empty($this->accessToken) || empty($this->wabaId)) {
            return ['error' => 'Integration disabled or missing WABA ID / Access Token.'];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->delete("{$this->apiUrl}/{$this->apiVersion}/{$this->wabaId}/message_templates", [
                    'name' => $name,
                ]);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error('WhatsApp Delete Template Error: '.$response->body());
            $errorData = $response->json();
            $msg = $errorData['error']['error_user_msg'] ?? $errorData['error']['message'] ?? 'Failed to delete template on Meta.';

            return ['error' => $msg];
        } catch (\Exception $e) {
            Log::error('WhatsApp API Exception: '.$e->getMessage());

            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Helper to send event-based WhatsApp templates
     */
    public function sendEventWhatsApp(string $event, $target)
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $templateName = $this->getTemplateName($event);
        if (! $templateName) {
            return false;
        }

        $template = \App\Models\WhatsappTemplate::where('name', $templateName)->first();
        $mapping = $template ? $template->variables_mapping : null;

        $phone = null;

        // Determine target phone
        if (in_array($event, ['confirmed', 'shipped', 'cancelled', 'abandoned_cart']) && $target instanceof Order) {
            $phone = ($target->user && !empty($target->user->phone)) ? $target->user->phone : ($target->shipping_phone ?? $target->billing_phone);
        } elseif (in_array($event, ['account_created', 'password_updated']) && $target instanceof User) {
            $phone = $target->phone;
        }

        if (! $phone) {
            return false;
        }

        $components = [];

        if (!empty($mapping) && is_array($mapping)) {
            // Header
            if (isset($mapping['header']) && is_array($mapping['header'])) {
                $params = [];
                foreach ($mapping['header'] as $varName) {
                    $params[] = ['type' => 'text', 'text' => (string) $this->resolveVariable($varName, $target)];
                }
                if (!empty($params)) {
                    $components[] = ['type' => 'header', 'parameters' => $params];
                }
            }
            // Body
            if (isset($mapping['body']) && is_array($mapping['body'])) {
                $params = [];
                foreach ($mapping['body'] as $varName) {
                    $params[] = ['type' => 'text', 'text' => (string) $this->resolveVariable($varName, $target)];
                }
                if (!empty($params)) {
                    $components[] = ['type' => 'body', 'parameters' => $params];
                }
            }
            // Buttons
            if (isset($mapping['buttons']) && is_array($mapping['buttons'])) {
                foreach ($mapping['buttons'] as $btnIndex => $vars) {
                    $params = [];
                    foreach ($vars as $varName) {
                        $params[] = ['type' => 'text', 'text' => (string) $this->resolveVariable($varName, $target)];
                    }
                    if (!empty($params)) {
                        $components[] = [
                            'type' => 'button',
                            'sub_type' => 'url',
                            'index' => (string)$btnIndex,
                            'parameters' => $params
                        ];
                    }
                }
            }
        } else {
            // Fallback for hardcoded old templates
            if (in_array($event, ['confirmed', 'shipped', 'cancelled']) && $target instanceof Order) {
                $components = [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $target->order_number],
                        ],
                    ],
                ];
            }
            if (in_array($event, ['account_created', 'password_updated']) && $target instanceof User) {
                $components = [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $target->first_name],
                        ],
                    ],
                ];
            }
        }

        return $this->sendTemplate($phone, $templateName, $template ? $template->language : 'en_US', $components);
    }

    /**
     * Send a free-form text message (requires 24h open window)
     */
    public function sendText(string $to, string $text)
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $to = $this->formatPhoneNumber($to);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $text,
            ],
        ];

        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->apiVersion}/{$this->phoneId}/messages", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $messageId = $data['messages'][0]['id'] ?? null;
                $this->logMessage($to, $text, 'text', $messageId);

                return true;
            }

            Log::error('WhatsApp Text Error: '.$response->body());

            return false;
        } catch (\Exception $e) {
            Log::error('WhatsApp API Exception: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Record the message in the local database
     */
    private function logMessage(string $to, string $body, string $type, ?string $messageId = null)
    {
        // Redact any OTP before saving it to the database for security
        if (stripos($body, 'is your verification code') !== false || 
            stripos($body, 'do not share this code') !== false || 
            stripos($body, 'login OTP') !== false ||
            stripos($body, 'OTP') !== false) {
            $body = preg_replace('/[0-9]{4,8}/', '******', $body);
        }

        $conversation = WhatsappConversation::firstOrCreate(
            ['phone_number' => $to],
            ['last_message_at' => now()]
        );
        $conversation->update(['last_message_at' => now()]);

        WhatsappMessage::create([
            'conversation_id' => $conversation->id,
            'direction' => 'outbound',
            'type' => $type,
            'body' => $body,
            'message_id' => $messageId,
            'status' => 'sent',
        ]);
    }

    /**
     * Process an incoming message from the Webhook
     */
    public function processIncomingMessage(array $message, string $customerPhone, string $customerName)
    {
        $conversation = WhatsappConversation::firstOrCreate(
            ['phone_number' => $customerPhone],
            ['customer_name' => $customerName, 'last_message_at' => now()]
        );

        $conversation->update([
            'customer_name' => $customerName,
            'last_message_at' => now(),
        ]);

        $type = $message['type'] ?? 'text';
        $body = $message['text']['body'] ?? '[Media/Unsupported Type]';
        $messageId = $message['id'] ?? null;

        // Ensure we don't save duplicates from retries
        if ($messageId && WhatsappMessage::where('message_id', $messageId)->exists()) {
            return;
        }

        WhatsappMessage::create([
            'conversation_id' => $conversation->id,
            'direction' => 'inbound',
            'type' => $type,
            'body' => $body,
            'message_id' => $messageId,
            'status' => 'delivered',
        ]);
    }

    /**
     * Send an OTP for login/verification.
     */
    public function sendOtpWhatsApp(string $to, string $otp): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        // Fetch custom template name from Auth Settings
        $authFields = ThemeSetting::where('group', 'auth_settings')->where('key', 'auth_fields')->value('value');
        $templateName = 'user_verification_otp'; // Default fallback

        if ($authFields) {
            $decoded = json_decode($authFields, true);
            if (! empty($decoded['phone']['whatsapp_template'])) {
                $templateName = $decoded['phone']['whatsapp_template'];
            }
        }

        $template = \App\Models\WhatsappTemplate::where('name', $templateName)->first();
        $mapping = $template ? $template->variables_mapping : null;
        $components = [];

        if (!empty($mapping) && is_array($mapping)) {
            // Header
            if (isset($mapping['header']) && is_array($mapping['header'])) {
                $params = [];
                foreach ($mapping['header'] as $varName) {
                    $params[] = ['type' => 'text', 'text' => (string) $this->resolveVariable($varName, $otp)];
                }
                if (!empty($params)) {
                    $components[] = ['type' => 'header', 'parameters' => $params];
                }
            }
            // Body
            if (isset($mapping['body']) && is_array($mapping['body'])) {
                $params = [];
                foreach ($mapping['body'] as $varName) {
                    $params[] = ['type' => 'text', 'text' => (string) $this->resolveVariable($varName, $otp)];
                }
                if (!empty($params)) {
                    $components[] = ['type' => 'body', 'parameters' => $params];
                }
            }
            // Buttons
            if (isset($mapping['buttons']) && is_array($mapping['buttons'])) {
                foreach ($mapping['buttons'] as $btnIndex => $vars) {
                    $params = [];
                    foreach ($vars as $varName) {
                        $params[] = ['type' => 'text', 'text' => (string) $this->resolveVariable($varName, $otp)];
                    }
                    if (!empty($params)) {
                        $components[] = [
                            'type' => 'button',
                            'sub_type' => 'url',
                            'index' => (string)$btnIndex,
                            'parameters' => $params
                        ];
                    }
                }
            }
        } else {
            // Fallback for non-authentication templates or unmapped templates
            $components = [
                [
                    'type' => 'body',
                    'parameters' => [
                        [
                            'type' => 'text',
                            'text' => $otp,
                        ],
                    ],
                ],
            ];
        }

        // Explicitly inject the button parameter for AUTHENTICATION templates if not already present
        if ($template && $template->category === 'AUTHENTICATION') {
            $hasButton = false;
            foreach ($components as $comp) {
                if ($comp['type'] === 'button') {
                    $hasButton = true;
                    break;
                }
            }
            if (!$hasButton) {
                $components[] = [
                    'type' => 'button',
                    'sub_type' => 'url',
                    'index' => '0',
                    'parameters' => [
                        [
                            'type' => 'text',
                            'text' => $otp,
                        ],
                    ],
                ];
            }
        }

        return $this->sendTemplate($to, $templateName, $template ? $template->language : 'en_US', $components);
    }

    /**
     * Send dynamic WhatsApp template based on database linking.
     */
    public function sendDynamicWhatsApp(Order $order, \App\Models\WhatsappTemplate $template): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $order->loadMissing(['shippingAddress', 'user']);
        
        // Priority 1: Registered Account Phone Number (Protects gift recipients)
        // Priority 2: Shipping Phone Number (For Guest Checkouts)
        $phone = null;
        
        if ($order->user && !empty($order->user->phone)) {
            $phone = $order->user->phone;
        } elseif ($order->shippingAddress && !empty($order->shippingAddress->phone)) {
            $phone = $order->shippingAddress->phone;
        }

        if (! $phone) {
            return false;
        }

        // Clean formatting
        $phone = $this->formatPhoneNumber($phone);

        if (empty($phone)) {
            return false;
        }

        $mapping = $template->variables_mapping;
        $components = [];

        if (!empty($mapping) && is_array($mapping)) {
            // Header
            if (isset($mapping['header']) && is_array($mapping['header'])) {
                $params = [];
                foreach ($mapping['header'] as $varName) {
                    $params[] = ['type' => 'text', 'text' => (string) $this->resolveVariable($varName, $order)];
                }
                if (!empty($params)) {
                    $components[] = ['type' => 'header', 'parameters' => $params];
                }
            }
            // Body
            if (isset($mapping['body']) && is_array($mapping['body'])) {
                $params = [];
                foreach ($mapping['body'] as $varName) {
                    $params[] = ['type' => 'text', 'text' => (string) $this->resolveVariable($varName, $order)];
                }
                if (!empty($params)) {
                    $components[] = ['type' => 'body', 'parameters' => $params];
                }
            }
            // Buttons
            if (isset($mapping['buttons']) && is_array($mapping['buttons'])) {
                foreach ($mapping['buttons'] as $btnIndex => $vars) {
                    $params = [];
                    foreach ($vars as $varName) {
                        $params[] = ['type' => 'text', 'text' => (string) $this->resolveVariable($varName, $order)];
                    }
                    if (!empty($params)) {
                        $components[] = [
                            'type' => 'button',
                            'sub_type' => 'url',
                            'index' => (string)$btnIndex,
                            'parameters' => $params
                        ];
                    }
                }
            }
        }

        return $this->sendTemplate($phone, $template->name, $template->language, $components);
    }

    public function sendAbandonedCartWhatsApp(\App\Models\Cart $cart, \App\Models\WhatsappTemplate $template): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $phone = $cart->user ? $cart->user->phone : null;
        if (! $phone) {
            return false;
        }

        $mapping = $template->variables_mapping ?? [];
        $components = [];

        // 1. Automatically inject Image Header if the template defines one
        $hasImageHeader = false;
        if (is_array($template->components)) {
            foreach ($template->components as $c) {
                if (isset($c['type']) && $c['type'] === 'HEADER' && isset($c['format']) && $c['format'] === 'IMAGE') {
                    $hasImageHeader = true;
                    break;
                }
            }
        }

        if ($hasImageHeader) {
            // Find the first product image in the cart
            $cart->loadMissing(['items.sku.product.categoryMasterImages']);
            $firstItem = $cart->items->first();
            $imageUrl = null;
            
            if ($firstItem && $firstItem->sku && $firstItem->sku->product) {
                $imageObj = $firstItem->sku->product->categoryMasterImages->first();
                if ($imageObj) {
                    $imageUrl = $imageObj->image_url;
                }
            }
            
            // Fallback image if product has no image
            if (! $imageUrl) {
                $imageUrl = asset('pwa-icon-512.png'); // generic fallback
            }
            
            // Meta WhatsApp API strictly blocks .webp images. Convert to .jpg on the fly and cache it.
            if (str_ends_with(strtolower($imageUrl), '.webp')) {
                $filename = md5($imageUrl) . '.jpg';
                $waCachePath = public_path('storage/wa-cache');
                if (!file_exists($waCachePath)) {
                    @mkdir($waCachePath, 0755, true);
                }
                
                $localPath = $waCachePath . '/' . $filename;
                
                if (!file_exists($localPath)) {
                    try {
                        // We must fetch it and convert it. Encode spaces to %20 to prevent 400 Bad Request.
                        $fetchUrl = str_replace(' ', '%20', $imageUrl);
                        $contents = file_get_contents($fetchUrl);
                        if ($contents) {
                            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                            $img = $manager->read($contents);
                            $img->toJpeg(90)->save($localPath);
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to convert WebP for WhatsApp: " . $e->getMessage());
                    }
                }
                
                if (file_exists($localPath)) {
                    $imageUrl = asset('storage/wa-cache/' . $filename);
                } else {
                    // Ultimate fallback if conversion fails
                    $imageUrl = asset('pwa-icon-512.png');
                }
            }

            $components[] = [
                'type' => 'header',
                'parameters' => [
                    [
                        'type' => 'image',
                        'image' => [
                            'link' => $imageUrl
                        ]
                    ]
                ]
            ];
        }

        // 2. Handle Text variables (if mapped)
        if (!empty($mapping) && is_array($mapping)) {
            // Header Text (if it's not an image)
            if (isset($mapping['header']) && is_array($mapping['header']) && !$hasImageHeader) {
                $params = [];
                foreach ($mapping['header'] as $varName) {
                    $params[] = ['type' => 'text', 'text' => (string) $this->resolveVariable($varName, $cart)];
                }
                if (!empty($params)) {
                    $components[] = ['type' => 'header', 'parameters' => $params];
                }
            }
            // Body
            if (isset($mapping['body']) && is_array($mapping['body'])) {
                $params = [];
                foreach ($mapping['body'] as $varName) {
                    $params[] = ['type' => 'text', 'text' => (string) $this->resolveVariable($varName, $cart)];
                }
                if (!empty($params)) {
                    $components[] = ['type' => 'body', 'parameters' => $params];
                }
            }
            // Buttons
            if (isset($mapping['buttons']) && is_array($mapping['buttons'])) {
                foreach ($mapping['buttons'] as $btnIndex => $vars) {
                    $params = [];
                    foreach ($vars as $varName) {
                        $params[] = ['type' => 'text', 'text' => (string) $this->resolveVariable($varName, $cart)];
                    }
                    if (!empty($params)) {
                        $components[] = [
                            'type' => 'button',
                            'sub_type' => 'url',
                            'index' => (string)$btnIndex,
                            'parameters' => $params
                        ];
                    }
                }
            }
        }

        return $this->sendTemplate($phone, $template->name, $template->language, $components);
    }

    private function formatPhoneNumber(string $number): string
    {
        // Remove everything except numbers
        $number = preg_replace('/[^0-9]/', '', $number);
        // Meta expects the country code without the + or 00
        if (str_starts_with($number, '00')) {
            $number = substr($number, 2);
        }

        return $number;
    }

    private function resolveVariable(string $varName, $target): string
    {
        if ($target instanceof Order) {
            switch ($varName) {
                case 'customer_name':
                    return $target->customer_name ?? $target->billing_first_name ?? ($target->user->name ?? 'Customer');
                case 'order_number':
                    return $target->order_number ?? '';
                case 'order_total':
                    return $target->total ? number_format($target->total, 2) : '';
                case 'product_names':
                    if ($target->items && $target->items->count() > 0) {
                        return $target->items->pluck('product_name')->implode(', ');
                    }
                    return 'Products';
                case 'tracking_number':
                    return $target->tracking_number ?? 'N/A';
                case 'tracking_url':
                    return $target->tracking_url ?? 'N/A';
            }
        } elseif ($target instanceof \App\Models\Cart) {
            switch ($varName) {
                case 'customer_name':
                    return $target->user ? ($target->user->first_name ?? $target->user->name ?? 'Customer') : 'Customer';
                case 'product_name':
                    $target->loadMissing(['items.sku.product']);
                    $firstItem = $target->items->first();
                    return $firstItem && $firstItem->sku && $firstItem->sku->product ? $firstItem->sku->product->name : 'Items';
                case 'cart_token':
                    return $target->cart_token ?? '';
            }
        } elseif ($target instanceof User) {
            switch ($varName) {
                case 'customer_name':
                    return $target->first_name ?? 'Customer';
                case 'customer_email':
                    return $target->email ?? '';
                case 'customer_phone':
                    return $target->phone ?? '';
            }
        } elseif (is_string($target)) {
            // For OTP
            if ($varName === 'otp') {
                return $target;
            }
        }

        return '';
    }
}

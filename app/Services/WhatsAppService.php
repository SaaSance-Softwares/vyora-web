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
                $this->logMessage($to, $templateName, 'template', $messageId);

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
            $msg = $errorData['error']['message'] ?? 'Failed to create template on Meta.';

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
            $phone = $target->shipping_phone ?? $target->billing_phone;
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
            // Fallback
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

        return $this->sendTemplate($to, $templateName, $template ? $template->language : 'en_US', $components);
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
                    return $target->customer_name ?? $target->billing_first_name ?? 'Customer';
                case 'order_number':
                    return $target->order_number ?? '';
                case 'order_total':
                    return $target->total ? number_format($target->total, 2) : '';
                case 'product_names':
                    if ($target->items && $target->items->count() > 0) {
                        return $target->items->pluck('product_name')->implode(', ');
                    }
                    return 'Products';
                case 'tracking_url':
                    return $target->tracking_url ?? 'N/A';
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

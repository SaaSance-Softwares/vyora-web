<?php

namespace App\Services;

use App\Models\ThemeSetting;
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
        return $this->enabled && !empty($this->accessToken) && !empty($this->phoneId);
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
        if (!$this->isEnabled()) return false;

        $to = $this->formatPhoneNumber($to);

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
            ]
        ];

        if (!empty($components)) {
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

            Log::error("WhatsApp Template Error: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("WhatsApp API Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch all templates from Meta for the configured WABA ID
     */
    public function getTemplatesFromMeta()
    {
        if (!$this->enabled || empty($this->accessToken) || empty($this->wabaId)) {
            return ['error' => 'Integration disabled or missing WABA ID / Access Token.'];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->get("{$this->apiUrl}/{$this->apiVersion}/{$this->wabaId}/message_templates");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("WhatsApp Get Templates Error: " . $response->body());
            return ['error' => 'Failed to fetch templates from Meta.'];
        } catch (\Exception $e) {
            Log::error("WhatsApp API Exception: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create a new template on Meta
     */
    public function createTemplateToMeta(array $data)
    {
        if (!$this->enabled || empty($this->accessToken) || empty($this->wabaId)) {
            return ['error' => 'Integration disabled or missing WABA ID / Access Token.'];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->post("{$this->apiUrl}/{$this->apiVersion}/{$this->wabaId}/message_templates", $data);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            Log::error("WhatsApp Create Template Error: " . $response->body());
            $errorData = $response->json();
            $msg = $errorData['error']['message'] ?? 'Failed to create template on Meta.';
            return ['error' => $msg];
        } catch (\Exception $e) {
            Log::error("WhatsApp API Exception: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Helper to send event-based WhatsApp templates
     */
    public function sendEventWhatsApp(string $event, $target)
    {
        if (!$this->isEnabled()) return false;

        $templateName = $this->getTemplateName($event);
        if (!$templateName) return false;

        $phone = null;
        $components = [];

        // Order Events
        if (in_array($event, ['confirmed', 'shipped', 'cancelled']) && $target instanceof \App\Models\Order) {
            $phone = $target->shipping_phone ?? $target->billing_phone;
            // Example basic components: You might need to adjust based on actual template placeholders
            $components = [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $target->order_number]
                    ]
                ]
            ];
        }
        
        // Account Events
        if (in_array($event, ['account_created', 'password_updated']) && $target instanceof \App\Models\User) {
            $phone = $target->phone;
            $components = [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $target->first_name]
                    ]
                ]
            ];
        }

        if (!$phone) return false;

        return $this->sendTemplate($phone, $templateName, 'en_US', $components);
    }

    /**
     * Send a free-form text message (requires 24h open window)
     */
    public function sendText(string $to, string $text)
    {
        if (!$this->isEnabled()) return false;

        $to = $this->formatPhoneNumber($to);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $text,
            ]
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

            Log::error("WhatsApp Text Error: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("WhatsApp API Exception: " . $e->getMessage());
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
            'last_message_at' => now()
        ]);

        $type = $message['type'] ?? 'text';
        $body = $message['text']['body'] ?? "[Media/Unsupported Type]";
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
        if (!$this->isEnabled()) {
            return false;
        }

        // Fetch custom template name from Auth Settings
        $authFields = ThemeSetting::where('group', 'auth_settings')->where('key', 'auth_fields')->value('value');
        $templateName = 'user_verification_otp'; // Default fallback
        
        if ($authFields) {
            $decoded = json_decode($authFields, true);
            if (!empty($decoded['phone']['whatsapp_template'])) {
                $templateName = $decoded['phone']['whatsapp_template'];
            }
        }

        // OTP is usually the 1st parameter in the template
        $components = [
            [
                'type' => 'body',
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => $otp
                    ]
                ]
            ],
            [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => '0',
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => $otp
                    ]
                ]
            ]
        ];

        // We try with body parameter and button parameter, as OTP templates often use both. 
        // A simple sendTemplate call will ignore unused components in Meta API usually, 
        // but to be safe, we will just send body parameters which is the standard for text-based templates.
        $safeComponents = [
            [
                'type' => 'body',
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => $otp
                    ]
                ]
            ]
        ];

        return $this->sendTemplate($to, $templateName, 'en_US', $safeComponents);
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
}

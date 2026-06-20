<?php

namespace App\Services;

use Twilio\Rest\Client;
use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Exception;

class TwilioSmsService
{
    private $client;
    private $fromNumber;
    private $enabled;

    public function __construct()
    {
        $settings = ThemeSetting::where('group', 'integration.twilio')->get()->keyBy('key');

        $this->enabled = $settings->get('enabled')?->value === '1';

        if ($this->enabled) {
            try {
                $sid = $settings->get('twilio_sid') ? Crypt::decryptString($settings->get('twilio_sid')->value) : null;
                $token = $settings->get('twilio_auth_token') ? Crypt::decryptString($settings->get('twilio_auth_token')->value) : null;
                $this->fromNumber = $settings->get('twilio_phone_number') ? Crypt::decryptString($settings->get('twilio_phone_number')->value) : null;

                if ($sid && $token && $this->fromNumber) {
                    $this->client = new Client($sid, $token);
                }
            } catch (Exception $e) {
                Log::error('Twilio Integration Decryption Failed: ' . $e->getMessage());
                $this->enabled = false;
            }
        }
    }

    /**
     * Send an SMS message.
     *
     * @param string $to Phone number in E.164 format (+14155552671)
     * @param string $message The body of the text message
     * @return bool True if successful, false otherwise
     */
    public function sendSms(string $to, string $message): bool
    {
        if (!$this->enabled || !$this->client || empty($to) || empty($message)) {
            Log::info("Twilio SMS ignored: Integration not enabled or missing credentials/number/message.");
            return false;
        }

        try {
            $hasPlus = str_starts_with(trim($to), '+');
            $cleanPhone = preg_replace('/[^0-9]/', '', $to);

            if ($hasPlus) {
                // If they explicitly provided a country code, respect it entirely.
                $to = '+' . $cleanPhone;
            } else {
                // If no country code was provided, we assume the store's primary local context (India)
                if (strlen($cleanPhone) === 10) {
                    $to = '+91' . $cleanPhone;
                } elseif (strlen($cleanPhone) === 11 && str_starts_with($cleanPhone, '0')) {
                    $to = '+91' . substr($cleanPhone, 1);
                } elseif (strlen($cleanPhone) === 12 && str_starts_with($cleanPhone, '91')) {
                    $to = '+' . $cleanPhone;
                } else {
                    // Fallback
                    $to = '+' . $cleanPhone;
                }
            }

            $this->client->messages->create(
                $to,
                [
                    'from' => $this->fromNumber,
                    'body' => $message
                ]
            );

            return true;
        } catch (Exception $e) {
            Log::error('Twilio SMS Sending Failed: ' . $e->getMessage() . " | To: {$to}");
            return false;
        }
    }

    /**
     * Parse the template with dynamic variables.
     */
    public function parseTemplate(string $template, array $variables): string
    {
        $parsed = $template;
        foreach ($variables as $key => $value) {
            $parsed = str_replace('{' . $key . '}', $value, $parsed);
        }
        return $parsed;
    }

    /**
     * Helper to send an event-based SMS for an order.
     * 
     * @param string $event 'confirmed', 'shipped', 'cancelled'
     * @param \App\Models\Order $order
     */
    public function sendEventSms(string $event, \App\Models\Order $order): bool
    {
        if (!$this->enabled) return false;

        $order->loadMissing(['shippingAddress', 'items.sku.product']);
        $phone = $order->shippingAddress?->phone;
        
        if (!$phone) return false;

        $defaultTemplates = [
            'confirmed' => 'Hi {name}, your order #{order_id} for {items} has been confirmed. Thank you for shopping with us!',
            'shipped'   => 'Hi {name}, your order #{order_id} has been shipped! Track here: {tracking_url}',
            'cancelled' => 'Hi {name}, your order #{order_id} has been cancelled. If you have questions, please contact support.',
        ];

        $templateKey = "twilio_template_{$event}";
        $templateSetting = ThemeSetting::where('group', 'integration.twilio')->where('key', $templateKey)->first();

        $template = '';
        if ($templateSetting) {
            try {
                $template = Crypt::decryptString($templateSetting->value);
            } catch (Exception $e) {
                $template = '';
            }
        }

        if (empty($template)) {
            $template = $defaultTemplates[$event] ?? '';
        }

        if (empty($template)) return false;

        $itemsSummary = $order->items->map(function($item) {
            $productName = $item->product_name ?? ($item->sku->product->name ?? 'Item');
            return "{$item->quantity}x {$productName}";
        })->join(', ');

        $message = $this->parseTemplate($template, [
            'name' => $order->shippingAddress->name ?? 'Customer',
            'order_id' => $order->order_number ?? explode('-', $order->uuid)[0] ?? $order->id,
            'tracking_url' => $order->tracking_url ?? 'N/A',
            'items' => $itemsSummary ?: 'Your items'
        ]);

        return $this->sendSms($phone, $message);
    }

    /**
     * Send an OTP for login/verification.
     */
    public function sendOtpSms(string $to, string $otp): bool
    {
        if (!$this->enabled || !$this->client) {
            return false;
        }

        // Fetch custom template from Auth Settings
        $authFields = ThemeSetting::where('group', 'auth_settings')->where('key', 'auth_fields')->value('value');
        $template = 'Your Dope Style login OTP is {otp}. Valid for 5 minutes.'; // Default fallback
        
        if ($authFields) {
            $decoded = json_decode($authFields, true);
            if (!empty($decoded['phone']['sms_template'])) {
                $template = $decoded['phone']['sms_template'];
            }
        }

        $message = $this->parseTemplate($template, ['otp' => $otp]);
        return $this->sendSms($to, $message);
    }
}

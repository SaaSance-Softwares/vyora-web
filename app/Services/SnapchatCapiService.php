<?php

namespace App\Services;

use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SnapchatCapiService
{
    private $pixelId;
    private $accessToken;

    public function __construct()
    {
        $settings = ThemeSetting::where('group', 'integration.snapchat-pixel')
            ->whereIn('key', ['enabled', 'pixel_id', 'access_token'])
            ->get()
            ->keyBy('key');

        if ($settings->get('enabled')?->value === '1' && $settings->has('access_token') && $settings->has('pixel_id')) {
            try {
                $this->pixelId = Crypt::decryptString($settings->get('pixel_id')->value);
                $this->accessToken = Crypt::decryptString($settings->get('access_token')->value);
            } catch (\Exception $e) {
                Log::warning('Snapchat CAPI decryption failed: ' . $e->getMessage());
            }
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->pixelId) && !empty($this->accessToken);
    }

    public function sendEvent(string $eventName, string $eventId, string $sourceUrl, array $userData, array $customData)
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $formattedUserData = [
            'client_ip_address' => $userData['client_ip_address'] ?? null,
            'client_user_agent' => $userData['client_user_agent'] ?? null,
            'sc_cookie1' => $userData['sc_cookie1'] ?? null,
        ];

        if (!empty($userData['email'])) {
            $formattedUserData['em'] = hash('sha256', strtolower(trim($userData['email'])));
        }

        if (!empty($userData['phone'])) {
            // Hash phone number (must include country code, remove non-numeric)
            $phone = preg_replace('/[^0-9]/', '', $userData['phone']);
            $formattedUserData['ph'] = hash('sha256', $phone);
        }

        $payload = [
            'data' => [
                [
                    'event_name' => $eventName,
                    'client_dedup_id' => $eventId,
                    'action_source' => 'WEB',
                    'event_source_url' => $sourceUrl,
                    'event_time' => time(),
                    'user_data' => (object) array_filter($formattedUserData),
                    'custom_data' => (object) array_filter([
                        'value' => isset($customData['price']) ? (float) $customData['price'] : null,
                        'currency' => $customData['currency'] ?? null,
                        'content_ids' => $customData['item_ids'] ?? null,
                        'item_category' => isset($customData['item_category']) ? $customData['item_category'] : null,
                        'num_items' => isset($customData['number_items']) ? (int) $customData['number_items'] : null,
                        'order_id' => $customData['transaction_id'] ?? null,
                        'search_string' => $customData['search_string'] ?? null,
                        'sign_up_method' => $customData['sign_up_method'] ?? null,
                    ]),
                ]
            ]
        ];

        try {
            $response = Http::post("https://tr.snapchat.com/v3/{$this->pixelId}/events?access_token={$this->accessToken}", $payload);

            if (!$response->successful()) {
                Log::error('Snapchat CAPI Error: ' . $response->body());
            }

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Snapchat CAPI Exception: ' . $e->getMessage());
            return false;
        }
    }
}

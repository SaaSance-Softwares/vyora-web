<?php

namespace App\Services;

use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaCapiService
{
    private ?string $pixelId;

    private ?string $accessToken;

    private ?string $testEventCode;

    private bool $enabled;

    public function __construct()
    {
        $settings = ThemeSetting::where('group', 'integration.meta-pixel')->get()->keyBy('key');

        $this->enabled = ($settings->get('enabled')?->value === '1');

        $this->pixelId = $settings->get('pixel_id') ? $this->maybeDecrypt($settings->get('pixel_id')->value) : null;
        $this->accessToken = $settings->get('access_token') ? $this->maybeDecrypt($settings->get('access_token')->value) : null;
        $this->testEventCode = $settings->get('test_event_code') ? $this->maybeDecrypt($settings->get('test_event_code')->value) : null;
    }

    public function isConfigured(): bool
    {
        return $this->enabled && ! empty($this->pixelId) && ! empty($this->accessToken);
    }

    public function sendEvent(string $eventName, string $eventId, string $sourceUrl, array $userData, array $customData)
    {
        if (! $this->isConfigured()) {
            return false;
        }

        // Format user data
        $hashedUserData = [];

        if (! empty($userData['email'])) {
            $hashedUserData['em'] = hash('sha256', strtolower(trim($userData['email'])));
        }
        if (! empty($userData['phone'])) {
            // Meta expects phone numbers without leading zeros or + signs, but typically just numbers
            $phone = preg_replace('/[^0-9]/', '', $userData['phone']);
            if ($phone) {
                $hashedUserData['ph'] = hash('sha256', $phone);
            }
        }
        if (! empty($userData['client_ip_address'])) {
            $hashedUserData['client_ip_address'] = $userData['client_ip_address'];
        }
        if (! empty($userData['client_user_agent'])) {
            $hashedUserData['client_user_agent'] = $userData['client_user_agent'];
        }
        if (! empty($userData['fbp'])) {
            $hashedUserData['fbp'] = $userData['fbp'];
        }
        if (! empty($userData['fbc'])) {
            $hashedUserData['fbc'] = $userData['fbc'];
        }

        // Add dummy IP and UA if missing (required by some versions of CAPI)
        if (empty($hashedUserData['client_ip_address'])) {
            $hashedUserData['client_ip_address'] = request()->ip();
        }
        if (empty($hashedUserData['client_user_agent'])) {
            $hashedUserData['client_user_agent'] = request()->userAgent() ?? 'Unknown Agent';
        }

        $payload = [
            'data' => [
                [
                    'event_name' => $eventName,
                    'event_time' => time(),
                    'event_id' => $eventId,
                    'action_source' => 'website',
                    'event_source_url' => $sourceUrl,
                    'user_data' => $hashedUserData,
                    'custom_data' => empty($customData) ? new \stdClass : $customData,
                ],
            ],
        ];

        if (! empty($this->testEventCode)) {
            $payload['test_event_code'] = $this->testEventCode;
        }

        try {
            $response = Http::post("https://graph.facebook.com/v19.0/{$this->pixelId}/events?access_token={$this->accessToken}", $payload);

            if (! $response->successful()) {
                Log::error('Meta CAPI Error: '.$response->body());

                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Meta CAPI Exception: '.$e->getMessage());

            return false;
        }
    }

    private function maybeDecrypt(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }
}

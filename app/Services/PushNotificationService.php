<?php

namespace App\Services;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Send a push notification to a specific user.
     *
     * @param \App\Models\User $user
     * @param string $title
     * @param string $body
     * @param array $data
     * @return void
     */
    public function sendToUser($user, $title, $body, $data = [])
    {
        $tokens = DeviceToken::where('user_id', $user->id)->pluck('token')->toArray();

        Log::info('PushNotification: sendToUser called', [
            'user_id'     => $user->id,
            'user_email'  => $user->email,
            'token_count' => count($tokens),
            'title'       => $title,
        ]);

        if (empty($tokens)) {
            Log::warning('PushNotification: No device tokens found for user', ['user_id' => $user->id]);
            return false;
        }

        return $this->relayToSaaSance($tokens, $title, $body, $data);
    }

    protected function relayToSaaSance(array $tokens, $title, $body, $data = [])
    {
        $relayUrl = env('SAASANCE_RELAY_URL', 'https://saasance.com/api/sso/push-relay');
        $apiKeyRow = \App\Models\ThemeSetting::where('group', 'integration.saasance')->where('key', 'api_key')->first();
        $apiKey = $apiKeyRow ? \Illuminate\Support\Facades\Crypt::decryptString($apiKeyRow->value) : null;

        if (!$apiKey) {
            Log::warning('SaaSance Push Relay Skipped: API Key not generated. Please connect in Settings > Project Vyora.');
            throw new \Exception('API Key not generated. Please connect in Settings > Project Vyora.');
        }

        try {
            Log::info('PushNotification: Sending to SaaSance relay', [
                'relay_url'   => $relayUrl,
                'token_count' => count($tokens),
                'title'       => $title,
            ]);

            $response = Http::withoutVerifying()->withHeaders([
                'X-SaaSance-API-Key' => $apiKey,
                'Accept'             => 'application/json',
            ])->post($relayUrl, [
                'tokens'   => $tokens,
                'title'    => $title,
                'body'     => $body,
                'data'     => array_map('strval', $data), // FCM requires all data values as strings
                'priority' => 'high',
            ]);

            if (!$response->successful()) {
                Log::error('SaaSance Push Relay Failed', [
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);
                throw new \Exception('Relay failed with status ' . $response->status() . ': ' . $response->body());
            }

            Log::info('PushNotification: Successfully sent via SaaSance relay', [
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('SaaSance Push Relay Exception: ' . $e->getMessage());
            throw $e;
        }
    }
}

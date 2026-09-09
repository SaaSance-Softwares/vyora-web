<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoLocationService
{
    /**
     * Get geolocation data from an IP address.
     * Returns an array with 'city', 'state', 'country', 'ip'.
     *
     * @param string $ip
     * @return array
     */
    public static function getLocation(string $ip): array
    {
        // For local development, 127.0.0.1 won't return useful data.
        // We can optionally use a fallback IP for testing or just return nulls.
        if ($ip === '127.0.0.1' || $ip === '::1') {
            // Uncomment to test with a real IP locally:
            // $ip = '8.8.8.8'; 
        }

        try {
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}");

            if ($response->successful() && $response->json('status') === 'success') {
                return [
                    'ip' => $ip,
                    'city' => $response->json('city'),
                    'state' => $response->json('regionName'),
                    'country' => $response->json('country'),
                ];
            }
        } catch (\Exception $e) {
            Log::warning("GeoLocationService failed for IP {$ip}: " . $e->getMessage());
        }

        return [
            'ip' => $ip,
            'city' => null,
            'state' => null,
            'country' => null,
        ];
    }
}

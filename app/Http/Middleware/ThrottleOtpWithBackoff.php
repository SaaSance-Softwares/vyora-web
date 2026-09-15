<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class ThrottleOtpWithBackoff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maxAttempts = 3;
        $backoffMinutes = [10, 1440, 2880]; // 10m, 24h, 48h

        $keys = $this->resolveRequestSignatures($request);

        foreach ($keys as $key) {
            if ($this->hasTooManyAttempts($key)) {
                $retryAfter = Cache::get($this->lockoutKey($key)) - time();
                $headers = ['Retry-After' => $retryAfter > 0 ? $retryAfter : 600];
                throw new ThrottleRequestsException('Too many requests. Please try again later.', null, $headers);
            }
        }

        $response = $next($request);

        // For OTP sending, we ALWAYS increment attempts on every request
        // because we want to prevent someone from spamming successful requests.
        // It is cleared by ThrottleAuthWithBackoff on successful login.
        foreach ($keys as $key) {
            $this->incrementAttempt($key, $maxAttempts, $backoffMinutes);
        }

        return $response;
    }

    protected function resolveRequestSignatures(Request $request): array
    {
        $keys = [
            'otp_backoff_ip:' . $request->ip()
        ];

        if ($request->filled('email')) {
            $keys[] = 'otp_backoff_email:' . strtolower($request->input('email'));
        }

        if ($request->filled('phone')) {
            $keys[] = 'otp_backoff_phone:' . preg_replace('/[^0-9]/', '', $request->input('phone'));
        }

        if ($request->filled('identifier')) {
            $keys[] = 'otp_backoff_identifier:' . strtolower($request->input('identifier'));
        }

        return $keys;
    }

    protected function hasTooManyAttempts(string $key): bool
    {
        $lockoutExpiry = Cache::get($this->lockoutKey($key));
        
        if ($lockoutExpiry && $lockoutExpiry > time()) {
            return true;
        }

        return false;
    }

    protected function incrementAttempt(string $key, int $maxAttempts, array $backoffMinutes)
    {
        $attemptsKey = $this->attemptsKey($key);
        $tierKey = $this->tierKey($key);

        $attempts = Cache::increment($attemptsKey);

        if ($attempts === 1) {
            // Reset attempts window to 10 minutes
            Cache::put($attemptsKey, 1, now()->addMinutes(10));
        }

        if ($attempts >= $maxAttempts) {
            $tier = Cache::get($tierKey, 0);
            $penaltyMinutes = $backoffMinutes[$tier] ?? end($backoffMinutes);
            
            // Set lockout expiry timestamp
            Cache::put($this->lockoutKey($key), time() + ($penaltyMinutes * 60), now()->addMinutes($penaltyMinutes));
            
            // Advance tier for next lockout
            Cache::put($tierKey, $tier + 1, now()->addMinutes($penaltyMinutes * 3));
            
            // Clear attempts so they start fresh after lockout
            Cache::forget($attemptsKey);
        }
    }

    public static function clearAll(Request $request)
    {
        $keys = [
            'otp_backoff_ip:' . $request->ip()
        ];

        if ($request->filled('email')) {
            $keys[] = 'otp_backoff_email:' . strtolower($request->input('email'));
        }

        if ($request->filled('phone')) {
            $keys[] = 'otp_backoff_phone:' . preg_replace('/[^0-9]/', '', $request->input('phone'));
        }

        if ($request->filled('identifier')) {
            $keys[] = 'otp_backoff_identifier:' . strtolower($request->input('identifier'));
        }

        foreach ($keys as $key) {
            Cache::forget($key . ':attempts');
            Cache::forget($key . ':lockout');
            Cache::forget($key . ':tier');
        }
    }

    protected function attemptsKey(string $key): string
    {
        return $key . ':attempts';
    }

    protected function lockoutKey(string $key): string
    {
        return $key . ':lockout';
    }

    protected function tierKey(string $key): string
    {
        return $key . ':tier';
    }
}

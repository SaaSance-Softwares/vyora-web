<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class ThrottleAuthWithBackoff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $maxAttempts = config('rate_limiting.auth.max_attempts', 5);
        $backoffMinutes = config('rate_limiting.auth.backoff_minutes', [1, 5, 15, 60]);

        $keys = $this->resolveRequestSignatures($request);

        foreach ($keys as $key) {
            if ($this->hasTooManyAttempts($key)) {
                $retryAfter = Cache::get($this->lockoutKey($key)) - time();
                $headers = ['Retry-After' => $retryAfter > 0 ? $retryAfter : 60];
                throw new ThrottleRequestsException('Too many authentication attempts. Please try again later.', null, $headers);
            }
        }

        $response = $next($request);

        if ($this->isSuccessfulLoginResponse($response)) {
            foreach ($keys as $key) {
                $this->clear($key);
            }
        } else {
            foreach ($keys as $key) {
                $this->incrementAttempt($key, $maxAttempts, $backoffMinutes);
            }
        }

        return $response;
    }

    protected function resolveRequestSignatures(Request $request): array
    {
        $keys = [
            'auth_backoff_ip:' . $request->ip()
        ];

        if ($request->filled('email')) {
            $keys[] = 'auth_backoff_email:' . strtolower($request->input('email'));
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
            // Reset attempts window to 1 minute
            Cache::put($attemptsKey, 1, now()->addMinutes(1));
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

    protected function clear(string $key)
    {
        Cache::forget($this->attemptsKey($key));
        Cache::forget($this->lockoutKey($key));
        Cache::forget($this->tierKey($key));
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

    protected function isSuccessfulLoginResponse(Response $response): bool
    {
        // For API (JSON) responses
        if ($response->getStatusCode() >= 400) {
            return false; // Error (422 validation, 401 unauthorized, etc.)
        }

        // For web (Redirect) responses with validation errors
        if ($response->isRedirection() && session()->has('errors')) {
            return false;
        }

        return true;
    }
}

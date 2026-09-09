<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Endpoints
    |--------------------------------------------------------------------------
    |
    | Limits for login, registration, and other sensitive endpoints.
    | Implements a custom exponential backoff mechanism.
    |
    */
    'auth' => [
        // Number of failed attempts before the first lockout
        'max_attempts' => env('RATE_LIMIT_AUTH_ATTEMPTS', 5),
        
        // Lockout durations (in minutes) that progressively increase
        'backoff_minutes' => [1, 5, 15, 60],
    ],

    /*
    |--------------------------------------------------------------------------
    | Public API Endpoints
    |--------------------------------------------------------------------------
    |
    | Moderate limits for unauthenticated endpoints (e.g., product listings).
    | Default is 60 requests per minute per IP address.
    |
    */
    'public' => [
        'max_attempts' => env('RATE_LIMIT_PUBLIC_ATTEMPTS', 60),
        'decay_minutes' => env('RATE_LIMIT_PUBLIC_DECAY', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authenticated API Endpoints
    |--------------------------------------------------------------------------
    |
    | Looser limits for authenticated users.
    | Default is 120 requests per minute per User ID (or IP as fallback).
    |
    */
    'authenticated' => [
        'max_attempts' => env('RATE_LIMIT_AUTH_USER_ATTEMPTS', 120),
        'decay_minutes' => env('RATE_LIMIT_AUTH_USER_DECAY', 1),
    ],

];

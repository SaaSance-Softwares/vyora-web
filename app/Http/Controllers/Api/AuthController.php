<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->strictValidate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|unique:users',
            'password' => 'required|string|min:8|max:128|confirmed',
            'has_consented_to_terms' => 'required|boolean|accepted',
            'has_consented_to_marketing' => 'nullable|boolean',
        ]);

        $geo = \App\Services\GeoLocationService::getLocation($request->ip());

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'has_consented_to_terms' => $request->boolean('has_consented_to_terms'),
            'has_consented_to_marketing' => $request->boolean('has_consented_to_marketing'),
            'consent_timestamp' => now(),
            'consent_ip_address' => $request->ip(),
            'registration_ip' => $geo['ip'],
            'city' => $geo['city'],
            'state' => $geo['state'],
            'country' => $geo['country'],
        ]);

        try {
            app(WhatsAppService::class)->sendEventWhatsApp('account_created', $user);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp account created: '.$e->getMessage());
        }

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\WelcomeEmail($user));
        } catch (\Exception $e) {
            Log::error('Failed to send Welcome Email: '.$e->getMessage());
        }

        if ($request->hasSession()) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function sendRegistrationOtp(Request $request)
    {
        $request->strictValidate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users', // Must have phone for OTP, unique required
            'password' => 'required|string|min:8|max:128|confirmed',
            'has_consented_to_terms' => 'required|boolean|accepted',
            'has_consented_to_marketing' => 'nullable|boolean',
        ]);

        $phone = $request->phone;
        
        // Check if phone is already registered
        if (User::where('phone', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => ['This phone number is already registered.'],
            ]);
        }

        // Generate 6-digit OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Cache pending user data + OTP for 10 minutes
        $cacheKey = 'register_otp_' . preg_replace('/[^0-9]/', '', $phone);
        Cache::put($cacheKey, [
            'data' => $request->only([
                'name', 'email', 'phone', 'password', 'has_consented_to_terms', 'has_consented_to_marketing'
            ]),
            'otp' => $otp,
        ], now()->addMinutes(10));

        // Send OTP via WhatsApp
        try {
            $sent = app(WhatsAppService::class)->sendOtpWhatsApp($phone, $otp);
            if (!$sent) {
                return response()->json(['error' => 'Failed to send OTP via WhatsApp. Integration might be disabled.'], 500);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp OTP Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send OTP. Please try again later.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
            'requires_otp' => true,
        ]);
    }

    public function verifyRegistrationOtp(Request $request)
    {
        $request->strictValidate([
            'phone' => 'required|string|max:20',
            'otp' => 'required|string|size:6',
        ]);

        $phone = $request->phone;
        $cacheKey = 'register_otp_' . preg_replace('/[^0-9]/', '', $phone);
        
        $cached = Cache::get($cacheKey);

        if (!$cached || $cached['otp'] !== $request->otp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP.'],
            ]);
        }

        // OTP is valid, create the user
        $data = $cached['data'];

        $geo = \App\Services\GeoLocationService::getLocation($request->ip());

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
            'has_consented_to_terms' => $data['has_consented_to_terms'],
            'has_consented_to_marketing' => $data['has_consented_to_marketing'] ?? false,
            'consent_timestamp' => now(),
            'consent_ip_address' => $request->ip(),
            'registration_ip' => $geo['ip'],
            'city' => $geo['city'],
            'state' => $geo['state'],
            'country' => $geo['country'],
        ]);

        // Cleanup cache
        Cache::forget($cacheKey);

        try {
            app(WhatsAppService::class)->sendEventWhatsApp('account_created', $user);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp account created: '.$e->getMessage());
        }

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\WelcomeEmail($user));
        } catch (\Exception $e) {
            Log::error('Failed to send Welcome Email: '.$e->getMessage());
        }

        if ($request->hasSession()) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function login(Request $request)
    {
        $request->strictValidate([
            'identifier' => 'required|string|max:255',
            'password' => 'required|string|max:128',
        ]);

        $identifier = $request->identifier;

        $user = User::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['Invalid credentials provided.'],
            ]);
        }

        if ($request->hasSession()) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function sendLoginOtp(Request $request)
    {
        $request->strictValidate([
            'phone' => 'required|string|max:20',
        ]);

        $phone = $request->phone;
        
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'phone' => ['No account found with this phone number.'],
            ]);
        }

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $cacheKey = 'login_otp_' . preg_replace('/[^0-9]/', '', $phone);
        Cache::put($cacheKey, [
            'user_id' => $user->id,
            'otp' => $otp,
        ], now()->addMinutes(10));

        try {
            $sent = app(WhatsAppService::class)->sendOtpWhatsApp($phone, $otp);
            if (!$sent) {
                return response()->json(['error' => 'Failed to send OTP via WhatsApp. Integration might be disabled.'], 500);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp OTP Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send OTP. Please try again later.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully',
            'requires_otp' => true,
        ]);
    }

    public function verifyLoginOtp(Request $request)
    {
        try {
            $request->strictValidate([
                'phone' => 'required|string|max:20',
                'otp' => 'required|string|size:6',
            ]);

            $phone = $request->phone;
            $cacheKey = 'login_otp_' . preg_replace('/[^0-9]/', '', $phone);
            
            $cached = Cache::get($cacheKey);

            if (!$cached || (string)$cached['otp'] !== (string)$request->otp) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => ['otp' => ['Invalid or expired OTP.']]
                ], 422);
            }

            $user = User::find($cached['user_id']);
            if (!$user) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => ['phone' => ['Account no longer exists.']]
                ], 422);
            }

            Cache::forget($cacheKey);

            if ($request->hasSession()) {
                Auth::guard('web')->login($user);
                $request->session()->regenerate();
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('OTP Verify Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function user(Request $request)
    {
        return $request->user();
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }
}

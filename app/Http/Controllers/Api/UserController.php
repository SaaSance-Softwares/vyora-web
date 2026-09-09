<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountDeletionOtpEmail;
use App\Services\WhatsAppService;

class UserController extends Controller
{
    public function updateConsent(Request $request)
    {
        $request->strictValidate([
            'has_consented_to_terms' => 'required|boolean',
            'has_consented_to_marketing' => 'required|boolean',
        ]);

        $user = $request->user();

        $user->update([
            'has_consented_to_terms' => $request->boolean('has_consented_to_terms'),
            'has_consented_to_marketing' => $request->boolean('has_consented_to_marketing'),
            'consent_timestamp' => now(),
            'consent_ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Consent preferences updated successfully.',
            'user' => $user,
        ]);
    }

    public function sendDeleteOtp(Request $request)
    {
        $user = $request->user();

        // Prevent admins and managers from deleting their accounts via frontend
        if (in_array($user->role, ['administrator', 'admin', 'manager', 'superadmin', 'editor', 'customer_service'])) {
            return response()->json([
                'message' => 'Admin and Manager accounts cannot be deleted from the customer portal.'
            ], 403);
        }

        // Generate 6-digit OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $cacheKey = 'delete_otp_' . $user->id;

        // Cache OTP for 10 minutes
        Cache::put($cacheKey, $otp, now()->addMinutes(10));

        $whatsappService = app(WhatsAppService::class);
        
        try {
            if ($whatsappService->isEnabled() && !empty($user->phone)) {
                // Send via WhatsApp
                $sent = $whatsappService->sendOtpWhatsApp($user->phone, $otp);
                if (!$sent) {
                    throw new \Exception("WhatsApp API returned false");
                }
            } else {
                // Fallback to Email
                if (!empty($user->email)) {
                    Mail::to($user->email)->send(new AccountDeletionOtpEmail($otp));
                } else {
                    return response()->json(['error' => 'No phone or email available to send OTP.'], 400);
                }
            }
        } catch (\Exception $e) {
            Log::error('OTP Send Error (Delete Account): ' . $e->getMessage());
            
            // Try fallback to email if WhatsApp threw an exception
            if (!empty($user->email)) {
                try {
                    Mail::to($user->email)->send(new AccountDeletionOtpEmail($otp));
                } catch (\Exception $e2) {
                    return response()->json(['error' => 'Failed to send OTP via WhatsApp and Email. Please try again later.'], 500);
                }
            } else {
                return response()->json(['error' => 'Failed to send OTP. Please try again later.'], 500);
            }
        }

        return response()->json([
            'message' => 'OTP sent successfully. Please check your WhatsApp or Email.'
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $request->strictValidate([
            'otp' => 'required|string|size:6',
        ]);

        $user = $request->user();

        // Prevent admins and managers from deleting their accounts via frontend
        if (in_array($user->role, ['administrator', 'admin', 'manager', 'superadmin', 'editor', 'customer_service'])) {
            return response()->json([
                'message' => 'Admin and Manager accounts cannot be deleted from the customer portal.'
            ], 403);
        }

        // Validate OTP
        $cacheKey = 'delete_otp_' . $user->id;
        $cachedOtp = Cache::get($cacheKey);

        if (!$cachedOtp || $cachedOtp !== $request->otp) {
            return response()->json([
                'errors' => ['otp' => ['Invalid or expired OTP.']]
            ], 422);
        }

        // Clear OTP
        Cache::forget($cacheKey);

        // Data Minimization Rule (DPDP)
        // If user has orders, we anonymize instead of hard deleting to preserve financial/tax records.
        if ($user->orders()->count() > 0) {
            $randomId = Str::random(12);
            $user->update([
                'name' => 'Deleted_User_' . $randomId,
                'email' => 'deleted_' . $randomId . '@vyora.local',
                'phone' => '0000000000',
                'password' => \Illuminate\Support\Facades\Hash::make(Str::random(32)),
                'provider' => null,
                'provider_id' => null,
                'has_consented_to_terms' => false,
                'has_consented_to_marketing' => false,
                'consent_ip_address' => null,
            ]);
            
            // Log out user and revoke tokens
            $user->tokens()->delete();
            \Illuminate\Support\Facades\Auth::guard('web')->logout();
            
            if ($request->hasSession()) {
                $request->session()->invalidate();
            }
            
            return response()->json([
                'message' => 'Account successfully anonymized and deleted according to DPDP data minimization rules.'
            ]);
        }

        // Hard delete if no orders
        $user->tokens()->delete();
        \Illuminate\Support\Facades\Auth::guard('web')->logout();
        
        if ($request->hasSession()) {
            $request->session()->invalidate();
        }
        
        $user->delete();

        return response()->json([
            'message' => 'Account and personal data successfully deleted.'
        ]);
    }
}

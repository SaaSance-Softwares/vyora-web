<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MetaCapiService;
use App\Services\SnapchatCapiService;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function metaEvent(Request $request, MetaCapiService $capiService)
    {
        $request->validate([
            'event_name' => 'required|string|max:255',
            'event_id' => 'required|string|max:255',
            'event_source_url' => 'required|string|max:255',
            'custom_data' => 'nullable|array',
            'user_data' => 'nullable|array',
        ]);

        if (! $capiService->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'CAPI not configured']);
        }

        $inputUserData = $request->input('user_data', []);

        $userData = [
            'client_ip_address' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
            'fbp' => $request->cookie('_fbp') ?? ($inputUserData['fbp'] ?? null),
            'fbc' => $request->cookie('_fbc') ?? ($inputUserData['fbc'] ?? null),
        ];

        // 1. Authenticated user profile priority
        if (auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            $userData['email'] = $user->email;
            $userData['phone'] = $user->phone;
            $userData['first_name'] = $user->name;
        }

        // 2. Guest checkout and form inputs
        if (empty($userData['email']) && ! empty($inputUserData['email'])) {
            $userData['email'] = $inputUserData['email'];
        }
        if (empty($userData['phone']) && ! empty($inputUserData['phone'])) {
            $userData['phone'] = $inputUserData['phone'];
        }
        if (empty($userData['first_name']) && ! empty($inputUserData['name'])) {
            $userData['first_name'] = $inputUserData['name'];
        }
        if (! empty($inputUserData['first_name'])) {
            $userData['first_name'] = $inputUserData['first_name'];
        }
        if (! empty($inputUserData['last_name'])) {
            $userData['last_name'] = $inputUserData['last_name'];
        }
        if (! empty($inputUserData['city'])) {
            $userData['city'] = $inputUserData['city'];
        }
        if (! empty($inputUserData['state'])) {
            $userData['state'] = $inputUserData['state'];
        }
        if (! empty($inputUserData['zip'])) {
            $userData['zip'] = $inputUserData['zip'];
        }
        if (! empty($inputUserData['country'])) {
            $userData['country'] = $inputUserData['country'];
        }

        $capiService->sendEvent(
            $request->event_name,
            $request->event_id,
            $request->event_source_url,
            $userData,
            $request->custom_data ?? []
        );

        return response()->json(['success' => true]);
    }

    public function snapchatEvent(Request $request, SnapchatCapiService $capiService)
    {
        $request->validate([
            'event_name' => 'required|string|max:255',
            'event_id' => 'required|string|max:255',
            'event_source_url' => 'required|string|max:255',
            'custom_data' => 'nullable|array',
            'user_data' => 'nullable|array',
        ]);

        if (! $capiService->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'CAPI not configured']);
        }

        $inputUserData = $request->input('user_data', []);

        $userData = [
            'client_ip_address' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
            'sc_cookie1' => $request->cookie('_scid') ?? ($inputUserData['sc_cookie1'] ?? null),
        ];

        if (auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            $userData['email'] = $user->email;
            $userData['phone'] = $user->phone;
        }

        if (empty($userData['email']) && ! empty($inputUserData['email'])) {
            $userData['email'] = $inputUserData['email'];
        }
        if (empty($userData['phone']) && ! empty($inputUserData['phone'])) {
            $userData['phone'] = $inputUserData['phone'];
        }

        $capiService->sendEvent(
            $request->event_name,
            $request->event_id,
            $request->event_source_url,
            $userData,
            $request->custom_data ?? []
        );

        return response()->json(['success' => true]);
    }
}

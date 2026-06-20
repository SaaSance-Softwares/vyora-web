<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MetaCapiService;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function metaEvent(Request $request, MetaCapiService $capiService)
    {
        $request->validate([
            'event_name' => 'required|string',
            'event_id' => 'required|string',
            'event_source_url' => 'required|string',
            'custom_data' => 'nullable|array',
        ]);

        if (! $capiService->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'CAPI not configured']);
        }

        $userData = [
            'client_ip_address' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
            'fbp' => $request->cookie('_fbp') ?? $request->input('fbp'),
            'fbc' => $request->cookie('_fbc') ?? $request->input('fbc'),
        ];

        if (auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            $userData['email'] = $user->email;
            $userData['phone'] = $user->phone;
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

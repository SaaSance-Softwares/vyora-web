<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPincode;
use Illuminate\Http\Request;

class DeliveryPinApiController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'pincode' => 'required|string|max:20',
        ]);

        $pin = trim($request->pincode);

        $allowedPins = DeliveryPincode::where('type', 'allowed')->pluck('pincode')->toArray();
        $excludedPins = DeliveryPincode::where('type', 'excluded')->pluck('pincode')->toArray();

        // Rule 1: If there are allowed pins configured, the pin MUST be in the allowed list
        if (! empty($allowedPins)) {
            if (! in_array($pin, $allowedPins)) {
                return response()->json([
                    'available' => false,
                    'message' => 'Delivery is not available in your area.',
                ]);
            }
        }

        // Rule 2: The pin MUST NOT be in the excluded list
        if (in_array($pin, $excludedPins)) {
            return response()->json([
                'available' => false,
                'message' => 'Delivery is not available in your area.',
            ]);
        }

        // If it passes the checks
        return response()->json([
            'available' => true,
            'message' => 'Delivery is available in your area!',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\PostalCode;
use Illuminate\Http\Request;

class LocalizationApiController extends Controller
{
    public function getActiveCountries()
    {
        $countries = Country::where('is_active', true)->select('id', 'name', 'code')->get();
        return response()->json($countries);
    }

    public function lookupPostalCode(Request $request, $country_id, $postal_code)
    {
        $country = Country::where('is_active', true)->find($country_id);

        if (!$country) {
            return response()->json(['error' => 'Country not found or inactive'], 404);
        }

        $record = PostalCode::where('country_id', $country->id)
            ->where('postal_code', $postal_code)
            ->select('city', 'district', 'state')
            ->first();

        if ($record) {
            return response()->json([
                'found' => true,
                'city' => $record->city,
                'district' => $record->district,
                'state' => $record->state
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'Postal code not found in dictionary'
        ], 404);
    }
}

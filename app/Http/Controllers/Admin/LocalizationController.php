<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\PostalCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocalizationController extends Controller
{
    public function index()
    {
        $countries = Country::withCount('postalCodes')->get();
        return view('admin.localization.index', compact('countries'));
    }

    public function storeCountry(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:countries,name',
            'code' => 'required|string|max:10|unique:countries,code',
        ]);

        Country::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Country added successfully.');
    }

    public function uploadChunk(Request $request, Country $country)
    {
        $request->validate([
            'chunk' => 'required|array',
            'chunk.*.postal_code' => 'required|string|max:50',
            'chunk.*.city' => 'nullable|string|max:255',
            'chunk.*.district' => 'nullable|string|max:255',
            'chunk.*.state' => 'required|string|max:255',
        ]);

        $chunk = $request->input('chunk');
        $insertData = [];

        foreach ($chunk as $row) {
            $insertData[] = [
                'country_id' => $country->id,
                'postal_code' => $row['postal_code'],
                'city' => $row['city'] ?? null,
                'district' => $row['district'] ?? null,
                'state' => $row['state'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Use chunk insert to handle max placeholders limit in MySQL
        foreach (array_chunk($insertData, 500) as $insertChunk) {
            PostalCode::insert($insertChunk);
        }

        return response()->json(['status' => 'success', 'message' => count($insertData) . ' rows inserted']);
    }

    public function exportCountry(Country $country)
    {
        $fileName = 'postal_codes_' . strtolower($country->code) . '_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($country) {
            $handle = fopen('php://output', 'w');

            // CSV Header matching required import format
            fputcsv($handle, ['postal_code', 'city', 'district', 'state']);

            PostalCode::where('country_id', $country->id)
                ->select(['postal_code', 'city', 'district', 'state'])
                ->orderBy('postal_code')
                ->chunk(2000, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->postal_code,
                            $row->city ?? '',
                            $row->district ?? '',
                            $row->state,
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function truncateCountry(Country $country)
    {
        // Delete all postal codes for this country
        $country->postalCodes()->delete();

        return redirect()->back()->with('success', 'All postal codes for ' . $country->name . ' have been wiped.');
    }

    public function destroyCountry(Country $country)
    {
        // Will cascade delete postal codes based on DB schema, but let's be explicit
        $country->postalCodes()->delete();
        $country->delete();

        return redirect()->back()->with('success', 'Country deleted successfully.');
    }
}

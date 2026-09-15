<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StoreLocatorController extends Controller
{
    public function index()
    {
        $stores = DB::table('pos_locations')
            ->where('is_active', 1)
            ->where('show_in_store', 1)
            ->get();

        $stores->transform(function ($store) {
            if (!empty($store->open_time) && !empty($store->close_time)) {
                $store->operating_hours = \Carbon\Carbon::parse($store->open_time)->format('g:i A') . ' - ' . \Carbon\Carbon::parse($store->close_time)->format('g:i A');
            } elseif (!empty($store->operating_hours)) {
                $store->operating_hours = $store->operating_hours;
            } else {
                $store->operating_hours = null;
            }
            return $store;
        });

        $storeName = \App\Models\ThemeSetting::where('key', 'store_name')->value('value') ?: 'Our Store';

        // Generate Geo/AEO/LocalBusiness Schema for multiple stores
        $schemaList = [];
        if ($stores->isNotEmpty()) {
            foreach ($stores as $store) {
                $schemaList[] = [
                    "@context" => "https://schema.org",
                    "@type" => "Store",
                    "name" => $store->name,
                    "openingHours" => $store->operating_hours ?? "",
                    "image" => $store->store_image ? asset('storage/' . $store->store_image) : asset('/favicon.ico'),
                    "telephone" => $store->contact_phone ?? '',
                    "email" => $store->contact_email ?? '',
                    "address" => [
                        "@type" => "PostalAddress",
                        "streetAddress" => $store->address . ' ' . $store->address_line_2,
                        "addressLocality" => $store->city,
                        "addressRegion" => $store->state,
                        "postalCode" => $store->pincode,
                        "addressCountry" => $store->country ?? "IN"
                    ],
                    "url" => $store->map_link ?? url('/stores')
                ];
            }
        }

        $viewData = [
            'og_title' => "Our Stores | " . $storeName,
            'og_description' => "Find a {$storeName} physical store near you. View our locations, opening hours, and contact details.",
        ];

        // Only attach Schema if we actually have stores
        if (!empty($schemaList)) {
            $viewData['json_ld'] = $schemaList;
        }

        return Inertia::render('StoreLocator/Index', [
            'stores' => $stores
        ])->withViewData($viewData);
    }
}

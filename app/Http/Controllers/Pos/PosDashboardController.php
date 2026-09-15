<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PosDashboardController extends Controller
{
    public function index(Request $request)
    {
        Inertia::setRootView('pos');
        return Inertia::render('Pos/Dashboard', [
            'appName' => config('app.name', 'Vyora'),

            'user' => $request->user(),
        ]);
    }

    public function receiptBuilder(Request $request)
    {
        Inertia::setRootView('pos');
        return Inertia::render('Pos/ReceiptBuilder');
    }

    public function returns(Request $request)
    {
        Inertia::setRootView('pos');
        return Inertia::render('Pos/Returns');
    }

    public function manifest(Request $request)
    {
        $posUrl = 'pos';
        try {
            $setting = \Illuminate\Support\Facades\DB::table('theme_settings')
                ->where('group', 'pos_settings')
                ->where('key', 'pos_url')
                ->first();
            if ($setting && !empty($setting->value)) {
                $posUrl = ltrim($setting->value, '/');
            }
        } catch (\Exception $e) {}

        return response()->json([
            'name' => 'Vyora POS',
            'short_name' => 'Vyora POS',
            'description' => 'Vyora Point of Sale System',
            'start_url' => '/' . $posUrl,
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#000000',
            'orientation' => 'portrait',
            'icons' => [
                [
                    'src' => '/vyora-asset/pos/vyora-pos-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png'
                ],
                [
                    'src' => '/vyora-asset/pos/vyora-pos.png',
                    'sizes' => '512x512',
                    'type' => 'image/png'
                ]
            ]
        ]);
    }
}

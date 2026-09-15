<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ThemeSetting;
use App\Models\OrderStatus;

class PosSettingController extends Controller
{
    public function index()
    {
        $settings = ThemeSetting::where('group', 'pos_settings')->pluck('value', 'key');
        $posEnabled = $settings['pos_enabled'] ?? '0';
        $posUrl = $settings['pos_url'] ?? 'pos';
        $defaultPosStatus = $settings['default_pos_status'] ?? null;
        $defaultPosGuestName = $settings['default_pos_guest_name'] ?? 'POS Customer';
        
        $orderStatuses = OrderStatus::orderBy('sort_order')->get();

        return view('admin.pos-settings.index', compact('posEnabled', 'posUrl', 'defaultPosStatus', 'defaultPosGuestName', 'orderStatuses'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'pos_enabled' => 'nullable|string',
            'pos_url' => 'nullable|string|regex:/^[a-zA-Z0-9\-\_]+$/',
            'default_pos_status' => 'nullable|integer',
            'default_pos_guest_name' => 'nullable|string|max:255',
        ]);

        $enabled = $request->has('pos_enabled') ? '1' : '0';
        
        ThemeSetting::updateOrCreate(
            ['group' => 'pos_settings', 'key' => 'pos_enabled'],
            ['value' => $enabled]
        );

        if ($request->filled('pos_url')) {
            ThemeSetting::updateOrCreate(
                ['group' => 'pos_settings', 'key' => 'pos_url'],
                ['value' => ltrim($request->pos_url, '/')]
            );
        }

        if ($request->filled('default_pos_status')) {
            ThemeSetting::updateOrCreate(
                ['group' => 'pos_settings', 'key' => 'default_pos_status'],
                ['value' => $request->default_pos_status]
            );
        }

        if ($request->filled('default_pos_guest_name')) {
            ThemeSetting::updateOrCreate(
                ['group' => 'pos_settings', 'key' => 'default_pos_guest_name'],
                ['value' => $request->default_pos_guest_name]
            );
        }

        return back()->with('success', 'POS Settings updated successfully.');
    }
}

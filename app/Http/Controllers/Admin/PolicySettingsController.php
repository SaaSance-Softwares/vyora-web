<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;

class PolicySettingsController extends Controller
{
    const GROUP = 'policies';

    const KEYS = [
        'cod_charges',
        'prepaid_charges',
        'delivery_timeline',
        'return_policy',
        'exchange_policy',
        'refund_method',
        'extra_sections', // JSON array: [{heading, content}, ...]
    ];

    public function index()
    {
        $rows = ThemeSetting::where('group', self::GROUP)->get()->keyBy('key');

        // Build a simple key→value array for the view
        $settings = collect(self::KEYS)->mapWithKeys(fn($k) => [$k => $rows->get($k)?->value ?? '']);

        return view('admin.policy-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach (self::KEYS as $key) {
            ThemeSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $request->input($key, ''), 'group' => self::GROUP]
            );
        }

        return redirect()->back()->with('success', 'Policy settings saved successfully.');
    }
}

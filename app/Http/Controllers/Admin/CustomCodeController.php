<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;

class CustomCodeController extends Controller
{
    const KEYS = [
        'custom_code_header',
        'custom_code_body',
        'custom_code_footer'
    ];

    public function index()
    {
        $settings = ThemeSetting::whereIn('key', self::KEYS)->pluck('value', 'key');
        
        return view('admin.custom-code.index', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach (self::KEYS as $key) {
            ThemeSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $request->input($key, ''), 'group' => 'custom_code']
            );
        }

        return back()->with('success', 'Custom code settings updated successfully.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use App\Models\LegalPage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FooterSettingsController extends Controller
{
    const GROUP = 'footer';

    const KEYS = [
        'footer_structure', 
        'footer_bg_color', 
        'footer_text_color', 
        'footer_bottom_text', 
        'footer_social_links',
        'footer_show_newsletter'
    ];

    public function index()
    {
        $rows = ThemeSetting::where('group', self::GROUP)->get()->keyBy('key');

        $settings = [];
        foreach (self::KEYS as $key) {
            $settings[$key] = $rows->get($key)?->value;
        }

        // Defaults
        $settings['footer_structure'] = $settings['footer_structure'] ?? '[]';
        $settings['footer_bg_color'] = $settings['footer_bg_color'] ?? '#ffffff';
        $settings['footer_text_color'] = $settings['footer_text_color'] ?? '#000000';
        $settings['footer_bottom_text'] = $settings['footer_bottom_text'] ?? '© ' . date('Y') . ' Your Store Name. All rights reserved.';
        $settings['footer_social_links'] = $settings['footer_social_links'] ?? '[]';
        $settings['footer_show_newsletter'] = $settings['footer_show_newsletter'] ?? '0';

        // Fetch Legal Pages to allow linking them
        $legalPages = LegalPage::where('is_published', true)->get(['id', 'title', 'slug']);

        return view('admin.footer-settings.index', compact('settings', 'legalPages'));
    }

    public function update(Request $request)
    {
        $data = $request->strictValidate([
            'footer_structure' => 'nullable|string|max:5000',
            'footer_bg_color' => 'nullable|string|max:255',
            'footer_text_color' => 'nullable|string|max:255',
            'footer_bottom_text' => 'nullable|string|max:255',
            'footer_social_links' => 'nullable|string|max:5000',
            'footer_show_newsletter' => 'nullable|string|max:255',
        ]);

        foreach (self::KEYS as $key) {
            if (isset($data[$key])) {
                ThemeSetting::updateOrCreate(
                    ['group' => self::GROUP, 'key' => $key],
                    ['value' => $data[$key]]
                );
            }
        }

        return redirect()->back()->with('success', 'Footer settings updated successfully.');
    }
}

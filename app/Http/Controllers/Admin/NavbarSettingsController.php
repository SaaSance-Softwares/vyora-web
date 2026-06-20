<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NavbarSettingsController extends Controller
{
    const GROUP = 'navbar';
    const KEYS = ['navbar_style', 'nav_alignment', 'nav_position', 'nav_hover_style', 'menu_structure'];

    public function index()
    {
        $rows = ThemeSetting::where('group', self::GROUP)->get()->keyBy('key');

        $settings = [];
        foreach (self::KEYS as $key) {
            $settings[$key] = $rows->get($key)?->value;
        }

        // Defaults
        $settings['navbar_style'] = $settings['navbar_style'] ?? 'default';
        $settings['nav_alignment'] = $settings['nav_alignment'] ?? 'left';
        $settings['nav_position'] = $settings['nav_position'] ?? 'inline';
        $settings['nav_hover_style'] = $settings['nav_hover_style'] ?? 'none';
        $settings['menu_structure'] = $settings['menu_structure'] ?? '[]';

        // Fetch all categories for the dropdown, formatted hierarchically
        $allCats = \App\Models\Category::orderBy('sort_order')->get();
        // Flatten into hierarchy name
        $categories = collect();
        foreach ($allCats->whereNull('parent_id') as $root) {
            $categories->push((object)[
                'id' => $root->id,
                'slug' => $root->slug,
                'name' => $root->name
            ]);
            foreach ($allCats->where('parent_id', $root->id) as $sub) {
                $categories->push((object)[
                    'id' => $sub->id,
                    'slug' => $sub->slug,
                    'name' => $root->name . ' > ' . $sub->name
                ]);
                foreach ($allCats->where('parent_id', $sub->id) as $deep) {
                    $categories->push((object)[
                        'id' => $deep->id,
                        'slug' => $deep->slug,
                        'name' => $root->name . ' > ' . $sub->name . ' > ' . $deep->name
                    ]);
                }
            }
        }

        $collections = \App\Models\Collection::all();
        $pages = \App\Models\CmsPage::all();

        return view('admin.navbar-settings.index', compact('settings', 'categories', 'collections', 'pages'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'navbar_style' => 'required|in:default,mega_menu,custom',
            'nav_alignment' => 'required|in:left,center,right',
            'nav_position' => 'required|in:inline,below',
            'nav_hover_style' => 'nullable|in:none,underline,left_to_right',
            'menu_structure' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            ThemeSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => self::GROUP]
            );
        }

        Cache::forget('settings'); // Clear settings cache

        return redirect()->back()->with('success', 'Navbar settings updated successfully.');
    }
}


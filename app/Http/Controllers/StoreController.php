<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function home()
    {
        $page = \App\Models\CmsPage::where('is_home', true)->where('is_active', true)->first();

        // If no home page, maybe redirect to admin or show a placeholder
        if (!$page) {
            // For now, let's just show a simple view or redirect to admin login if no page exists
            // BUT, we should probably check if any pages exist at all.
            // If not, redirect to admin login.
            if (\App\Models\CmsPage::count() === 0) {
                return redirect()->route('login');
            }
            return abort(404, 'Home page not set.');
        }

        $theme = $this->loadThemeSettings();
        return view('store.page', compact('page', 'theme'));
    }

    public function page($slug)
    {
        $page = \App\Models\CmsPage::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $theme = $this->loadThemeSettings();
        return view('store.page', compact('page', 'theme'));
    }

    private function loadThemeSettings()
    {
        return \App\Models\ThemeSetting::all()->groupBy('group')->mapWithKeys(function ($group, $key) {
            return [$key => $group->pluck('value', 'key')];
        });
    }
}

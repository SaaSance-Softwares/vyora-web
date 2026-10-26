<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;

class PdpSettingsController extends Controller
{
    public function index()
    {
        $settings = ThemeSetting::all()->groupBy('group');
        $allSettings = ThemeSetting::all()->pluck('value', 'key');

        return view('admin.pdp-settings.index', compact('settings', 'allSettings'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token', '_method');

        foreach ($data as $key => $value) {
            ThemeSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => $this->getGroupForKey($key),
                ]
            );
        }

        return redirect()->back()->with('success', 'Widget settings updated successfully.');
    }

    private function getGroupForKey($key)
    {
        if (str_starts_with($key, 'mega_deal_')) {
            return 'mega_deal';
        }

        if (str_starts_with($key, 'milestone_')) {
            return 'milestone';
        }

        return 'pdp';
    }
}

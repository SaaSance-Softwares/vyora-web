<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ThemeSetting;

class PdpSettingsController extends Controller
{
    public function index()
    {
        $settings = ThemeSetting::all()->groupBy('group');
        return view('admin.pdp-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token', '_method');

        foreach ($data as $key => $value) {
            ThemeSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => $this->getGroupForKey($key)
                ]
            );
        }

        return redirect()->back()->with('success', 'PDP settings updated successfully.');
    }

    private function getGroupForKey($key)
    {
        if (str_starts_with($key, 'mega_deal_')) {
            return 'mega_deal';
        }
        
        return 'pdp';
    }
}

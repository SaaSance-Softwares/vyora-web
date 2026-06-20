<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeneralSettingsController extends Controller
{
    const GROUP = 'general';

    const KEYS = [
        'store_name',
        'store_email',
        'support_email',
        'support_phone',
        'whatsapp_number',
        'store_address',
        'default_currency',
        'currency_symbol',
        'time_zone',
        'date_format',
        'weight_unit',
        'length_unit',
        'social_instagram',
        'social_facebook',
        'social_twitter',
        'social_youtube',
        'social_tiktok',
        'social_pinterest',
        'business_name',
        'tax_id',
        'customer_support_hours',
        'store_description',
    ];

    public function index()
    {
        // Load general settings
        $rows = ThemeSetting::where('group', self::GROUP)->get()->keyBy('key');
        $settings = collect(self::KEYS)->mapWithKeys(fn($k) => [$k => $rows->get($k)?->value ?? '']);

        // Load all theme settings (for colors, typography, logos)
        $themeSettings = ThemeSetting::all()->groupBy('group');

        $googleFonts = Cache::remember('google_fonts_list', now()->addDays(7), function () {
            try {
                $response = Http::get('https://gwfh.mranftl.com/api/fonts');
                if ($response->successful()) {
                    return collect($response->json())
                        ->pluck('family')
                        ->sort()
                        ->values()
                        ->toArray();
                }
            } catch (\Exception $e) {
                // Ignore and fall back to defaults
            }
            return ['Inter', 'Roboto', 'Open Sans', 'Montserrat', 'Playfair Display'];
        });

        $timezones = [];
        foreach (\DateTimeZone::listIdentifiers() as $tz) {
            $dt = new \DateTime('now', new \DateTimeZone($tz));
            $offsetSeconds = $dt->getOffset();
            $offsetLabel = $dt->format('P');
            $timezones[] = [
                'id' => $tz,
                'label' => "({$offsetLabel}) {$tz}",
                'offset' => $offsetSeconds
            ];
        }

        usort($timezones, function ($a, $b) {
            return $a['offset'] <=> $b['offset'] ?: strcmp($a['id'], $b['id']);
        });

        $currencies = [
            'INR' => 'Indian Rupee (₹)',
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'AUD' => 'Australian Dollar (A$)',
            'CAD' => 'Canadian Dollar (C$)',
            'JPY' => 'Japanese Yen (¥)',
            'AED' => 'UAE Dirham (د.إ)',
        ];

        return view('admin.general-settings.index', compact('settings', 'themeSettings', 'timezones', 'currencies', 'googleFonts'));
    }

    public function update(Request $request)
    {
        // 1. Save standard general settings (KEYS)
        foreach (self::KEYS as $key) {
            ThemeSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $request->input($key, ''), 'group' => self::GROUP]
            );
        }

        // 2. Save dynamic theme settings (colors, typography)
        $dynamicData = $request->except(array_merge(['_token', '_method', 'logos'], self::KEYS));
        foreach ($dynamicData as $key => $value) {
            ThemeSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => $this->getGroupForKey($key)
                ]
            );
        }

        // 3. Handle File Uploads (Logos)
        if ($request->hasFile('logos')) {
            foreach ($request->file('logos') as $key => $file) {
                $fileName = time() . '_' . $key . '.' . $file->getClientOriginalExtension();
                $relativePath = "storage/theme/logos";
                $backendPath = public_path($relativePath);
                if (!file_exists($backendPath)) {
                    mkdir($backendPath, 0755, true);
                }

                $file->move($backendPath, $fileName);
                $finalPath = "{$relativePath}/{$fileName}";

                ThemeSetting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $finalPath,
                        'group' => 'logos'
                    ]
                );

                if ($key === 'favicon') {
                    Cache::forget('site_favicon');
                }
            }
        }

        return redirect()->back()->with('success', 'General settings saved successfully.');
    }

    private function getGroupForKey($key)
    {
        if (str_starts_with($key, 'mega_deal_'))
            return 'mega_deal';
        if (str_contains($key, 'color'))
            return 'colors';
        if (str_contains($key, 'font'))
            return 'typography';
        if (str_starts_with($key, 'social_'))
            return 'social';
        if (str_starts_with($key, 'contact_'))
            return 'contact';
        if (str_starts_with($key, 'store_'))
            return 'store_info';
        if (str_contains($key, 'layout'))
            return 'layout';
        
        return 'general';
    }
}

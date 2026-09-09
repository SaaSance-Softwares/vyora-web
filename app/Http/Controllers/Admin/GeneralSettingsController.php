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
        'social_whatsapp',
        'social_arattai',
        'social_linkedin',
        'social_custom_links',
        'business_name',
        'tax_id',
        'customer_support_hours',
        'store_description',
    ];

    public function index()
    {
        // Load general settings
        $rows = ThemeSetting::where('group', self::GROUP)->get()->keyBy('key');
        $settings = collect(self::KEYS)->mapWithKeys(fn ($k) => [$k => $rows->get($k)?->value ?? '']);

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
                'offset' => $offsetSeconds,
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

        // Sync tax_id to tax_shipping's store_tax_number
        if ($request->has('tax_id')) {
            ThemeSetting::updateOrCreate(
                ['key' => 'store_tax_number'],
                ['value' => $request->input('tax_id', ''), 'group' => 'tax_shipping']
            );
        }

        // 2. Save dynamic theme settings (colors, typography)
        $dynamicData = $request->except(array_merge(['_token', '_method', 'logos'], self::KEYS));
        foreach ($dynamicData as $key => $value) {
            ThemeSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => $this->getGroupForKey($key),
                ]
            );
        }

        // 3. Handle File Uploads (Logos)
        if ($request->hasFile('logos')) {
            foreach ($request->file('logos') as $key => $file) {
                $fileName = time().'_'.$key.'.'.$file->getClientOriginalExtension();
                $relativePath = 'storage/theme/logos';
                $backendPath = public_path($relativePath);
                if (! file_exists($backendPath)) {
                    mkdir($backendPath, 0755, true);
                }

                $file->move($backendPath, $fileName);
                $finalPath = "{$relativePath}/{$fileName}";

                ThemeSetting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $finalPath,
                        'group' => 'logos',
                    ]
                );

                if ($key === 'favicon') {
                    Cache::forget('site_favicon');
                    
                    // Generate PWA icons from the new favicon
                    try {
                        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                        $img = $manager->read($finalPath);
                        
                        // Scale down to 70% (134x134) and place on a 192x192 transparent canvas
                        $img192 = clone $img;
                        $img192->scaleDown(134, 134);
                        $canvas192 = $manager->create(192, 192)->fill('rgba(255,255,255,0)');
                        $canvas192->place($img192, 'center');
                        $canvas192->toPng()->save(public_path('pwa-icon-192.png'));
                        
                        // --- GOOGLE SEO FAVICONS ---
                        // 1. Generate 192x192 exact square for /favicon.png
                        $faviconPng = clone $img;
                        $faviconPng->cover(192, 192); // Crop to perfect square
                        $faviconPng->toPng()->save(public_path('favicon.png'));
                        
                        // 2. Generate smaller fallback for /favicon.ico (browsers accept PNG data named .ico)
                        $faviconIco = clone $img;
                        $faviconIco->cover(48, 48); // Google prefers 48x48 multiple for classic
                        $faviconIco->toPng()->save(public_path('favicon.ico'));
                        
                        // Scale down to 70% (358x358) and place on a 512x512 transparent canvas
                        $img512 = clone $img;
                        $img512->scaleDown(358, 358);
                        $canvas512 = $manager->create(512, 512)->fill('rgba(255,255,255,0)');
                        $canvas512->place($img512, 'center');
                        $canvas512->toPng()->save(public_path('pwa-icon-512.png'));
                        
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Failed to generate PWA icons: ' . $e->getMessage());
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'General settings saved successfully.');
    }

    private function getGroupForKey($key)
    {
        if (str_starts_with($key, 'mega_deal_')) {
            return 'mega_deal';
        }
        if (str_contains($key, 'color')) {
            return 'colors';
        }
        if (str_contains($key, 'font')) {
            return 'typography';
        }
        if (str_starts_with($key, 'social_')) {
            return 'social';
        }
        if (str_starts_with($key, 'contact_')) {
            return 'contact';
        }
        if (str_starts_with($key, 'store_')) {
            return 'store_info';
        }
        if (str_contains($key, 'layout')) {
            return 'layout';
        }

        return 'general';
    }
}

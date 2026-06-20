<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $settings = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('theme_settings')) {
                $all = \App\Models\ThemeSetting::all();
                $integrationSettings = $all->filter(fn($s) => str_starts_with($s->group, 'integration.'))
                    ->groupBy('group')
                    ->map(function ($groupSettings) {
                        return $groupSettings->keyBy('key')->map(function ($s) {
                            if (in_array($s->key, ['key_id', 'key_secret', 'smtp_password', 'access_token'])) {
                                return null;
                            }
                            return $s->value;
                        })->filter()->toArray();
                    });

                $allData = $all->filter(fn($s) => !str_starts_with($s->group, 'integration.'))
                    ->pluck('value', 'key')->toArray();

                if (isset($allData['taxes'])) {
                    $allData['taxes'] = json_decode($allData['taxes'], true);
                }
                if (isset($allData['shipping_rules'])) {
                    $allData['shipping_rules'] = json_decode($allData['shipping_rules'], true);
                }

                $policyKeys = ['cod_charges', 'prepaid_charges', 'delivery_timeline', 'return_policy', 'exchange_policy', 'refund_method', 'extra_sections', 'mega_deal_label', 'mega_deal_icon', 'mega_deal_badge', 'mega_deal_bg_from', 'mega_deal_bg_to', 'mega_deal_text_color', 'mega_deal_subtext_color'];
                $generalKeys = ['store_name', 'store_email', 'support_phone', 'store_address', 'default_currency', 'currency_symbol', 'time_zone', 'date_format', 'weight_unit', 'length_unit'];

                $settings = array_merge($allData, [
                    'integrations' => $integrationSettings,
                    'policies'     => $all->whereIn('key', $policyKeys)->pluck('value', 'key'),
                    'general'      => $all->whereIn('key', $generalKeys)->pluck('value', 'key')
                ]);
            }
        } catch (\Exception $e) {
            // Ignore DB errors during installation
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'settings' => $settings,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}

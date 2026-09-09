<?php

namespace App\Http\Middleware;

use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
     * Prevent browser from aggressively caching Inertia JSON responses
     * which causes raw JSON to be displayed on back/forward navigation or session restore.
     */
    public function handle(Request $request, \Closure $next)
    {
        $response = parent::handle($request, $next);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

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
            if (Schema::hasTable('theme_settings')) {
                $all = ThemeSetting::all();
                $integrationSettings = $all->filter(fn ($s) => str_starts_with($s->group, 'integration.'))
                    ->groupBy('group')
                    ->map(function ($groupSettings) {
                        return $groupSettings->keyBy('key')->map(function ($s) {
                            if (in_array($s->key, ['key_id', 'key_secret', 'smtp_password', 'access_token'])) {
                                return null;
                            }

                            return $s->value;
                        })->filter()->toArray();
                    });

                $allData = $all->filter(fn ($s) => ! str_starts_with($s->group, 'integration.'))
                    ->pluck('value', 'key')->toArray();

                if (isset($allData['taxes'])) {
                    $allData['taxes'] = json_decode($allData['taxes'], true);
                }
                if (isset($allData['shipping_rules'])) {
                    $allData['shipping_rules'] = json_decode($allData['shipping_rules'], true);
                }
                if (isset($allData['footer_structure'])) {
                    $allData['footer_structure'] = json_decode($allData['footer_structure'], true) ?? [];
                }

                $policyKeys = ['cod_charges', 'prepaid_charges', 'delivery_timeline', 'return_policy', 'exchange_policy', 'refund_method', 'extra_sections', 'mega_deal_label', 'mega_deal_icon', 'mega_deal_badge', 'mega_deal_bg_from', 'mega_deal_bg_to', 'mega_deal_text_color', 'mega_deal_subtext_color'];
                $generalKeys = ['store_name', 'store_email', 'support_phone', 'store_address', 'default_currency', 'currency_symbol', 'time_zone', 'date_format', 'weight_unit', 'length_unit', 'social_instagram', 'social_facebook', 'social_twitter', 'social_youtube', 'social_tiktok', 'social_pinterest', 'social_whatsapp', 'social_arattai', 'social_linkedin', 'social_custom_links'];

                $settings = array_merge($allData, [
                    'integrations' => $integrationSettings,
                    'policies' => $all->whereIn('key', $policyKeys)->pluck('value', 'key'),
                    'general' => $all->whereIn('key', $generalKeys)->pluck('value', 'key')->toArray(),
                ]);

                if (isset($settings['general']['social_custom_links'])) {
                    $settings['general']['social_custom_links'] = json_decode($settings['general']['social_custom_links'], true) ?? [];
                }

                $socialProviders = [];
                if (isset($settings['social_login_enabled']) && $settings['social_login_enabled'] === '1') {
                    $providers = ['google', 'facebook', 'apple', 'github', 'snapchat'];
                    foreach ($providers as $provider) {
                        if ($all->where('group', "integration.{$provider}-login")->where('key', 'enabled')->first()?->value === '1') {
                            $socialProviders[] = $provider;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore DB errors during installation
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? array_merge($request->user()->toArray(), [
                    'default_pincode' => $request->user()->addresses()->where('is_default', true)->value('zip_code')
                                      ?? $request->user()->addresses()->latest()->value('zip_code'),
                ]) : null,
                'social_providers' => $socialProviders ?? [],
            ],
            'app_url' => url('/'),
            'settings' => $settings,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}

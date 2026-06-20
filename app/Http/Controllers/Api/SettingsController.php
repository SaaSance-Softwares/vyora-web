<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;

class SettingsController extends Controller
{
    public function index()
    {
        $all = ThemeSetting::all();

        // 1. Separate integration settings from general ones
        $integrationSettings = $all->filter(fn ($s) => str_starts_with($s->group, 'integration.'))
            ->groupBy('group')
            ->map(function ($groupSettings) {
                return $groupSettings->keyBy('key')->map(function ($s) {
                    // Exclude sensitive keys
                    if (in_array($s->key, ['key_id', 'key_secret', 'smtp_password', 'access_token'])) {
                        return null;
                    }

                    return $s->value;
                })->filter()->toArray();
            });

        // 2. Map standard settings (excluding integrations handled above)
        $allData = $all->filter(fn ($s) => ! str_starts_with($s->group, 'integration.'))
            ->pluck('value', 'key')->toArray();

        if (isset($allData['taxes'])) {
            $allData['taxes'] = json_decode($allData['taxes'], true);
        }
        if (isset($allData['shipping_rules'])) {
            $allData['shipping_rules'] = json_decode($allData['shipping_rules'], true);
        }

        // Convert image paths to full URLs
        $imageKeys = ['favicon', 'logo'];
        foreach ($imageKeys as $key) {
            if (isset($allData[$key]) && ! str_starts_with($allData[$key], 'http')) {
                $allData[$key] = asset(ltrim($allData[$key], '/'));
            }
        }

        // Policy & General keys for backward compatibility/specific UI needs
        $policyKeys = [
            'cod_charges', 'prepaid_charges', 'delivery_timeline',
            'return_policy', 'exchange_policy', 'refund_method',
            'extra_sections',
            'mega_deal_label', 'mega_deal_icon', 'mega_deal_badge',
            'mega_deal_bg_from', 'mega_deal_bg_to', 'mega_deal_text_color',
            'mega_deal_subtext_color',
        ];
        $generalKeys = [
            'store_name', 'store_email', 'support_phone', 'store_address',
            'default_currency', 'currency_symbol', 'time_zone', 'date_format',
            'weight_unit', 'length_unit',
        ];

        $policies = $all->whereIn('key', $policyKeys)->pluck('value', 'key');
        $general = $all->whereIn('key', $generalKeys)->pluck('value', 'key');

        return response()->json(array_merge(
            $allData,
            [
                'integrations' => $integrationSettings,
                'policies' => $policies,
                'general' => $general,
            ]
        ));
    }
}

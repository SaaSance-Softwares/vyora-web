<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TaxShippingSettingsController extends Controller
{
    const GROUP = 'tax_shipping';

    public function index()
    {
        $rows = \App\Models\ThemeSetting::where('group', self::GROUP)->get()->keyBy('key');

        $settings = [
            'is_tax_enabled' => $rows->get('is_tax_enabled')?->value ?? '1',
            'tax_label' => $rows->get('tax_label')?->value ?? 'Tax',
            'store_tax_number' => $rows->get('store_tax_number')?->value ?? '',
            'tax_inclusion' => $rows->get('tax_inclusion')?->value ?? 'exclude',
            'taxes' => json_decode($rows->get('taxes')?->value ?? '[{"id":"t1","name":"GST 5%","rate":5},{"id":"t2","name":"GST 18%","rate":18}]', true),
            'shipping_rules' => json_decode($rows->get('shipping_rules')?->value ?? '{"prepaid":{"type":"free","threshold":0,"fee":0,"notes":""},"cod":{"type":"flat","threshold":0,"fee":0,"notes":""}}', true),
            'show_tax_in_cart_checkout' => $rows->get('show_tax_in_cart_checkout')?->value ?? '1',
            'shipping_tax_rate' => $rows->get('shipping_tax_rate')?->value ?? '18',
        ];

        return view('admin.tax-shipping.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $keys = ['is_tax_enabled', 'tax_label', 'store_tax_number', 'tax_inclusion', 'taxes', 'shipping_rules', 'show_tax_in_cart_checkout', 'shipping_tax_rate'];
        foreach ($keys as $key) {
            if ($request->has($key)) {
                $val = $request->input($key);
                if (is_array($val)) {
                    if ($key === 'taxes') {
                        $val = array_values($val);
                    }
                    if ($key === 'shipping_rules') {
                        foreach (['prepaid', 'cod'] as $method) {
                            if (isset($val[$method]['tiers']) && is_array($val[$method]['tiers'])) {
                                $tiers = array_values($val[$method]['tiers']);
                                // Sort by up_to ascending
                                usort($tiers, function ($a, $b) {
                                    return (float)($a['up_to'] ?? 0) <=> (float)($b['up_to'] ?? 0);
                                });
                                $val[$method]['tiers'] = $tiers;
                            }
                        }
                    }
                    $val = json_encode($val);
                }
                \App\Models\ThemeSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $val, 'group' => self::GROUP]
                );
            }
        }

        return redirect()->back()->with('success', 'Tax and Shipping settings updated successfully.');
    }
}

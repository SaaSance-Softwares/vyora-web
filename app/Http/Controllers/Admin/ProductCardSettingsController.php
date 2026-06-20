<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;

class ProductCardSettingsController extends Controller
{
    /**
     * Display the product card settings interface.
     */
    public function index()
    {
        $keys = [
            'pc_style',
            'pc_bg_color',
            'pc_text_color',
            'pc_border_radius',
            'pc_shadow',
            'pc_image_aspect',
            'pc_buynow_style',
            'pc_buynow_bg_color',
            'pc_buynow_text_color',
            'pc_cart_style',
            'pc_cart_bg_color',
            'pc_cart_text_color',
            'pc_wishlist_style',
            'pc_wishlist_bg_color',
            'pc_wishlist_text_color',
        ];

        // Fetch all existing product card settings
        $settingsRaw = ThemeSetting::whereIn('key', $keys)->get();
        $settings = $settingsRaw->pluck('value', 'key')->toArray();

        // Ensure defaults are populated if missing
        $defaults = [
            'pc_style'               => 'lift',
            'pc_bg_color'            => '#ffffff',
            'pc_text_color'          => '#000000',
            'pc_border_radius'       => 'rounded',
            'pc_shadow'              => 'soft',
            'pc_image_aspect'        => 'aspect-[4/5]',
            'pc_buynow_style'        => 'text_only',
            'pc_buynow_bg_color'     => '#000000',
            'pc_buynow_text_color'   => '#ffffff',
            'pc_cart_style'          => 'hidden',
            'pc_cart_bg_color'       => '#f3f4f6',
            'pc_cart_text_color'     => '#1f2937',
            'pc_wishlist_style'      => 'icon_only',
            'pc_wishlist_bg_color'   => '#ffffff',
            'pc_wishlist_text_color' => '#9ca3af',
        ];

        $settings = array_merge($defaults, $settings);

        return view('admin.product-card-settings.index', compact('settings'));
    }

    /**
     * Update the product card settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'pc_style'               => 'nullable|string',
            'pc_bg_color'            => 'nullable|string',
            'pc_text_color'          => 'nullable|string',
            'pc_border_radius'       => 'nullable|string',
            'pc_shadow'              => 'nullable|string',
            'pc_image_aspect'        => 'nullable|string',
            'pc_buynow_style'        => 'nullable|string',
            'pc_buynow_bg_color'     => 'nullable|string',
            'pc_buynow_text_color'   => 'nullable|string',
            'pc_cart_style'          => 'nullable|string',
            'pc_cart_bg_color'       => 'nullable|string',
            'pc_cart_text_color'     => 'nullable|string',
            'pc_wishlist_style'      => 'nullable|string',
            'pc_wishlist_bg_color'   => 'nullable|string',
            'pc_wishlist_text_color' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            ThemeSetting::updateOrCreate(
                ['group' => 'product_card', 'key' => $key],
                ['value' => $value ?? '']
            );
        }

        return redirect()->back()->with('success', 'Product Card settings updated successfully!');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;

class CartDrawerSettingsController extends Controller
{
    /**
     * Display the Cart Offcanvas / Drawer settings interface.
     */
    public function index()
    {
        $keys = [
            'cart_drawer_enabled',
            'cart_drawer_title',
            'cart_drawer_show_milestones',
            'cart_drawer_show_recommendations',
            'cart_drawer_recommendations_title',
            'cart_drawer_recommendation_rule',
            'cart_drawer_category_id',
            'cart_drawer_collection_id',
            'cart_drawer_fixed_product_ids',
            'cart_drawer_recommendations_limit',
        ];

        $settingsRaw = ThemeSetting::whereIn('key', $keys)->get();
        $settings = $settingsRaw->pluck('value', 'key')->toArray();

        $defaults = [
            'cart_drawer_enabled' => '1',
            'cart_drawer_title' => 'Your Bag',
            'cart_drawer_show_milestones' => '1',
            'cart_drawer_show_recommendations' => '1',
            'cart_drawer_recommendations_title' => 'Pairs Well With',
            'cart_drawer_recommendation_rule' => 'near_milestone',
            'cart_drawer_category_id' => '',
            'cart_drawer_collection_id' => '',
            'cart_drawer_fixed_product_ids' => '[]',
            'cart_drawer_recommendations_limit' => '4',
        ];

        $settings = array_merge($defaults, $settings);

        // Parse fixed product IDs
        $fixedProductIds = [];
        if (!empty($settings['cart_drawer_fixed_product_ids'])) {
            $decoded = json_decode($settings['cart_drawer_fixed_product_ids'], true);
            if (is_array($decoded)) {
                $fixedProductIds = array_map('intval', $decoded);
            } elseif (is_string($settings['cart_drawer_fixed_product_ids'])) {
                $fixedProductIds = array_filter(array_map('intval', explode(',', $settings['cart_drawer_fixed_product_ids'])));
            }
        }

        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $collections = Collection::select('id', 'name')->orderBy('name')->get();
        $products = Product::select('id', 'name', 'preview_image')->with('skus:id,product_id,price,mrp')->orderBy('name')->get();

        return view('admin.cart-drawer.index', compact('settings', 'categories', 'collections', 'products', 'fixedProductIds'));
    }

    /**
     * Update the Cart Offcanvas / Drawer settings.
     */
    public function update(Request $request)
    {
        $validated = $request->strictValidate([
            'cart_drawer_enabled' => 'nullable|string|in:0,1',
            'cart_drawer_title' => 'nullable|string|max:255',
            'cart_drawer_show_milestones' => 'nullable|string|in:0,1',
            'cart_drawer_show_recommendations' => 'nullable|string|in:0,1',
            'cart_drawer_recommendations_title' => 'nullable|string|max:255',
            'cart_drawer_recommendation_rule' => 'required|string|in:top_sold,near_milestone,category,collection,fixed',
            'cart_drawer_category_id' => 'nullable|integer',
            'cart_drawer_collection_id' => 'nullable|integer',
            'cart_drawer_fixed_product_ids' => 'nullable',
            'cart_drawer_recommendations_limit' => 'nullable|integer|min:1|max:10',
        ]);

        // Process checkboxes that might not be submitted if unchecked
        $validated['cart_drawer_enabled'] = $request->has('cart_drawer_enabled') ? '1' : '0';
        $validated['cart_drawer_show_milestones'] = $request->has('cart_drawer_show_milestones') ? '1' : '0';
        $validated['cart_drawer_show_recommendations'] = $request->has('cart_drawer_show_recommendations') ? '1' : '0';

        // Process fixed product IDs (ensure it's saved as JSON array string)
        if ($request->has('fixed_products') && is_array($request->input('fixed_products'))) {
            $validated['cart_drawer_fixed_product_ids'] = json_encode(array_values(array_map('intval', $request->input('fixed_products'))));
        } elseif (isset($validated['cart_drawer_fixed_product_ids']) && is_array($validated['cart_drawer_fixed_product_ids'])) {
            $validated['cart_drawer_fixed_product_ids'] = json_encode(array_values(array_map('intval', $validated['cart_drawer_fixed_product_ids'])));
        }

        foreach ($validated as $key => $value) {
            ThemeSetting::updateOrCreate(
                ['group' => 'cart_drawer', 'key' => $key],
                ['value' => $value ?? '']
            );
        }

        return redirect()->back()->with('success', 'Cart Offcanvas settings updated successfully!');
    }
}

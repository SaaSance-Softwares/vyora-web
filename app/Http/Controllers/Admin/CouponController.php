<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(15);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'name' => 'nullable|string',
            'type' => 'required|in:percentage,fixed,free_shipping,bogo',
            'bogo_buy_qty' => 'nullable|integer|min:1',
            'bogo_get_qty' => 'nullable|integer|min:1',
            'bogo_max_discount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_default_magic' => 'boolean',
            'show_on_product_page' => 'boolean',
            'show_on_checkout_page' => 'boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'min_cart_value' => 'nullable|numeric|min:0',
            'min_item_quantity' => 'nullable|integer|min:1',
            'exclude_sale_items' => 'boolean',
            'first_time_users_only' => 'boolean',
            'can_combine' => 'boolean',
            'applicable_product_ids' => 'nullable|string',
            'applicable_category_ids' => 'nullable|string',
            'excluded_product_ids' => 'nullable|string',
        ]);

        // Fix boolean values for checkboxes
        $validated['is_active'] = $request->has('is_active');
        $validated['is_default_magic'] = $request->has('is_default_magic');
        $validated['show_on_product_page'] = $request->has('show_on_product_page');
        $validated['show_on_checkout_page'] = $request->has('show_on_checkout_page');
        $validated['exclude_sale_items'] = $request->has('exclude_sale_items');
        $validated['first_time_users_only'] = $request->has('first_time_users_only');
        $validated['can_combine'] = $request->has('can_combine');

        // Convert CSV strings to JSON arrays
        foreach (['applicable_product_ids', 'applicable_category_ids', 'excluded_product_ids'] as $field) {
            if (! empty($validated[$field])) {
                $validated[$field] = array_map('trim', explode(',', $validated[$field]));
            } else {
                $validated[$field] = null;
            }
        }

        Coupon::create($validated);

        return redirect()->route('admin.online-store.coupons.index')->with('success', 'Coupon campaign launched successfully!');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code,'.$coupon->id,
            'name' => 'nullable|string',
            'type' => 'required|in:percentage,fixed,free_shipping,bogo',
            'bogo_buy_qty' => 'nullable|integer|min:1',
            'bogo_get_qty' => 'nullable|integer|min:1',
            'bogo_max_discount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_default_magic' => 'boolean',
            'show_on_product_page' => 'boolean',
            'show_on_checkout_page' => 'boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'min_cart_value' => 'nullable|numeric|min:0',
            'min_item_quantity' => 'nullable|integer|min:1',
            'exclude_sale_items' => 'boolean',
            'first_time_users_only' => 'boolean',
            'can_combine' => 'boolean',
            'applicable_product_ids' => 'nullable|string',
            'applicable_category_ids' => 'nullable|string',
            'excluded_product_ids' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_default_magic'] = $request->has('is_default_magic');
        $validated['show_on_product_page'] = $request->has('show_on_product_page');
        $validated['show_on_checkout_page'] = $request->has('show_on_checkout_page');
        $validated['exclude_sale_items'] = $request->has('exclude_sale_items');
        $validated['first_time_users_only'] = $request->has('first_time_users_only');
        $validated['can_combine'] = $request->has('can_combine');

        foreach (['applicable_product_ids', 'applicable_category_ids', 'excluded_product_ids'] as $field) {
            if (! empty($validated[$field])) {
                $itemValue = $request->input($field);
                $validated[$field] = array_map('trim', explode(',', $itemValue));
            } else {
                $validated[$field] = null;
            }
        }

        $coupon->update($validated);

        return redirect()->route('admin.online-store.coupons.index')->with('success', 'Coupon configuration updated!');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.online-store.coupons.index')->with('success', 'Coupon archived and removed.');
    }
}

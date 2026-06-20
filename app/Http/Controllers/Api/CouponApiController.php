<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponApiController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    /**
     * Fetch active magic coupons and public coupons for display.
     */
    public function getActivePublicCoupons()
    {
        $now = now();
        $coupons = Coupon::where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->get();

        $magicCoupons = $coupons->where('is_default_magic', true)->values();
        $productPageCoupons = $coupons->where('show_on_product_page', true)->values();
        $checkoutCoupons = $coupons->where('show_on_checkout_page', true)->values();

        return response()->json([
            'magic_coupons' => $magicCoupons,
            'product_coupons' => $productPageCoupons,
            'checkout_coupons' => $checkoutCoupons,
        ]);
    }

    /**
     * Apply a specific coupon code against a cart.
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'cart' => 'required|array',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (! $coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code.',
            ], 404);
        }

        $result = $this->couponService->validateAndCalculate($coupon, $request->cart, $request->user('sanctum'));

        if (! $result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully.',
            'data' => $result,
        ]);
    }
}

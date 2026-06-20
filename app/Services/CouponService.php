<?php

namespace App\Services;

use App\Models\Coupon;
use Carbon\Carbon;

class CouponService
{
    /**
     * Validate a coupon against cart structure constraints.
     */
    public function validateAndCalculate(Coupon $coupon, array $cartData, $user = null): array
    {
        // 1. ACTIVE & BASIC TIME CHECKS
        if (!$coupon->is_active) {
            return ['valid' => false, 'error' => 'Coupon is not active.'];
        }
        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return ['valid' => false, 'error' => 'Coupon is not yet active.'];
        }
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return ['valid' => false, 'error' => 'Coupon has expired.'];
        }

        // 2. USAGE LIMITS
        if ($coupon->usage_limit && $coupon->times_used >= $coupon->usage_limit) {
            return ['valid' => false, 'error' => 'Coupon usage limit has been reached.'];
        }

        // TODO: usage_limit_per_user (Requires Order tracking by user ID or email)
        
        // 3. CART CONSTRAINTS
        $cartSubtotal = $cartData['subtotal'] ?? 0;
        $cartItems = $cartData['items'] ?? [];
        $totalQuantity = array_sum(array_column($cartItems, 'quantity'));

        if ($coupon->min_cart_value && $cartSubtotal < $coupon->min_cart_value) {
            return ['valid' => false, 'error' => 'Minimum cart value of ₹' . $coupon->min_cart_value . ' not met.'];
        }
        if ($coupon->min_item_quantity && $totalQuantity < $coupon->min_item_quantity) {
            return ['valid' => false, 'error' => 'Minimum order quantity of ' . $coupon->min_item_quantity . ' not met.'];
        }

        // 4. ADVANCED RESTRAINTS
        if ($coupon->first_time_users_only) {
            if (!$user || $user->orders()->count() > 0) {
                return ['valid' => false, 'error' => 'This coupon is for first-time buyers only.'];
            }
        }

        // 5. CALCULATE ELIGIBLE TOTAL
        $eligibleTotal = 0;
        $applicableProductIds = $coupon->applicable_product_ids ?: [];
        $applicableCategoryIds = $coupon->applicable_category_ids ?: [];
        $excludedProductIds = $coupon->excluded_product_ids ?: [];

        foreach ($cartItems as $item) {
            // Check Exclusions First
            if (in_array((string)$item['product_id'], $excludedProductIds)) continue;
            
            // Check Sale Exclusion
            $isOnSale = $item['original_price'] > $item['price'];
            if ($coupon->exclude_sale_items && $isOnSale) continue;

            // Check Inclusion (if applicable sets exist, item must match one)
            $isEligible = true;
            if (!empty($applicableProductIds) || !empty($applicableCategoryIds)) {
                $isEligible = false;
                if (in_array((string)$item['product_id'], $applicableProductIds)) {
                    $isEligible = true;
                }
                // (Assumes category is passed in cart or fetched)
                if (isset($item['category_id']) && in_array((string)$item['category_id'], $applicableCategoryIds)) {
                    $isEligible = true;
                }
            }

            if ($isEligible) {
                $eligibleTotal += ($item['price'] * $item['quantity']);
            }
        }

        if ($eligibleTotal <= 0 && $coupon->type !== 'free_shipping') {
            return ['valid' => false, 'error' => 'No eligible items in cart for this coupon.'];
        }

        // 6. CALCULATE FINAL DISCOUNT
        $discountAmount = 0;
        if ($coupon->type === 'percentage') {
            $discountAmount = ($eligibleTotal * $coupon->discount_amount) / 100;
            // Apply "upto" cap if set (e.g. 50% OFF upto ₹80)
            if ($coupon->max_discount_amount && $discountAmount > $coupon->max_discount_amount) {
                $discountAmount = $coupon->max_discount_amount;
            }
        } elseif ($coupon->type === 'fixed') {
            $discountAmount = min($coupon->discount_amount, $eligibleTotal); // Cap at cart subtotal
        } elseif ($coupon->type === 'free_shipping') {
            $discountAmount = $cartData['shipping_cost'] ?? 0;
        } elseif ($coupon->type === 'bogo') {
            // "Buy X Get Y Free" logic
            $buyQty = $coupon->bogo_buy_qty ?? 1;
            $getQty = $coupon->bogo_get_qty ?? 1;
            
            // Flatten all eligible items into individual units to easily extract the cheapest Y items for every X+Y group
            $eligibleUnits = [];
            foreach ($cartItems as $item) {
                // We re-check basic eligibility for BOGO items
                if (in_array((string)$item['product_id'], $excludedProductIds)) continue;
                if ($coupon->exclude_sale_items && $item['original_price'] > $item['price']) continue;
                
                $isEligible = true;
                if (!empty($applicableProductIds) || !empty($applicableCategoryIds)) {
                    $isEligible = false;
                    if (in_array((string)$item['product_id'], $applicableProductIds)) $isEligible = true;
                    if (isset($item['category_id']) && in_array((string)$item['category_id'], $applicableCategoryIds)) $isEligible = true;
                }

                if ($isEligible) {
                    for ($i = 0; $i < $item['quantity']; $i++) {
                        $eligibleUnits[] = $item['price'];
                    }
                }
            }

            // Sort unit prices descending (highest price first)
            rsort($eligibleUnits);

            $groupSize = $buyQty + $getQty;
            $totalGroups = floor(count($eligibleUnits) / $groupSize);

            // Tally the discount amount for every 'getQty' item within each valid group (the cheapest items in the group)
            for ($g = 0; $g < $totalGroups; $g++) {
                // The free items are at the tail end of the group slice
                $startIndex = ($g * $groupSize) + $buyQty;
                for ($i = 0; $i < $getQty; $i++) {
                    $freeItemPrice = $eligibleUnits[$startIndex + $i];
                    
                    // Cap the free item value if a bogo_max_discount is set
                    if ($coupon->bogo_max_discount && $freeItemPrice > $coupon->bogo_max_discount) {
                        $discountAmount += $coupon->bogo_max_discount;
                    } else {
                        $discountAmount += $freeItemPrice;
                    }
                }
            }
        }

        return [
            'valid' => true,
            'discount_amount' => $discountAmount,
            'coupon' => $coupon->only(['code', 'name', 'type', 'discount_amount', 'can_combine'])
        ];
    }
}

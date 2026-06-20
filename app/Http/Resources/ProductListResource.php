<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Calculate price range
        // Since a product has many SKUs, we show min_price or a range.
        $minPrice = $this->skus->min('price');
        $maxPrice = $this->skus->max('price');
        $mrp = $this->skus->max('mrp'); // Usually we show the highest MRP strike-through? Or lowest. Let's start simple.

        // Get hover image (from the color gallery)
        $hoverImage = $this->images->where('is_primary', true)->first() ?? $this->images->first();

        $imageUrl = $this->image_url;

        if ($request->filled('category') && $this->relationLoaded('categoryMasterImages') && $this->relationLoaded('categories')) {
            $requestedSlugs = explode(',', $request->category);
            $matchedCategories = $this->categories->whereIn('slug', $requestedSlugs);
            
            foreach ($matchedCategories as $cat) {
                $catImage = null;
                $current = $cat;
                
                // Walk up the category tree to find an image
                while ($current && !$catImage) {
                    $catImage = $this->categoryMasterImages->where('category_id', $current->id)->first();
                    $current = $current->parent;
                }
                
                if ($catImage && $catImage->image_path) {
                    $imageUrl = $catImage->image_url;
                    break;
                }
            }
        }

        // Magic Coupon Logic
        static $magicCoupon = null;
        static $magicCouponFetched = false;
        
        if (!$magicCouponFetched) {
            $magicCoupon = \App\Models\Coupon::where('is_active', true)
                ->where('is_default_magic', true)
                ->where(function ($q) {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->first();
            $magicCouponFetched = true;
        }

        $couponPrice = null;
        if ($magicCoupon && $minPrice > 0) {
            $isEligible = true;
            
            // Exclude sale items check
            if ($magicCoupon->exclude_sale_items && $mrp > $minPrice) {
                $isEligible = false;
            }

            // Min cart value for the single item
            if ($isEligible && $magicCoupon->min_cart_value > 0 && $minPrice < $magicCoupon->min_cart_value) {
                $isEligible = false;
            }

            // Product/Category inclusions and exclusions
            if ($isEligible) {
                if (!empty($magicCoupon->applicable_product_ids) && !in_array($this->id, $magicCoupon->applicable_product_ids)) {
                    $isEligible = false;
                }
                if ($isEligible && !empty($magicCoupon->excluded_product_ids) && in_array($this->id, $magicCoupon->excluded_product_ids)) {
                    $isEligible = false;
                }
                if ($isEligible && !empty($magicCoupon->applicable_category_ids)) {
                    $productCategoryIds = $this->categories->pluck('id')->toArray();
                    if (empty(array_intersect($productCategoryIds, $magicCoupon->applicable_category_ids))) {
                        $isEligible = false;
                    }
                }
            }

            if ($isEligible) {
                $discount = 0;
                if ($magicCoupon->type === 'percentage') {
                    $discount = $minPrice * ($magicCoupon->discount_amount / 100);
                    if ($magicCoupon->max_discount_amount > 0 && $discount > $magicCoupon->max_discount_amount) {
                        $discount = $magicCoupon->max_discount_amount;
                    }
                } elseif ($magicCoupon->type === 'fixed') {
                    $discount = $magicCoupon->discount_amount;
                }

                if ($discount > 0) {
                    $couponPrice = max(0, $minPrice - $discount);
                }
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'brand' => $this->brand_name,
            'price' => (float) $minPrice,
            'price_formatted' => '₹' . number_format($minPrice),
            'mrp' => (float) $mrp,
            'coupon_price' => $couponPrice ? (float) $couponPrice : null,
            // Calculate Discount %
            'discount_percentage' => ($mrp > $minPrice) ? round((($mrp - $minPrice) / $mrp) * 100) : 0,
            'image' => $imageUrl,
            'hover_image' => $hoverImage ? $hoverImage->url : null,
            'category' => $this->categories->first()?->name ?? 'General',
            'is_new' => $this->created_at->diffInDays(now()) < 7,
        ];
    }
}

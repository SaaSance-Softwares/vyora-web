<?php

namespace App\Http\Resources;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $minPrice = $this->skus->min('price') ?? 0;
        $mrp = $this->skus->max('mrp') ?? 0;

        // Fetch Magic Coupon once per request
        static $magicCoupon = null;
        static $magicCouponFetched = false;

        if (! $magicCouponFetched) {
            $magicCoupon = Coupon::where('is_active', true)
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
                if (! empty($magicCoupon->applicable_product_ids) && ! in_array($this->id, $magicCoupon->applicable_product_ids)) {
                    $isEligible = false;
                }
                if ($isEligible && ! empty($magicCoupon->excluded_product_ids) && in_array($this->id, $magicCoupon->excluded_product_ids)) {
                    $isEligible = false;
                }
                if ($isEligible && ! empty($magicCoupon->applicable_category_ids)) {
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

        $imageUrl = $this->image_url;
        // The product model lacks video_url accessor, we must construct it if video is just a filename
        $videoUrl = null;
        if ($this->video_url) {
            $path = $this->video_url;
            if (str_starts_with($path, 'http')) {
                $videoUrl = $path;
            } else {
                $cleanPath = ltrim($path, '/');
                if (str_starts_with($cleanPath, 'storage/') || str_starts_with($cleanPath, 'uploads/')) {
                    $videoUrl = asset($cleanPath);
                } else {
                    $videoUrl = asset('storage/'.$cleanPath);
                }
            }
        }

        if ($request->filled('category')) {
            $this->loadMissing('categoryMasterImages');
            $requestedSlugs = explode(',', $request->category);
            $matchedCategories = $this->categories->whereIn('slug', $requestedSlugs);

            foreach ($matchedCategories as $cat) {
                $catImage = null;
                $current = $cat;

                // Walk up the category tree to find an image/video
                while ($current && ! $catImage) {
                    $catImage = $this->categoryMasterImages->where('category_id', $current->id)->first();
                    $current = $current->parent;
                }

                if ($catImage) {
                    if ($catImage->image_path) {
                        $imageUrl = $catImage->image_url;
                    }
                    if ($catImage->video_path) {
                        $videoUrl = $catImage->video_url;
                    }
                    if ($catImage->image_path || $catImage->video_path) {
                        break;
                    }
                }
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'brand' => $this->brand_name,
            'product_type' => $this->productType?->name,
            'tax_class' => $this->tax_class,

            // Global List Properties mapping securely
            'price' => (float) $minPrice,
            'price_formatted' => '₹'.number_format($minPrice),
            'mrp' => (float) $mrp,
            'coupon_price' => $couponPrice ? (float) $couponPrice : null,
            'discount_percentage' => ($mrp > $minPrice) ? round((($mrp - $minPrice) / $mrp) * 100) : 0,
            'image' => $imageUrl,
            'video' => $videoUrl,
            'category' => $this->categories->first()?->name ?? 'General',
            'is_new' => $this->created_at->diffInDays(now()) < 7,
            'view_count' => $this->view_count,

            // Categories
            'categories' => $this->categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'slug' => $c->slug]),

            // Images — ordered by sort_order via the model relation
            'images' => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => $img->url, // uses accessor
                'is_primary' => $img->is_primary,
                'color_id' => $img->color_id,
                'sort_order' => $img->sort_order,
                'media_type' => $img->media_type,
            ]),

            // SKUs (Variants)
            'variants' => $this->skus->map(fn ($sku) => [
                'id' => $sku->id,
                'code' => $sku->code ?? $sku->sku,
                'price' => (float) $sku->price,
                'mrp' => (float) $sku->mrp,
                'stock' => $sku->stock,
                // Attributes for this SKU (e.g. Color: Red, Size: L)
                'attributes' => collect([
                    $sku->color ? [
                        'id' => $sku->color->id,
                        'name' => 'Color',
                        'value' => $sku->color->name,
                        'code' => $sku->color->name, // Using name as code for now, or hex if needed by frontend
                        'meta' => $sku->color->hex_code,
                    ] : null,
                    $sku->size ? [
                        'id' => $sku->size->id,
                        'name' => 'Size',
                        'value' => $sku->size->name,
                        'code' => $sku->size->code ?? $sku->size->name,
                        'meta' => null,
                    ] : null,
                ])->filter()->values(),
            ]),

            // SEO
            'seo' => [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
            ],

            // Size Chart
            'size_chart' => $this->sizeChart->first() ? [
                'id' => $this->sizeChart->first()->id,
                'name' => $this->sizeChart->first()->name,
                'description' => $this->sizeChart->first()->description,
                'measurements' => $this->sizeChart->first()->data?->table_data ?? [],
            ] : null,

            // Reviews
            'reviews_summary' => [
                'average_rating' => $this->reviews->avg('rating') ? round($this->reviews->avg('rating'), 1) : 0,
                'total_reviews' => $this->reviews->count(),
            ],
            'reviews' => $this->reviews->map(fn ($r) => [
                'id' => $r->id,
                'user' => [
                    'name' => $r->user->name,
                ],
                'rating' => $r->rating,
                'comment' => $r->comment,
                'admin_reply' => $r->admin_reply,
                'created_at' => $r->created_at->format('M d, Y'),
                'images' => $r->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => str_starts_with($img->image_path, 'storage/') || str_starts_with($img->image_path, 'uploads/') ? asset($img->image_path) : asset('storage/'.$img->image_path),
                ]),
            ])->values(),
        ];
    }
}

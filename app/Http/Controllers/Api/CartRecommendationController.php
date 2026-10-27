<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Coupon;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartRecommendationController extends Controller
{
    /**
     * Get recommended products for Cart Offcanvas based on configured rules.
     */
    public function index(Request $request)
    {
        // 1. Load settings
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

        $settings = ThemeSetting::whereIn('key', $keys)->pluck('value', 'key')->toArray();

        $drawerEnabled = ($settings['cart_drawer_enabled'] ?? '1') === '1';
        $recommendationsEnabled = ($settings['cart_drawer_show_recommendations'] ?? '1') === '1';
        $rule = $request->input('rule') ?: ($settings['cart_drawer_recommendation_rule'] ?? 'near_milestone');
        $title = $settings['cart_drawer_recommendations_title'] ?? 'Pairs Well With';
        $limit = max(1, min(10, intval($settings['cart_drawer_recommendations_limit'] ?? 4)));

        if (!$recommendationsEnabled) {
            return response()->json([
                'enabled' => false,
                'title' => $title,
                'rule' => $rule,
                'products' => [],
            ]);
        }

        // 2. Parse cart input
        $subtotal = floatval($request->input('subtotal', 0));
        $cartProductIds = [];
        $rawCartIds = $request->input('cart_product_ids', []);
        if (is_string($rawCartIds)) {
            $cartProductIds = array_filter(array_map('intval', explode(',', $rawCartIds)));
        } elseif (is_array($rawCartIds)) {
            $cartProductIds = array_filter(array_map('intval', $rawCartIds));
        }

        // 3. Find next milestone if any
        $nextMilestone = Coupon::where('is_active', true)
            ->where('is_milestone', true)
            ->where('min_cart_value', '>', $subtotal)
            ->orderBy('min_cart_value', 'asc')
            ->first();

        $nextMilestoneData = null;
        if ($nextMilestone) {
            $gap = max(0, floatval($nextMilestone->min_cart_value) - $subtotal);
            $nextMilestoneData = [
                'code' => $nextMilestone->code,
                'name' => $nextMilestone->name,
                'min_cart_value' => floatval($nextMilestone->min_cart_value),
                'gap' => $gap,
                'discount_amount' => floatval($nextMilestone->discount_amount),
                'type' => $nextMilestone->type,
            ];
        }

        $productIds = [];
        $actualRuleUsed = $rule;

        // 4. Execute selected rule
        switch ($rule) {
            case 'top_sold':
                $productIds = $this->getTopSoldProductIds($cartProductIds, $limit);
                break;

            case 'near_milestone':
                if ($nextMilestone && $nextMilestoneData['gap'] > 0) {
                    $productIds = $this->getNearMilestoneProductIds($nextMilestoneData['gap'], $cartProductIds, $limit);
                } else {
                    // Fallback to top sold if no next milestone is pending
                    $productIds = $this->getTopSoldProductIds($cartProductIds, $limit);
                    $actualRuleUsed = 'top_sold_fallback';
                }
                break;

            case 'category':
                $catId = intval($settings['cart_drawer_category_id'] ?? 0);
                if ($catId > 0) {
                    $productIds = $this->getCategoryProductIds($catId, $cartProductIds, $limit);
                }
                if (empty($productIds)) {
                    $productIds = $this->getTopSoldProductIds($cartProductIds, $limit);
                    $actualRuleUsed = 'top_sold_fallback';
                }
                break;

            case 'collection':
                $colId = intval($settings['cart_drawer_collection_id'] ?? 0);
                if ($colId > 0) {
                    $productIds = $this->getCollectionProductIds($colId, $cartProductIds, $limit);
                }
                if (empty($productIds)) {
                    $productIds = $this->getTopSoldProductIds($cartProductIds, $limit);
                    $actualRuleUsed = 'top_sold_fallback';
                }
                break;

            case 'fixed':
                $rawFixed = $settings['cart_drawer_fixed_product_ids'] ?? '[]';
                $fixedIds = [];
                if (is_string($rawFixed)) {
                    $decoded = json_decode($rawFixed, true);
                    $fixedIds = is_array($decoded) ? $decoded : array_filter(array_map('intval', explode(',', $rawFixed)));
                } elseif (is_array($rawFixed)) {
                    $fixedIds = $rawFixed;
                }
                $fixedIds = array_diff(array_map('intval', $fixedIds), $cartProductIds);
                $productIds = array_slice($fixedIds, 0, $limit);

                if (empty($productIds)) {
                    $productIds = $this->getTopSoldProductIds($cartProductIds, $limit);
                    $actualRuleUsed = 'top_sold_fallback';
                }
                break;

            default:
                $productIds = $this->getTopSoldProductIds($cartProductIds, $limit);
                break;
        }

        // 5. Fetch full product models with relations
        if (empty($productIds)) {
            // Ultimate fallback to any active products not in cart
            $productIds = Product::where('is_active', true)
                ->when(!empty($cartProductIds), fn($q) => $q->whereNotIn('id', $cartProductIds))
                ->latest()
                ->limit($limit)
                ->pluck('id')
                ->toArray();
        }

        if (empty($productIds)) {
            return response()->json([
                'enabled' => true,
                'title' => $title,
                'rule' => $actualRuleUsed,
                'next_milestone' => $nextMilestoneData,
                'products' => [],
            ]);
        }

        $products = Product::whereIn('id', $productIds)
            ->where('is_active', true)
            ->with(['skus.color', 'skus.size', 'images'])
            ->get();

        // Preserve order of $productIds
        $orderedProducts = [];
        $indexed = $products->keyBy('id');
        foreach ($productIds as $pid) {
            if (isset($indexed[$pid])) {
                $orderedProducts[] = $this->formatProductPayload($indexed[$pid]);
            }
        }

        return response()->json([
            'enabled' => true,
            'title' => $title,
            'rule' => $actualRuleUsed,
            'next_milestone' => $nextMilestoneData,
            'products' => $orderedProducts,
        ]);
    }

    /**
     * Query top sold products by quantity from order items.
     */
    protected function getTopSoldProductIds(array $excludeIds, int $limit): array
    {
        $topSold = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereNotNull('product_id')
            ->when(!empty($excludeIds), fn($q) => $q->whereNotIn('product_id', $excludeIds))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->pluck('product_id')
            ->toArray();

        // If fewer than limit, supplement with latest active products
        if (count($topSold) < $limit) {
            $allExclude = array_unique(array_merge($excludeIds, $topSold));
            $fillers = Product::where('is_active', true)
                ->when(!empty($allExclude), fn($q) => $q->whereNotIn('id', $allExclude))
                ->latest()
                ->limit($limit - count($topSold))
                ->pluck('id')
                ->toArray();

            $topSold = array_merge($topSold, $fillers);
        }

        return $topSold;
    }

    /**
     * Query products near the target milestone gap amount.
     */
    protected function getNearMilestoneProductIds(float $gap, array $excludeIds, int $limit): array
    {
        // Find products ordered by closeness to gap: ABS(MIN(skus.price) - gap) ASC
        $query = Product::where('products.is_active', true)
            ->whereHas('skus')
            ->when(!empty($excludeIds), fn($q) => $q->whereNotIn('products.id', $excludeIds))
            ->join('skus', 'products.id', '=', 'skus.product_id')
            ->select('products.id')
            ->groupBy('products.id')
            ->orderByRaw("ABS(MIN(skus.price) - {$gap}) ASC")
            ->limit($limit);

        $ids = $query->pluck('products.id')->toArray();

        if (count($ids) < $limit) {
            $allExclude = array_unique(array_merge($excludeIds, $ids));
            $fillers = Product::where('is_active', true)
                ->when(!empty($allExclude), fn($q) => $q->whereNotIn('id', $allExclude))
                ->latest()
                ->limit($limit - count($ids))
                ->pluck('id')
                ->toArray();

            $ids = array_merge($ids, $fillers);
        }

        return $ids;
    }

    /**
     * Query products from a specific category (and its subcategories).
     */
    protected function getCategoryProductIds(int $categoryId, array $excludeIds, int $limit): array
    {
        $category = Category::find($categoryId);
        if (!$category) {
            return [];
        }

        $allCategoryIds = [$categoryId];
        $children = $category->children()->pluck('id')->toArray();
        $allCategoryIds = array_merge($allCategoryIds, $children);

        return Product::where('is_active', true)
            ->when(!empty($excludeIds), fn($q) => $q->whereNotIn('id', $excludeIds))
            ->whereHas('categories', fn($q) => $q->whereIn('categories.id', $allCategoryIds))
            ->latest()
            ->limit($limit)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Query products from a specific collection.
     */
    protected function getCollectionProductIds(int $collectionId, array $excludeIds, int $limit): array
    {
        return Product::where('is_active', true)
            ->when(!empty($excludeIds), fn($q) => $q->whereNotIn('id', $excludeIds))
            ->whereHas('collections', fn($q) => $q->where('collections.id', $collectionId))
            ->latest()
            ->limit($limit)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Format product payload for cart drawer quick add and rendering.
     */
    protected function formatProductPayload(Product $product): array
    {
        $minPrice = $product->skus->isNotEmpty() ? $product->skus->min('price') : 0;
        $mrp = $product->skus->isNotEmpty() ? $product->skus->max('mrp') : $minPrice;

        $primarySku = $product->skus->first();
        $color = $primarySku?->color;
        $size = $primarySku?->size;

        $imageUrl = $product->image_url;
        if (!$imageUrl && $product->images->isNotEmpty()) {
            $imageUrl = $product->images->first()->image_url ?? null;
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => floatval($minPrice ?? 0),
            'mrp' => floatval($mrp ?? $minPrice ?? 0),
            'image' => $imageUrl,
            'first_sku_id' => $primarySku?->id ?? $product->id,
            'first_sku_code' => $primarySku?->code ?? $product->slug,
            'color_name' => $color?->name,
            'color_hex' => $color?->hex,
            'size_name' => $size?->name,
            'has_multiple_variants' => $product->skus->count() > 1,
            'is_exchangeable' => (bool) $product->is_exchangeable,
            'exchange_days' => $product->exchange_days,
            'is_returnable' => (bool) $product->is_returnable,
            'return_days' => $product->return_days,
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductListResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('is_active', true)
            ->with(['skus', 'images', 'categories.parent.parent', 'categoryMasterImages']);

        // Slugs filter
        if ($request->has('slugs')) {
            $slugs = explode(',', $request->slugs);
            $query->whereIn('slug', $slugs);
            if (! empty($slugs)) {
                $orderByRaw = 'CASE slug ';
                foreach ($slugs as $index => $slug) {
                    $orderByRaw .= "WHEN '".addslashes($slug)."' THEN $index ";
                }
                $orderByRaw .= 'ELSE '.count($slugs).' END';
                $query->orderByRaw($orderByRaw);
            }
        }

        // Category filter (Dynamic - includes deep descendants)
        if ($request->filled('category')) {
            $categorySlugs = explode(',', $request->category);

            // Fetch the requested categories
            $categoriesToSearch = Category::whereIn('slug', $categorySlugs)->get();
            $allCategoryIds = [];

            foreach ($categoriesToSearch as $cat) {
                $allCategoryIds[] = $cat->id;
                // Add 1st level children
                $children = $cat->children()->pluck('id')->toArray();
                $allCategoryIds = array_merge($allCategoryIds, $children);

                // Add 2nd level children
                foreach ($cat->children as $child) {
                    $allCategoryIds = array_merge($allCategoryIds, $child->children()->pluck('id')->toArray());
                }
            }
            $allCategoryIds = array_unique($allCategoryIds);

            if (! empty($allCategoryIds)) {
                $query->whereHas('categories', function ($q) use ($allCategoryIds) {
                    $q->whereIn('categories.id', $allCategoryIds);
                });
            } else {
                // If the slug doesn't match any category, still apply filter to return nothing
                $query->whereHas('categories', function ($q) use ($categorySlugs) {
                    $q->whereIn('slug', $categorySlugs);
                });
            }
        }

        // Collection filter (if collections relation exists, assuming it does as per user prompt about collection page)
        if ($request->filled('collection')) {
            $collectionSlugs = explode(',', $request->collection);
            $query->whereHas('collections', function ($q) use ($collectionSlugs) {
                $q->whereIn('slug', $collectionSlugs);
            });
        }

        // Price Filter (via SKUs)
        if ($request->filled('min_price')) {
            $query->whereHas('skus', function ($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            });
        }
        if ($request->filled('max_price')) {
            $query->whereHas('skus', function ($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
            });
        }

        // Size & Color (via SKUs)
        if ($request->filled('size') || $request->filled('color') || $request->filled('in_stock')) {
            $query->whereHas('skus', function ($q) use ($request) {
                if ($request->filled('in_stock') && $request->in_stock == '1') {
                    $q->where('stock', '>', 0);
                }
                if ($request->filled('size')) {
                    // try to match size by name or id, assuming ids or names passed
                    $sizes = explode(',', $request->size);
                    $q->whereHas('size', function ($sq) use ($sizes) {
                        $sq->whereIn('name', $sizes);
                    });
                }
                if ($request->filled('color')) {
                    $colors = explode(',', $request->color);
                    $q->whereHas('color', function ($cq) use ($colors) {
                        $cq->whereIn('name', $colors);
                    });
                }
            });
        }

        // Fit & Fabric (Custom Attributes)
        if ($request->filled('fit') || $request->filled('fabric')) {
            // Assuming attributes relation exists on Product or via SKUs
            // For now we'll write a generic hook. If it's via skus -> attributeValues
            $query->whereHas('skus.attributeValues', function ($q) use ($request) {
                if ($request->filled('fit')) {
                    $fits = explode(',', $request->fit);
                    $q->whereHas('attribute', function ($aq) {
                        $aq->where('name', 'like', '%Fit%');
                    })->whereIn('value', $fits);
                }
            });
            $query->whereHas('skus.attributeValues', function ($q) use ($request) {
                if ($request->filled('fabric')) {
                    $fabrics = explode(',', $request->fabric);
                    $q->whereHas('attribute', function ($aq) {
                        $aq->where('name', 'like', '%Fabric%');
                    })->whereIn('value', $fabrics);
                }
            });
        }

        // Sorting
        $sort = $request->input('sort', 'new');

        // For price sorting, we join the skus table to get the minimum price per product
        if ($sort === 'price_low_high' || $sort === 'price_high_low') {
            $query->addSelect([
                'min_sku_price' => Sku::selectRaw('min(price)')
                    ->whereColumn('product_id', 'products.id'),
            ]);
        }

        switch ($sort) {
            case 'price_low_high':
                $query->orderBy('min_sku_price', 'asc');
                break;
            case 'price_high_low':
                $query->orderBy('min_sku_price', 'desc');
                break;
            case 'a_z':
                $query->orderBy('name', 'asc');
                break;
            case 'z_a':
                $query->orderBy('name', 'desc');
                break;
            case 'best_seller':
                // Fallback to latest if no sales column
                $query->latest();
                break;
            case 'featured':
                if (Schema::hasColumn('products', 'is_featured')) {
                    $query->orderBy('is_featured', 'desc');
                }
                $query->latest();
                break;
            case 'new':
            default:
                if (! $request->has('slugs')) {
                    $query->latest();
                }
                break;
        }

        $limit = $request->input('limit', 20);
        $products = $query->paginate($limit);

        return ProductListResource::collection($products);
    }

    public function show(Request $request, $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['skus.attributeValues.attribute', 'images', 'categories.parent.parent', 'productType', 'sizeChart.data', 'categoryMasterImages'])
            ->firstOrFail();

        if ($request->filled('category')) {
            $requestedSlugs = explode(',', $request->category);
            $matchedCategories = $product->categories->whereIn('slug', $requestedSlugs);

            foreach ($matchedCategories as $cat) {
                $catImage = null;
                $current = $cat;

                // Walk up the category tree to find an image
                while ($current && ! $catImage) {
                    $catImage = $product->categoryMasterImages->where('category_id', $current->id)->first();
                    $current = $current->parent;
                }

                if ($catImage && ($catImage->image_path || $catImage->video_path)) {
                    // Force override the appended attribute dynamically
                    if ($catImage->video_path) {
                        $product->video_url = $catImage->video_url;
                    }
                    if ($catImage->image_path) {
                        $product->image_url = $catImage->image_url;
                        $product->preview_image = $catImage->image_path;
                    }
                    break;
                }
            }
        }

        return new ProductResource($product);
    }
}

<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\CmsPage;
use App\Models\Product;
use App\Models\Category;
use App\Models\Collection;

class PageController extends Controller
{
    public function home(\Illuminate\Http\Request $request)
    {
        $page = CmsPage::where('is_home', true)->where('is_active', true)->first();

        if (!$page) {
            return Inertia::render('Home', [
                'page' => null,
                'content' => null,
            ]);
        }

        return Inertia::render('Home', [
            'page' => $page,
            'content' => $request->has('preview') && $request->preview === 'true' ? $page->draft_content : $page->content,
            'layout' => $page->layout ?? 'default',
        ]);
    }

    public function show(\Illuminate\Http\Request $request, $slug)
    {
        $page = CmsPage::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return Inertia::render('CmsPage', [
            'page' => $page,
            'content' => $request->has('preview') && $request->preview === 'true' ? $page->draft_content : $page->content,
            'layout' => $page->layout ?? 'default',
        ]);
    }

    public function search()
    {
        return Inertia::render('Shop/Search');
    }

    public function shop()
    {
        $products = Product::where('is_active', true)->paginate(12);
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $collections = Collection::all();

        return Inertia::render('Shop/Index', [
            'products' => $products,
            'categories' => $categories,
            'collections' => $collections,
        ]);
    }

    public function product($slug)
    {
        $product = Product::where('slug', $slug)
            ->with(['images', 'skus.color', 'skus.size', 'sizeChart', 'categories', 'productType', 'reviews.user', 'reviews.images'])
            ->firstOrFail();

        return Inertia::render('Product/Show', [
            'product' => (new \App\Http\Resources\ProductResource($product))->resolve(),
        ]);
    }

    public function category($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $products = Product::whereHas('categories', function($q) use ($category) {
            $q->where('category_id', $category->id);
        })->where('is_active', true)->paginate(12);

        return Inertia::render('Category/Show', [
            'category' => $category,
            'products' => $products,
        ]);
    }

    public function collection($slug)
    {
        $collection = Collection::where('slug', $slug)->firstOrFail();
        $products = Product::whereHas('collections', function($q) use ($collection) {
            $q->where('collection_id', $collection->id);
        })->where('is_active', true)->paginate(12);

        return Inertia::render('Collection/Show', [
            'collection' => $collection,
            'products' => $products,
        ]);
    }

    public function cart()
    {
        return Inertia::render('Cart');
    }

    public function checkout()
    {
        return Inertia::render('Checkout');
    }

    public function thankYou($uuid)
    {
        $order = \App\Models\Order::with(['items.product', 'items.sku.color', 'items.sku.size'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return Inertia::render('Checkout/ThankYou', [
            'order' => $order
        ]);
    }

    public function wishlist()
    {
        return Inertia::render('Wishlist');
    }

}

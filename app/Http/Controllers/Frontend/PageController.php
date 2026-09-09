<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\CmsPage;
use App\Models\Collection;
use App\Models\LegalPage;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageController extends Controller
{
    private function getDefaultOgImage()
    {
        $favicon = \App\Models\ThemeSetting::where('key', 'favicon')->value('value');
        if ($favicon) return url($favicon);
        
        $logo = \App\Models\ThemeSetting::where('key', 'main_logo')->value('value');
        if ($logo) return url($logo);

        return url('/favicon.ico');
    }

    public function home(Request $request)
    {
        $page = CmsPage::where('is_home', true)->where('is_active', true)->first();

        if (! $page) {
            return Inertia::render('Home', [
                'page' => null,
                'content' => null,
            ]);
        }

        return Inertia::render('Home', [
            'page' => $page,
            'content' => $request->has('preview') && $request->preview === 'true' ? $page->draft_content : $page->content,
            'layout' => $page->layout ?? 'default',
        ])->withViewData([
            'og_title' => $page->meta_title ?: $page->title,
            'og_description' => $page->meta_description ?: '',
            'og_image' => $page->meta_image ? url($page->meta_image) : $this->getDefaultOgImage(),
            'og_url' => url()->current(),
        ]);
    }

    public function show(Request $request, $slug)
    {
        $page = CmsPage::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $viewData = [
            'og_title' => $page->meta_title ?: $page->title,
            'og_description' => $page->meta_description ?: '',
            'og_image' => $page->meta_image ? (str_starts_with($page->meta_image, 'http') ? $page->meta_image : url($page->meta_image)) : $this->getDefaultOgImage(),
            'og_url' => url()->current(),
        ];

        if ($page->is_about_page) {
            $viewData['json_ld'] = [
                "@context" => "https://schema.org",
                "@type" => "AboutPage",
                "name" => $page->title,
                "description" => $page->meta_description,
                "url" => url()->current(),
                "mainEntity" => [
                    "@type" => "Organization",
                    "name" => config('app.name', 'Vyora'),
                ]
            ];
            
            // Add a dumb bot fallback of the text
            $botText = "";
            $contentArray = $request->has('preview') && $request->preview === 'true' ? $page->draft_content : $page->content;
            if (is_array($contentArray) && !empty($contentArray['blocks'])) {
                foreach ($contentArray['blocks'] as $block) {
                    if ($block['type'] === 'text' && !empty($block['data']['text'])) {
                        $botText .= "<p>" . strip_tags($block['data']['text']) . "</p>";
                    } else if ($block['type'] === 'header' && !empty($block['data']['text'])) {
                        $botText .= "<h2>" . strip_tags($block['data']['text']) . "</h2>";
                    }
                }
            }
            if ($botText) {
                $viewData['bot_html'] = $botText;
            }
        }

        return Inertia::render('CmsPage', [
            'page' => $page,
            'content' => $request->has('preview') && $request->preview === 'true' ? $page->draft_content : $page->content,
            'layout' => $page->layout ?? 'default',
        ])->withViewData($viewData);
    }

    public function legal($slug)
    {
        $page = LegalPage::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return Inertia::render('LegalPage', [
            'page' => $page,
        ]);
    }

    public function search()
    {
        return Inertia::render('Shop/Search');
    }

    public function shop(\Illuminate\Http\Request $request)
    {
        $products = Product::where('is_active', true)->paginate(12);
        $categories = Category::whereNull('parent_id')->with('children')->get();
        $collections = Collection::all();

        $ogTitle = 'Shop All';
        $ogDescription = 'Browse our complete collection of products.';
        $ogImage = $this->getDefaultOgImage();

        if ($request->has('category')) {
            $cat = Category::where('slug', $request->category)->first();
            if ($cat) {
                $ogTitle = $cat->meta_title ?: $cat->name;
                $ogDescription = $cat->meta_description ?: ($cat->description ? strip_tags($cat->description) : "Shop {$cat->name} at our store.");
                $ogImage = $cat->social_image ? url($cat->social_image) : $ogImage;
            }
        } elseif ($request->has('collection')) {
            $col = Collection::where('slug', $request->collection)->first();
            if ($col) {
                $ogTitle = $col->social_title ?: $col->name;
                $ogDescription = $col->social_description ?: ($col->description ? strip_tags($col->description) : "Shop the {$col->name} collection.");
                $ogImage = $col->social_image ? url($col->social_image) : $ogImage;
            }
        }

        return Inertia::render('Shop/Index', [
            'products' => $products,
            'categories' => $categories,
            'collections' => $collections,
        ])->withViewData([
            'og_title' => $ogTitle,
            'og_description' => $ogDescription,
            'og_image' => $ogImage,
            'og_url' => url()->current(),
        ]);
    }

    public function product($slug)
    {
        $product = Product::where('slug', $slug)
            ->with(['images', 'skus.color', 'skus.size', 'sizeChart', 'categories', 'productType', 'reviews.user', 'reviews.images', 'deliveryTimeline'])
            ->firstOrFail();

        $product->increment('view_count');

        $productResource = (new ProductResource($product))->resolve(request());
        $productData = json_decode(json_encode($productResource), true);

        $botHtml = "<h1>{$product->name}</h1>";
        if (!empty($productData['brand'])) {
            $botHtml .= "<p>Brand: {$productData['brand']}</p>";
        }
        $botHtml .= "<p>" . strip_tags($productData['short_description'] ?? '') . "</p>";
        $botHtml .= "<div>" . $product->long_description . "</div>";
        if (!empty($productData['use_case'])) {
            $botHtml .= "<p><strong>Use Case:</strong> {$productData['use_case']}</p>";
        }
        if ($product->skus) {
            $botHtml .= "<ul>";
            foreach ($product->skus as $sku) {
                $botHtml .= "<li>" . ($sku->color ? $sku->color->name : '') . " " . ($sku->size ? $sku->size->name : '') . " - ₹" . $sku->price . "</li>";
            }
            $botHtml .= "</ul>";
        }
        
        $jsonLd = [
            "@context" => "https://schema.org/",
            "@type" => "Product",
            "name" => $productData['name'] ?? $product->name,
            "image" => !empty($productData['images']) ? $productData['images'][0]['url'] : (!empty($productData['image']) ? $productData['image'] : $this->getDefaultOgImage()),
            "description" => strip_tags($productData['short_description'] ?? $productData['name'] ?? '') . (!empty($productData['use_case']) ? " Ideal for: " . $productData['use_case'] : ""),
            "sku" => !empty($productData['variants']) ? ($productData['variants'][0]['code'] ?? 'SKU-01') : 'SKU-01',
            "brand" => [
                "@type" => "Brand",
                "name" => $productData['brand'] ?? "Dope Style"
            ],
            "offers" => [
                "@type" => "Offer",
                "url" => url()->current(),
                "priceCurrency" => "INR",
                "price" => !empty($productData['variants']) ? $productData['variants'][0]['price'] : ($productData['price'] ?? 0),
                "itemCondition" => "https://schema.org/NewCondition",
                "availability" => (!empty($productData['variants']) && $productData['variants'][0]['stock'] > 0) ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
            ]
        ];

        if (!empty($productData['reviews_summary']) && $productData['reviews_summary']['total_reviews'] > 0) {
            $jsonLd['aggregateRating'] = [
                "@type" => "AggregateRating",
                "ratingValue" => $productData['reviews_summary']['average_rating'],
                "reviewCount" => $productData['reviews_summary']['total_reviews']
            ];
            
            if (!empty($productData['reviews'])) {
                $jsonLd['review'] = collect($productData['reviews'])->map(function($r) {
                    return [
                        "@type" => "Review",
                        "reviewRating" => [
                            "@type" => "Rating",
                            "ratingValue" => $r['rating'],
                            "bestRating" => "5"
                        ],
                        "author" => [
                            "@type" => "Person",
                            "name" => $r['user']['name'] ?? "Customer"
                        ],
                        "reviewBody" => $r['comment'] ?? ""
                    ];
                })->toArray();
            }
        }

        return Inertia::render('Product/Show', [
            'product' => $productResource,
        ])->withViewData([
            'og_title' => $productData['name'] ?? $product->name,
            'og_description' => strip_tags($productData['short_description'] ?? $productData['name'] ?? ''),
            'og_image' => !empty($productData['image']) ? $productData['image'] : $this->getDefaultOgImage(),
            'og_url' => url()->current(),
            'json_ld' => $jsonLd,
            'bot_html' => $botHtml,
        ]);
    }

    public function category($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $products = Product::whereHas('categories', function ($q) use ($category) {
            $q->where('category_id', $category->id);
        })->where('is_active', true)->paginate(12);

        return Inertia::render('Category/Show', [
            'category' => $category,
            'products' => $products,
        ])->withViewData([
            'og_title' => $category->meta_title ?: $category->name,
            'og_description' => $category->meta_description ?: ($category->description ? strip_tags($category->description) : "Shop {$category->name} at our store."),
            'og_image' => $category->social_image ? url($category->social_image) : $this->getDefaultOgImage(),
            'og_url' => url()->current(),
        ]);
    }

    public function collection($slug)
    {
        $collection = Collection::where('slug', $slug)->firstOrFail();
        $products = Product::whereHas('collections', function ($q) use ($collection) {
            $q->where('collection_id', $collection->id);
        })->where('is_active', true)->paginate(12);

        return Inertia::render('Collection/Show', [
            'collection' => $collection,
            'products' => $products,
        ])->withViewData([
            'og_title' => $collection->social_title ?: $collection->name,
            'og_description' => $collection->social_description ?: ($collection->description ? strip_tags($collection->description) : "Shop the {$collection->name} collection."),
            'og_image' => $collection->social_image ? url($collection->social_image) : $this->getDefaultOgImage(),
            'og_url' => url()->current(),
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
        $order = Order::with(['items.product', 'items.sku.color', 'items.sku.size'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return Inertia::render('Checkout/ThankYou', [
            'order' => $order,
        ]);
    }

    public function wishlist()
    {
        return Inertia::render('Wishlist');
    }
}

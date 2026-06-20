<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;

class SitemapController extends Controller
{
    public function index()
    {
        $appUrl = config('app.url');

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        // Static Pages
        $staticPages = [
            '/',
            '/shop',
            '/search',
            '/cart',
            '/checkout',
            '/wishlist',
        ];

        foreach ($staticPages as $page) {
            $url = $xml->addChild('url');
            $url->addChild('loc', $appUrl.$page);
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', $page === '/' ? '1.0' : '0.8');
        }

        // Products
        $products = Product::where('is_active', true)->select('slug', 'updated_at')->get();
        foreach ($products as $product) {
            $url = $xml->addChild('url');
            $url->addChild('loc', $appUrl.'/product/'.$product->slug);
            $url->addChild('lastmod', $product->updated_at->toAtomString());
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '0.9');
        }

        // Categories
        $categories = Category::select('slug', 'updated_at')->get();
        foreach ($categories as $category) {
            $url = $xml->addChild('url');
            $url->addChild('loc', $appUrl.'/category/'.$category->slug);
            $url->addChild('lastmod', $category->updated_at->toAtomString());
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.7');
        }

        // Collections
        if (class_exists(Collection::class)) {
            $collections = Collection::select('slug', 'updated_at')->get();
            foreach ($collections as $collection) {
                $url = $xml->addChild('url');
                $url->addChild('loc', $appUrl.'/collection/'.$collection->slug);
                $url->addChild('lastmod', $collection->updated_at->toAtomString());
                $url->addChild('changefreq', 'weekly');
                $url->addChild('priority', '0.7');
            }
        }

        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}

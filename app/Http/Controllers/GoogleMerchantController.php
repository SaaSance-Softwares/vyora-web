<?php

namespace App\Http\Controllers;

use App\Models\Product;

class GoogleMerchantController extends Controller
{
    public function feed()
    {
        $products = Product::with(['skus', 'images'])
            ->where('is_active', true)
            ->get();

        $appUrl = rtrim(config('app.url', 'https://dopestyle.in'), '/');
        $storeName = config('app.name', 'Dope Style');

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss xmlns:g="http://base.google.com/ns/1.0" version="2.0"></rss>');
        $channel = $xml->addChild('channel');
        $channel->addChild('title', htmlspecialchars($storeName, ENT_XML1, 'UTF-8'));
        $channel->addChild('link', $appUrl);
        $channel->addChild('description', htmlspecialchars($storeName . ' Product Feed', ENT_XML1, 'UTF-8'));

        foreach ($products as $product) {
            if ($product->skus->isEmpty()) {
                continue;
            }

            // Determine primary image
            $image = $product->images->first();
            $imageUrl = $product->image_url ?: ($image ? $image->url : ($appUrl . '/images/placeholder.png'));
            if (! str_starts_with($imageUrl, 'http')) {
                $imageUrl = $appUrl . '/' . ltrim($imageUrl, '/');
            }

            // Determine lowest active price
            $minPrice = (float) ($product->skus->min('price') ?: $product->price ?: 0);
            $formattedPrice = number_format($minPrice, 2, '.', '') . ' INR';

            // Determine stock availability
            $hasStock = $product->skus->where('stock', '>', 0)->isNotEmpty();
            $availability = $hasStock ? 'in_stock' : 'out_of_stock';

            // Clean title
            $title = trim((string) $product->name);
            if (empty($title)) {
                $title = 'Product #' . $product->id;
            }

            // Clean description (must never be empty and stripped of HTML)
            $rawDesc = $product->description ?: $product->short_description ?: $product->name;
            $description = trim(strip_tags((string) $rawDesc));
            if (empty($description)) {
                $description = $title . ' - Available at ' . $storeName;
            }
            $description = mb_substr($description, 0, 4900);

            // Canonical link
            $slug = $product->slug ?: $product->id;
            $link = $appUrl . '/product/' . $slug;

            // Brand
            $brand = trim((string) ($product->brand_name ?: '')) ?: $storeName;

            $item = $channel->addChild('item');
            // Guaranteed unique product ID matching Meta Pixel / CAPI events exactly
            $item->addChild('g:id', (string) $product->id, 'http://base.google.com/ns/1.0');
            $item->addChild('g:title', htmlspecialchars($title, ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
            $item->addChild('g:description', htmlspecialchars($description, ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
            $item->addChild('g:link', htmlspecialchars($link, ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
            $item->addChild('g:image_link', htmlspecialchars($imageUrl, ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
            $item->addChild('g:condition', 'new', 'http://base.google.com/ns/1.0');
            $item->addChild('g:availability', $availability, 'http://base.google.com/ns/1.0');
            $item->addChild('g:price', $formattedPrice, 'http://base.google.com/ns/1.0');
            $item->addChild('g:brand', htmlspecialchars($brand, ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
            $item->addChild('g:google_product_category', 'Apparel & Accessories > Clothing', 'http://base.google.com/ns/1.0');
            $item->addChild('g:identifier_exists', 'no', 'http://base.google.com/ns/1.0');
        }

        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}

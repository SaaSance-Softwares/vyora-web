<?php

namespace App\Http\Controllers;

use App\Models\Product;

class GoogleMerchantController extends Controller
{
    public function feed()
    {
        $products = Product::with(['skus.color', 'skus.size', 'images'])
            ->where('is_active', true)
            ->get();

        $appUrl = config('app.url');
        $storeName = config('app.name', 'Dope Style');

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss xmlns:g="http://base.google.com/ns/1.0" version="2.0"></rss>');
        $channel = $xml->addChild('channel');
        $channel->addChild('title', htmlspecialchars($storeName, ENT_XML1, 'UTF-8'));
        $channel->addChild('link', $appUrl);
        $channel->addChild('description', htmlspecialchars($storeName.' Product Feed', ENT_XML1, 'UTF-8'));

        foreach ($products as $product) {
            if ($product->skus->isEmpty()) {
                continue;
            }

            foreach ($product->skus as $sku) {
                // Determine image
                $image = $product->images->where('color_id', $sku->color_id)->first();
                if (! $image) {
                    $image = $product->images->first();
                }

                $imageUrl = $image ? $image->url : ($appUrl.'/images/placeholder.png');
                if (! str_starts_with($imageUrl, 'http')) {
                    $imageUrl = rtrim($appUrl, '/').'/'.ltrim($imageUrl, '/');
                }

                $title = $product->name;
                if ($sku->color) {
                    $title .= ' - '.$sku->color->name;
                }
                if ($sku->size) {
                    $title .= ' - '.$sku->size->name;
                }

                $item = $channel->addChild('item');
                $item->addChild('g:id', htmlspecialchars($sku->product_sku ?? $sku->id, ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
                $item->addChild('g:item_group_id', $product->id, 'http://base.google.com/ns/1.0');
                $item->addChild('g:title', htmlspecialchars($title, ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
                $item->addChild('g:description', htmlspecialchars(strip_tags($product->short_description ?: $product->name), ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
                $item->addChild('g:link', $appUrl.'/product/'.$product->slug, 'http://base.google.com/ns/1.0');
                $item->addChild('g:image_link', htmlspecialchars($imageUrl, ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
                $item->addChild('g:condition', 'new', 'http://base.google.com/ns/1.0');
                $item->addChild('g:availability', $sku->stock > 0 ? 'in_stock' : 'out_of_stock', 'http://base.google.com/ns/1.0');
                $item->addChild('g:price', $sku->price.' INR', 'http://base.google.com/ns/1.0');
                $item->addChild('g:brand', htmlspecialchars($product->brand_name ?: $storeName, ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');

                if ($sku->color) {
                    $item->addChild('g:color', htmlspecialchars($sku->color->name, ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
                }
                if ($sku->size) {
                    $item->addChild('g:size', htmlspecialchars($sku->size->name, ENT_XML1, 'UTF-8'), 'http://base.google.com/ns/1.0');
                }
            }
        }

        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}

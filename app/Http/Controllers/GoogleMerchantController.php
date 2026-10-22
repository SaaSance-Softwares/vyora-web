<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use XMLWriter;

class GoogleMerchantController extends Controller
{
    /**
     * Product feed used by Google Merchant Center AND Meta (Facebook/Instagram) Catalog.
     *
     * Rules:
     *  - One <item> per product. g:id == product id, which is exactly what the
     *    Meta Pixel / CAPI send in content_ids, so catalog matching works.
     *  - Every item always has id, title, description, link, image_link, price,
     *    availability and condition. Items that can't satisfy this are skipped.
     *  - Streamed with XMLWriter in chunks (no 500s from memory/XML warnings),
     *    and cached briefly so Meta/Google fetchers never hammer the database.
     */
    public function feed()
    {
        try {
            $xml = Cache::remember('merchant_feed_xml_v2', 900, fn () => $this->buildFeed());
        } catch (\Throwable $e) {
            Log::error('Merchant feed generation failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response('Feed temporarily unavailable', 503, ['Retry-After' => '300']);
        }

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=900',
        ]);
    }

    private function buildFeed(): string
    {
        $appUrl = rtrim(config('app.url') ?: url('/'), '/');
        $storeName = (string) config('app.name', 'Store');
        $seen = [];

        $w = new XMLWriter;
        $w->openMemory();
        $w->setIndent(false);
        $w->startDocument('1.0', 'UTF-8');
        $w->startElement('rss');
        $w->writeAttribute('version', '2.0');
        $w->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $w->startElement('channel');
        $w->writeElement('title', $storeName);
        $w->writeElement('link', $appUrl);
        $w->writeElement('description', $storeName.' Product Feed');

        Product::query()
            ->with(['skus:id,product_id,price,stock', 'images'])
            ->where('is_active', true)
            ->chunkById(200, function ($products) use ($w, $appUrl, $storeName, &$seen) {
                foreach ($products as $product) {
                    $id = (string) $product->id;
                    if (isset($seen[$id]) || $product->skus->isEmpty()) {
                        continue;
                    }

                    $imageUrl = $this->resolveImage($product, $appUrl);
                    $slug = trim((string) $product->slug);
                    if ($imageUrl === null || $slug === '') {
                        continue; // Meta rejects items without link/image
                    }
                    $seen[$id] = true;

                    $title = trim((string) $product->name) ?: 'Product #'.$id;
                    $description = trim(strip_tags((string) ($product->description ?: $product->short_description ?: $title)));
                    $description = mb_substr($description !== '' ? $description : $title.' - Available at '.$storeName, 0, 4900);

                    $minPrice = (float) ($product->skus->where('price', '>', 0)->min('price') ?: $product->price ?: 0);
                    if ($minPrice <= 0) {
                        continue;
                    }

                    $inStock = $product->skus->where('stock', '>', 0)->isNotEmpty();
                    $brand = trim((string) $product->brand_name) ?: $storeName;

                    $w->startElement('item');
                    $w->writeElement('g:id', $id);
                    $w->writeElement('g:title', mb_substr($title, 0, 150));
                    $w->writeElement('g:description', $description);
                    $w->writeElement('g:link', $appUrl.'/product/'.rawurlencode($slug));
                    $w->writeElement('g:image_link', $imageUrl);
                    $w->writeElement('g:condition', 'new');
                    $w->writeElement('g:availability', $inStock ? 'in stock' : 'out of stock');
                    $w->writeElement('g:price', number_format($minPrice, 2, '.', '').' INR');
                    $w->writeElement('g:brand', $brand);
                    $w->writeElement('g:google_product_category', '1604'); // Apparel & Accessories > Clothing
                    $w->writeElement('g:identifier_exists', 'no');
                    $w->endElement(); // item
                }
            });

        $w->endElement(); // channel
        $w->endElement(); // rss
        $w->endDocument();

        return $w->outputMemory();
    }

    private function resolveImage(Product $product, string $appUrl): ?string
    {
        $url = $product->image_url;

        if (! $url) {
            $first = $product->images->first(fn ($img) => ($img->media_type ?? 'image') !== 'video' && $img->image_path);
            $url = $first?->url;
        }
        if (! $url) {
            return null;
        }
        if (! str_starts_with($url, 'http')) {
            $clean = ltrim($url, '/');
            $url = $appUrl.'/'.(str_starts_with($clean, 'storage/') || str_starts_with($clean, 'uploads/') ? $clean : 'storage/'.$clean);
        }

        // Encode spaces/special characters in the path (Meta rejects raw spaces in image URLs)
        $parts = parse_url($url);
        if (! empty($parts['scheme']) && ! empty($parts['host'])) {
            $path = implode('/', array_map('rawurlencode', array_map('rawurldecode', explode('/', $parts['path'] ?? ''))));
            $url = $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '').$path
                .(isset($parts['query']) ? '?'.$parts['query'] : '');
        }

        return $url;
    }
}

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
     * Structure (fashion variant model):
     *  - One <item> per SKU (colour + size variant).
     *  - g:id            = skus.code (globally unique in the DB) -> no duplicate IDs.
     *  - g:item_group_id = product id. The Meta Pixel / CAPI send this same product id
     *                      as content_ids with content_type "product_group", so events
     *                      match the catalog group.
     *  - Every item always has id, title, description, link, image_link, price,
     *    availability and condition. Items that can't satisfy this are skipped.
     *
     * Streamed with XMLWriter in chunks (low memory, correct XML escaping) and cached
     * briefly so Meta/Google fetchers never hammer the database.
     */
    public function feed()
    {
        try {
            $xml = Cache::remember('merchant_feed_xml_v3', 900, fn () => $this->buildFeed());
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
            ->with(['skus.color', 'skus.size', 'images'])
            ->where('is_active', true)
            ->chunkById(50, function ($products) use ($w, $appUrl, $storeName, &$seen) {
                foreach ($products as $product) {
                    $slug = trim((string) $product->slug);
                    if ($slug === '' || $product->skus->isEmpty()) {
                        continue;
                    }

                    $groupId = (string) $product->id;
                    $name = trim((string) $product->name) ?: 'Product #'.$groupId;
                    $description = trim(strip_tags((string) ($product->description ?: $product->short_description ?: $name)));
                    $description = mb_substr($description !== '' ? $description : $name.' - Available at '.$storeName, 0, 4900);
                    $brand = trim((string) $product->brand_name) ?: $storeName;
                    $link = $appUrl.'/product/'.rawurlencode($slug);

                    foreach ($product->skus as $sku) {
                        $id = trim((string) $sku->code) !== '' ? trim((string) $sku->code) : $groupId.'-'.$sku->id;
                        if (isset($seen[$id])) {
                            $id = $groupId.'-'.$sku->id; // guarantee uniqueness
                        }
                        if (isset($seen[$id])) {
                            continue;
                        }

                        $price = (float) $sku->price;
                        if ($price <= 0) {
                            continue;
                        }

                        $imageUrl = $this->resolveImage($product, $sku->color_id, $appUrl);
                        if ($imageUrl === null) {
                            continue; // Meta rejects items without an image
                        }
                        $seen[$id] = true;

                        $color = $sku->color?->name;
                        $size = $sku->size?->name;
                        $title = $name.($color ? ' - '.$color : '').($size ? ' - '.$size : '');
                        $mrp = (float) $sku->mrp;

                        $w->startElement('item');
                        $w->writeElement('g:id', $id);
                        $w->writeElement('g:item_group_id', $groupId);
                        $w->writeElement('g:title', mb_substr($title, 0, 150));
                        $w->writeElement('g:description', $description);
                        $w->writeElement('g:link', $link);
                        $w->writeElement('g:image_link', $imageUrl);
                        $w->writeElement('g:condition', 'new');
                        $w->writeElement('g:availability', ((int) $sku->stock) > 0 ? 'in stock' : 'out of stock');
                        if ($mrp > $price) {
                            $w->writeElement('g:price', number_format($mrp, 2, '.', '').' INR');
                            $w->writeElement('g:sale_price', number_format($price, 2, '.', '').' INR');
                        } else {
                            $w->writeElement('g:price', number_format($price, 2, '.', '').' INR');
                        }
                        $w->writeElement('g:brand', $brand);
                        if ($color) {
                            $w->writeElement('g:color', $color);
                        }
                        if ($size) {
                            $w->writeElement('g:size', $size);
                        }
                        $w->writeElement('g:google_product_category', '1604'); // Apparel & Accessories > Clothing
                        $w->writeElement('g:identifier_exists', 'no');
                        $w->endElement(); // item
                    }
                }
            });

        $w->endElement(); // channel
        $w->endElement(); // rss
        $w->endDocument();

        return $w->outputMemory();
    }

    /**
     * Prefer an image of the SKU's colour, then the first image, then the preview image.
     */
    private function resolveImage(Product $product, $colorId, string $appUrl): ?string
    {
        $isImage = fn ($img) => ($img->media_type ?? 'image') !== 'video' && $img->image_path;

        $image = null;
        if ($colorId) {
            $image = $product->images->first(fn ($img) => $img->color_id == $colorId && $isImage($img));
        }
        $image ??= $product->images->first($isImage);

        $url = $image?->url ?: $product->image_url;
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

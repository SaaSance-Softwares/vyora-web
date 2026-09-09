<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate([
            'items' => 'array',
        ]);

        $userId = auth('sanctum')->id();

        try {
            WishlistItem::where('user_id', $userId)->delete();
            
            $items = [];
            foreach ($request->items as $item) {
                $items[] = [
                    'user_id' => $userId,
                    'product_id' => $item['productId'],
                    'sku_id' => $item['skuId'] ?? null,
                    'created_at' => isset($item['addedAt']) ? \Carbon\Carbon::parse($item['addedAt']) : now(),
                    'updated_at' => now(),
                ];
            }
            
            // Remove duplicates by product_id
            $items = collect($items)->unique('product_id')->toArray();
            
            if (count($items) > 0) {
                WishlistItem::insert($items);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Wishlist sync failed: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    public function get()
    {
        $userId = auth('sanctum')->id();
        $items = WishlistItem::with(['product.category', 'product.deliveryTimeline', 'sku.color', 'sku.size'])->where('user_id', $userId)->get();

        $formattedItems = [];
        foreach ($items as $item) {
            if ($item->product) {
                $cName = $item->sku && $item->sku->color ? $item->sku->color->name : null;
                $sName = $item->sku && $item->sku->size ? $item->sku->size->name : null;
                
                $variantName = $cName;
                if ($sName) {
                    $variantName .= $variantName ? ' - ' . $sName : $sName;
                }
                
                $price = $item->sku ? ($item->sku->price ?? 0) : ($item->product->base_price ?? 0);
                $mrp = $item->sku ? ($item->sku->mrp ?? 0) : ($item->product->mrp ?? 0);
                $discount = $item->sku ? ($item->sku->discount_percentage ?? 0) : ($item->product->discount_percentage ?? 0);

                $formattedItems[] = [
                    'productId' => $item->product->id,
                    'skuId' => $item->sku_id,
                    'name' => $item->product->name,
                    'slug' => $item->product->slug,
                    'variant' => $variantName ?: null,
                    'price' => (float) $price,
                    'mrp' => (float) $mrp,
                    'discount_percentage' => (float) $discount,
                    'image' => $item->product->featured_image ?? null,
                    'category' => $item->product->category ? $item->product->category->name : '',
                    'addedAt' => $item->created_at->toISOString(),
                    'colorName' => $cName,
                    'sizeName' => $sName,
                    'deliveryDate' => $item->product->deliveryTimeline ? now()->addDays($item->product->deliveryTimeline->max_days)->format('jS F') : null,
                ];
            }
        }

        return response()->json(['items' => $formattedItems]);
    }
}

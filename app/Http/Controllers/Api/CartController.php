<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function sync(Request $request)
    {
        $request->strictValidate([
            'cart_token' => 'required|string|uuid|max:255',
            'guest_email' => 'nullable|string|email|max:255',
            'items' => 'array',
        ]);

        try {
            $cart = null;

            if (auth('sanctum')->check()) {
                $cart = Cart::where('user_id', auth('sanctum')->id())
                            ->whereIn('status', ['active', 'abandoned'])
                            ->latest()
                            ->first();
            }

            if (!$cart) {
                $cart = Cart::where('cart_token', $request->cart_token)->first();
            }

            if (!$cart) {
                $cart = new Cart();
                $cart->cart_token = $request->cart_token;
                $cart->status = 'active';
            }

            // Update user/guest info
            if (auth('sanctum')->check()) {
                $cart->user_id = auth('sanctum')->id();
            } elseif ($request->guest_email) {
                $cart->guest_email = $request->guest_email;
            }
            
            if ($cart->status === 'abandoned') {
                $cart->status = 'active';
                $cart->abandoned_email_sent_at = null;
                $cart->whatsapp_status = 'pending';
            }
            
            $cart->save();

            // Sync items without deleting all (to preserve created_at)
            $cart->load('items');
            $existingItems = $cart->items->keyBy('sku_id');
            $incomingSkuIds = [];
            
            foreach ($request->items as $item) {
                $incomingSkuIds[] = $item['skuId'];
                
                if ($existingItems->has($item['skuId'])) {
                    // Update existing
                    $existingItem = $existingItems->get($item['skuId']);
                    $existingItem->update([
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'image' => $item['image'] ?? null,
                    ]);
                } else {
                    // Create new
                    $cart->items()->create([
                        'sku_id' => $item['skuId'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'image' => $item['image'] ?? null,
                    ]);
                }
            }
            
            // Delete items not in incoming request
            if (!empty($incomingSkuIds)) {
                $cart->items()->whereNotIn('sku_id', $incomingSkuIds)->delete();
            } elseif (empty($request->items)) {
                $cart->items()->delete();
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Cart sync failed: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    public function get(Request $request)
    {
        $cart = Cart::with(['items.sku.product.deliveryTimeline'])->where('user_id', auth('sanctum')->id())->whereIn('status', ['active', 'abandoned'])->latest()->first();
        
        if (!$cart) {
            return response()->json(['items' => [], 'cart_token' => null]);
        }

        $formattedItems = [];
        foreach ($cart->items as $item) {
            if ($item->sku && $item->sku->product) {
                $cName = $item->sku->color ? $item->sku->color->name : '';
                $sName = $item->sku->size ? $item->sku->size->name : '';
                
                $variantName = $cName;
                if ($sName) {
                    $variantName .= $variantName ? ' - ' . $sName : $sName;
                }
                
                $formattedItems[] = [
                    'skuId' => $item->sku->id,
                    'productId' => $item->sku->product->id,
                    'name' => $item->sku->product->name,
                    'slug' => $item->sku->product->slug,
                    'variant' => $variantName ?: 'Standard',
                    'price' => (float) $item->price,
                    'mrp' => (float) ($item->sku->mrp ?? $item->price),
                    'image' => $item->image ?? '',
                    'quantity' => $item->quantity,
                    'colorName' => $cName,
                    'sizeName' => $sName,
                    'deliveryDate' => $item->sku->product->deliveryTimeline ? now()->addDays($item->sku->product->deliveryTimeline->max_days)->format('jS F') : null,
                ];
            }
        }

        return response()->json([
            'items' => $formattedItems,
            'cart_token' => $cart->cart_token,
        ]);
    }

    public function recover($token)
    {
        $cart = Cart::with(['items.sku.product.deliveryTimeline'])->where('cart_token', $token)->first();
        
        if (!$cart) {
            return redirect('/')->with('error', 'Cart not found or expired.');
        }

        // Format items to match frontend CartItem interface
        $formattedItems = [];
        foreach ($cart->items as $item) {
            if ($item->sku && $item->sku->product) {
                // Determine Variant name based on sku's color and size
                $cName = $item->sku->color ? $item->sku->color->name : '';
                $sName = $item->sku->size ? $item->sku->size->name : '';
                
                $variantName = $cName;
                if ($sName) {
                    $variantName .= $variantName ? ' - ' . $sName : $sName;
                }
                
                $formattedItems[] = [
                    'skuId' => $item->sku->id,
                    'productId' => $item->sku->product->id,
                    'name' => $item->sku->product->name,
                    'slug' => $item->sku->product->slug,
                    'variant' => $variantName ?: 'Standard',
                    'price' => (float) $item->price,
                    'mrp' => (float) ($item->sku->mrp ?? $item->price),
                    'image' => $item->image ?? '',
                    'quantity' => $item->quantity,
                    'colorName' => $cName,
                    'sizeName' => $sName,
                    'deliveryDate' => $item->sku->product->deliveryTimeline ? now()->addDays($item->sku->product->deliveryTimeline->max_days)->format('jS F') : null,
                ];
            }
        }

        return view('recover-cart', [
            'items' => $formattedItems,
            'cartToken' => $cart->cart_token,
            'guestEmail' => $cart->guest_email ?? ($cart->user ? $cart->user->email : '')
        ]);
    }
}

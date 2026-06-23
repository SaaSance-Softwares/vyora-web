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
        $request->validate([
            'cart_token' => 'required|uuid',
            'guest_email' => 'nullable|email',
            'items' => 'array',
        ]);

        try {
            $cart = Cart::firstOrCreate(
                ['cart_token' => $request->cart_token],
                [
                    'user_id' => auth('sanctum')->id(),
                    'status' => 'active'
                ]
            );

            // Update user/guest info
            if (auth('sanctum')->check()) {
                $cart->user_id = auth('sanctum')->id();
            } elseif ($request->guest_email) {
                $cart->guest_email = $request->guest_email;
            }
            
            // Touch the cart to update the updated_at timestamp
            $cart->touch();
            $cart->save();

            // Sync items
            $cart->items()->delete();
            
            $items = [];
            foreach ($request->items as $item) {
                $items[] = new CartItem([
                    'sku_id' => $item['skuId'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'image' => $item['image'] ?? null,
                ]);
            }
            
            if (count($items) > 0) {
                $cart->items()->saveMany($items);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Cart sync failed: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
}

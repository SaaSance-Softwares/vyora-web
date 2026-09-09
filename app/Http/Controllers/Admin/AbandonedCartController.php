<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\Request;

class AbandonedCartController extends Controller
{
    public function index()
    {
        $carts = Cart::with(['items.sku.product', 'user'])
            ->has('items')
            ->whereIn('status', ['active', 'abandoned'])
            ->where(function($q) {
                $q->whereNotNull('guest_email')
                  ->orWhereNotNull('user_id');
            })
            ->latest('updated_at')
            ->paginate(20);

        return view('admin.marketing.abandoned-carts', compact('carts'));
    }

    public function show(\App\Models\Cart $cart)
    {
        $cart->loadMissing(['items.sku.product', 'user']);
        return view('admin.marketing.abandoned-carts-show', compact('cart'));
    }
}

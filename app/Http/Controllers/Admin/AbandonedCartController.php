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
            ->where('status', 'abandoned')
            ->orWhere(function($query) {
                $query->where('status', 'active')
                      ->where('updated_at', '<', now()->subHours(2))
                      ->where(function($q) {
                          $q->whereNotNull('guest_email')
                            ->orWhereNotNull('user_id');
                      });
            })
            ->latest('updated_at')
            ->paginate(20);

        return view('admin.marketing.abandoned-carts', compact('carts'));
    }
}

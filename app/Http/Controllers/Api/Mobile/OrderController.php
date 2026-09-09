<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user', 'shippingAddress');
        
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('shippingAddress', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(50); // increased to 50 to make mobile scroll easier since there is no infinite scroll yet

        return response()->json([
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'total_amount' => $order->total_amount,
                    'created_at' => $order->created_at->toIso8601String(),
                    'customer_name' => $order->user ? $order->user->name : ($order->shippingAddress ? $order->shippingAddress->name : 'Guest'),
                ];
            }),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
        ]);
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['items.sku.product', 'items.sku.size', 'items.sku.color', 'shippingAddress', 'user'])->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $mrpTotal = 0;
        foreach ($order->items as $item) {
            $itemMrp = max(floatval($item->sku?->mrp ?? 0), floatval($item->price));
            $mrpTotal += $itemMrp * $item->quantity;
        }
        $mrpDiscount = $mrpTotal > $order->subtotal ? $mrpTotal - $order->subtotal : 0;
        $totalSavings = $mrpDiscount + $order->discount_amount;

        return response()->json([
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            
            'mrp_total' => $mrpTotal,
            'mrp_discount' => $mrpDiscount,
            'cart_subtotal' => $order->subtotal,
            'discount_amount' => $order->discount_amount,
            'coupon_code' => $order->coupon_code,
            'shipping_amount' => $order->shipping_amount,
            'tax_amount' => $order->tax_amount,
            'tax_breakdown' => json_decode($order->tax_breakdown ?? '{}', true),
            'total_savings' => $totalSavings,
            'total_amount' => $order->total_amount,
            
            'created_at' => $order->created_at->toIso8601String(),
            'customer' => [
                'name' => $order->user ? $order->user->name : ($order->shippingAddress ? $order->shippingAddress->name : 'Guest'),
                'email' => $order->user ? $order->user->email : ($order->shippingAddress ? $order->shippingAddress->email : null),
                'phone' => $order->shippingAddress ? $order->shippingAddress->phone : null,
            ],
            'shipping_address' => $order->shippingAddress ? [
                'address_line_1' => $order->shippingAddress->address_line1,
                'address_line_2' => $order->shippingAddress->address_line2,
                'city' => $order->shippingAddress->city,
                'state' => $order->shippingAddress->state,
                'postal_code' => $order->shippingAddress->zip_code,
                'country' => $order->shippingAddress->country,
                'phone' => $order->shippingAddress->phone,
            ] : null,
            'items' => $order->items->map(function ($item) {
                $variantName = $item->variant_name;
                if (empty($variantName) && $item->sku) {
                    $parts = [];
                    if ($item->sku->color) $parts[] = $item->sku->color->name;
                    if ($item->sku->size) $parts[] = $item->sku->size->name;
                    if (!empty($parts)) $variantName = implode(' - ', $parts);
                }

                return [
                    'id' => $item->id,
                    'name' => $item->product_name ?? ($item->product ? $item->product->name : 'Unknown Product'),
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'size' => $variantName ?: 'NA',
                    'image_url' => $item->image_url ?? ($item->product && class_exists('\App\Models\Media') ? url('storage/' . ($item->product->images->first()->path ?? '')) : null),
                ];
            }),
        ]);
    }
}

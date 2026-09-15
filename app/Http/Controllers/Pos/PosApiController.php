<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class PosApiController extends Controller
{
    use \App\Traits\OrderStatusNotificationTrait;

    /**
     * Fetch all active physical stores and temporary markets.
     */
    public function getLocations()
    {
        $locations = DB::table('pos_locations')->where('is_active', true)->get();
        return response()->json($locations);
    }

    public function getCoupons()
    {
        $coupons = DB::table('coupons')
            ->where('is_active', true)
            ->whereIn('channel', ['offline', 'both'])
            ->get();
        return response()->json($coupons);
    }

    /**
     * Download the store-specific product catalog into the browser's local memory.
     * Only returns SKUs that have been assigned to this location via pos_market_pricing.
     */
    public function getCatalog(Request $request)
    {
        $locationId = $request->query('location_id');

        if (!$locationId) {
            return response()->json(['error' => 'Location ID is required'], 400);
        }

        // Get only the SKUs assigned to this location
        $assignedSkus = DB::table('pos_market_pricing')
            ->where('pos_location_id', $locationId)
            ->join('skus', 'pos_market_pricing.sku_id', '=', 'skus.id')
            ->join('products', 'skus.product_id', '=', 'products.id')
            ->leftJoin('colors', 'skus.color_id', '=', 'colors.id')
            ->leftJoin('sizes', 'skus.size_id', '=', 'sizes.id')
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'skus.id as sku_id',
                'skus.color_id',
                'skus.code as barcode',
                'skus.short_code',
                'skus.mrp',
                'skus.price as base_price',
                'pos_market_pricing.override_price',
                'pos_market_pricing.stock',
                'colors.name as color_name',
                'sizes.name as size_name'
            )
            ->get();

        // Group SKUs by product
        $products = $assignedSkus->groupBy('product_id')->map(function ($skus, $productId) {
            $first = $skus->first();

            // Load product with images once per product (not per SKU)
            $product = \App\Models\Product::with('images')->find($productId);
            $productDefaultImage = $product ? $product->image_url : null;

            return [
                'id'    => $productId,
                'name'  => $first->product_name,
                'image' => $productDefaultImage,
                'skus'  => $skus->map(function($sku) use ($product, $productDefaultImage) {
                    // Resolve the color-specific image for this variant
                    $variantImage = $productDefaultImage;
                    $allVariantImages = [];
                    if ($product && $sku->color_id) {
                        $colorImages = $product->images->where('color_id', $sku->color_id);
                        foreach ($colorImages as $cImg) {
                            $path = $cImg->image_path;
                            if (str_starts_with($path, 'http')) {
                                $allVariantImages[] = $path;
                            } else {
                                $cleanPath = ltrim($path, '/');
                                $allVariantImages[] = (str_starts_with($cleanPath, 'storage/') || str_starts_with($cleanPath, 'uploads/'))
                                    ? asset($cleanPath)
                                    : asset('storage/' . $cleanPath);
                            }
                        }
                        if (count($allVariantImages) > 0) {
                            $variantImage = $allVariantImages[0];
                        }
                    }
                    if (count($allVariantImages) === 0 && $variantImage) {
                        $allVariantImages[] = $variantImage;
                    }

                    return [
                        'id'            => $sku->sku_id,
                        'barcode'       => $sku->barcode,
                        'short_code'    => $sku->short_code,
                        'color_name'    => $sku->color_name ?? 'Default',
                        'size_name'     => $sku->size_name ?? 'Default',
                        'image'         => $variantImage,
                        'gallery'       => $allVariantImages,
                        'mrp'           => (float) $sku->mrp,
                        'base_price'    => (float) $sku->base_price,
                        'selling_price' => (float) ($sku->override_price ?? $sku->base_price),
                        'stock'         => (int) $sku->stock,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'catalog' => $products,
            'synced_at' => now()->toIso8601String()
        ]);
    }

    /**
     * Receive offline orders from the React POS and sync them to the database.
     */
    public function submitOrder(Request $request)
    {
        $payload = $request->validate([
            'orders' => 'required|array',
            'orders.*.uuid' => 'required|string',
            'orders.*.location_id' => 'required|integer',
            'orders.*.total_amount' => 'required|numeric',
            'orders.*.customer_name' => 'nullable|string',
            'orders.*.customer_phone' => 'nullable|string',
            'orders.*.items' => 'required|array',
            'orders.*.created_at' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $processedCount = 0;
            
            $posSettings = \Illuminate\Support\Facades\DB::table('theme_settings')->where('group', 'pos_settings')->pluck('value', 'key');
            $dynamicStatusId = $posSettings['default_pos_status'] ?? null;
            $defaultGuestName = $posSettings['default_pos_guest_name'] ?? 'POS Customer';
            
            foreach ($payload['orders'] as $posOrder) {
                // Prevent duplicate offline syncs
                if (DB::table('orders')->where('uuid', $posOrder['uuid'])->exists()) {
                    continue; 
                }
                
                // Get default order status
                $statusId = $dynamicStatusId 
                    ?? DB::table('order_statuses')->where('name', 'POS')->value('id') 
                    ?? DB::table('order_statuses')->where('name', 'POS Delivered')->value('id') 
                    ?? DB::table('order_statuses')->where('name', 'Delivered')->value('id') 
                    ?? 1;
                $uuid = !empty($posOrder['uuid']) ? $posOrder['uuid'] : \Illuminate\Support\Str::uuid()->toString();

                $userId = null;
                $phone = $posOrder['customer_phone'] ?? null;
                $name = $posOrder['customer_name'] ?? null;

                if (!empty($phone)) {
                    // Clean phone number (remove +, spaces, dashes)
                    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                    // Get last 10 digits to bypass country code differences, or use exactly what was provided
                    $searchPhone = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;
                    
                    // Try exact match first, then fallback to matching the last 10 digits
                    $user = \App\Models\User::where('phone', $phone)
                                            ->orWhere('phone', 'LIKE', '%' . $searchPhone)
                                            ->first();
                    
                    if ($user) {
                        $userId = $user->id;
                    } else {
                        $user = \App\Models\User::create([
                            'name' => $name ?: $defaultGuestName,
                            'phone' => $phone,
                            'email' => $phone . '@vyorapos.local',
                            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                            'provider' => 'POS',
                            'role' => 'user',
                            'has_consented_to_terms' => true,
                            'has_consented_to_marketing' => false,
                        ]);
                        $userId = $user->id;
                    }
                }

                $orderId = DB::table('orders')->insertGetId([
                    'uuid' => $uuid,
                    'user_id' => $userId,
                    'order_number' => 'POS-' . strtoupper(substr(uniqid(), -6)),
                    'pos_location_id' => $posOrder['location_id'],
                    'customer_name' => $name,
                    'customer_phone' => $phone,
                    'source' => 'pos',
                    'status' => 'delivered',
                    'payment_status' => 'paid',
                    'payment_method' => 'cash/upi',
                    'total_amount' => $posOrder['total_amount'],
                    'amount_paid' => $posOrder['total_amount'],
                    'discount_amount' => $posOrder['discount_amount'] ?? 0,
                    'coupon_discount_amount' => $posOrder['discount_amount'] ?? 0,
                    'coupon_code' => $posOrder['coupon_code'] ?? null,
                    'balance_due' => 0,
                    'order_status_id' => $statusId,
                    'created_at' => \Carbon\Carbon::parse($posOrder['created_at']),
                    'updated_at' => now(),
                ]);

                foreach ($posOrder['items'] as $item) {
                    DB::table('order_items')->insert([
                        'order_id' => $orderId,
                        'product_id' => $item['product_id'],
                        'sku_id' => $item['sku_id'],
                        'product_name' => $item['name'],
                        'image_url' => $item['image'] ?? null,
                        'quantity' => $item['qty'],
                        'price' => $item['price'],
                        'total' => $item['price'] * $item['qty'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Decrement stock in DB
                    DB::table('skus')->where('id', $item['sku_id'])->decrement('stock', $item['qty']);
                    DB::table('pos_market_pricing')
                        ->where('pos_location_id', $posOrder['location_id'])
                        ->where('sku_id', $item['sku_id'])
                        ->decrement('stock', $item['qty']);
                }

                // Dynamic Notifications Trigger for Offline Sales
                try {
                    $orderModel = \App\Models\Order::with(['orderStatus.whatsappTemplate', 'orderStatus.smsTemplate', 'orderStatus.emailTemplate', 'user'])->find($orderId);
                    
                    if ($orderModel && $orderModel->orderStatus) {
                        // WhatsApp
                        if ($orderModel->orderStatus->whatsappTemplate && !empty($posOrder['customer_phone'])) {
                            app(\App\Services\WhatsAppService::class)->sendDynamicWhatsApp($orderModel, $orderModel->orderStatus->whatsappTemplate);
                        }
                        
                        // SMS
                        if ($orderModel->orderStatus->smsTemplate && !empty($posOrder['customer_phone'])) {
                            app(\App\Services\TwilioSmsService::class)->sendDynamicSms($orderModel, $orderModel->orderStatus->smsTemplate);
                        }
                        
                        // Email (Skip if dummy email generated by POS)
                        if ($orderModel->orderStatus->emailTemplate && $orderModel->user && $orderModel->user->email) {
                            if (!str_ends_with($orderModel->user->email, '@vyorapos.local')) {
                                \Illuminate\Support\Facades\Mail::to($orderModel->user->email)
                                    ->send(new \App\Mail\DynamicOrderStatusEmail($orderModel, $orderModel->orderStatus->emailTemplate));
                            }
                        }
                    } else if (!empty($posOrder['customer_phone'])) {
                        // Fallback to hardcoded event if no status configured
                        app(\App\Services\WhatsAppService::class)->sendEventWhatsApp('confirmed', $orderModel);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("POS Notification Error: " . $e->getMessage());
                }

                $processedCount++;
            }
            DB::commit();
            
            return response()->json(['success' => true, 'processed' => $processedCount]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search orders by Phone Number or Receipt Barcode (Order Number)
     */
    public function searchOrders(Request $request)
    {
        $query = $request->query('query');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $orders = DB::table('orders')
            ->leftJoin('pos_locations', 'orders.pos_location_id', '=', 'pos_locations.id')
            ->where('orders.customer_phone', 'LIKE', "%{$query}%")
            ->orWhere('orders.order_number', $query)
            ->select('orders.*', 'pos_locations.name as location_name')
            ->orderBy('orders.created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($orders as $order) {
            $items = DB::table('order_items')->where('order_id', $order->id)->get();
            foreach ($items as $item) {
                $item->gallery = [];
                if ($item->image_url) {
                    $item->gallery[] = $item->image_url;
                }
                
                // If it's a known product/sku, try to fetch all images for that specific color
                if ($item->product_id && $item->sku_id) {
                    $sku = DB::table('skus')->where('id', $item->sku_id)->first();
                    if ($sku && $sku->color_id) {
                        $images = DB::table('product_images')
                            ->where('product_id', $item->product_id)
                            ->where('color_id', $sku->color_id)
                            ->orderBy('sort_order')
                            ->get();
                            
                        $gallery = [];
                        foreach ($images as $img) {
                            $path = $img->image_path;
                            if (str_starts_with($path, 'http')) {
                                $gallery[] = $path;
                            } else {
                                $cleanPath = ltrim($path, '/');
                                $gallery[] = (str_starts_with($cleanPath, 'storage/') || str_starts_with($cleanPath, 'uploads/'))
                                    ? asset($cleanPath)
                                    : asset('storage/' . $cleanPath);
                            }
                        }
                        if (count($gallery) > 0) {
                            $item->gallery = $gallery;
                            if (!$item->image_url) {
                                $item->image_url = $gallery[0];
                            }
                        }
                    }
                }
            }
            $order->items = $items;
        }

        return response()->json($orders);
    }

    /**
     * Process a partial or full refund for a POS order
     */
    public function processRefund(Request $request, $orderId)
    {
        $payload = $request->validate([
            'location_id' => 'required|integer',
            'items' => 'required|array', // Returning items
            'items.*.id' => 'required|integer',
            'items.*.return_qty' => 'required|integer|min:1',
            'exchange_items' => 'nullable|array', // New items being bought
            'exchange_items.*.sku_id' => 'required|integer',
            'exchange_items.*.qty' => 'required|integer|min:1',
            'exchange_items.*.price' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            $order = DB::table('orders')->where('id', $orderId)->first();
            if (!$order) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            $totalRefundAmount = 0;

            foreach ($payload['items'] as $returnItem) {
                $orderItem = DB::table('order_items')->where('id', $returnItem['id'])->where('order_id', $orderId)->first();
                if (!$orderItem) continue;

                $maxReturnable = $orderItem->quantity - ($orderItem->returned_quantity ?? 0);
                $qtyToReturn = min($returnItem['return_qty'], $maxReturnable);

                if ($qtyToReturn <= 0) continue;

                // Refund the exact effective price paid (accounts for proportional discounts)
                $refundAmount = $orderItem->price * $qtyToReturn;
                $totalRefundAmount += $refundAmount;

                // Mark as returned
                DB::table('order_items')
                    ->where('id', $orderItem->id)
                    ->update([
                        'returned_quantity' => ($orderItem->returned_quantity ?? 0) + $qtyToReturn
                    ]);

                // Restock inventory at the CURRENT location (where the return is happening)
                // Assuming we use pos_market_pricing for location-specific stock or global skus stock.
                // Looking at submitOrder, you decrement skus->stock. We will increment here.
                DB::table('skus')->where('id', $orderItem->sku_id)->increment('stock', $qtyToReturn);
                DB::table('pos_market_pricing')
                    ->where('pos_location_id', $payload['location_id'])
                    ->where('sku_id', $orderItem->sku_id)
                    ->increment('stock', $qtyToReturn);
            }

            // Update original order
            DB::table('orders')->where('id', $orderId)->update([
                'amount_refunded' => ($order->amount_refunded ?? 0) + $totalRefundAmount,
                'updated_at' => now()
            ]);

            // Determine if old order is fully or partially returned/exchanged
            $totalQuantityOrdered = DB::table('order_items')->where('order_id', $orderId)->sum('quantity');
            $totalReturnedQuantity = DB::table('order_items')->where('order_id', $orderId)->sum('returned_quantity');
            
            $newStatusId = null;
            if ($totalReturnedQuantity == $totalQuantityOrdered) {
                // Full Exchange / Return
                $newStatusId = empty($payload['exchange_items']) ? 10 : 13; // 10=Returned, 13=Exchanged
            } else if ($totalReturnedQuantity > 0) {
                // Partial Exchange / Return
                $newStatusId = 12; // 12=Partially Returned
            }

            if ($newStatusId && $order->order_status_id != $newStatusId) {
                $statusModel = \App\Models\OrderStatus::with(['smsTemplate', 'emailTemplate', 'whatsappTemplate'])->find($newStatusId);
                if ($statusModel) {
                    DB::table('orders')->where('id', $orderId)->update([
                        'order_status_id' => $newStatusId,
                        'status' => strtolower($statusModel->name),
                        'updated_at' => now()
                    ]);
                    
                    // Fetch full order model to pass to the notification trait
                    $fullOrderModel = \App\Models\Order::with(['user', 'shippingAddress'])->find($orderId);
                    if ($fullOrderModel) {
                        $this->fireOrderStatusNotifications($fullOrderModel, $statusModel);
                    }
                }
            }

            // Process Exchange Items (New Purchase)
            $newOrderTotal = 0;
            $newOrderId = null;
            $exchangeItems = $payload['exchange_items'] ?? [];

            if (count($exchangeItems) > 0) {
                foreach ($exchangeItems as $exItem) {
                    $newOrderTotal += ($exItem['price'] * $exItem['qty']);
                }

                $uuid = \Illuminate\Support\Str::uuid()->toString();
                $orderNumber = 'EXC-' . strtoupper(substr(uniqid(), -6));
                
                $amountPaid = max(0, $newOrderTotal - $totalRefundAmount);
                $exchangeCreditApplied = min($newOrderTotal, $totalRefundAmount);

                $newOrderId = DB::table('orders')->insertGetId([
                    'uuid' => $uuid,
                    'user_id' => $order->user_id,
                    'order_number' => $orderNumber,
                    'pos_location_id' => $payload['location_id'],
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone,
                    'source' => 'pos',
                    'status' => 'delivered',
                    'payment_status' => 'paid',
                    'payment_method' => 'exchange',
                    'total_amount' => $newOrderTotal,
                    'amount_paid' => $amountPaid,
                    'discount_amount' => $exchangeCreditApplied, // Using discount_amount to represent credit applied
                    'notes' => "Exchange against Order #{$order->order_number}",
                    'order_status_id' => $order->order_status_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($exchangeItems as $exItem) {
                    $sku = DB::table('skus')->where('id', $exItem['sku_id'])->first();
                    $product = DB::table('products')->where('id', $sku->product_id)->first();

                    DB::table('order_items')->insert([
                        'order_id' => $newOrderId,
                        'product_id' => $product->id,
                        'sku_id' => $sku->id,
                        'product_name' => $product->name,
                        'quantity' => $exItem['qty'],
                        'price' => $exItem['price'],
                        'total' => $exItem['price'] * $exItem['qty'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('skus')->where('id', $sku->id)->decrement('stock', $exItem['qty']);
                    DB::table('pos_market_pricing')
                        ->where('pos_location_id', $payload['location_id'])
                        ->where('sku_id', $sku->id)
                        ->decrement('stock', $exItem['qty']);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true, 
                'refunded_amount' => $totalRefundAmount,
                'new_order_id' => $newOrderId,
                'new_order_total' => $newOrderTotal
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

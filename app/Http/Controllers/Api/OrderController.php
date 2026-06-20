<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sku;
use App\Models\Coupon;
use App\Models\ThemeSetting;
use App\Services\GiftCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $orders = Order::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('shippingAddress', function($q) use ($user) {
                          $q->where('email', $user->email);
                      });
            })
            ->with(['items' => function($query) {
                $query->select(['id', 'order_id', 'product_name', 'variant_name', 'image_url', 'quantity', 'price']);
            }])
            ->latest()
            ->paginate(10);

        $orders->getCollection()->transform(function($order) {
            $order->items_count   = $order->items->sum('quantity');
            $order->tracking_url  = $order->tracking_url;
            $order->has_tracking  = $order->has_tracking;
            $order->courier_partner = $order->courier_partner;
            return $order;
        });

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer.name' => 'required',
            'customer.email' => 'required|email',
            'customer.phone' => 'required',

            'address.line1' => 'required',
            'address.city' => 'required',
            'address.state' => 'required',
            'address.zip' => 'required',

            'items' => 'required|array',
            'items.*.sku_id' => 'required|exists:skus,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $order = DB::transaction(function () use ($request) {
                // 1. Create Address
                $addressData = $request->input('address');
                $customerData = $request->input('customer');

                $address = Address::firstOrCreate([
                    'user_id' => $request->user()?->id, // If authenticated
                    'name' => $customerData['name'],
                    'email' => $customerData['email'],
                    'phone' => $customerData['phone'],
                    'address_line1' => $addressData['line1'],
                    'address_line2' => $addressData['line2'] ?? null,
                    'city' => $addressData['city'],
                    'state' => $addressData['state'],
                    'zip_code' => $addressData['zip'],
                    'type' => 'shipping',
                ]);
                \Log::info('Created address after firstOrCreate:', ['address' => $address->toArray(), 'id' => $address->id]);

                // 2. Calculate Total & Create Order
                $subtotal = 0;
                $orderItemsData = [];
                $skusToProcess = [];

                // Validate and calculate subtotal
                foreach ($request->input('items') as $item) {
                    $sku = Sku::lockForUpdate()->with('product')->find($item['sku_id']);
                    if (!$sku || $sku->stock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for SKU " . ($sku ? $sku->code : $item['sku_id']));
                    }
                    $lineTotal = $sku->price * $item['quantity'];
                    $subtotal += $lineTotal;

                    $skusToProcess[] = [
                        'sku' => $sku,
                        'qty' => $item['quantity'],
                        'price' => $sku->price,
                        'total' => $lineTotal,
                        'image_url' => $item['image'] ?? null
                    ];
                }

                // Apply Coupon
                $discountAmount = 0;
                $couponCode = $request->input('coupon_code');
                if ($couponCode) {
                    $coupon = Coupon::where('code', $couponCode)->where('is_active', true)->first();
                    if ($coupon) {
                        $couponService = app(\App\Services\CouponService::class);
                        $cartDataForCoupon = [
                            'subtotal' => $subtotal,
                            'items' => array_map(function ($data) {
                                return [
                                    'product_id' => $data['sku']->product_id,
                                    'price' => $data['price'],
                                    'original_price' => $data['sku']->mrp ?? $data['price'],
                                    'quantity' => $data['qty'],
                                ];
                            }, $skusToProcess)
                        ];

                        $couponResult = $couponService->validateAndCalculate($coupon, $cartDataForCoupon, $request->user());
                        if ($couponResult['valid']) {
                            $discountAmount = $couponResult['discount_amount'];
                        }
                    }
                }

                $subtotalAfterDiscount = max(0, $subtotal - $discountAmount);

                // Fetch Tax & Shipping Settings
                $settings = \App\Models\ThemeSetting::where('group', 'tax_shipping')->pluck('value', 'key');
                $taxInclusive = ($settings->get('tax_inclusion') ?? 'exclude') === 'include';
                $isTaxEnabled = ($settings->get('is_tax_enabled') ?? '1') === '1';
                $taxes = json_decode($settings->get('taxes') ?? '[]', true);
                $shippingRules = json_decode($settings->get('shipping_rules') ?? '{}', true);

                // Calculate Shipping & Prepaid Discount
                $isCod = $request->input('payment_method') === 'cod';
                $shippingAmount = 0;
                $prepaidDiscount = 0;
                $activeRule = $isCod ? ($shippingRules['cod'] ?? null) : ($shippingRules['prepaid'] ?? null);

                if ($activeRule) {
                    if (($activeRule['type'] ?? '') === 'flat') {
                        $shippingAmount = floatval($activeRule['fee'] ?? 0);
                    } elseif (($activeRule['type'] ?? '') === 'conditional') {
                        if ($subtotalAfterDiscount >= floatval($activeRule['threshold'] ?? 0)) {
                            $shippingAmount = 0;
                        } else {
                            $shippingAmount = floatval($activeRule['fee'] ?? 0);
                        }
                    } elseif (($activeRule['type'] ?? '') === 'tiered') {
                        $tiers = $activeRule['tiers'] ?? [];
                        $matchedFee = 0;
                        $applied = false;
                        foreach ($tiers as $t) {
                            if ($subtotalAfterDiscount <= floatval($t['up_to'] ?? 0)) {
                                $matchedFee = floatval($t['fee'] ?? 0);
                                $applied = true;
                                break;
                            }
                        }
                        if (!$applied && count($tiers) > 0) {
                            $matchedFee = floatval($tiers[count($tiers) - 1]['fee'] ?? 0);
                        }
                        $shippingAmount = $matchedFee;
                    }

                    if (!$isCod) {
                        if (($activeRule['discount_type'] ?? '') === 'percent') {
                            $prepaidDiscount = ($subtotalAfterDiscount * floatval($activeRule['discount_value'] ?? 0)) / 100;
                        } elseif (($activeRule['discount_type'] ?? '') === 'flat') {
                            $prepaidDiscount = floatval($activeRule['discount_value'] ?? 0);
                        }
                    }
                }

                // Apply Gift Card
                $giftCardDiscount = 0;
                $giftCardCode = $request->input('gift_card_code');
                $giftCardService = app(GiftCardService::class);

                if ($giftCardCode) {
                    $giftCardValidation = \App\Models\GiftCard::whereIn('status', ['active', 'partially_used'])->get()
                        ->first(fn($gc) => $gc->plain_code === strtoupper($giftCardCode));

                    if ($giftCardValidation && $giftCardValidation->isRedeemable()) {
                        if ($giftCardValidation->assigned_to && $giftCardValidation->assigned_to !== $request->user()?->id) {
                            throw new \Exception("This gift card is not assigned to your account.");
                        }
                        $giftCardDiscount = min($giftCardValidation->remaining_amount, $subtotalAfterDiscount + $shippingAmount - $prepaidDiscount);
                    }
                }

                // TAX BREAKDOWN CALCULATION
                $taxBreakdown = [];
                $totalTaxAmount = 0;
                $trueSubtotalAfterDiscount = 0;
                $trueSubtotalBeforeDiscount = 0;

                foreach ($skusToProcess as &$data) {
                    $itemDiscount = $subtotal > 0 ? ($data['total'] / $subtotal) * $discountAmount : 0;
                    $itemFinal = $data['total'] - $itemDiscount;
                    $itemTax = 0;

                    if ($isTaxEnabled) {
                        $taxRate = 0;
                        if ($data['sku']->product && $data['sku']->product->tax_class) {
                            $t = collect($taxes)->firstWhere('id', $data['sku']->product->tax_class);
                            if ($t) $taxRate = floatval($t['rate']);
                        }

                        if ($taxRate > 0) {
                            if ($taxInclusive) {
                                $trueItemFinal = $itemFinal / (1 + ($taxRate / 100));
                                $trueItemTotal = $data['total'] / (1 + ($taxRate / 100));
                                $itemTax = $itemFinal - $trueItemFinal;
                            } else {
                                $trueItemFinal = $itemFinal;
                                $trueItemTotal = $data['total'];
                                $itemTax = $itemFinal * ($taxRate / 100);
                            }
                            
                            $rateKey = (string)$taxRate;
                            if (!isset($taxBreakdown[$rateKey])) $taxBreakdown[$rateKey] = 0;
                            $taxBreakdown[$rateKey] += $itemTax;
                            $totalTaxAmount += $itemTax;
                        } else {
                            $trueItemFinal = $itemFinal;
                            $trueItemTotal = $data['total'];
                        }
                        
                        $trueSubtotalAfterDiscount += $trueItemFinal;
                        $trueSubtotalBeforeDiscount += $trueItemTotal;
                    } else {
                        $trueSubtotalAfterDiscount += $itemFinal;
                        $trueSubtotalBeforeDiscount += $data['total'];
                    }

                    $data['tax_amount'] = $itemTax;
                    $data['sku']->decrement('stock', $data['qty']);
                    $orderItemsData[] = $data;
                }

                $trueShippingAmount = $shippingAmount;
                if ($isTaxEnabled && $shippingAmount > 0) {
                    $shippingTaxRate = floatval($settings->get('shipping_tax_rate') ?? '18');
                    if ($shippingTaxRate > 0) {
                        if ($taxInclusive) {
                            $trueShippingAmount = $shippingAmount / (1 + ($shippingTaxRate / 100));
                            $shippingTax = $shippingAmount - $trueShippingAmount;
                        } else {
                            $trueShippingAmount = $shippingAmount;
                            $shippingTax = $shippingAmount * ($shippingTaxRate / 100);
                        }
                        $rateKey = (string)$shippingTaxRate;
                        if (!isset($taxBreakdown[$rateKey])) $taxBreakdown[$rateKey] = 0;
                        $taxBreakdown[$rateKey] += $shippingTax;
                        $totalTaxAmount += $shippingTax;
                    }
                }

                $totalAmount = $trueSubtotalAfterDiscount + $trueShippingAmount + $totalTaxAmount - $prepaidDiscount - $giftCardDiscount;
                $totalAmount = max(0, $totalAmount);

                $order = Order::create([
                    'user_id' => $request->user()?->id,
                    'status' => ($totalAmount <= 0 || $isCod) ? 'processing' : 'pending',
                    'payment_status' => $totalAmount <= 0 ? 'paid' : 'pending',
                    'payment_method' => $totalAmount <= 0 ? 'Gift Card/Coupon' : ($isCod ? 'COD' : 'Prepaid'),
                    'total_amount' => $totalAmount,
                    'shipping_amount' => $shippingAmount,
                    'tax_amount' => $totalTaxAmount,
                    'tax_breakdown' => json_encode($taxBreakdown),
                    'discount_amount' => $discountAmount + $prepaidDiscount + $giftCardDiscount,
                    'coupon_code' => $couponCode,
                    'shipping_address_id' => $address->id,
                    'billing_address_id' => $address->id,
                ]);

                // Redeem gift card if valid
                if ($giftCardCode && $giftCardDiscount > 0) {
                    $giftCardService->validateAndRedeem(
                        plainCode: $giftCardCode,
                        requestedAmount: $giftCardDiscount,
                        performedBy: $request->user()?->id,
                        orderId: $order->id,
                    );
                }

                // 3. Create Order Items
                foreach ($orderItemsData as $data) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $data['sku']->product_id,
                        'sku_id' => $data['sku']->id,
                        'product_name' => $data['sku']->product->name,
                        'variant_name' => '', // TODO: Generate variant name
                        'image_url' => $data['image_url'],
                        'quantity' => $data['qty'],
                        'price' => $data['price'],
                        'total' => $data['total'],
                    ]);
                }

                return $order;
            });

            // Dispatch Qikink order processing asynchronously if possible, or inline
            $qikinkService = app(\App\Services\QikinkOrderService::class);
            $qikinkService->processOrder($order);

            // Dispatch Twilio SMS for COD/Free orders (already confirmed)
            if ($order->status === 'processing') {
                app(\App\Services\TwilioSmsService::class)->sendEventSms('confirmed', $order);
                app(\App\Services\WhatsAppService::class)->sendEventWhatsApp('confirmed', $order);
            }

            return response()->json([
                'success' => true,
                'order_uuid' => $order->uuid,
                'message' => 'Order placed successfully!'
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function show(Request $request, $uuid)
    {
        $user = $request->user();
        
        $order = Order::where('uuid', $uuid)
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('shippingAddress', function($q) use ($user) {
                          $q->where('email', $user->email);
                      });
            })
            ->with(['items.sku.product', 'shippingAddress'])
            ->firstOrFail();

        $data = $order->toArray();
        $data['tracking_url']    = $order->tracking_url;
        $data['tracking_number'] = $order->tracking_number;
        $data['courier_partner'] = $order->courier_partner;
        $data['has_tracking']    = $order->has_tracking;
        $data['shipping_address'] = $order->shippingAddress;
        $data['tax_breakdown'] = json_decode($order->tax_breakdown ?? '{}', true);

        return response()->json($data);
    }
}

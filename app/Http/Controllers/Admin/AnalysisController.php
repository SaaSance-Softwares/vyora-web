<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\Sku;
use App\Models\ThemeSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalysisController extends Controller
{
    /**
     * Resolve start/end dates and preset label from request.
     */
    protected function resolveDateRange(Request $request): array
    {
        $now = Carbon::now();
        $preset = $request->input('preset', 'last_30_days');

        if ($request->filled('start_date') && $request->filled('end_date') && $preset === 'custom') {
            $start = Carbon::parse($request->input('start_date'))->startOfDay();
            $end = Carbon::parse($request->input('end_date'))->endOfDay();
        } else {
            switch ($preset) {
                case 'today':
                    $start = $now->copy()->startOfDay();
                    $end = $now->copy()->endOfDay();
                    break;
                case 'yesterday':
                    $start = $now->copy()->subDay()->startOfDay();
                    $end = $now->copy()->subDay()->endOfDay();
                    break;
                case 'last_7_days':
                    $start = $now->copy()->subDays(6)->startOfDay();
                    $end = $now->copy()->endOfDay();
                    break;
                case 'last_30_days':
                    $start = $now->copy()->subDays(29)->startOfDay();
                    $end = $now->copy()->endOfDay();
                    break;
                case 'this_month':
                    $start = $now->copy()->startOfMonth();
                    $end = $now->copy()->endOfMonth();
                    break;
                case 'last_month':
                    $start = $now->copy()->subMonth()->startOfMonth();
                    $end = $now->copy()->subMonth()->endOfMonth();
                    break;
                case 'this_quarter':
                    $start = $now->copy()->startOfQuarter();
                    $end = $now->copy()->endOfQuarter();
                    break;
                case 'this_year':
                    $start = $now->copy()->startOfYear();
                    $end = $now->copy()->endOfYear();
                    break;
                default:
                    $preset = 'last_30_days';
                    $start = $now->copy()->subDays(29)->startOfDay();
                    $end = $now->copy()->endOfDay();
                    break;
            }
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        $daysDiff = max(1, (int) round($start->diffInDays($end)) + 1);
        $prevEnd = $start->copy()->subSecond();
        $prevStart = $prevEnd->copy()->subDays($daysDiff - 1)->startOfDay();

        return [
            'preset' => $preset,
            'start' => $start,
            'end' => $end,
            'startDateInput' => $start->toDateString(),
            'endDateInput' => $end->toDateString(),
            'prevStart' => $prevStart,
            'prevEnd' => $prevEnd,
            'daysDiff' => $daysDiff,
        ];
    }

    /**
     * Apply channel filter to an orders query.
     */
    protected function applyChannelFilter($query, string $channel, string $tablePrefix = 'orders')
    {
        if ($channel === 'online') {
            $query->whereNull("{$tablePrefix}.pos_location_id");
        } elseif ($channel === 'pos') {
            $query->whereNotNull("{$tablePrefix}.pos_location_id");
        } elseif (str_starts_with($channel, 'pos_')) {
            $locId = (int) substr($channel, 4);
            $query->where("{$tablePrefix}.pos_location_id", $locId);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $dateInfo = $this->resolveDateRange($request);
        $start = $dateInfo['start'];
        $end = $dateInfo['end'];
        $prevStart = $dateInfo['prevStart'];
        $prevEnd = $dateInfo['prevEnd'];
        $preset = $dateInfo['preset'];
        $startDateInput = $dateInfo['startDateInput'];
        $endDateInput = $dateInfo['endDateInput'];
        $daysDiff = $dateInfo['daysDiff'];

        $channel = $request->input('channel', 'all');
        $currencySymbol = ThemeSetting::where('key', 'currency_symbol')->value('value') ?? '₹';
        $posLocations = DB::table('pos_locations')->select('id', 'name')->get();

        // ------------------------------------------------------------------
        // 1. BASE QUERIES & EXECUTIVE FINANCIAL OVERVIEW
        // ------------------------------------------------------------------
        $allOrdersQuery = Order::whereBetween('orders.created_at', [$start, $end]);
        $this->applyChannelFilter($allOrdersQuery, $channel, 'orders');

        $validOrdersQuery = (clone $allOrdersQuery)->whereNotIn('orders.status', ['cancelled', 'failed']);

        // Previous Period Valid Orders for PoP comparison
        $prevAllOrdersQuery = Order::whereBetween('orders.created_at', [$prevStart, $prevEnd]);
        $this->applyChannelFilter($prevAllOrdersQuery, $channel, 'orders');
        $prevValidOrdersQuery = (clone $prevAllOrdersQuery)->whereNotIn('orders.status', ['cancelled', 'failed']);

        $totalOrdersPlaced = (clone $allOrdersQuery)->count();
        $validOrdersCount = (clone $validOrdersQuery)->count();
        $cancelledOrdersCount = (clone $allOrdersQuery)->where('orders.status', 'cancelled')->count();
        $cancelledOrdersValue = (float) (clone $allOrdersQuery)->where('orders.status', 'cancelled')->sum('orders.total_amount');

        $returnedOrdersCount = (clone $allOrdersQuery)->whereIn('orders.status', ['returned', 'refunded', 'exchanged'])->count();

        // Gross Item Sales (sum of item price * qty before order discounts/shipping/tax) on valid orders
        $grossItemSales = (float) DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereNotIn('orders.status', ['cancelled', 'failed'])
            ->when($channel !== 'all', function ($q) use ($channel) {
                $this->applyChannelFilter($q, $channel, 'orders');
            })
            ->selectRaw('COALESCE(SUM(order_items.price * order_items.quantity), 0) as gross_sales')
            ->value('gross_sales');

        // Discount Breakdowns on valid orders
        $discountStats = (clone $validOrdersQuery)->selectRaw('
            COALESCE(SUM(discount_amount), 0) as total_discount,
            COALESCE(SUM(coupon_discount_amount), 0) as coupon_discount,
            COALESCE(SUM(prepaid_discount_amount), 0) as prepaid_discount,
            COALESCE(SUM(gift_card_discount_amount), 0) as gift_card_discount,
            SUM(CASE WHEN discount_amount > 0 THEN 1 ELSE 0 END) as discounted_orders_count,
            SUM(CASE WHEN (coupon_discount_amount > 0 OR (coupon_code IS NOT NULL AND coupon_code != "")) THEN 1 ELSE 0 END) as coupon_orders_count,
            SUM(CASE WHEN prepaid_discount_amount > 0 THEN 1 ELSE 0 END) as prepaid_discount_orders_count,
            SUM(CASE WHEN gift_card_discount_amount > 0 THEN 1 ELSE 0 END) as gift_card_orders_count,
            COALESCE(SUM(shipping_amount), 0) as total_shipping,
            COALESCE(SUM(tax_amount), 0) as total_tax,
            COALESCE(SUM(total_amount), 0) as gross_order_total,
            COALESCE(SUM(amount_refunded), 0) as total_refunded,
            COALESCE(SUM(balance_due), 0) as total_balance_due
        ')->first();

        $totalDiscount = (float) ($discountStats->total_discount ?? 0);
        $couponDiscount = (float) ($discountStats->coupon_discount ?? 0);
        $prepaidDiscount = (float) ($discountStats->prepaid_discount ?? 0);
        $giftCardDiscount = (float) ($discountStats->gift_card_discount ?? 0);

        // If older orders used coupon_code before coupon_discount_amount was populated, attribute remainder to coupon/manual
        $otherManualDiscount = max(0, $totalDiscount - ($couponDiscount + $prepaidDiscount + $giftCardDiscount));

        $grossOrderTotal = (float) ($discountStats->gross_order_total ?? 0);
        $totalRefunded = (float) ($discountStats->total_refunded ?? 0);
        $netRevenue = max(0, $grossOrderTotal - $totalRefunded);
        $totalShipping = (float) ($discountStats->total_shipping ?? 0);
        $totalTax = (float) ($discountStats->total_tax ?? 0);
        $totalBalanceDue = (float) ($discountStats->total_balance_due ?? 0);
        $aov = $validOrdersCount > 0 ? ($grossOrderTotal / $validOrdersCount) : 0;
        $discountRatePercent = $grossItemSales > 0 ? (($totalDiscount / $grossItemSales) * 100) : 0;

        // Online vs POS Split
        $onlineSales = (float) (clone $validOrdersQuery)->whereNull('orders.pos_location_id')->sum('orders.total_amount');
        $posSales = (float) (clone $validOrdersQuery)->whereNotNull('orders.pos_location_id')->sum('orders.total_amount');

        // Previous Period Comparison Metrics
        $prevValidCount = (clone $prevValidOrdersQuery)->count();
        $prevGrossOrderTotal = (float) (clone $prevValidOrdersQuery)->sum('orders.total_amount');
        $prevRefunded = (float) (clone $prevValidOrdersQuery)->sum('orders.amount_refunded');
        $prevNetRevenue = max(0, $prevGrossOrderTotal - $prevRefunded);
        $prevAov = $prevValidCount > 0 ? ($prevGrossOrderTotal / $prevValidCount) : 0;
        $prevTotalDiscount = (float) (clone $prevValidOrdersQuery)->sum('orders.discount_amount');

        $pctChange = function (float $current, float $previous): ?float {
            if ($previous <= 0) {
                return $current > 0 ? 100.0 : null;
            }

            return round((($current - $previous) / $previous) * 100, 1);
        };

        $comparisons = [
            'net_revenue' => $pctChange($netRevenue, $prevNetRevenue),
            'valid_orders' => $pctChange($validOrdersCount, $prevValidCount),
            'aov' => $pctChange($aov, $prevAov),
            'total_discount' => $pctChange($totalDiscount, $prevTotalDiscount),
        ];

        // ------------------------------------------------------------------
        // 2. COUPON LEADERBOARD ("WHICH COUPON WAS USED")
        // ------------------------------------------------------------------
        $couponBreakdown = (clone $validOrdersQuery)
            ->whereNotNull('orders.coupon_code')
            ->where('orders.coupon_code', '!=', '')
            ->selectRaw('
                UPPER(TRIM(orders.coupon_code)) as code,
                COUNT(orders.id) as orders_count,
                COALESCE(SUM(CASE WHEN orders.coupon_discount_amount > 0 THEN orders.coupon_discount_amount ELSE GREATEST(0, orders.discount_amount - orders.prepaid_discount_amount - orders.gift_card_discount_amount) END), 0) as coupon_discount_total,
                COALESCE(SUM(orders.prepaid_discount_amount), 0) as prepaid_discount_stacked,
                COALESCE(SUM(orders.discount_amount), 0) as total_discount_given,
                COALESCE(SUM(orders.total_amount), 0) as total_revenue,
                COALESCE(AVG(orders.total_amount), 0) as avg_order_value
            ')
            ->groupBy(DB::raw('UPPER(TRIM(orders.coupon_code))'))
            ->orderByDesc('orders_count')
            ->get();

        // Enrich with coupon metadata if available
        $couponModels = DB::table('coupons')
            ->select('code', 'type', 'discount_amount')
            ->get()
            ->keyBy(fn ($c) => strtoupper(trim($c->code)));

        $couponBreakdown->transform(function ($row) use ($couponModels) {
            $meta = $couponModels->get($row->code);
            $row->coupon_type = $meta->type ?? null;
            $row->coupon_value = $meta->discount_amount ?? null;

            return $row;
        });

        // ------------------------------------------------------------------
        // 3. SKU PURCHASE COST (COGS) & APPROX. GROSS PROFIT ANALYSIS
        // ------------------------------------------------------------------
        // Pro-rate order-level discount across items based on item share of order subtotal
        $skuRows = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('skus', 'order_items.sku_id', '=', 'skus.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('colors', 'skus.color_id', '=', 'colors.id')
            ->leftJoin('sizes', 'skus.size_id', '=', 'sizes.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereNotIn('orders.status', ['cancelled', 'failed'])
            ->when($channel !== 'all', function ($q) use ($channel) {
                $this->applyChannelFilter($q, $channel, 'orders');
            })
            ->selectRaw('
                order_items.sku_id,
                order_items.product_id,
                COALESCE(products.name, order_items.product_name) as product_name,
                skus.code as sku_code,
                skus.short_code,
                colors.name as color_name,
                sizes.name as size_name,
                order_items.variant_name,
                COALESCE(skus.purchase_price, skus.cost_price) as unit_purchase_cost,
                skus.price as current_sku_price,
                skus.stock as current_stock,
                SUM(order_items.quantity) as gross_qty_sold,
                SUM(COALESCE(order_items.returned_quantity, 0)) as returned_qty,
                SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0))) as net_qty_sold,
                AVG(order_items.price) as avg_sold_price,
                SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) * order_items.price) as net_sold_revenue,
                SUM(
                    CASE
                        WHEN (orders.total_amount + orders.discount_amount - orders.shipping_amount - orders.tax_amount) > 0
                        THEN (
                            (GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) * order_items.price)
                            / (orders.total_amount + orders.discount_amount - orders.shipping_amount - orders.tax_amount)
                        ) * orders.discount_amount
                        ELSE 0
                    END
                ) as allocated_discount
            ')
            ->groupBy(
                'order_items.sku_id',
                'order_items.product_id',
                'products.name',
                'order_items.product_name',
                'skus.code',
                'skus.short_code',
                'colors.name',
                'sizes.name',
                'order_items.variant_name',
                'skus.purchase_price',
                'skus.cost_price',
                'skus.price',
                'skus.stock'
            )
            ->orderByDesc('net_qty_sold')
            ->get();

        $totalNetUnitsSold = 0;
        $trackedUnitsSold = 0;
        $untrackedUnitsSold = 0;
        $trackedSkusCount = 0;
        $untrackedSkusCount = 0;
        $trackedSoldValueBeforeDiscount = 0.0;
        $trackedSoldValueAfterDiscount = 0.0;
        $totalPurchaseCost = 0.0;

        $skuProfitability = $skuRows->map(function ($row) use (
            &$totalNetUnitsSold,
            &$trackedUnitsSold,
            &$untrackedUnitsSold,
            &$trackedSkusCount,
            &$untrackedSkusCount,
            &$trackedSoldValueBeforeDiscount,
            &$trackedSoldValueAfterDiscount,
            &$totalPurchaseCost
        ) {
            $netQty = (int) $row->net_qty_sold;
            $grossSoldVal = (float) $row->net_sold_revenue;
            $allocDiscount = min($grossSoldVal, max(0.0, (float) $row->allocated_discount));
            $netSoldValAfterDiscount = max(0.0, $grossSoldVal - $allocDiscount);

            $unitCost = $row->unit_purchase_cost !== null ? (float) $row->unit_purchase_cost : null;
            $hasCost = $unitCost !== null && $unitCost > 0;

            $totalNetUnitsSold += $netQty;

            if ($hasCost) {
                $trackedSkusCount++;
                $trackedUnitsSold += $netQty;
                $skuTotalCost = $netQty * $unitCost;
                $totalPurchaseCost += $skuTotalCost;
                $trackedSoldValueBeforeDiscount += $grossSoldVal;
                $trackedSoldValueAfterDiscount += $netSoldValAfterDiscount;

                $profitBeforeDiscount = $grossSoldVal - $skuTotalCost;
                $profitAfterDiscount = $netSoldValAfterDiscount - $skuTotalCost;
                $marginBeforeDiscount = $grossSoldVal > 0 ? (($profitBeforeDiscount / $grossSoldVal) * 100) : 0;
                $marginAfterDiscount = $netSoldValAfterDiscount > 0 ? (($profitAfterDiscount / $netSoldValAfterDiscount) * 100) : 0;
            } else {
                $untrackedSkusCount++;
                $untrackedUnitsSold += $netQty;
                $skuTotalCost = null;
                $profitBeforeDiscount = null;
                $profitAfterDiscount = null;
                $marginBeforeDiscount = null;
                $marginAfterDiscount = null;
            }

            $variantLabel = trim(($row->color_name ?? '') . ($row->size_name ? ' / ' . $row->size_name : ''), ' /');
            if ($variantLabel === '' && ! empty($row->variant_name)) {
                $variantLabel = $row->variant_name;
            }

            $row->variant_label = $variantLabel ?: 'Standard';
            $row->has_cost = $hasCost;
            $row->unit_cost = $unitCost;
            $row->total_purchase_cost = $skuTotalCost;
            $row->net_sold_after_discount = $netSoldValAfterDiscount;
            $row->allocated_discount = $allocDiscount;
            $row->profit_before_discount = $profitBeforeDiscount;
            $row->profit_after_discount = $profitAfterDiscount;
            $row->margin_before_discount = $marginBeforeDiscount;
            $row->margin_after_discount = $marginAfterDiscount;

            return $row;
        });

        $approxGrossProfitBeforeDiscount = $trackedSoldValueBeforeDiscount - $totalPurchaseCost;
        $approxGrossProfitAfterDiscount = $trackedSoldValueAfterDiscount - $totalPurchaseCost;
        $grossMarginBeforeDiscountPct = $trackedSoldValueBeforeDiscount > 0
            ? (($approxGrossProfitBeforeDiscount / $trackedSoldValueBeforeDiscount) * 100)
            : 0;
        $grossMarginAfterDiscountPct = $trackedSoldValueAfterDiscount > 0
            ? (($approxGrossProfitAfterDiscount / $trackedSoldValueAfterDiscount) * 100)
            : 0;
        $costCoveragePct = $totalNetUnitsSold > 0
            ? round(($trackedUnitsSold / $totalNetUnitsSold) * 100, 1)
            : 0;

        $profitSummary = [
            'total_net_units_sold' => $totalNetUnitsSold,
            'tracked_units_sold' => $trackedUnitsSold,
            'untracked_units_sold' => $untrackedUnitsSold,
            'tracked_skus_count' => $trackedSkusCount,
            'untracked_skus_count' => $untrackedSkusCount,
            'cost_coverage_pct' => $costCoveragePct,
            'tracked_sold_before_discount' => $trackedSoldValueBeforeDiscount,
            'tracked_sold_after_discount' => $trackedSoldValueAfterDiscount,
            'total_purchase_cost' => $totalPurchaseCost,
            'approx_profit_before_discount' => $approxGrossProfitBeforeDiscount,
            'approx_profit_after_discount' => $approxGrossProfitAfterDiscount,
            'margin_before_discount_pct' => $grossMarginBeforeDiscountPct,
            'margin_after_discount_pct' => $grossMarginAfterDiscountPct,
        ];

        // ------------------------------------------------------------------
        // 4. OPERATIONS, FULFILLMENT & RETURNS (RMA) INTELLIGENCE
        // ------------------------------------------------------------------
        // 4A. Order Status Funnel
        $statusBreakdownRaw = (clone $allOrdersQuery)
            ->selectRaw('LOWER(status) as status_key, COUNT(id) as orders_count, COALESCE(SUM(total_amount), 0) as total_amount')
            ->groupBy(DB::raw('LOWER(status)'))
            ->get()
            ->keyBy('status_key');

        $statusPipeline = [
            'pending' => [
                'label' => 'Pending / Placed',
                'count' => (int) ($statusBreakdownRaw['pending']->orders_count ?? 0),
                'amount' => (float) ($statusBreakdownRaw['pending']->total_amount ?? 0),
                'color' => 'amber',
            ],
            'processing' => [
                'label' => 'Confirmed / Packed',
                'count' => (int) (($statusBreakdownRaw['processing']->orders_count ?? 0) + ($statusBreakdownRaw['confirmed']->orders_count ?? 0)),
                'amount' => (float) (($statusBreakdownRaw['processing']->total_amount ?? 0) + ($statusBreakdownRaw['confirmed']->total_amount ?? 0)),
                'color' => 'indigo',
            ],
            'shipped' => [
                'label' => 'Shipped / In Transit',
                'count' => (int) ($statusBreakdownRaw['shipped']->orders_count ?? 0),
                'amount' => (float) ($statusBreakdownRaw['shipped']->total_amount ?? 0),
                'color' => 'blue',
            ],
            'delivered' => [
                'label' => 'Delivered',
                'count' => (int) ($statusBreakdownRaw['delivered']->orders_count ?? 0),
                'amount' => (float) ($statusBreakdownRaw['delivered']->total_amount ?? 0),
                'color' => 'emerald',
            ],
            'returned' => [
                'label' => 'Returned / Exchanged / Refunded',
                'count' => (int) (($statusBreakdownRaw['returned']->orders_count ?? 0) + ($statusBreakdownRaw['refunded']->orders_count ?? 0) + ($statusBreakdownRaw['exchanged']->orders_count ?? 0)),
                'amount' => (float) (($statusBreakdownRaw['returned']->total_amount ?? 0) + ($statusBreakdownRaw['refunded']->total_amount ?? 0) + ($statusBreakdownRaw['exchanged']->total_amount ?? 0)),
                'color' => 'orange',
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'count' => (int) (($statusBreakdownRaw['cancelled']->orders_count ?? 0) + ($statusBreakdownRaw['cancel_requested']->orders_count ?? 0)),
                'amount' => (float) (($statusBreakdownRaw['cancelled']->total_amount ?? 0) + ($statusBreakdownRaw['cancel_requested']->total_amount ?? 0)),
                'color' => 'rose',
            ],
        ];

        // 4B. Payment Mode & Risk Split (Prepaid vs COD vs Partial COD vs POS)
        $paymentSplitRaw = (clone $allOrdersQuery)
            ->selectRaw("
                CASE
                    WHEN pos_location_id IS NOT NULL THEN 'POS (In-Store)'
                    WHEN LOWER(COALESCE(payment_method, '')) IN ('cod', 'cash_on_delivery') AND COALESCE(upfront_amount, 0) > 0 THEN 'Partial COD'
                    WHEN LOWER(COALESCE(payment_method, '')) IN ('cod', 'cash_on_delivery') THEN 'Cash on Delivery (COD)'
                    ELSE 'Prepaid (Online)'
                END as payment_segment,
                COUNT(id) as total_orders,
                SUM(CASE WHEN status NOT IN ('cancelled', 'failed') THEN 1 ELSE 0 END) as valid_orders,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN status IN ('returned', 'refunded', 'exchanged') THEN 1 ELSE 0 END) as returned_orders,
                COALESCE(SUM(CASE WHEN status NOT IN ('cancelled', 'failed') THEN total_amount ELSE 0 END), 0) as valid_revenue,
                COALESCE(SUM(CASE WHEN status NOT IN ('cancelled', 'failed') THEN balance_due ELSE 0 END), 0) as pending_cod_due
            ")
            ->groupBy('payment_segment')
            ->orderByDesc('total_orders')
            ->get()
            ->map(function ($row) {
                $row->cancel_rate = $row->total_orders > 0 ? round(($row->cancelled_orders / $row->total_orders) * 100, 1) : 0;
                $row->return_rate = $row->valid_orders > 0 ? round(($row->returned_orders / $row->valid_orders) * 100, 1) : 0;

                return $row;
            });

        // 4C. Fulfillment SLA & Turnaround Speed
        $slaStats = (clone $validOrdersQuery)
            ->selectRaw('
                AVG(CASE WHEN shipped_at IS NOT NULL AND shipped_at >= created_at THEN TIMESTAMPDIFF(HOUR, created_at, shipped_at) ELSE NULL END) as avg_dispatch_hours,
                AVG(CASE WHEN delivered_at IS NOT NULL AND shipped_at IS NOT NULL AND delivered_at >= shipped_at THEN TIMESTAMPDIFF(HOUR, shipped_at, delivered_at) / 24.0 ELSE NULL END) as avg_transit_days,
                AVG(CASE WHEN delivered_at IS NOT NULL AND delivered_at >= created_at THEN TIMESTAMPDIFF(HOUR, created_at, delivered_at) / 24.0 ELSE NULL END) as avg_total_delivery_days,
                SUM(CASE WHEN status IN ("pending", "processing") AND created_at <= ? THEN 1 ELSE 0 END) as aging_unshipped_orders
            ', [Carbon::now()->subHours(48)])
            ->first();

        // Qikink (POD) vs In-House Fulfillment Split
        $fulfillmentSourceSplit = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereNotIn('orders.status', ['cancelled', 'failed'])
            ->when($channel !== 'all', function ($q) use ($channel) {
                $this->applyChannelFilter($q, $channel, 'orders');
            })
            ->selectRaw('
                CASE WHEN COALESCE(products.use_qikink, 0) = 1 THEN "Qikink (Print-on-Demand)" ELSE "In-House Inventory" END as fulfillment_source,
                COUNT(DISTINCT orders.id) as orders_count,
                SUM(order_items.quantity) as units_ordered,
                COALESCE(SUM(order_items.total), 0) as gross_revenue
            ')
            ->groupBy('fulfillment_source')
            ->get();

        // 4D. Returns & Exchanges (RMA) Deep Dive
        $rmaQuery = ReturnRequest::whereBetween('return_requests.created_at', [$start, $end])
            ->join('orders', 'return_requests.order_id', '=', 'orders.id');
        $this->applyChannelFilter($rmaQuery, $channel, 'orders');

        $rmaSummary = (clone $rmaQuery)->selectRaw("
            COUNT(return_requests.id) as total_requests,
            SUM(CASE WHEN return_requests.type = 'return' THEN 1 ELSE 0 END) as return_count,
            SUM(CASE WHEN return_requests.type = 'exchange' THEN 1 ELSE 0 END) as exchange_count,
            SUM(CASE WHEN return_requests.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN return_requests.status = 'approved' THEN 1 ELSE 0 END) as approved_count,
            SUM(CASE WHEN return_requests.status = 'received' THEN 1 ELSE 0 END) as received_count,
            SUM(CASE WHEN return_requests.status = 'completed' THEN 1 ELSE 0 END) as completed_count,
            SUM(CASE WHEN return_requests.status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
            COALESCE(SUM(return_requests.refund_amount), 0) as total_refund_amount,
            COALESCE(SUM(return_requests.fee_deducted), 0) as total_fee_deducted,
            AVG(CASE WHEN return_requests.approved_at IS NOT NULL AND return_requests.approved_at >= return_requests.created_at THEN TIMESTAMPDIFF(HOUR, return_requests.created_at, return_requests.approved_at) ELSE NULL END) as avg_approval_hours,
            AVG(CASE WHEN return_requests.completed_at IS NOT NULL AND return_requests.completed_at >= return_requests.created_at THEN TIMESTAMPDIFF(HOUR, return_requests.created_at, return_requests.completed_at) / 24.0 ELSE NULL END) as avg_completion_days
        ")->first();

        $returnReasons = (clone $rmaQuery)
            ->selectRaw("
                COALESCE(NULLIF(TRIM(return_requests.reason), ''), 'Unspecified') as reason_label,
                return_requests.type,
                COUNT(return_requests.id) as requests_count,
                COALESCE(SUM(return_requests.refund_amount), 0) as refund_total
            ")
            ->groupBy('reason_label', 'return_requests.type')
            ->orderByDesc('requests_count')
            ->limit(15)
            ->get();

        // Most Returned SKUs (from order_items returned_quantity + return_request_items)
        $mostReturnedSkus = DB::table('return_request_items')
            ->join('return_requests', 'return_request_items.return_request_id', '=', 'return_requests.id')
            ->join('orders', 'return_requests.order_id', '=', 'orders.id')
            ->join('order_items', 'return_request_items.order_item_id', '=', 'order_items.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('skus', 'order_items.sku_id', '=', 'skus.id')
            ->leftJoin('colors', 'skus.color_id', '=', 'colors.id')
            ->leftJoin('sizes', 'skus.size_id', '=', 'sizes.id')
            ->whereBetween('return_requests.created_at', [$start, $end])
            ->when($channel !== 'all', function ($q) use ($channel) {
                $this->applyChannelFilter($q, $channel, 'orders');
            })
            ->selectRaw("
                order_items.product_id,
                COALESCE(products.name, order_items.product_name) as product_name,
                skus.code as sku_code,
                colors.name as color_name,
                sizes.name as size_name,
                order_items.variant_name,
                SUM(return_request_items.quantity) as rma_units_count,
                SUM(CASE WHEN return_requests.type = 'return' THEN return_request_items.quantity ELSE 0 END) as return_units,
                SUM(CASE WHEN return_requests.type = 'exchange' THEN return_request_items.quantity ELSE 0 END) as exchange_units,
                COUNT(DISTINCT return_requests.id) as rma_requests_count
            ")
            ->groupBy(
                'order_items.product_id',
                'products.name',
                'order_items.product_name',
                'skus.code',
                'colors.name',
                'sizes.name',
                'order_items.variant_name'
            )
            ->orderByDesc('rma_units_count')
            ->limit(20)
            ->get();

        // ------------------------------------------------------------------
        // 5. DAILY / MONTHLY TREND CHART DATA
        // ------------------------------------------------------------------
        $groupByMonth = $daysDiff > 62;
        $trendQuery = (clone $validOrdersQuery)
            ->selectRaw(
                ($groupByMonth ? "DATE_FORMAT(orders.created_at, '%Y-%m')" : 'DATE(orders.created_at)') . ' as period_label,
                COUNT(orders.id) as orders_count,
                COALESCE(SUM(orders.total_amount), 0) as net_sales,
                COALESCE(SUM(orders.discount_amount), 0) as discount_total'
            )
            ->groupBy('period_label')
            ->orderBy('period_label')
            ->get()
            ->keyBy('period_label');

        $chartLabels = [];
        $chartSales = [];
        $chartDiscounts = [];
        $chartOrders = [];

        $cursor = $start->copy();
        while ($cursor <= $end) {
            $lbl = $groupByMonth ? $cursor->format('Y-m') : $cursor->format('Y-m-d');
            if (! in_array($lbl, $chartLabels, true)) {
                $chartLabels[] = $lbl;
                $pt = $trendQuery->get($lbl);
                $chartSales[] = $pt ? round((float) $pt->net_sales, 2) : 0;
                $chartDiscounts[] = $pt ? round((float) $pt->discount_total, 2) : 0;
                $chartOrders[] = $pt ? (int) $pt->orders_count : 0;
            }
            $groupByMonth ? $cursor->addMonth() : $cursor->addDay();
        }

        // ------------------------------------------------------------------
        // 6. GEOGRAPHY, INVENTORY HEALTH & CUSTOMER RETENTION
        // ------------------------------------------------------------------
        $geographicStats = DB::table('orders')
            ->leftJoin('addresses', 'orders.shipping_address_id', '=', 'addresses.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->when($channel !== 'all', function ($q) use ($channel) {
                $this->applyChannelFilter($q, $channel, 'orders');
            })
            ->selectRaw("
                COALESCE(NULLIF(TRIM(addresses.state), ''), NULLIF(TRIM(orders.placed_from_state), ''), 'Unknown / POS') as state_name,
                COUNT(orders.id) as total_orders,
                SUM(CASE WHEN orders.status NOT IN ('cancelled', 'failed') THEN 1 ELSE 0 END) as valid_orders,
                SUM(CASE WHEN orders.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN orders.status IN ('returned', 'refunded', 'exchanged') THEN 1 ELSE 0 END) as returned_orders,
                COALESCE(SUM(CASE WHEN orders.status NOT IN ('cancelled', 'failed') THEN orders.total_amount ELSE 0 END), 0) as net_revenue
            ")
            ->groupBy('state_name')
            ->orderByDesc('net_revenue')
            ->limit(15)
            ->get()
            ->map(function ($row) {
                $row->cancel_rate = $row->total_orders > 0 ? round(($row->cancelled_orders / $row->total_orders) * 100, 1) : 0;
                $row->return_rate = $row->valid_orders > 0 ? round(($row->returned_orders / $row->valid_orders) * 100, 1) : 0;

                return $row;
            });

        // Inventory Valuation & Stock Health (Warehouse snapshot)
        $inventorySummary = DB::table('skus')
            ->join('products', 'skus.product_id', '=', 'products.id')
            ->where('products.is_active', true)
            ->selectRaw('
                COUNT(skus.id) as total_active_skus,
                SUM(CASE WHEN skus.stock <= 0 THEN 1 ELSE 0 END) as out_of_stock_skus,
                SUM(CASE WHEN skus.stock > 0 AND skus.stock <= 5 THEN 1 ELSE 0 END) as low_stock_skus,
                SUM(GREATEST(0, skus.stock)) as total_units_in_stock,
                COALESCE(SUM(GREATEST(0, skus.stock) * skus.price), 0) as retail_stock_value,
                COALESCE(SUM(CASE WHEN skus.purchase_price IS NOT NULL AND skus.purchase_price > 0 THEN GREATEST(0, skus.stock) * skus.purchase_price ELSE 0 END), 0) as cost_stock_value
            ')
            ->first();

        $overallCancelRate = $totalOrdersPlaced > 0 ? round(($cancelledOrdersCount / $totalOrdersPlaced) * 100, 1) : 0;
        $overallReturnRate = $validOrdersCount > 0 ? round(($returnedOrdersCount / $validOrdersCount) * 100, 1) : 0;

        // ------------------------------------------------------------------
        // 7. OMNICHANNEL SPLIT (ONLINE VS OFFLINE) & MERCHANDISING ATTRIBUTES
        //    (TOP COLORS, TOP SIZES, TOP FITS)
        // ------------------------------------------------------------------
        $onlineVsOfflineRaw = DB::table('orders')
            ->leftJoin('pos_locations', 'orders.pos_location_id', '=', 'pos_locations.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw("
                CASE WHEN orders.pos_location_id IS NULL THEN 'Online Storefront' ELSE CONCAT('Offline POS: ', COALESCE(pos_locations.name, 'Store')) END as channel_name,
                CASE WHEN orders.pos_location_id IS NULL THEN 'online' ELSE 'offline' END as channel_group,
                COUNT(orders.id) as total_orders,
                SUM(CASE WHEN orders.status NOT IN ('cancelled', 'failed') THEN 1 ELSE 0 END) as valid_orders,
                SUM(CASE WHEN orders.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                SUM(CASE WHEN orders.status IN ('returned', 'refunded', 'exchanged') THEN 1 ELSE 0 END) as returned_orders,
                COALESCE(SUM(CASE WHEN orders.status NOT IN ('cancelled', 'failed') THEN orders.discount_amount ELSE 0 END), 0) as total_discount,
                COALESCE(SUM(CASE WHEN orders.status NOT IN ('cancelled', 'failed') THEN orders.total_amount ELSE 0 END), 0) as valid_revenue
            ")
            ->groupBy('channel_name', 'channel_group')
            ->orderByDesc('valid_revenue')
            ->get();

        $totalOmnichannelRevenue = max(0.0, (float) $onlineVsOfflineRaw->sum('valid_revenue'));
        $totalOmnichannelValidOrders = (int) $onlineVsOfflineRaw->sum('valid_orders');

        $onlineVsOfflineSummary = [
            'online_orders' => (int) $onlineVsOfflineRaw->where('channel_group', 'online')->sum('valid_orders'),
            'offline_orders' => (int) $onlineVsOfflineRaw->where('channel_group', 'offline')->sum('valid_orders'),
            'online_revenue' => (float) $onlineVsOfflineRaw->where('channel_group', 'online')->sum('valid_revenue'),
            'offline_revenue' => (float) $onlineVsOfflineRaw->where('channel_group', 'offline')->sum('valid_revenue'),
            'online_discount' => (float) $onlineVsOfflineRaw->where('channel_group', 'online')->sum('total_discount'),
            'offline_discount' => (float) $onlineVsOfflineRaw->where('channel_group', 'offline')->sum('total_discount'),
        ];
        $onlineVsOfflineSummary['online_rev_share'] = $totalOmnichannelRevenue > 0
            ? round(($onlineVsOfflineSummary['online_revenue'] / $totalOmnichannelRevenue) * 100, 1)
            : 0;
        $onlineVsOfflineSummary['offline_rev_share'] = $totalOmnichannelRevenue > 0
            ? round(($onlineVsOfflineSummary['offline_revenue'] / $totalOmnichannelRevenue) * 100, 1)
            : 0;
        $onlineVsOfflineSummary['online_orders_share'] = $totalOmnichannelValidOrders > 0
            ? round(($onlineVsOfflineSummary['online_orders'] / $totalOmnichannelValidOrders) * 100, 1)
            : 0;
        $onlineVsOfflineSummary['offline_orders_share'] = $totalOmnichannelValidOrders > 0
            ? round(($onlineVsOfflineSummary['offline_orders'] / $totalOmnichannelValidOrders) * 100, 1)
            : 0;
        $onlineVsOfflineSummary['online_aov'] = $onlineVsOfflineSummary['online_orders'] > 0
            ? ($onlineVsOfflineSummary['online_revenue'] / $onlineVsOfflineSummary['online_orders'])
            : 0;
        $onlineVsOfflineSummary['offline_aov'] = $onlineVsOfflineSummary['offline_orders'] > 0
            ? ($onlineVsOfflineSummary['offline_revenue'] / $onlineVsOfflineSummary['offline_orders'])
            : 0;

        $onlineVsOfflineTable = $onlineVsOfflineRaw->map(function ($row) use ($totalOmnichannelRevenue) {
            $row->aov = $row->valid_orders > 0 ? ($row->valid_revenue / $row->valid_orders) : 0;
            $row->revenue_share = $totalOmnichannelRevenue > 0 ? round(($row->valid_revenue / $totalOmnichannelRevenue) * 100, 1) : 0;
            $row->cancel_rate = $row->total_orders > 0 ? round(($row->cancelled_orders / $row->total_orders) * 100, 1) : 0;
            $row->return_rate = $row->valid_orders > 0 ? round(($row->returned_orders / $row->valid_orders) * 100, 1) : 0;

            return $row;
        });

        // Top Colors
        $topColorsRaw = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('skus', 'order_items.sku_id', '=', 'skus.id')
            ->leftJoin('colors', 'skus.color_id', '=', 'colors.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereNotIn('orders.status', ['cancelled', 'failed'])
            ->when($channel !== 'all', fn ($q) => $this->applyChannelFilter($q, $channel, 'orders'))
            ->selectRaw("
                COALESCE(colors.name, 'Unassigned Color') as color_name,
                colors.hex_code,
                SUM(order_items.quantity) as gross_units,
                SUM(COALESCE(order_items.returned_quantity, 0)) as returned_units,
                SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0))) as net_units,
                SUM(CASE WHEN orders.pos_location_id IS NULL THEN GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) ELSE 0 END) as online_units,
                SUM(CASE WHEN orders.pos_location_id IS NOT NULL THEN GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) ELSE 0 END) as offline_units,
                COALESCE(SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) * order_items.price), 0) as net_revenue
            ")
            ->groupBy('color_name', 'colors.hex_code')
            ->orderByDesc('net_units')
            ->limit(15)
            ->get();

        $totalColorUnits = max(1, (int) $topColorsRaw->sum('net_units'));
        $topColors = $topColorsRaw->map(function ($row) use ($totalColorUnits) {
            $row->unit_share_pct = round(($row->net_units / $totalColorUnits) * 100, 1);
            $row->return_rate_pct = $row->gross_units > 0 ? round(($row->returned_units / $row->gross_units) * 100, 1) : 0;

            return $row;
        });

        // Top Sizes
        $topSizesRaw = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('skus', 'order_items.sku_id', '=', 'skus.id')
            ->leftJoin('sizes', 'skus.size_id', '=', 'sizes.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereNotIn('orders.status', ['cancelled', 'failed'])
            ->when($channel !== 'all', fn ($q) => $this->applyChannelFilter($q, $channel, 'orders'))
            ->selectRaw("
                COALESCE(sizes.name, 'Free / Unassigned') as size_name,
                sizes.code as size_code,
                SUM(order_items.quantity) as gross_units,
                SUM(COALESCE(order_items.returned_quantity, 0)) as returned_units,
                SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0))) as net_units,
                SUM(CASE WHEN orders.pos_location_id IS NULL THEN GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) ELSE 0 END) as online_units,
                SUM(CASE WHEN orders.pos_location_id IS NOT NULL THEN GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) ELSE 0 END) as offline_units,
                COALESCE(SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) * order_items.price), 0) as net_revenue
            ")
            ->groupBy('size_name', 'sizes.code')
            ->orderByDesc('net_units')
            ->limit(15)
            ->get();

        $totalSizeUnits = max(1, (int) $topSizesRaw->sum('net_units'));
        $topSizes = $topSizesRaw->map(function ($row) use ($totalSizeUnits) {
            $row->unit_share_pct = round(($row->net_units / $totalSizeUnits) * 100, 1);
            $row->return_rate_pct = $row->gross_units > 0 ? round(($row->returned_units / $row->gross_units) * 100, 1) : 0;

            return $row;
        });

        // Top Fits
        $topFitsRaw = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('fits', 'products.fit_id', '=', 'fits.id')
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereNotIn('orders.status', ['cancelled', 'failed'])
            ->when($channel !== 'all', fn ($q) => $this->applyChannelFilter($q, $channel, 'orders'))
            ->selectRaw("
                COALESCE(fits.name, 'Unassigned Fit') as fit_name,
                SUM(order_items.quantity) as gross_units,
                SUM(COALESCE(order_items.returned_quantity, 0)) as returned_units,
                SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0))) as net_units,
                SUM(CASE WHEN orders.pos_location_id IS NULL THEN GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) ELSE 0 END) as online_units,
                SUM(CASE WHEN orders.pos_location_id IS NOT NULL THEN GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) ELSE 0 END) as offline_units,
                COALESCE(SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) * order_items.price), 0) as net_revenue
            ")
            ->groupBy('fit_name')
            ->orderByDesc('net_units')
            ->limit(15)
            ->get();

        $totalFitUnits = max(1, (int) $topFitsRaw->sum('net_units'));
        $topFits = $topFitsRaw->map(function ($row) use ($totalFitUnits) {
            $row->unit_share_pct = round(($row->net_units / $totalFitUnits) * 100, 1);
            $row->return_rate_pct = $row->gross_units > 0 ? round(($row->returned_units / $row->gross_units) * 100, 1) : 0;

            return $row;
        });

        // ------------------------------------------------------------------
        // 8. USER SIGNUP SPLIT, MARKETING CONSENT (EMAIL / WHATSAPP / SMS)
        //    & COOKIE TRACKING STATUS (OUT OF TOTAL REGISTERED USERS)
        // ------------------------------------------------------------------
        $registeredUsers = DB::table('users')
            ->where(function ($q) {
                $q->whereIn('role', ['customer', 'user'])
                    ->orWhereNull('role')
                    ->orWhere('role', '');
            })
            ->select(
                'id',
                'provider',
                'phone',
                'email',
                'has_consented_to_terms',
                'has_consented_to_marketing',
                'marketing_preferences',
                'tracking_consent',
                'created_at'
            )
            ->get();

        $totalRegisteredUsers = $registeredUsers->count();
        $newUsersInPeriod = 0;

        // Signup Method Split
        $googleSignupCount = 0;
        $emailWhatsappSignupCount = 0;
        $otherSocialSignupCount = 0;

        // Marketing Channel Consent (Out of Total Registered Users)
        $termsConsentedCount = 0;
        $anyMarketingConsentCount = 0;
        $emailConsentCount = 0;
        $whatsappConsentCount = 0;
        $smsConsentCount = 0;

        // Cookie / Tracking Status
        $cookieAcceptedAllCount = 0;
        $cookieAcceptedAnyCount = 0;
        $cookieRejectedAllCount = 0;
        $cookieNotSetCount = 0;
        $cookieGaCount = 0;
        $cookieMetaCount = 0;
        $cookieSnapchatCount = 0;

        foreach ($registeredUsers as $u) {
            if ($u->created_at && Carbon::parse($u->created_at)->between($start, $end)) {
                $newUsersInPeriod++;
            }

            // 1. Signup Provider Split
            $prov = strtolower(trim((string) ($u->provider ?? '')));
            if ($prov === 'google') {
                $googleSignupCount++;
            } elseif ($prov === '' || $prov === 'email' || $prov === 'phone' || $prov === 'whatsapp') {
                $emailWhatsappSignupCount++;
            } else {
                $otherSocialSignupCount++;
            }

            // 2. Terms & Marketing Consents (Email, WhatsApp, SMS)
            if ($u->has_consented_to_terms) {
                $termsConsentedCount++;
            }

            $mktPrefs = is_string($u->marketing_preferences)
                ? (json_decode($u->marketing_preferences, true) ?: [])
                : (is_array($u->marketing_preferences) ? $u->marketing_preferences : []);

            $hasEmail = ! empty($mktPrefs['email']) || ($u->has_consented_to_marketing && empty($mktPrefs));
            $hasWhatsapp = ! empty($mktPrefs['whatsapp']);
            $hasSms = ! empty($mktPrefs['sms']);

            if ($hasEmail) {
                $emailConsentCount++;
            }
            if ($hasWhatsapp) {
                $whatsappConsentCount++;
            }
            if ($hasSms) {
                $smsConsentCount++;
            }
            if ($u->has_consented_to_marketing || $hasEmail || $hasWhatsapp || $hasSms) {
                $anyMarketingConsentCount++;
            }

            // 3. Cookie / Tracking Status
            $trk = is_string($u->tracking_consent)
                ? json_decode($u->tracking_consent, true)
                : (is_array($u->tracking_consent) ? $u->tracking_consent : null);

            if (! is_array($trk) || empty($trk)) {
                $cookieNotSetCount++;
            } else {
                $ga = ! empty($trk['google_analytics']);
                $meta = ! empty($trk['meta_pixel']);
                $snap = ! empty($trk['snapchat_pixel']);

                if ($ga) {
                    $cookieGaCount++;
                }
                if ($meta) {
                    $cookieMetaCount++;
                }
                if ($snap) {
                    $cookieSnapchatCount++;
                }

                if ($ga && $meta && $snap) {
                    $cookieAcceptedAllCount++;
                    $cookieAcceptedAnyCount++;
                } elseif ($ga || $meta || $snap) {
                    $cookieAcceptedAnyCount++;
                } else {
                    $cookieRejectedAllCount++;
                }
            }
        }

        $pctOfUsers = fn (int $cnt) => $totalRegisteredUsers > 0 ? round(($cnt / $totalRegisteredUsers) * 100, 1) : 0.0;

        $userConsentAnalytics = [
            'total_users' => $totalRegisteredUsers,
            'new_users_in_period' => $newUsersInPeriod,

            // Signup Split
            'google_signup_count' => $googleSignupCount,
            'google_signup_pct' => $pctOfUsers($googleSignupCount),
            'email_whatsapp_signup_count' => $emailWhatsappSignupCount,
            'email_whatsapp_signup_pct' => $pctOfUsers($emailWhatsappSignupCount),
            'other_social_signup_count' => $otherSocialSignupCount,
            'other_social_signup_pct' => $pctOfUsers($otherSocialSignupCount),

            // Marketing Consent (Email, WhatsApp, SMS)
            'terms_consent_count' => $termsConsentedCount,
            'terms_consent_pct' => $pctOfUsers($termsConsentedCount),
            'any_marketing_count' => $anyMarketingConsentCount,
            'any_marketing_pct' => $pctOfUsers($anyMarketingConsentCount),
            'email_consent_count' => $emailConsentCount,
            'email_consent_pct' => $pctOfUsers($emailConsentCount),
            'whatsapp_consent_count' => $whatsappConsentCount,
            'whatsapp_consent_pct' => $pctOfUsers($whatsappConsentCount),
            'sms_consent_count' => $smsConsentCount,
            'sms_consent_pct' => $pctOfUsers($smsConsentCount),

            // Cookie Status
            'cookie_accepted_any_count' => $cookieAcceptedAnyCount,
            'cookie_accepted_any_pct' => $pctOfUsers($cookieAcceptedAnyCount),
            'cookie_accepted_all_count' => $cookieAcceptedAllCount,
            'cookie_accepted_all_pct' => $pctOfUsers($cookieAcceptedAllCount),
            'cookie_rejected_all_count' => $cookieRejectedAllCount,
            'cookie_rejected_all_pct' => $pctOfUsers($cookieRejectedAllCount),
            'cookie_not_set_count' => $cookieNotSetCount,
            'cookie_not_set_pct' => $pctOfUsers($cookieNotSetCount),
            'cookie_ga_count' => $cookieGaCount,
            'cookie_ga_pct' => $pctOfUsers($cookieGaCount),
            'cookie_meta_count' => $cookieMetaCount,
            'cookie_meta_pct' => $pctOfUsers($cookieMetaCount),
            'cookie_snapchat_count' => $cookieSnapchatCount,
            'cookie_snapchat_pct' => $pctOfUsers($cookieSnapchatCount),
        ];

        return view('admin.analysis.index', compact(
            'preset',
            'startDateInput',
            'endDateInput',
            'daysDiff',
            'channel',
            'currencySymbol',
            'posLocations',
            'totalOrdersPlaced',
            'validOrdersCount',
            'cancelledOrdersCount',
            'cancelledOrdersValue',
            'returnedOrdersCount',
            'overallCancelRate',
            'overallReturnRate',
            'grossItemSales',
            'grossOrderTotal',
            'netRevenue',
            'totalRefunded',
            'totalShipping',
            'totalTax',
            'totalBalanceDue',
            'aov',
            'totalDiscount',
            'couponDiscount',
            'prepaidDiscount',
            'giftCardDiscount',
            'otherManualDiscount',
            'discountRatePercent',
            'discountStats',
            'onlineSales',
            'posSales',
            'comparisons',
            'couponBreakdown',
            'skuProfitability',
            'profitSummary',
            'statusPipeline',
            'paymentSplitRaw',
            'slaStats',
            'fulfillmentSourceSplit',
            'rmaSummary',
            'returnReasons',
            'mostReturnedSkus',
            'chartLabels',
            'chartSales',
            'chartDiscounts',
            'chartOrders',
            'geographicStats',
            'inventorySummary',
            'onlineVsOfflineSummary',
            'onlineVsOfflineTable',
            'topColors',
            'topSizes',
            'topFits',
            'userConsentAnalytics'
        ));
    }

    /**
     * Export CSV reports for any section of Analysis (Beta).
     */
    public function export(Request $request): StreamedResponse
    {
        $section = $request->input('section', 'sku_profitability');
        $dateInfo = $this->resolveDateRange($request);
        $start = $dateInfo['start'];
        $end = $dateInfo['end'];
        $channel = $request->input('channel', 'all');

        $filename = 'analysis_' . $section . '_' . $start->format('Ymd') . '_to_' . $end->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($section, $start, $end, $channel) {
            $handle = fopen('php://output', 'w');

            if ($section === 'coupons') {
                fputcsv($handle, [
                    'Coupon Code',
                    'Orders Used',
                    'Coupon Discount Amount',
                    'Stacked Prepaid Discount',
                    'Total Discount on Orders',
                    'Net Order Revenue Generated',
                    'Average Order Value (AOV)',
                ]);

                $rows = Order::whereBetween('orders.created_at', [$start, $end])
                    ->whereNotIn('orders.status', ['cancelled', 'failed'])
                    ->when($channel !== 'all', fn ($q) => $this->applyChannelFilter($q, $channel, 'orders'))
                    ->whereNotNull('orders.coupon_code')
                    ->where('orders.coupon_code', '!=', '')
                    ->selectRaw('
                        UPPER(TRIM(orders.coupon_code)) as code,
                        COUNT(orders.id) as orders_count,
                        COALESCE(SUM(CASE WHEN orders.coupon_discount_amount > 0 THEN orders.coupon_discount_amount ELSE GREATEST(0, orders.discount_amount - orders.prepaid_discount_amount - orders.gift_card_discount_amount) END), 0) as coupon_discount_total,
                        COALESCE(SUM(orders.prepaid_discount_amount), 0) as prepaid_discount_stacked,
                        COALESCE(SUM(orders.discount_amount), 0) as total_discount_given,
                        COALESCE(SUM(orders.total_amount), 0) as total_revenue,
                        COALESCE(AVG(orders.total_amount), 0) as avg_order_value
                    ')
                    ->groupBy(DB::raw('UPPER(TRIM(orders.coupon_code))'))
                    ->orderByDesc('orders_count')
                    ->get();

                foreach ($rows as $r) {
                    fputcsv($handle, [
                        $r->code,
                        $r->orders_count,
                        number_format((float) $r->coupon_discount_total, 2, '.', ''),
                        number_format((float) $r->prepaid_discount_stacked, 2, '.', ''),
                        number_format((float) $r->total_discount_given, 2, '.', ''),
                        number_format((float) $r->total_revenue, 2, '.', ''),
                        number_format((float) $r->avg_order_value, 2, '.', ''),
                    ]);
                }
            } elseif ($section === 'operations_payments') {
                fputcsv($handle, [
                    'Payment Mode / Channel',
                    'Total Orders Placed',
                    'Valid Orders',
                    'Cancelled Orders',
                    'Cancellation Rate (%)',
                    'Returned / Exchanged Orders',
                    'Return Rate (%)',
                    'Valid Revenue',
                    'Pending Balance Due (COD)',
                ]);

                $rows = Order::whereBetween('orders.created_at', [$start, $end])
                    ->when($channel !== 'all', fn ($q) => $this->applyChannelFilter($q, $channel, 'orders'))
                    ->selectRaw("
                        CASE
                            WHEN pos_location_id IS NOT NULL THEN 'POS (In-Store)'
                            WHEN LOWER(COALESCE(payment_method, '')) IN ('cod', 'cash_on_delivery') AND COALESCE(upfront_amount, 0) > 0 THEN 'Partial COD'
                            WHEN LOWER(COALESCE(payment_method, '')) IN ('cod', 'cash_on_delivery') THEN 'Cash on Delivery (COD)'
                            ELSE 'Prepaid (Online)'
                        END as payment_segment,
                        COUNT(id) as total_orders,
                        SUM(CASE WHEN status NOT IN ('cancelled', 'failed') THEN 1 ELSE 0 END) as valid_orders,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                        SUM(CASE WHEN status IN ('returned', 'refunded', 'exchanged') THEN 1 ELSE 0 END) as returned_orders,
                        COALESCE(SUM(CASE WHEN status NOT IN ('cancelled', 'failed') THEN total_amount ELSE 0 END), 0) as valid_revenue,
                        COALESCE(SUM(CASE WHEN status NOT IN ('cancelled', 'failed') THEN balance_due ELSE 0 END), 0) as pending_cod_due
                    ")
                    ->groupBy('payment_segment')
                    ->orderByDesc('total_orders')
                    ->get();

                foreach ($rows as $r) {
                    $cancelRate = $r->total_orders > 0 ? round(($r->cancelled_orders / $r->total_orders) * 100, 1) : 0;
                    $returnRate = $r->valid_orders > 0 ? round(($r->returned_orders / $r->valid_orders) * 100, 1) : 0;
                    fputcsv($handle, [
                        $r->payment_segment,
                        $r->total_orders,
                        $r->valid_orders,
                        $r->cancelled_orders,
                        $cancelRate,
                        $r->returned_orders,
                        $returnRate,
                        number_format((float) $r->valid_revenue, 2, '.', ''),
                        number_format((float) $r->pending_cod_due, 2, '.', ''),
                    ]);
                }
            } elseif ($section === 'geography') {
                fputcsv($handle, [
                    'State / Region',
                    'Total Orders',
                    'Valid Orders',
                    'Cancelled Orders',
                    'Cancel Rate (%)',
                    'Returned Orders',
                    'Return Rate (%)',
                    'Net Revenue',
                ]);

                $rows = DB::table('orders')
                    ->leftJoin('addresses', 'orders.shipping_address_id', '=', 'addresses.id')
                    ->whereBetween('orders.created_at', [$start, $end])
                    ->when($channel !== 'all', fn ($q) => $this->applyChannelFilter($q, $channel, 'orders'))
                    ->selectRaw("
                        COALESCE(NULLIF(TRIM(addresses.state), ''), NULLIF(TRIM(orders.placed_from_state), ''), 'Unknown / POS') as state_name,
                        COUNT(orders.id) as total_orders,
                        SUM(CASE WHEN orders.status NOT IN ('cancelled', 'failed') THEN 1 ELSE 0 END) as valid_orders,
                        SUM(CASE WHEN orders.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                        SUM(CASE WHEN orders.status IN ('returned', 'refunded', 'exchanged') THEN 1 ELSE 0 END) as returned_orders,
                        COALESCE(SUM(CASE WHEN orders.status NOT IN ('cancelled', 'failed') THEN orders.total_amount ELSE 0 END), 0) as net_revenue
                    ")
                    ->groupBy('state_name')
                    ->orderByDesc('net_revenue')
                    ->get();

                foreach ($rows as $r) {
                    $cancelRate = $r->total_orders > 0 ? round(($r->cancelled_orders / $r->total_orders) * 100, 1) : 0;
                    $returnRate = $r->valid_orders > 0 ? round(($r->returned_orders / $r->valid_orders) * 100, 1) : 0;
                    fputcsv($handle, [
                        $r->state_name,
                        $r->total_orders,
                        $r->valid_orders,
                        $r->cancelled_orders,
                        $cancelRate,
                        $r->returned_orders,
                        $returnRate,
                        number_format((float) $r->net_revenue, 2, '.', ''),
                    ]);
                }
            } elseif ($section === 'merchandising_split') {
                fputcsv($handle, [
                    'Dimension',
                    'Attribute / Channel Name',
                    'Code / Hex',
                    'Gross Units / Placed Orders',
                    'Returned Units / Cancelled Orders',
                    'Net Units Sold / Valid Orders',
                    'Online Net Units',
                    'Offline (POS) Net Units',
                    'Net Revenue',
                ]);

                // 1. Online vs Offline
                $channelRows = DB::table('orders')
                    ->leftJoin('pos_locations', 'orders.pos_location_id', '=', 'pos_locations.id')
                    ->whereBetween('orders.created_at', [$start, $end])
                    ->selectRaw("
                        CASE WHEN orders.pos_location_id IS NULL THEN 'Online Storefront' ELSE CONCAT('Offline POS: ', COALESCE(pos_locations.name, 'Store')) END as name_label,
                        COUNT(orders.id) as total_orders,
                        SUM(CASE WHEN orders.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                        SUM(CASE WHEN orders.status NOT IN ('cancelled', 'failed') THEN 1 ELSE 0 END) as valid_orders,
                        COALESCE(SUM(CASE WHEN orders.status NOT IN ('cancelled', 'failed') THEN orders.total_amount ELSE 0 END), 0) as valid_revenue
                    ")
                    ->groupBy('name_label')
                    ->orderByDesc('valid_revenue')
                    ->get();

                foreach ($channelRows as $cr) {
                    fputcsv($handle, [
                        'Channel (Online vs Offline)',
                        $cr->name_label,
                        '-',
                        $cr->total_orders,
                        $cr->cancelled_orders,
                        $cr->valid_orders,
                        '-',
                        '-',
                        number_format((float) $cr->valid_revenue, 2, '.', ''),
                    ]);
                }

                // 2. Top Colors
                $colorRows = DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->leftJoin('skus', 'order_items.sku_id', '=', 'skus.id')
                    ->leftJoin('colors', 'skus.color_id', '=', 'colors.id')
                    ->whereBetween('orders.created_at', [$start, $end])
                    ->whereNotIn('orders.status', ['cancelled', 'failed'])
                    ->when($channel !== 'all', fn ($q) => $this->applyChannelFilter($q, $channel, 'orders'))
                    ->selectRaw("
                        COALESCE(colors.name, 'Unassigned Color') as color_name,
                        COALESCE(colors.hex_code, '-') as hex_code,
                        SUM(order_items.quantity) as gross_units,
                        SUM(COALESCE(order_items.returned_quantity, 0)) as returned_units,
                        SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0))) as net_units,
                        SUM(CASE WHEN orders.pos_location_id IS NULL THEN GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) ELSE 0 END) as online_units,
                        SUM(CASE WHEN orders.pos_location_id IS NOT NULL THEN GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) ELSE 0 END) as offline_units,
                        COALESCE(SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) * order_items.price), 0) as net_revenue
                    ")
                    ->groupBy('color_name', 'colors.hex_code')
                    ->orderByDesc('net_units')
                    ->get();

                foreach ($colorRows as $clr) {
                    fputcsv($handle, [
                        'Color',
                        $clr->color_name,
                        $clr->hex_code,
                        $clr->gross_units,
                        $clr->returned_units,
                        $clr->net_units,
                        $clr->online_units,
                        $clr->offline_units,
                        number_format((float) $clr->net_revenue, 2, '.', ''),
                    ]);
                }

                // 3. Top Sizes
                $sizeRows = DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->leftJoin('skus', 'order_items.sku_id', '=', 'skus.id')
                    ->leftJoin('sizes', 'skus.size_id', '=', 'sizes.id')
                    ->whereBetween('orders.created_at', [$start, $end])
                    ->whereNotIn('orders.status', ['cancelled', 'failed'])
                    ->when($channel !== 'all', fn ($q) => $this->applyChannelFilter($q, $channel, 'orders'))
                    ->selectRaw("
                        COALESCE(sizes.name, 'Free / Unassigned') as size_name,
                        COALESCE(sizes.code, '-') as size_code,
                        SUM(order_items.quantity) as gross_units,
                        SUM(COALESCE(order_items.returned_quantity, 0)) as returned_units,
                        SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0))) as net_units,
                        SUM(CASE WHEN orders.pos_location_id IS NULL THEN GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) ELSE 0 END) as online_units,
                        SUM(CASE WHEN orders.pos_location_id IS NOT NULL THEN GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) ELSE 0 END) as offline_units,
                        COALESCE(SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) * order_items.price), 0) as net_revenue
                    ")
                    ->groupBy('size_name', 'sizes.code')
                    ->orderByDesc('net_units')
                    ->get();

                foreach ($sizeRows as $sz) {
                    fputcsv($handle, [
                        'Size',
                        $sz->size_name,
                        $sz->size_code,
                        $sz->gross_units,
                        $sz->returned_units,
                        $sz->net_units,
                        $sz->online_units,
                        $sz->offline_units,
                        number_format((float) $sz->net_revenue, 2, '.', ''),
                    ]);
                }

                // 4. Top Fits
                $fitRows = DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
                    ->leftJoin('fits', 'products.fit_id', '=', 'fits.id')
                    ->whereBetween('orders.created_at', [$start, $end])
                    ->whereNotIn('orders.status', ['cancelled', 'failed'])
                    ->when($channel !== 'all', fn ($q) => $this->applyChannelFilter($q, $channel, 'orders'))
                    ->selectRaw("
                        COALESCE(fits.name, 'Unassigned Fit') as fit_name,
                        SUM(order_items.quantity) as gross_units,
                        SUM(COALESCE(order_items.returned_quantity, 0)) as returned_units,
                        SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0))) as net_units,
                        SUM(CASE WHEN orders.pos_location_id IS NULL THEN GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) ELSE 0 END) as online_units,
                        SUM(CASE WHEN orders.pos_location_id IS NOT NULL THEN GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) ELSE 0 END) as offline_units,
                        COALESCE(SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) * order_items.price), 0) as net_revenue
                    ")
                    ->groupBy('fit_name')
                    ->orderByDesc('net_units')
                    ->get();

                foreach ($fitRows as $ft) {
                    fputcsv($handle, [
                        'Fit',
                        $ft->fit_name,
                        '-',
                        $ft->gross_units,
                        $ft->returned_units,
                        $ft->net_units,
                        $ft->online_units,
                        $ft->offline_units,
                        number_format((float) $ft->net_revenue, 2, '.', ''),
                    ]);
                }
            } elseif ($section === 'user_consent') {
                fputcsv($handle, [
                    'User ID',
                    'Name',
                    'Email',
                    'Phone',
                    'Signup Method / Provider',
                    'Terms Consent',
                    'Email Marketing Consent',
                    'WhatsApp Marketing Consent',
                    'SMS Marketing Consent',
                    'Cookie Status Summary',
                    'Google Analytics Cookie',
                    'Meta Pixel Cookie',
                    'Snapchat Pixel Cookie',
                    'Joined Date',
                ]);

                $users = DB::table('users')
                    ->where(function ($q) {
                        $q->whereIn('role', ['customer', 'user'])
                            ->orWhereNull('role')
                            ->orWhere('role', '');
                    })
                    ->select(
                        'id',
                        'name',
                        'email',
                        'phone',
                        'provider',
                        'has_consented_to_terms',
                        'has_consented_to_marketing',
                        'marketing_preferences',
                        'tracking_consent',
                        'created_at'
                    )
                    ->orderByDesc('id')
                    ->get();

                foreach ($users as $u) {
                    $prov = strtolower(trim((string) ($u->provider ?? '')));
                    $signupMethod = $prov === 'google'
                        ? 'Google Signup'
                        : ($prov === '' ? 'Email / WhatsApp OTP' : ucfirst($prov));

                    $mktPrefs = is_string($u->marketing_preferences)
                        ? (json_decode($u->marketing_preferences, true) ?: [])
                        : (is_array($u->marketing_preferences) ? $u->marketing_preferences : []);

                    $hasEmail = ! empty($mktPrefs['email']) || ($u->has_consented_to_marketing && empty($mktPrefs));
                    $hasWhatsapp = ! empty($mktPrefs['whatsapp']);
                    $hasSms = ! empty($mktPrefs['sms']);

                    $trk = is_string($u->tracking_consent)
                        ? json_decode($u->tracking_consent, true)
                        : (is_array($u->tracking_consent) ? $u->tracking_consent : null);

                    if (! is_array($trk) || empty($trk)) {
                        $cookieSummary = 'Not Configured (Essential Only)';
                        $ga = 'No';
                        $meta = 'No';
                        $snap = 'No';
                    } else {
                        $gaBool = ! empty($trk['google_analytics']);
                        $metaBool = ! empty($trk['meta_pixel']);
                        $snapBool = ! empty($trk['snapchat_pixel']);
                        $ga = $gaBool ? 'Yes' : 'No';
                        $meta = $metaBool ? 'Yes' : 'No';
                        $snap = $snapBool ? 'Yes' : 'No';

                        if ($gaBool && $metaBool && $snapBool) {
                            $cookieSummary = 'Accepted All Cookies';
                        } elseif ($gaBool || $metaBool || $snapBool) {
                            $cookieSummary = 'Partial / Custom Consent';
                        } else {
                            $cookieSummary = 'Rejected Optional Cookies';
                        }
                    }

                    fputcsv($handle, [
                        $u->id,
                        $u->name,
                        $u->email,
                        $u->phone ?? '',
                        $signupMethod,
                        $u->has_consented_to_terms ? 'Yes' : 'No',
                        $hasEmail ? 'Yes' : 'No',
                        $hasWhatsapp ? 'Yes' : 'No',
                        $hasSms ? 'Yes' : 'No',
                        $cookieSummary,
                        $ga,
                        $meta,
                        $snap,
                        $u->created_at ?? '',
                    ]);
                }
            } else {
                // Default: sku_profitability
                fputcsv($handle, [
                    'Product ID',
                    'Product Name',
                    'SKU Code',
                    'Variant (Color / Size)',
                    'Gross Qty Sold',
                    'Returned Qty',
                    'Net Qty Sold',
                    'Avg Sold Price (Unit)',
                    'Purchase Cost (Unit)',
                    'Gross Sold Value (Before Discount)',
                    'Allocated Order Discount',
                    'Net Sold Value (After Discount)',
                    'Total Purchase Cost (COGS)',
                    'Approx Gross Profit (Before Discount)',
                    'Approx Gross Profit (After Discount)',
                    'Gross Margin % (After Discount)',
                    'Current Stock',
                ]);

                $rows = DB::table('order_items')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->leftJoin('skus', 'order_items.sku_id', '=', 'skus.id')
                    ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
                    ->leftJoin('colors', 'skus.color_id', '=', 'colors.id')
                    ->leftJoin('sizes', 'skus.size_id', '=', 'sizes.id')
                    ->whereBetween('orders.created_at', [$start, $end])
                    ->whereNotIn('orders.status', ['cancelled', 'failed'])
                    ->when($channel !== 'all', fn ($q) => $this->applyChannelFilter($q, $channel, 'orders'))
                    ->selectRaw('
                        order_items.sku_id,
                        order_items.product_id,
                        COALESCE(products.name, order_items.product_name) as product_name,
                        COALESCE(skus.code, skus.short_code, "N/A") as sku_code,
                        colors.name as color_name,
                        sizes.name as size_name,
                        order_items.variant_name,
                        COALESCE(skus.purchase_price, skus.cost_price) as unit_purchase_cost,
                        skus.stock as current_stock,
                        SUM(order_items.quantity) as gross_qty_sold,
                        SUM(COALESCE(order_items.returned_quantity, 0)) as returned_qty,
                        SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0))) as net_qty_sold,
                        AVG(order_items.price) as avg_sold_price,
                        SUM(GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) * order_items.price) as net_sold_revenue,
                        SUM(
                            CASE
                                WHEN (orders.total_amount + orders.discount_amount - orders.shipping_amount - orders.tax_amount) > 0
                                THEN (
                                    (GREATEST(0, order_items.quantity - COALESCE(order_items.returned_quantity, 0)) * order_items.price)
                                    / (orders.total_amount + orders.discount_amount - orders.shipping_amount - orders.tax_amount)
                                ) * orders.discount_amount
                                ELSE 0
                            END
                        ) as allocated_discount
                    ')
                    ->groupBy(
                        'order_items.sku_id',
                        'order_items.product_id',
                        'products.name',
                        'order_items.product_name',
                        'skus.code',
                        'skus.short_code',
                        'colors.name',
                        'sizes.name',
                        'order_items.variant_name',
                        'skus.purchase_price',
                        'skus.cost_price',
                        'skus.stock'
                    )
                    ->orderByDesc('net_qty_sold')
                    ->get();

                foreach ($rows as $r) {
                    $netQty = (int) $r->net_qty_sold;
                    $grossVal = (float) $r->net_sold_revenue;
                    $allocDisc = min($grossVal, max(0.0, (float) $r->allocated_discount));
                    $netVal = max(0.0, $grossVal - $allocDisc);
                    $unitCost = $r->unit_purchase_cost !== null && (float) $r->unit_purchase_cost > 0
                        ? (float) $r->unit_purchase_cost
                        : null;
                    $totalCost = $unitCost !== null ? ($netQty * $unitCost) : null;
                    $profitBefore = $totalCost !== null ? ($grossVal - $totalCost) : null;
                    $profitAfter = $totalCost !== null ? ($netVal - $totalCost) : null;
                    $marginPct = ($profitAfter !== null && $netVal > 0) ? round(($profitAfter / $netVal) * 100, 2) : null;

                    $variantLabel = trim(($r->color_name ?? '') . ($r->size_name ? ' / ' . $r->size_name : ''), ' /');
                    if ($variantLabel === '' && ! empty($r->variant_name)) {
                        $variantLabel = $r->variant_name;
                    }

                    fputcsv($handle, [
                        $r->product_id ?? '',
                        $r->product_name,
                        $r->sku_code,
                        $variantLabel ?: 'Standard',
                        $r->gross_qty_sold,
                        $r->returned_qty,
                        $netQty,
                        number_format((float) $r->avg_sold_price, 2, '.', ''),
                        $unitCost !== null ? number_format($unitCost, 2, '.', '') : 'Not Set',
                        number_format($grossVal, 2, '.', ''),
                        number_format($allocDisc, 2, '.', ''),
                        number_format($netVal, 2, '.', ''),
                        $totalCost !== null ? number_format($totalCost, 2, '.', '') : 'Not Set',
                        $profitBefore !== null ? number_format($profitBefore, 2, '.', '') : 'Not Set',
                        $profitAfter !== null ? number_format($profitAfter, 2, '.', '') : 'Not Set',
                        $marginPct !== null ? $marginPct . '%' : 'N/A',
                        $r->current_stock ?? 0,
                    ]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

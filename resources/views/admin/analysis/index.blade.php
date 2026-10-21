@extends('layouts.admin')

@section('header')
    <div class="flex items-center gap-2.5">
        <span>Analysis</span>
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-indigo-100 text-indigo-700 border border-indigo-200">
            Beta
        </span>
    </div>
@endsection

@push('styles')
<style>
    details.analysis-section > summary {
        list-style: none;
    }
    details.analysis-section > summary::-webkit-details-marker {
        display: none;
    }
    details.analysis-section[open] > summary .chevron-icon {
        transform: rotate(180deg);
    }
</style>
@endpush

@section('content')
<div class="space-y-6 pb-14" x-data="{
    preset: '{{ $preset }}',
    skuFilter: 'all',
    skuSearch: '',
    expandAll() {
        document.querySelectorAll('details.analysis-section').forEach(d => d.open = true);
    },
    collapseAll() {
        document.querySelectorAll('details.analysis-section').forEach(d => d.open = false);
    }
}">

    <!-- Sticky Global Filter & Navigation Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 sticky top-16 z-20">
        <form method="GET" action="{{ route('admin.analysis.index') }}" class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <!-- Left: Date Preset & Custom Range -->
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="flex items-center gap-2">
                    <label for="preset-select" class="text-xs font-bold uppercase tracking-wider text-gray-500">Period:</label>
                    <select id="preset-select" name="preset" x-model="preset"
                            class="text-sm font-semibold border-gray-300 rounded-lg focus:ring-black focus:border-black py-1.5 pl-3 pr-8 bg-gray-50">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="last_7_days">Last 7 Days</option>
                        <option value="last_30_days">Last 30 Days</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_quarter">This Quarter</option>
                        <option value="this_year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>

                <div class="flex items-center gap-2" x-show="preset === 'custom'" x-cloak>
                    <input type="date" name="start_date" value="{{ $startDateInput }}"
                           class="text-sm border-gray-300 rounded-lg py-1.5 px-2.5 focus:ring-black focus:border-black">
                    <span class="text-xs text-gray-400 font-bold">to</span>
                    <input type="date" name="end_date" value="{{ $endDateInput }}"
                           class="text-sm border-gray-300 rounded-lg py-1.5 px-2.5 focus:ring-black focus:border-black">
                </div>

                <!-- Channel Filter -->
                <div class="flex items-center gap-2">
                    <label for="channel-select" class="text-xs font-bold uppercase tracking-wider text-gray-500">Channel:</label>
                    <select id="channel-select" name="channel"
                            class="text-sm font-semibold border-gray-300 rounded-lg focus:ring-black focus:border-black py-1.5 pl-3 pr-8 bg-gray-50">
                        <option value="all" {{ $channel === 'all' ? 'selected' : '' }}>All Channels (Online + POS)</option>
                        <option value="online" {{ $channel === 'online' ? 'selected' : '' }}>Online Storefront Only</option>
                        <option value="pos" {{ $channel === 'pos' ? 'selected' : '' }}>All POS Stores</option>
                        @foreach($posLocations as $loc)
                            <option value="pos_{{ $loc->id }}" {{ $channel === 'pos_' . $loc->id ? 'selected' : '' }}>
                                POS: {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="px-4 py-1.5 bg-black text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-gray-800 transition shadow-sm">
                    Apply Filters
                </button>

                <span class="text-xs text-gray-500 font-medium pl-1">
                    Showing <strong class="text-gray-800">{{ \Carbon\Carbon::parse($startDateInput)->format('d M Y') }}</strong>
                    – <strong class="text-gray-800">{{ \Carbon\Carbon::parse($endDateInput)->format('d M Y') }}</strong>
                    ({{ $daysDiff }} {{ Str::plural('day', $daysDiff) }})
                </span>
            </div>

            <!-- Right: Quick Jump & Expand/Collapse Controls -->
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <a href="#discounts-coupons" class="px-2.5 py-1 rounded-md bg-amber-50 text-amber-800 font-semibold hover:bg-amber-100 transition">Discounts & Coupons</a>
                <a href="#sku-profitability" class="px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-800 font-semibold hover:bg-emerald-100 transition">SKU Cost & Profit</a>
                <a href="#merchandising-split" class="px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-800 font-semibold hover:bg-indigo-100 transition">Online/Offline, Colors, Sizes & Fits</a>
                <a href="#operations-fulfillment" class="px-2.5 py-1 rounded-md bg-blue-50 text-blue-800 font-semibold hover:bg-blue-100 transition">Operations & SLA</a>
                <a href="#returns-rma" class="px-2.5 py-1 rounded-md bg-rose-50 text-rose-800 font-semibold hover:bg-rose-100 transition">Returns (RMA)</a>
                <a href="#geography-inventory" class="px-2.5 py-1 rounded-md bg-purple-50 text-purple-800 font-semibold hover:bg-purple-100 transition">Geo & Stock</a>
                <a href="#user-consent-cookies" class="px-2.5 py-1 rounded-md bg-teal-50 text-teal-800 font-semibold hover:bg-teal-100 transition">Users, Consent & Cookies</a>
                <button type="button" @click="expandAll()" class="px-2 py-1 text-gray-500 hover:text-black font-semibold border border-gray-200 rounded-md" title="Expand All Sections">Expand All</button>
                <button type="button" @click="collapseAll()" class="px-2 py-1 text-gray-500 hover:text-black font-semibold border border-gray-200 rounded-md" title="Collapse All Sections">Collapse All</button>
            </div>
        </form>
    </div>

    <!-- =================================================================== -->
    <!-- EXECUTIVE KPI STRIP                                                 -->
    <!-- =================================================================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
        <!-- 1. Net Revenue -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">Net Order Revenue</span>
                @if($comparisons['net_revenue'] !== null)
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $comparisons['net_revenue'] >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ $comparisons['net_revenue'] >= 0 ? '▲' : '▼' }} {{ abs($comparisons['net_revenue']) }}%
                    </span>
                @endif
            </div>
            <div class="text-2xl font-black text-gray-900">{{ $currencySymbol }}{{ number_format($netRevenue, 2) }}</div>
            <div class="mt-2 pt-2 border-t border-gray-50 flex justify-between text-[11px] text-gray-500">
                <span>Gross Items: {{ $currencySymbol }}{{ number_format($grossItemSales, 0) }}</span>
                <span>AOV: <strong class="text-gray-800">{{ $currencySymbol }}{{ number_format($aov, 0) }}</strong></span>
            </div>
        </div>

        <!-- 2. Total Discount Value -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-amber-100 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-widest text-amber-600">Total Discount Given</span>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-50 text-amber-700">
                    {{ number_format($discountRatePercent, 1) }}% of Gross
                </span>
            </div>
            <div class="text-2xl font-black text-gray-900">{{ $currencySymbol }}{{ number_format($totalDiscount, 2) }}</div>
            <div class="mt-2 pt-2 border-t border-gray-50 flex justify-between text-[11px] text-gray-500">
                <span>Coupon: <strong class="text-gray-800">{{ $currencySymbol }}{{ number_format($couponDiscount, 0) }}</strong></span>
                <span>Prepaid: <strong class="text-gray-800">{{ $currencySymbol }}{{ number_format($prepaidDiscount, 0) }}</strong></span>
            </div>
        </div>

        <!-- 3. Approx. Gross Profit (Tracked SKUs) -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-emerald-100 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-widest text-emerald-600">Approx. Gross Profit</span>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700" title="Percentage of sold units that have a SKU purchase price set">
                    {{ $profitSummary['cost_coverage_pct'] }}% Cost Coverage
                </span>
            </div>
            <div class="text-2xl font-black {{ $profitSummary['approx_profit_after_discount'] >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                {{ $currencySymbol }}{{ number_format($profitSummary['approx_profit_after_discount'], 2) }}
            </div>
            <div class="mt-2 pt-2 border-t border-gray-50 flex justify-between text-[11px] text-gray-500">
                <span>COGS: {{ $currencySymbol }}{{ number_format($profitSummary['total_purchase_cost'], 0) }}</span>
                <span>Margin: <strong class="text-emerald-700">{{ number_format($profitSummary['margin_after_discount_pct'], 1) }}%</strong></span>
            </div>
        </div>

        <!-- 4. Valid vs Placed Orders -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">Valid / Fulfilled Orders</span>
                @if($comparisons['valid_orders'] !== null)
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $comparisons['valid_orders'] >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ $comparisons['valid_orders'] >= 0 ? '▲' : '▼' }} {{ abs($comparisons['valid_orders']) }}%
                    </span>
                @endif
            </div>
            <div class="text-2xl font-black text-gray-900">
                {{ number_format($validOrdersCount) }}
                <span class="text-xs font-medium text-gray-400">/ {{ number_format($totalOrdersPlaced) }} placed</span>
            </div>
            <div class="mt-2 pt-2 border-t border-gray-50 flex justify-between text-[11px] text-gray-500">
                <span>Online: {{ $currencySymbol }}{{ number_format($onlineSales, 0) }}</span>
                <span>POS: {{ $currencySymbol }}{{ number_format($posSales, 0) }}</span>
            </div>
        </div>

        <!-- 5. Operational Leakage (Cancel & Return Rate) -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-rose-100 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-widest text-rose-600">Cancel & Return Rate</span>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-50 text-rose-700">
                    COD Due: {{ $currencySymbol }}{{ number_format($totalBalanceDue, 0) }}
                </span>
            </div>
            <div class="flex items-baseline gap-3">
                <div>
                    <span class="text-2xl font-black text-gray-900">{{ $overallCancelRate }}%</span>
                    <span class="text-[10px] uppercase font-bold text-gray-400">Cancel</span>
                </div>
                <span class="text-gray-300">|</span>
                <div>
                    <span class="text-2xl font-black text-gray-900">{{ $overallReturnRate }}%</span>
                    <span class="text-[10px] uppercase font-bold text-gray-400">Return</span>
                </div>
            </div>
            <div class="mt-2 pt-2 border-t border-gray-50 flex justify-between text-[11px] text-gray-500">
                <span>Cancelled: {{ $currencySymbol }}{{ number_format($cancelledOrdersValue, 0) }}</span>
                <span>Refunded: {{ $currencySymbol }}{{ number_format($totalRefunded, 0) }}</span>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- SECTION 1: DISCOUNTS, PREPAID DISCOUNT & COUPON INTELLIGENCE        -->
    <!-- =================================================================== -->
    <details id="discounts-coupons" class="analysis-section bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" open>
        <summary class="px-6 py-4 bg-gradient-to-r from-amber-50/70 via-white to-white border-b border-gray-100 cursor-pointer flex items-center justify-between select-none">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">1. Total Discounts, Prepaid Discounts & Coupon Usage</h3>
                    <p class="text-xs text-gray-500">Breakdown of total discount value, automatic prepaid checkout discounts, and exact coupon codes redeemed</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.analysis.export', array_merge(request()->query(), ['section' => 'coupons'])) }}"
                   onclick="event.stopPropagation();"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Coupons CSV
                </a>
                <svg class="w-5 h-5 text-gray-400 chevron-icon transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </summary>

        <div class="p-6 space-y-6">
            <!-- 4 Discount Pillars -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200/80">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-800">Total Discount Value</span>
                        <span class="text-[11px] font-semibold text-amber-700">{{ number_format($discountStats->discounted_orders_count ?? 0) }} orders</span>
                    </div>
                    <div class="text-2xl font-black text-amber-950">{{ $currencySymbol }}{{ number_format($totalDiscount, 2) }}</div>
                    <p class="text-[11px] text-amber-700 mt-1">Effective discount rate: <strong>{{ number_format($discountRatePercent, 1) }}%</strong> of gross item value</p>
                </div>

                <div class="p-4 rounded-xl bg-indigo-50/60 border border-indigo-200/80">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-800">Coupon Code Discount</span>
                        <span class="text-[11px] font-semibold text-indigo-700">{{ number_format($discountStats->coupon_orders_count ?? 0) }} orders</span>
                    </div>
                    <div class="text-2xl font-black text-indigo-950">{{ $currencySymbol }}{{ number_format($couponDiscount, 2) }}</div>
                    <p class="text-[11px] text-indigo-700 mt-1">
                        {{ $totalDiscount > 0 ? number_format(($couponDiscount / $totalDiscount) * 100, 1) : 0 }}% of total discounts given
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200/80">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Prepaid Checkout Discount</span>
                        <span class="text-[11px] font-semibold text-emerald-700">{{ number_format($discountStats->prepaid_discount_orders_count ?? 0) }} orders</span>
                    </div>
                    <div class="text-2xl font-black text-emerald-950">{{ $currencySymbol }}{{ number_format($prepaidDiscount, 2) }}</div>
                    <p class="text-[11px] text-emerald-700 mt-1">
                        Incentivized prepaid adoption on {{ $validOrdersCount > 0 ? number_format((($discountStats->prepaid_discount_orders_count ?? 0) / $validOrdersCount) * 100, 1) : 0 }}% of orders
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-purple-50/60 border border-purple-200/80">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-purple-800">Gift Card & POS Manual</span>
                        <span class="text-[11px] font-semibold text-purple-700">{{ number_format($discountStats->gift_card_orders_count ?? 0) }} GC orders</span>
                    </div>
                    <div class="text-2xl font-black text-purple-950">{{ $currencySymbol }}{{ number_format($giftCardDiscount + $otherManualDiscount, 2) }}</div>
                    <p class="text-[11px] text-purple-700 mt-1">
                        Gift Cards: {{ $currencySymbol }}{{ number_format($giftCardDiscount, 0) }} | Manual/Other: {{ $currencySymbol }}{{ number_format($otherManualDiscount, 0) }}
                    </p>
                </div>
            </div>

            <!-- Coupon Leaderboard Table -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-bold text-gray-800">Coupon Performance Leaderboard (Which Coupon Was Used)</h4>
                    <span class="text-xs text-gray-500">{{ $couponBreakdown->count() }} distinct coupon {{ Str::plural('code', $couponBreakdown->count()) }} used in period</span>
                </div>
                <div class="overflow-x-auto border border-gray-100 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-[11px] uppercase tracking-wider border-b border-gray-100">
                                <th class="px-4 py-3 font-bold">Coupon Code</th>
                                <th class="px-4 py-3 font-bold">Rule / Offer</th>
                                <th class="px-4 py-3 font-bold text-right">Times Used</th>
                                <th class="px-4 py-3 font-bold text-right">Coupon Discount</th>
                                <th class="px-4 py-3 font-bold text-right">Stacked Prepaid Disc.</th>
                                <th class="px-4 py-3 font-bold text-right">Total Order Discount</th>
                                <th class="px-4 py-3 font-bold text-right">Net Revenue Generated</th>
                                <th class="px-4 py-3 font-bold text-right">Coupon AOV</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($couponBreakdown as $cp)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-4 py-3 font-bold text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-200 font-mono">
                                            {{ $cp->code }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600">
                                        @if($cp->coupon_type === 'percentage')
                                            {{ number_format((float)$cp->coupon_value, 0) }}% OFF
                                        @elseif($cp->coupon_type === 'fixed')
                                            {{ $currencySymbol }}{{ number_format((float)$cp->coupon_value, 0) }} OFF
                                        @elseif($cp->coupon_type)
                                            {{ strtoupper($cp->coupon_type) }}
                                        @else
                                            <span class="text-gray-400">Custom / Archived</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900">{{ number_format($cp->orders_count) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-indigo-700">{{ $currencySymbol }}{{ number_format($cp->coupon_discount_total, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-emerald-700">{{ $currencySymbol }}{{ number_format($cp->prepaid_discount_stacked, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-amber-700">{{ $currencySymbol }}{{ number_format($cp->total_discount_given, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900">{{ $currencySymbol }}{{ number_format($cp->total_revenue, 2) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $currencySymbol }}{{ number_format($cp->avg_order_value, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-400 text-sm">
                                        No coupon codes were used on valid orders in this date range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </details>

    <!-- =================================================================== -->
    <!-- SECTION 2: SKU PURCHASE COST & APPROX. GROSS PROFIT TALLY           -->
    <!-- =================================================================== -->
    <details id="sku-profitability" class="analysis-section bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" open>
        <summary class="px-6 py-4 bg-gradient-to-r from-emerald-50/70 via-white to-white border-b border-gray-100 cursor-pointer flex items-center justify-between select-none">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">2. SKU Purchase Cost vs. Sold Price & Approx. Gross Profit (Before Shipping)</h3>
                    <p class="text-xs text-gray-500">Tallies each sold SKU against its <code class="text-emerald-700 font-semibold">purchase_price</code> configured in Product Edit &rarr; SKUs</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.analysis.export', array_merge(request()->query(), ['section' => 'sku_profitability'])) }}"
                   onclick="event.stopPropagation();"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export SKU Profit CSV
                </a>
                <svg class="w-5 h-5 text-gray-400 chevron-icon transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </summary>

        <div class="p-6 space-y-6">
            <!-- Profit Tally Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
                <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Cost Coverage</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">{{ $profitSummary['cost_coverage_pct'] }}%</div>
                    <p class="text-[11px] text-gray-600 mt-1">
                        <strong>{{ number_format($profitSummary['tracked_units_sold']) }}</strong> of {{ number_format($profitSummary['total_net_units_sold']) }} net units sold have Purchase Cost set
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-200">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-800">Sold Value (Tracked SKUs)</span>
                    <div class="text-2xl font-black text-blue-950 mt-1">{{ $currencySymbol }}{{ number_format($profitSummary['tracked_sold_before_discount'], 2) }}</div>
                    <p class="text-[11px] text-blue-700 mt-1">
                        After Order Discounts: <strong>{{ $currencySymbol }}{{ number_format($profitSummary['tracked_sold_after_discount'], 2) }}</strong>
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Approx. Purchase Cost (COGS)</span>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ $currencySymbol }}{{ number_format($profitSummary['total_purchase_cost'], 2) }}</div>
                    <p class="text-[11px] text-slate-600 mt-1">
                        Across {{ number_format($profitSummary['tracked_skus_count']) }} cost-tracked {{ Str::plural('SKU', $profitSummary['tracked_skus_count']) }}
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Gross Profit (Before Disc.)</span>
                    <div class="text-2xl font-black text-emerald-900 mt-1">{{ $currencySymbol }}{{ number_format($profitSummary['approx_profit_before_discount'], 2) }}</div>
                    <p class="text-[11px] text-emerald-700 mt-1">
                        Item-Level Margin: <strong>{{ number_format($profitSummary['margin_before_discount_pct'], 1) }}%</strong>
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-emerald-100/60 border border-emerald-300">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-900">Net Gross Profit (After Disc.)</span>
                    <div class="text-2xl font-black text-emerald-950 mt-1">{{ $currencySymbol }}{{ number_format($profitSummary['approx_profit_after_discount'], 2) }}</div>
                    <p class="text-[11px] text-emerald-800 mt-1">
                        Realized Margin (pre-shipping): <strong>{{ number_format($profitSummary['margin_after_discount_pct'], 1) }}%</strong>
                    </p>
                </div>
            </div>

            @if($profitSummary['untracked_skus_count'] > 0)
                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-amber-900">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>
                            <strong>{{ number_format($profitSummary['untracked_skus_count']) }} sold {{ Str::plural('SKU', $profitSummary['untracked_skus_count']) }}</strong>
                            ({{ number_format($profitSummary['untracked_units_sold']) }} net units) do not have a <code>Purchase Price</code> entered yet. Their revenue is excluded from the profit cards above so your margin % stays accurate. Click <strong>Set Cost ↗</strong> on any row below to add it.
                        </span>
                    </div>
                    <button type="button" @click="skuFilter = 'missing'" class="px-3 py-1 bg-amber-600 text-white font-bold rounded-lg hover:bg-amber-700 transition flex-shrink-0">
                        Filter Missing Cost SKUs ({{ $profitSummary['untracked_skus_count'] }})
                    </button>
                </div>
            @endif

            <!-- Filter Bar for SKU Table -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1 text-xs font-bold">
                    <button type="button" @click="skuFilter = 'all'"
                            :class="skuFilter === 'all' ? 'bg-white text-black shadow-sm' : 'text-gray-600 hover:text-black'"
                            class="px-3 py-1.5 rounded-md transition">
                        All Sold SKUs ({{ $skuProfitability->count() }})
                    </button>
                    <button type="button" @click="skuFilter = 'tracked'"
                            :class="skuFilter === 'tracked' ? 'bg-white text-emerald-700 shadow-sm' : 'text-gray-600 hover:text-black'"
                            class="px-3 py-1.5 rounded-md transition">
                        With Purchase Cost ({{ $profitSummary['tracked_skus_count'] }})
                    </button>
                    <button type="button" @click="skuFilter = 'missing'"
                            :class="skuFilter === 'missing' ? 'bg-white text-amber-700 shadow-sm' : 'text-gray-600 hover:text-black'"
                            class="px-3 py-1.5 rounded-md transition">
                        Missing Purchase Cost ({{ $profitSummary['untracked_skus_count'] }})
                    </button>
                </div>

                <div class="relative w-full sm:w-72">
                    <input type="text" x-model="skuSearch" placeholder="Search product, SKU, color, size..."
                           class="w-full text-xs border-gray-300 rounded-lg py-2 pl-3 pr-8 focus:ring-black focus:border-black">
                </div>
            </div>

            <!-- SKU Profitability Table -->
            <div class="overflow-x-auto border border-gray-100 rounded-xl max-h-[540px] overflow-y-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-gray-50 text-gray-500 text-[11px] uppercase tracking-wider border-b border-gray-200">
                            <th class="px-4 py-3 font-bold">Product & SKU Variant</th>
                            <th class="px-3 py-3 font-bold text-right">Net Qty Sold</th>
                            <th class="px-3 py-3 font-bold text-right">Avg Sold Price</th>
                            <th class="px-3 py-3 font-bold text-right">Purchase Cost (Unit)</th>
                            <th class="px-3 py-3 font-bold text-right">Sold Value (Gross)</th>
                            <th class="px-3 py-3 font-bold text-right">Order Disc. Share</th>
                            <th class="px-3 py-3 font-bold text-right">Total Purchase Cost</th>
                            <th class="px-3 py-3 font-bold text-right">Profit (Before Disc.)</th>
                            <th class="px-3 py-3 font-bold text-right">Net Profit (After Disc.)</th>
                            <th class="px-3 py-3 font-bold text-right">Net Margin</th>
                            <th class="px-3 py-3 font-bold text-right">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @forelse($skuProfitability as $skuRow)
                            @php
                                $searchTarget = strtolower(($skuRow->product_name ?? '') . ' ' . ($skuRow->sku_code ?? '') . ' ' . ($skuRow->short_code ?? '') . ' ' . ($skuRow->variant_label ?? ''));
                            @endphp
                            <tr class="hover:bg-gray-50/90 transition-colors"
                                x-show="(skuFilter === 'all' || (skuFilter === 'tracked' && {{ $skuRow->has_cost ? 'true' : 'false' }}) || (skuFilter === 'missing' && {{ $skuRow->has_cost ? 'false' : 'true' }})) && (skuSearch === '' || '{{ addslashes($searchTarget) }}'.includes(skuSearch.toLowerCase()))">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-900">{{ $skuRow->product_name }}</div>
                                    <div class="flex items-center gap-2 mt-0.5 text-[11px] text-gray-500">
                                        <span class="px-1.5 py-0.5 bg-gray-100 rounded font-semibold text-gray-700">{{ $skuRow->variant_label }}</span>
                                        @if($skuRow->sku_code || $skuRow->short_code)
                                            <span class="font-mono text-gray-400">{{ $skuRow->sku_code ?: $skuRow->short_code }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <span class="font-bold text-gray-900">{{ number_format($skuRow->net_qty_sold) }}</span>
                                    @if($skuRow->returned_qty > 0)
                                        <span class="block text-[10px] text-rose-500">({{ $skuRow->gross_qty_sold }} - {{ $skuRow->returned_qty }} ret)</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right font-medium text-gray-800">
                                    {{ $currencySymbol }}{{ number_format($skuRow->avg_sold_price, 2) }}
                                </td>
                                <td class="px-3 py-3 text-right">
                                    @if($skuRow->has_cost)
                                        <span class="font-bold text-blue-700">{{ $currencySymbol }}{{ number_format($skuRow->unit_cost, 2) }}</span>
                                        @if($skuRow->product_id)
                                            <a href="{{ route('admin.products.edit', $skuRow->product_id) }}#skus" target="_blank" class="ml-1 text-[10px] text-gray-400 hover:text-black" title="Edit SKU Cost">✎</a>
                                        @endif
                                    @else
                                        @if($skuRow->product_id)
                                            <a href="{{ route('admin.products.edit', $skuRow->product_id) }}#skus" target="_blank"
                                               class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 font-bold text-[10px] hover:bg-amber-100">
                                                Set Cost ↗
                                            </a>
                                        @else
                                            <span class="text-gray-400 italic">Not Set</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right font-semibold text-gray-900">
                                    {{ $currencySymbol }}{{ number_format($skuRow->net_sold_revenue, 2) }}
                                </td>
                                <td class="px-3 py-3 text-right text-amber-700">
                                    -{{ $currencySymbol }}{{ number_format($skuRow->allocated_discount, 2) }}
                                </td>
                                <td class="px-3 py-3 text-right font-semibold text-slate-700">
                                    @if($skuRow->has_cost)
                                        {{ $currencySymbol }}{{ number_format($skuRow->total_purchase_cost, 2) }}
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right font-bold">
                                    @if($skuRow->has_cost)
                                        <span class="{{ $skuRow->profit_before_discount >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                            {{ $currencySymbol }}{{ number_format($skuRow->profit_before_discount, 2) }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right font-black">
                                    @if($skuRow->has_cost)
                                        <span class="{{ $skuRow->profit_after_discount >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                                            {{ $currencySymbol }}{{ number_format($skuRow->profit_after_discount, 2) }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right">
                                    @if($skuRow->has_cost)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $skuRow->margin_after_discount >= 40 ? 'bg-emerald-50 text-emerald-700' : ($skuRow->margin_after_discount >= 15 ? 'bg-blue-50 text-blue-700' : 'bg-rose-50 text-rose-700') }}">
                                            {{ number_format($skuRow->margin_after_discount, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <span class="font-semibold {{ ($skuRow->current_stock ?? 0) <= 0 ? 'text-rose-600' : (($skuRow->current_stock ?? 0) <= 5 ? 'text-amber-600' : 'text-gray-700') }}">
                                        {{ $skuRow->current_stock ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-8 text-center text-gray-400 text-sm">
                                    No SKU sales recorded for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </details>

    <!-- =================================================================== -->
    <!-- SECTION 2B: ONLINE VS OFFLINE SPLIT, TOP COLORS, SIZES & FITS       -->
    <!-- =================================================================== -->
    <details id="merchandising-split" class="analysis-section bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" open>
        <summary class="px-6 py-4 bg-gradient-to-r from-indigo-50/70 via-white to-white border-b border-gray-100 cursor-pointer flex items-center justify-between select-none">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">Omnichannel & Merchandising Split: Online vs. Offline, Top Colors, Top Sizes & Top Fits</h3>
                    <p class="text-xs text-gray-500">Revenue and unit share split across Online Storefront vs. Offline POS Stores, plus attribute velocity for Colors, Sizes, and Fits</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.analysis.export', array_merge(request()->query(), ['section' => 'merchandising_split'])) }}"
                   onclick="event.stopPropagation();"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Splits CSV
                </a>
                <svg class="w-5 h-5 text-gray-400 chevron-icon transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </summary>

        <div class="p-6 space-y-6">
            <!-- 1. Online vs Offline (POS) Split -->
            <div>
                <h4 class="text-sm font-bold text-gray-800 mb-3">1. Online Storefront vs. Offline (POS Stores) Split</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Online Card -->
                    <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-200">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-black uppercase tracking-wider text-blue-800">Online Storefront (Website / App)</span>
                            <span class="px-2 py-0.5 text-[10px] font-black rounded-full bg-blue-100 text-blue-800">
                                {{ $onlineVsOfflineSummary['online_rev_share'] }}% of Revenue
                            </span>
                        </div>
                        <div class="text-2xl font-black text-blue-950">{{ $currencySymbol }}{{ number_format($onlineVsOfflineSummary['online_revenue'], 2) }}</div>
                        <div class="mt-2 pt-2 border-t border-blue-100 flex flex-wrap justify-between gap-2 text-xs text-blue-800">
                            <span>Valid Orders: <strong>{{ number_format($onlineVsOfflineSummary['online_orders']) }}</strong> ({{ $onlineVsOfflineSummary['online_orders_share'] }}%)</span>
                            <span>AOV: <strong>{{ $currencySymbol }}{{ number_format($onlineVsOfflineSummary['online_aov'], 0) }}</strong></span>
                            <span>Discounts: <strong>{{ $currencySymbol }}{{ number_format($onlineVsOfflineSummary['online_discount'], 0) }}</strong></span>
                        </div>
                    </div>

                    <!-- Offline (POS) Card -->
                    <div class="p-4 rounded-xl bg-purple-50/50 border border-purple-200">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-black uppercase tracking-wider text-purple-800">Offline (Physical POS Stores)</span>
                            <span class="px-2 py-0.5 text-[10px] font-black rounded-full bg-purple-100 text-purple-800">
                                {{ $onlineVsOfflineSummary['offline_rev_share'] }}% of Revenue
                            </span>
                        </div>
                        <div class="text-2xl font-black text-purple-950">{{ $currencySymbol }}{{ number_format($onlineVsOfflineSummary['offline_revenue'], 2) }}</div>
                        <div class="mt-2 pt-2 border-t border-purple-100 flex flex-wrap justify-between gap-2 text-xs text-purple-800">
                            <span>Valid Orders: <strong>{{ number_format($onlineVsOfflineSummary['offline_orders']) }}</strong> ({{ $onlineVsOfflineSummary['offline_orders_share'] }}%)</span>
                            <span>AOV: <strong>{{ $currencySymbol }}{{ number_format($onlineVsOfflineSummary['offline_aov'], 0) }}</strong></span>
                            <span>Discounts: <strong>{{ $currencySymbol }}{{ number_format($onlineVsOfflineSummary['offline_discount'], 0) }}</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Visual Share Bar -->
                <div class="w-full h-3 rounded-full bg-gray-100 overflow-hidden flex mb-4">
                    <div class="bg-blue-600 h-full transition-all" style="width: {{ $onlineVsOfflineSummary['online_rev_share'] }}%" title="Online: {{ $onlineVsOfflineSummary['online_rev_share'] }}%"></div>
                    <div class="bg-purple-600 h-full transition-all" style="width: {{ $onlineVsOfflineSummary['offline_rev_share'] }}%" title="Offline POS: {{ $onlineVsOfflineSummary['offline_rev_share'] }}%"></div>
                </div>

                <!-- Store-by-Store & Online Breakdown Table -->
                <div class="overflow-x-auto border border-gray-100 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-[11px] uppercase tracking-wider border-b border-gray-100">
                                <th class="px-4 py-2.5 font-bold">Channel / Store Location</th>
                                <th class="px-3 py-2.5 font-bold text-right">Placed</th>
                                <th class="px-3 py-2.5 font-bold text-right">Valid Orders</th>
                                <th class="px-3 py-2.5 font-bold text-right">Cancel %</th>
                                <th class="px-3 py-2.5 font-bold text-right">Return %</th>
                                <th class="px-3 py-2.5 font-bold text-right">AOV</th>
                                <th class="px-3 py-2.5 font-bold text-right">Share %</th>
                                <th class="px-4 py-2.5 font-bold text-right">Valid Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @forelse($onlineVsOfflineTable as $chRow)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2.5 font-bold text-gray-900">
                                        <span class="inline-block w-2 h-2 rounded-full mr-1.5 {{ $chRow->channel_group === 'online' ? 'bg-blue-600' : 'bg-purple-600' }}"></span>
                                        {{ $chRow->channel_name }}
                                    </td>
                                    <td class="px-3 py-2.5 text-right">{{ number_format($chRow->total_orders) }}</td>
                                    <td class="px-3 py-2.5 text-right font-semibold">{{ number_format($chRow->valid_orders) }}</td>
                                    <td class="px-3 py-2.5 text-right text-rose-600">{{ $chRow->cancel_rate }}%</td>
                                    <td class="px-3 py-2.5 text-right text-amber-700">{{ $chRow->return_rate }}%</td>
                                    <td class="px-3 py-2.5 text-right">{{ $currencySymbol }}{{ number_format($chRow->aov, 0) }}</td>
                                    <td class="px-3 py-2.5 text-right font-semibold text-indigo-700">{{ $chRow->revenue_share }}%</td>
                                    <td class="px-4 py-2.5 text-right font-black text-gray-900">{{ $currencySymbol }}{{ number_format($chRow->valid_revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-400">No channel sales data in this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2, 3 & 4: Top Colors, Top Sizes & Top Fits (3-Column Grid) -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 pt-2">
                <!-- 2. Top Colors -->
                <div class="border border-gray-100 rounded-xl overflow-hidden flex flex-col">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                        <h4 class="text-sm font-bold text-gray-900">2. Top Colors</h4>
                        <span class="text-[11px] font-semibold text-gray-500">By Net Units Sold</span>
                    </div>
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white text-gray-400 text-[10px] uppercase tracking-wider border-b border-gray-100">
                                    <th class="px-3.5 py-2.5 font-bold">Color</th>
                                    <th class="px-2.5 py-2.5 font-bold text-right">Net Qty</th>
                                    <th class="px-2.5 py-2.5 font-bold text-right">Onl / Off</th>
                                    <th class="px-2.5 py-2.5 font-bold text-right">Share</th>
                                    <th class="px-3.5 py-2.5 font-bold text-right">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-xs">
                                @forelse($topColors as $clr)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3.5 py-2.5 font-bold text-gray-900 flex items-center gap-2">
                                            @if($clr->hex_code)
                                                <span class="w-3.5 h-3.5 rounded-full border border-gray-300 flex-shrink-0" style="background-color: {{ $clr->hex_code }}"></span>
                                            @else
                                                <span class="w-3.5 h-3.5 rounded-full bg-gray-200 border border-gray-300 flex-shrink-0"></span>
                                            @endif
                                            <span class="truncate">{{ $clr->color_name }}</span>
                                        </td>
                                        <td class="px-2.5 py-2.5 text-right font-black text-gray-900">{{ number_format($clr->net_units) }}</td>
                                        <td class="px-2.5 py-2.5 text-right text-[11px] text-gray-500">
                                            <span class="text-blue-600 font-semibold">{{ $clr->online_units }}</span> / <span class="text-purple-600 font-semibold">{{ $clr->offline_units }}</span>
                                        </td>
                                        <td class="px-2.5 py-2.5 text-right">
                                            <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold text-[10px]">{{ $clr->unit_share_pct }}%</span>
                                        </td>
                                        <td class="px-3.5 py-2.5 text-right font-semibold text-gray-800">{{ $currencySymbol }}{{ number_format($clr->net_revenue, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">No color data in this range.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. Top Sizes -->
                <div class="border border-gray-100 rounded-xl overflow-hidden flex flex-col">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                        <h4 class="text-sm font-bold text-gray-900">3. Top Sizes</h4>
                        <span class="text-[11px] font-semibold text-gray-500">Size Curve Velocity</span>
                    </div>
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white text-gray-400 text-[10px] uppercase tracking-wider border-b border-gray-100">
                                    <th class="px-3.5 py-2.5 font-bold">Size</th>
                                    <th class="px-2.5 py-2.5 font-bold text-right">Net Qty</th>
                                    <th class="px-2.5 py-2.5 font-bold text-right">Onl / Off</th>
                                    <th class="px-2.5 py-2.5 font-bold text-right">Share</th>
                                    <th class="px-3.5 py-2.5 font-bold text-right">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-xs">
                                @forelse($topSizes as $sz)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3.5 py-2.5 font-bold text-gray-900">
                                            @if($sz->size_code)
                                                <span class="px-1.5 py-0.5 rounded bg-gray-100 border border-gray-200 font-mono text-[11px] mr-1">{{ $sz->size_code }}</span>
                                            @endif
                                            <span>{{ $sz->size_name }}</span>
                                        </td>
                                        <td class="px-2.5 py-2.5 text-right font-black text-gray-900">{{ number_format($sz->net_units) }}</td>
                                        <td class="px-2.5 py-2.5 text-right text-[11px] text-gray-500">
                                            <span class="text-blue-600 font-semibold">{{ $sz->online_units }}</span> / <span class="text-purple-600 font-semibold">{{ $sz->offline_units }}</span>
                                        </td>
                                        <td class="px-2.5 py-2.5 text-right">
                                            <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold text-[10px]">{{ $sz->unit_share_pct }}%</span>
                                        </td>
                                        <td class="px-3.5 py-2.5 text-right font-semibold text-gray-800">{{ $currencySymbol }}{{ number_format($sz->net_revenue, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">No size data in this range.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 4. Top Fits -->
                <div class="border border-gray-100 rounded-xl overflow-hidden flex flex-col">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                        <h4 class="text-sm font-bold text-gray-900">4. Top Fits</h4>
                        <span class="text-[11px] font-semibold text-gray-500">Fit Preference & Return %</span>
                    </div>
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white text-gray-400 text-[10px] uppercase tracking-wider border-b border-gray-100">
                                    <th class="px-3.5 py-2.5 font-bold">Fit</th>
                                    <th class="px-2.5 py-2.5 font-bold text-right">Net Qty</th>
                                    <th class="px-2.5 py-2.5 font-bold text-right">Onl / Off</th>
                                    <th class="px-2.5 py-2.5 font-bold text-right">Share</th>
                                    <th class="px-3.5 py-2.5 font-bold text-right">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-xs">
                                @forelse($topFits as $ft)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3.5 py-2.5 font-bold text-gray-900">
                                            {{ $ft->fit_name }}
                                            @if($ft->return_rate_pct > 0)
                                                <span class="block text-[10px] font-normal text-rose-500">Return: {{ $ft->return_rate_pct }}%</span>
                                            @endif
                                        </td>
                                        <td class="px-2.5 py-2.5 text-right font-black text-gray-900">{{ number_format($ft->net_units) }}</td>
                                        <td class="px-2.5 py-2.5 text-right text-[11px] text-gray-500">
                                            <span class="text-blue-600 font-semibold">{{ $ft->online_units }}</span> / <span class="text-purple-600 font-semibold">{{ $ft->offline_units }}</span>
                                        </td>
                                        <td class="px-2.5 py-2.5 text-right">
                                            <span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 font-bold text-[10px]">{{ $ft->unit_share_pct }}%</span>
                                        </td>
                                        <td class="px-3.5 py-2.5 text-right font-semibold text-gray-800">{{ $currencySymbol }}{{ number_format($ft->net_revenue, 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-400">No fit data in this range.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </details>

    <!-- =================================================================== -->
    <!-- SECTION 3: REVENUE & DISCOUNT TREND CHART                           -->
    <!-- =================================================================== -->
    <details class="analysis-section bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" open>
        <summary class="px-6 py-4 border-b border-gray-100 cursor-pointer flex items-center justify-between select-none">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-black text-white flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">3. Revenue, Discounts & Order Velocity Trend</h3>
                    <p class="text-xs text-gray-500">Daily/Monthly progression of Net Order Value vs. Total Discounts & Order Count</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-gray-400 chevron-icon transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </summary>
        <div class="p-6">
            <div class="h-72 w-full">
                <canvas id="analysisTrendChart"></canvas>
            </div>
        </div>
    </details>

    <!-- =================================================================== -->
    <!-- SECTION 4: OPERATIONS, FULFILLMENT & PAYMENT RISK INTELLIGENCE      -->
    <!-- =================================================================== -->
    <details id="operations-fulfillment" class="analysis-section bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" open>
        <summary class="px-6 py-4 bg-gradient-to-r from-blue-50/70 via-white to-white border-b border-gray-100 cursor-pointer flex items-center justify-between select-none">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">4. Operations, Fulfillment Speed & Payment Risk (Prepaid vs. COD)</h3>
                    <p class="text-xs text-gray-500">Order pipeline funnel, dispatch/delivery SLA turnaround times, and cancellation risk by payment mode</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.analysis.export', array_merge(request()->query(), ['section' => 'operations_payments'])) }}"
                   onclick="event.stopPropagation();"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Payment Split CSV
                </a>
                <svg class="w-5 h-5 text-gray-400 chevron-icon transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </summary>

        <div class="p-6 space-y-6">
            <!-- Order Pipeline Funnel Strip -->
            <div>
                <h4 class="text-xs font-black uppercase tracking-wider text-gray-400 mb-3">Order Status Pipeline ({{ number_format($totalOrdersPlaced) }} Total Orders in Period)</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    @foreach($statusPipeline as $key => $stage)
                        <div class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/70">
                            <div class="text-[11px] font-bold text-gray-500">{{ $stage['label'] }}</div>
                            <div class="text-xl font-black text-gray-900 mt-1">{{ number_format($stage['count']) }}</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">{{ $currencySymbol }}{{ number_format($stage['amount'], 0) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- SLA Speed & Bottleneck Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-700">Avg Dispatch Speed</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">
                        {{ $slaStats->avg_dispatch_hours !== null ? number_format((float)$slaStats->avg_dispatch_hours, 1) . ' hrs' : 'N/A' }}
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">Order Placed &rarr; Shipped timestamp</p>
                </div>

                <div class="p-4 rounded-xl bg-indigo-50/50 border border-indigo-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-700">Avg Courier Transit Time</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">
                        {{ $slaStats->avg_transit_days !== null ? number_format((float)$slaStats->avg_transit_days, 1) . ' days' : 'N/A' }}
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">Shipped &rarr; Delivered timestamp</p>
                </div>

                <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">End-to-End Delivery Time</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">
                        {{ $slaStats->avg_total_delivery_days !== null ? number_format((float)$slaStats->avg_total_delivery_days, 1) . ' days' : 'N/A' }}
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">Order Placed &rarr; Customer Doorstep</p>
                </div>

                <div class="p-4 rounded-xl {{ ($slaStats->aging_unshipped_orders ?? 0) > 0 ? 'bg-rose-50 border-rose-200' : 'bg-gray-50 border-gray-200' }} border">
                    <span class="text-xs font-bold uppercase tracking-wider {{ ($slaStats->aging_unshipped_orders ?? 0) > 0 ? 'text-rose-700' : 'text-gray-500' }}">
                        Unshipped Bottleneck (&gt;48h)
                    </span>
                    <div class="text-2xl font-black {{ ($slaStats->aging_unshipped_orders ?? 0) > 0 ? 'text-rose-700' : 'text-gray-900' }} mt-1">
                        {{ number_format($slaStats->aging_unshipped_orders ?? 0) }} orders
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">Pending/Processing for over 48 hours</p>
                </div>
            </div>

            <!-- Payment Mode Split & Cancellation Risk Table -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <h4 class="text-sm font-bold text-gray-800 mb-3">Payment Mode Split & Cancellation / Return Risk</h4>
                    <div class="overflow-x-auto border border-gray-100 rounded-xl">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-[11px] uppercase tracking-wider border-b border-gray-100">
                                    <th class="px-4 py-3 font-bold">Payment Segment</th>
                                    <th class="px-3 py-3 font-bold text-right">Placed</th>
                                    <th class="px-3 py-3 font-bold text-right">Cancelled</th>
                                    <th class="px-3 py-3 font-bold text-right">Cancel %</th>
                                    <th class="px-3 py-3 font-bold text-right">Return %</th>
                                    <th class="px-3 py-3 font-bold text-right">Valid Revenue</th>
                                    <th class="px-4 py-3 font-bold text-right">COD Balance Due</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                @forelse($paymentSplitRaw as $pm)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-bold text-gray-900">{{ $pm->payment_segment }}</td>
                                        <td class="px-3 py-3 text-right font-semibold">{{ number_format($pm->total_orders) }}</td>
                                        <td class="px-3 py-3 text-right text-rose-600">{{ number_format($pm->cancelled_orders) }}</td>
                                        <td class="px-3 py-3 text-right">
                                            <span class="px-2 py-0.5 rounded-full font-bold {{ $pm->cancel_rate >= 20 ? 'bg-rose-50 text-rose-700' : 'bg-gray-100 text-gray-700' }}">
                                                {{ $pm->cancel_rate }}%
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-right">
                                            <span class="px-2 py-0.5 rounded-full font-bold {{ $pm->return_rate >= 15 ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-700' }}">
                                                {{ $pm->return_rate }}%
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-right font-bold text-emerald-700">{{ $currencySymbol }}{{ number_format($pm->valid_revenue, 2) }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-cyan-700">{{ $currencySymbol }}{{ number_format($pm->pending_cod_due, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-400">No payment data in this range.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- In-House vs Qikink POD Split -->
                <div>
                    <h4 class="text-sm font-bold text-gray-800 mb-3">Fulfillment Source (In-House vs. POD)</h4>
                    <div class="space-y-3">
                        @forelse($fulfillmentSourceSplit as $src)
                            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/70 flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-bold text-gray-900">{{ $src->fulfillment_source }}</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        {{ number_format($src->orders_count) }} orders &bull; {{ number_format($src->units_ordered) }} units
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-black text-gray-900">{{ $currencySymbol }}{{ number_format($src->gross_revenue, 0) }}</div>
                                    <div class="text-[10px] uppercase font-bold text-gray-400">Item Value</div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-xs text-gray-400 border border-gray-100 rounded-xl">No fulfillment source data.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </details>

    <!-- =================================================================== -->
    <!-- SECTION 5: RETURNS, EXCHANGES (RMA) & QUALITY INSIGHTS              -->
    <!-- =================================================================== -->
    <details id="returns-rma" class="analysis-section bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" open>
        <summary class="px-6 py-4 bg-gradient-to-r from-rose-50/70 via-white to-white border-b border-gray-100 cursor-pointer flex items-center justify-between select-none">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">5. Returns, Exchanges (RMA) & Product Quality Intelligence</h3>
                    <p class="text-xs text-gray-500">Return vs. Exchange retention ratio, RMA resolution turnaround, return reasons, and most returned SKUs</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-gray-400 chevron-icon transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </summary>

        <div class="p-6 space-y-6">
            @php
                $totalRma = (int) ($rmaSummary->total_requests ?? 0);
                $exchangeCount = (int) ($rmaSummary->exchange_count ?? 0);
                $returnCount = (int) ($rmaSummary->return_count ?? 0);
                $exchangeRetentionPct = $totalRma > 0 ? round(($exchangeCount / $totalRma) * 100, 1) : 0;
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-rose-50/50 border border-rose-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-700">Total RMA Requests</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format($totalRma) }}</div>
                    <p class="text-[11px] text-gray-600 mt-1">
                        Returns: <strong>{{ $returnCount }}</strong> | Exchanges: <strong>{{ $exchangeCount }}</strong>
                    </p>
                </div>

                <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Exchange Retention Ratio</span>
                    <div class="text-2xl font-black text-emerald-800 mt-1">{{ $exchangeRetentionPct }}%</div>
                    <p class="text-[11px] text-gray-600 mt-1">Share of RMAs retained via Exchange instead of Refund</p>
                </div>

                <div class="p-4 rounded-xl bg-amber-50/50 border border-amber-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-700">RMA Refund Value</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">{{ $currencySymbol }}{{ number_format($rmaSummary->total_refund_amount ?? 0, 2) }}</div>
                    <p class="text-[11px] text-gray-600 mt-1">Fees Deducted: {{ $currencySymbol }}{{ number_format($rmaSummary->total_fee_deducted ?? 0, 2) }}</p>
                </div>

                <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-blue-700">RMA Resolution Speed</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">
                        {{ $rmaSummary->avg_approval_hours !== null ? number_format((float)$rmaSummary->avg_approval_hours, 1) . 'h' : 'N/A' }}
                    </div>
                    <p class="text-[11px] text-gray-600 mt-1">
                        Avg Approval Time (Full completion: {{ $rmaSummary->avg_completion_days !== null ? number_format((float)$rmaSummary->avg_completion_days, 1) . 'd' : 'N/A' }})
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Return Reasons -->
                <div>
                    <h4 class="text-sm font-bold text-gray-800 mb-3">Top Return & Exchange Reasons</h4>
                    <div class="overflow-x-auto border border-gray-100 rounded-xl">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-[11px] uppercase tracking-wider border-b border-gray-100">
                                    <th class="px-4 py-3 font-bold">Reason</th>
                                    <th class="px-3 py-3 font-bold">Type</th>
                                    <th class="px-3 py-3 font-bold text-right">Requests</th>
                                    <th class="px-4 py-3 font-bold text-right">Refund Value</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                @forelse($returnReasons as $rr)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $rr->reason_label }}</td>
                                        <td class="px-3 py-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $rr->type === 'exchange' ? 'bg-blue-50 text-blue-700' : 'bg-rose-50 text-rose-700' }}">
                                                {{ $rr->type }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-right font-bold">{{ number_format($rr->requests_count) }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ $currencySymbol }}{{ number_format($rr->refund_total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-400">No RMA requests in this date range.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Most Returned SKUs -->
                <div>
                    <h4 class="text-sm font-bold text-gray-800 mb-3">Most Returned / Exchanged SKUs</h4>
                    <div class="overflow-x-auto border border-gray-100 rounded-xl">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-[11px] uppercase tracking-wider border-b border-gray-100">
                                    <th class="px-4 py-3 font-bold">Product & Variant</th>
                                    <th class="px-3 py-3 font-bold text-right">Returned Units</th>
                                    <th class="px-3 py-3 font-bold text-right">Exchanged Units</th>
                                    <th class="px-4 py-3 font-bold text-right">Total RMA Units</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-xs">
                                @forelse($mostReturnedSkus as $rsku)
                                    @php
                                        $vLabel = trim(($rsku->color_name ?? '') . ($rsku->size_name ? ' / ' . $rsku->size_name : ''), ' /') ?: ($rsku->variant_name ?: 'Standard');
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-900">{{ $rsku->product_name }}</div>
                                            <div class="text-[11px] text-gray-500">{{ $vLabel }} {{ $rsku->sku_code ? '(' . $rsku->sku_code . ')' : '' }}</div>
                                        </td>
                                        <td class="px-3 py-3 text-right font-semibold text-rose-600">{{ number_format($rsku->return_units) }}</td>
                                        <td class="px-3 py-3 text-right font-semibold text-blue-600">{{ number_format($rsku->exchange_units) }}</td>
                                        <td class="px-4 py-3 text-right font-black text-gray-900">{{ number_format($rsku->rma_units_count) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-400">No returned SKU items in this date range.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </details>

    <!-- =================================================================== -->
    <!-- SECTION 6: GEOGRAPHY & WAREHOUSE INVENTORY VALUATION                -->
    <!-- =================================================================== -->
    <details id="geography-inventory" class="analysis-section bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" open>
        <summary class="px-6 py-4 bg-gradient-to-r from-purple-50/70 via-white to-white border-b border-gray-100 cursor-pointer flex items-center justify-between select-none">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">6. Geographic Demand, RTO Hotspots & Current Stock Valuation</h3>
                    <p class="text-xs text-gray-500">Top states by revenue and cancellation/return rates + live warehouse stock value at retail vs. purchase cost</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.analysis.export', array_merge(request()->query(), ['section' => 'geography'])) }}"
                   onclick="event.stopPropagation();"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Geography CSV
                </a>
                <svg class="w-5 h-5 text-gray-400 chevron-icon transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </summary>

        <div class="p-6 space-y-6">
            <!-- Warehouse Stock Valuation Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Active Warehouse Units</span>
                    <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format($inventorySummary->total_units_in_stock ?? 0) }}</div>
                    <p class="text-[11px] text-gray-600 mt-1">Across {{ number_format($inventorySummary->total_active_skus ?? 0) }} active SKUs</p>
                </div>

                <div class="p-4 rounded-xl bg-purple-50/50 border border-purple-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-800">Stock Retail Valuation</span>
                    <div class="text-2xl font-black text-purple-950 mt-1">{{ $currencySymbol }}{{ number_format($inventorySummary->retail_stock_value ?? 0, 0) }}</div>
                    <p class="text-[11px] text-purple-700 mt-1">At current SKU selling prices</p>
                </div>

                <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">Stock Purchase Cost Value</span>
                    <div class="text-2xl font-black text-emerald-950 mt-1">{{ $currencySymbol }}{{ number_format($inventorySummary->cost_stock_value ?? 0, 0) }}</div>
                    <p class="text-[11px] text-emerald-700 mt-1">Based on SKU <code>purchase_price</code></p>
                </div>

                <div class="p-4 rounded-xl bg-rose-50/50 border border-rose-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-rose-800">Stockout / Low Stock Risk</span>
                    <div class="text-2xl font-black text-rose-900 mt-1">
                        {{ number_format($inventorySummary->out_of_stock_skus ?? 0) }} OOS
                    </div>
                    <p class="text-[11px] text-rose-700 mt-1">{{ number_format($inventorySummary->low_stock_skus ?? 0) }} SKUs with &le; 5 units left</p>
                </div>
            </div>

            <!-- Geographic Performance Table -->
            <div>
                <h4 class="text-sm font-bold text-gray-800 mb-3">State / Region Demand & Cancellation / Return Hotspots</h4>
                <div class="overflow-x-auto border border-gray-100 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 text-[11px] uppercase tracking-wider border-b border-gray-100">
                                <th class="px-4 py-3 font-bold">State / Region</th>
                                <th class="px-3 py-3 font-bold text-right">Placed Orders</th>
                                <th class="px-3 py-3 font-bold text-right">Valid Orders</th>
                                <th class="px-3 py-3 font-bold text-right">Cancelled</th>
                                <th class="px-3 py-3 font-bold text-right">Cancel Rate %</th>
                                <th class="px-3 py-3 font-bold text-right">Return Rate %</th>
                                <th class="px-4 py-3 font-bold text-right">Net Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs">
                            @forelse($geographicStats as $geo)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-bold text-gray-900">{{ $geo->state_name }}</td>
                                    <td class="px-3 py-3 text-right">{{ number_format($geo->total_orders) }}</td>
                                    <td class="px-3 py-3 text-right font-semibold">{{ number_format($geo->valid_orders) }}</td>
                                    <td class="px-3 py-3 text-right text-rose-600">{{ number_format($geo->cancelled_orders) }}</td>
                                    <td class="px-3 py-3 text-right">
                                        <span class="px-2 py-0.5 rounded-full font-bold {{ $geo->cancel_rate >= 25 ? 'bg-rose-50 text-rose-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $geo->cancel_rate }}%
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <span class="px-2 py-0.5 rounded-full font-bold {{ $geo->return_rate >= 15 ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $geo->return_rate }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900">{{ $currencySymbol }}{{ number_format($geo->net_revenue, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-400">No regional order data in this date range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </details>

    <!-- =================================================================== -->
    <!-- SECTION 7: REGISTERED USERS, SIGNUP SPLIT, CONSENT & COOKIE STATUS  -->
    <!-- =================================================================== -->
    <details id="user-consent-cookies" class="analysis-section bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" open>
        <summary class="px-6 py-4 bg-gradient-to-r from-teal-50/70 via-white to-white border-b border-gray-100 cursor-pointer flex items-center justify-between select-none">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-teal-600 text-white flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 21a11.955 11.955 0 01-9.618-7.016m19.236 0a11.955 11.955 0 00-19.236 0M12 11V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900">7. Registered Users: Signup Split (Google vs. Email/WhatsApp), Marketing Consent & Cookie Status</h3>
                    <p class="text-xs text-gray-500">Breakdown out of <strong>{{ number_format($userConsentAnalytics['total_users']) }} total registered customers</strong> ({{ number_format($userConsentAnalytics['new_users_in_period']) }} new in selected period)</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.analysis.export', array_merge(request()->query(), ['section' => 'user_consent'])) }}"
                   onclick="event.stopPropagation();"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export User Consent CSV
                </a>
                <svg class="w-5 h-5 text-gray-400 chevron-icon transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
        </summary>

        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- 1. Signup Method Split (Google vs Email/WhatsApp OTP) -->
                <div class="p-5 rounded-xl border border-gray-200 bg-gray-50/50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-gray-900">1. Signup Method Split</h4>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white border border-gray-200 text-gray-700">
                                Total: {{ number_format($userConsentAnalytics['total_users']) }} Users
                            </span>
                        </div>

                        <div class="space-y-3">
                            <!-- Email / WhatsApp OTP -->
                            <div class="p-3.5 rounded-xl bg-white border border-gray-100 shadow-sm">
                                <div class="flex items-center justify-between text-xs font-bold text-gray-800 mb-1">
                                    <span>Email / Phone (WhatsApp OTP)</span>
                                    <span class="text-emerald-700">{{ $userConsentAnalytics['email_whatsapp_signup_pct'] }}%</span>
                                </div>
                                <div class="text-xl font-black text-gray-900">
                                    {{ number_format($userConsentAnalytics['email_whatsapp_signup_count']) }}
                                    <span class="text-xs font-medium text-gray-400">/ {{ number_format($userConsentAnalytics['total_users']) }} users</span>
                                </div>
                                <div class="w-full h-1.5 bg-gray-100 rounded-full mt-2 overflow-hidden">
                                    <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $userConsentAnalytics['email_whatsapp_signup_pct'] }}%"></div>
                                </div>
                            </div>

                            <!-- Google Signup -->
                            <div class="p-3.5 rounded-xl bg-white border border-gray-100 shadow-sm">
                                <div class="flex items-center justify-between text-xs font-bold text-gray-800 mb-1">
                                    <span>Google OAuth Signup</span>
                                    <span class="text-blue-700">{{ $userConsentAnalytics['google_signup_pct'] }}%</span>
                                </div>
                                <div class="text-xl font-black text-gray-900">
                                    {{ number_format($userConsentAnalytics['google_signup_count']) }}
                                    <span class="text-xs font-medium text-gray-400">/ {{ number_format($userConsentAnalytics['total_users']) }} users</span>
                                </div>
                                <div class="w-full h-1.5 bg-gray-100 rounded-full mt-2 overflow-hidden">
                                    <div class="bg-blue-500 h-full rounded-full" style="width: {{ $userConsentAnalytics['google_signup_pct'] }}%"></div>
                                </div>
                            </div>

                            @if($userConsentAnalytics['other_social_signup_count'] > 0)
                                <div class="p-3 rounded-xl bg-white border border-gray-100 flex items-center justify-between text-xs">
                                    <span class="font-bold text-gray-700">Other Social Login</span>
                                    <span class="font-black text-gray-900">{{ number_format($userConsentAnalytics['other_social_signup_count']) }} ({{ $userConsentAnalytics['other_social_signup_pct'] }}%)</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-gray-200/80 flex items-center justify-between text-xs text-gray-500">
                        <span>New Signups in Selected Period:</span>
                        <strong class="text-gray-900">{{ number_format($userConsentAnalytics['new_users_in_period']) }} users</strong>
                    </div>
                </div>

                <!-- 2. Marketing Channel Consent (Email, WhatsApp, SMS out of Total Users) -->
                <div class="p-5 rounded-xl border border-gray-200 bg-gray-50/50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-gray-900">2. Marketing Consent (Out of Total Users)</h4>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                Any Opt-In: {{ $userConsentAnalytics['any_marketing_pct'] }}%
                            </span>
                        </div>

                        <div class="space-y-3">
                            <!-- Email Consent -->
                            <div class="p-3.5 rounded-xl bg-white border border-gray-100 shadow-sm">
                                <div class="flex items-center justify-between text-xs font-bold text-gray-800 mb-1">
                                    <span>Email Marketing Consent</span>
                                    <span class="text-indigo-700">{{ $userConsentAnalytics['email_consent_pct'] }}%</span>
                                </div>
                                <div class="text-xl font-black text-gray-900">
                                    {{ number_format($userConsentAnalytics['email_consent_count']) }}
                                    <span class="text-xs font-medium text-gray-400">out of {{ number_format($userConsentAnalytics['total_users']) }} users</span>
                                </div>
                                <div class="w-full h-1.5 bg-gray-100 rounded-full mt-2 overflow-hidden">
                                    <div class="bg-indigo-500 h-full rounded-full" style="width: {{ $userConsentAnalytics['email_consent_pct'] }}%"></div>
                                </div>
                            </div>

                            <!-- WhatsApp Consent -->
                            <div class="p-3.5 rounded-xl bg-white border border-gray-100 shadow-sm">
                                <div class="flex items-center justify-between text-xs font-bold text-gray-800 mb-1">
                                    <span>WhatsApp Marketing Consent</span>
                                    <span class="text-emerald-700">{{ $userConsentAnalytics['whatsapp_consent_pct'] }}%</span>
                                </div>
                                <div class="text-xl font-black text-gray-900">
                                    {{ number_format($userConsentAnalytics['whatsapp_consent_count']) }}
                                    <span class="text-xs font-medium text-gray-400">out of {{ number_format($userConsentAnalytics['total_users']) }} users</span>
                                </div>
                                <div class="w-full h-1.5 bg-gray-100 rounded-full mt-2 overflow-hidden">
                                    <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $userConsentAnalytics['whatsapp_consent_pct'] }}%"></div>
                                </div>
                            </div>

                            <!-- SMS Consent -->
                            <div class="p-3.5 rounded-xl bg-white border border-gray-100 shadow-sm">
                                <div class="flex items-center justify-between text-xs font-bold text-gray-800 mb-1">
                                    <span>SMS Marketing Consent</span>
                                    <span class="text-amber-700">{{ $userConsentAnalytics['sms_consent_pct'] }}%</span>
                                </div>
                                <div class="text-xl font-black text-gray-900">
                                    {{ number_format($userConsentAnalytics['sms_consent_count']) }}
                                    <span class="text-xs font-medium text-gray-400">out of {{ number_format($userConsentAnalytics['total_users']) }} users</span>
                                </div>
                                <div class="w-full h-1.5 bg-gray-100 rounded-full mt-2 overflow-hidden">
                                    <div class="bg-amber-500 h-full rounded-full" style="width: {{ $userConsentAnalytics['sms_consent_pct'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-gray-200/80 flex items-center justify-between text-xs text-gray-500">
                        <span>Terms & Privacy Policy Accepted:</span>
                        <strong class="text-gray-900">{{ number_format($userConsentAnalytics['terms_consent_count']) }} / {{ number_format($userConsentAnalytics['total_users']) }} ({{ $userConsentAnalytics['terms_consent_pct'] }}%)</strong>
                    </div>
                </div>

                <!-- 3. Cookie & Pixel Tracking Status of Registered Users -->
                <div class="p-5 rounded-xl border border-gray-200 bg-gray-50/50 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-gray-900">3. Cookie Status of Registered Users</h4>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                Accepted Any: {{ $userConsentAnalytics['cookie_accepted_any_pct'] }}%
                            </span>
                        </div>

                        <!-- Cookie Overall Status Strip -->
                        <div class="grid grid-cols-3 gap-2 mb-3 text-center">
                            <div class="p-2.5 rounded-xl bg-emerald-50/80 border border-emerald-200">
                                <div class="text-[10px] font-bold uppercase text-emerald-800">Accepted</div>
                                <div class="text-base font-black text-emerald-950 mt-0.5">{{ number_format($userConsentAnalytics['cookie_accepted_any_count']) }}</div>
                                <div class="text-[10px] text-emerald-700">{{ $userConsentAnalytics['cookie_accepted_any_pct'] }}%</div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-rose-50/80 border border-rose-200">
                                <div class="text-[10px] font-bold uppercase text-rose-800">Rejected All</div>
                                <div class="text-base font-black text-rose-950 mt-0.5">{{ number_format($userConsentAnalytics['cookie_rejected_all_count']) }}</div>
                                <div class="text-[10px] text-rose-700">{{ $userConsentAnalytics['cookie_rejected_all_pct'] }}%</div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-gray-100 border border-gray-200">
                                <div class="text-[10px] font-bold uppercase text-gray-600">Not Saved</div>
                                <div class="text-base font-black text-gray-900 mt-0.5">{{ number_format($userConsentAnalytics['cookie_not_set_count']) }}</div>
                                <div class="text-[10px] text-gray-500">{{ $userConsentAnalytics['cookie_not_set_pct'] }}%</div>
                            </div>
                        </div>

                        <!-- Per-Tracker Cookie Breakdown -->
                        <div class="space-y-2.5">
                            <div class="p-3 rounded-xl bg-white border border-gray-100 flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold text-gray-900">Google Analytics Cookie</span>
                                    <span class="block text-[10px] text-gray-400"><code>tracking_consent.google_analytics</code></span>
                                </div>
                                <div class="text-right">
                                    <span class="font-black text-gray-900">{{ number_format($userConsentAnalytics['cookie_ga_count']) }} / {{ number_format($userConsentAnalytics['total_users']) }}</span>
                                    <span class="ml-1.5 px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 font-bold text-[10px]">{{ $userConsentAnalytics['cookie_ga_pct'] }}%</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-xl bg-white border border-gray-100 flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold text-gray-900">Meta (Facebook) Pixel Cookie</span>
                                    <span class="block text-[10px] text-gray-400"><code>tracking_consent.meta_pixel</code></span>
                                </div>
                                <div class="text-right">
                                    <span class="font-black text-gray-900">{{ number_format($userConsentAnalytics['cookie_meta_count']) }} / {{ number_format($userConsentAnalytics['total_users']) }}</span>
                                    <span class="ml-1.5 px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold text-[10px]">{{ $userConsentAnalytics['cookie_meta_pct'] }}%</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-xl bg-white border border-gray-100 flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold text-gray-900">Snapchat Pixel Cookie</span>
                                    <span class="block text-[10px] text-gray-400"><code>tracking_consent.snapchat_pixel</code></span>
                                </div>
                                <div class="text-right">
                                    <span class="font-black text-gray-900">{{ number_format($userConsentAnalytics['cookie_snapchat_count']) }} / {{ number_format($userConsentAnalytics['total_users']) }}</span>
                                    <span class="ml-1.5 px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 font-bold text-[10px]">{{ $userConsentAnalytics['cookie_snapchat_pct'] }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-gray-200/80 flex items-center justify-between text-xs text-gray-500">
                        <span>Accepted All 3 Trackers:</span>
                        <strong class="text-emerald-700">{{ number_format($userConsentAnalytics['cookie_accepted_all_count']) }} / {{ number_format($userConsentAnalytics['total_users']) }} ({{ $userConsentAnalytics['cookie_accepted_all_pct'] }}%)</strong>
                    </div>
                </div>
            </div>
        </div>
    </details>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('analysisTrendChart');
    if (!canvas) return;

    const labels = @json($chartLabels);
    const salesData = @json($chartSales);
    const discountData = @json($chartDiscounts);
    const ordersData = @json($chartOrders);
    const currencySymbol = @json($currencySymbol);

    new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    type: 'line',
                    label: 'Net Order Value (' + currencySymbol + ')',
                    data: salesData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    borderWidth: 2.5,
                    tension: 0.35,
                    fill: true,
                    yAxisID: 'y',
                    pointRadius: 2.5,
                },
                {
                    type: 'line',
                    label: 'Total Discounts (' + currencySymbol + ')',
                    data: discountData,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.08)',
                    borderWidth: 2,
                    borderDash: [4, 4],
                    tension: 0.35,
                    fill: false,
                    yAxisID: 'y',
                    pointRadius: 2,
                },
                {
                    type: 'bar',
                    label: 'Valid Orders',
                    data: ordersData,
                    backgroundColor: 'rgba(59, 130, 246, 0.22)',
                    borderColor: 'rgba(59, 130, 246, 0.6)',
                    borderWidth: 1,
                    borderRadius: 4,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: { family: 'Inter', size: 11, weight: 'bold' },
                        usePointStyle: true,
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Inter', size: 10 }, maxTicksLimit: 12 }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    grid: { color: '#f3f4f6' },
                    ticks: {
                        font: { family: 'Inter', size: 10 },
                        callback: function (value) {
                            return currencySymbol + new Intl.NumberFormat().format(value);
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    ticks: {
                        font: { family: 'Inter', size: 10 },
                        precision: 0
                    }
                }
            }
        }
    });
});
</script>
@endpush

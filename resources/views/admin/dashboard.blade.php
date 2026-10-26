@extends('layouts.admin')

@section('header')
    <div class="flex items-center gap-2.5">
        <span>Dashboard</span>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-700 border border-emerald-200">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Live Pulse
        </span>
    </div>
@endsection

@section('content')
<div class="space-y-6 pb-12">

    <!-- Top Command & Shortcut Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-base font-bold text-gray-900">Store Command Center</h1>
            <p class="text-xs text-gray-500">
                Real-time operational snapshot for <strong>{{ now()->format('l, d M Y') }}</strong> &bull; All-time Net Revenue: <strong class="text-gray-800">{{ $currencySymbol }}{{ number_format($stats['revenue'], 0) }}</strong> ({{ number_format($stats['total_orders']) }} total orders)
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Add Product
            </a>
            <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-lg transition">
                Manage Orders
            </a>
            <a href="{{ Route::has('admin.analysis.index') ? route('admin.analysis.index') : url(config('app.admin_path', 'occ').'/analysis') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-white bg-black hover:bg-gray-800 rounded-lg shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Open Analysis (Beta) &rarr;
            </a>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 1. EXECUTIVE & ACTION KPI STRIP (Matches Analysis UI)               -->
    <!-- =================================================================== -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
        <!-- Card 1: Today's Pulse -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-widest text-gray-400">Today's Revenue</span>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 text-emerald-700">
                    {{ number_format($stats['today_orders']) }} {{ Str::plural('Order', $stats['today_orders']) }}
                </span>
            </div>
            <div class="text-2xl font-black text-gray-900">{{ $currencySymbol }}{{ number_format($stats['today_revenue'], 2) }}</div>
            <div class="mt-2 pt-2 border-t border-gray-50 flex justify-between text-[11px] text-gray-500">
                <span>Yesterday: <strong class="text-gray-800">{{ $currencySymbol }}{{ number_format($stats['yesterday_revenue'], 0) }}</strong></span>
                <a href="{{ Route::has('admin.analysis.index') ? route('admin.analysis.index', ['preset' => 'today']) : url(config('app.admin_path', 'occ').'/analysis?preset=today') }}" class="text-indigo-600 font-bold hover:underline">Breakdown &rarr;</a>
            </div>
        </div>

        <!-- Card 2: This Month Revenue & AOV -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-emerald-100 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-widest text-emerald-600">This Month Revenue</span>
                @if($stats['month_growth_pct'] !== null)
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $stats['month_growth_pct'] >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ $stats['month_growth_pct'] >= 0 ? '▲' : '▼' }} {{ abs($stats['month_growth_pct']) }}%
                    </span>
                @endif
            </div>
            <div class="text-2xl font-black text-gray-900">{{ $currencySymbol }}{{ number_format($stats['month_revenue'], 2) }}</div>
            <div class="mt-2 pt-2 border-t border-gray-50 flex justify-between text-[11px] text-gray-500">
                <span>Orders: <strong class="text-gray-800">{{ number_format($stats['month_orders']) }}</strong></span>
                <span>AOV: <strong class="text-gray-800">{{ $currencySymbol }}{{ number_format($stats['month_aov'], 0) }}</strong></span>
            </div>
        </div>

        <!-- Card 3: Fulfillment Action Queue (Orders to Ship) -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border {{ $stats['aging_orders'] > 0 ? 'border-amber-200' : 'border-gray-100' }} flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-widest text-amber-600">Orders to Dispatch</span>
                @if($stats['aging_orders'] > 0)
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-50 text-rose-700 animate-pulse">
                        {{ $stats['aging_orders'] }} &gt; 48h old
                    </span>
                @else
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-50 text-amber-700">Action Queue</span>
                @endif
            </div>
            <div class="text-2xl font-black text-gray-900">{{ number_format($stats['orders_to_ship']) }}</div>
            <div class="mt-2 pt-2 border-t border-gray-50 flex justify-between text-[11px] text-gray-500">
                <span>Pending: <strong class="text-amber-700">{{ number_format($stats['pending_orders']) }}</strong></span>
                <span>Packed: <strong class="text-indigo-700">{{ number_format($stats['processing_orders']) }}</strong></span>
            </div>
        </div>

        <!-- Card 4: Returns (RMA) & COD Collections -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-rose-100 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-widest text-rose-600">Returns & COD Due</span>
                <a href="{{ route('admin.returns.index') }}" class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-50 text-rose-700 hover:bg-rose-100">
                    {{ number_format($stats['return_requests']) }} Pending RMA
                </a>
            </div>
            <div class="text-2xl font-black text-gray-900">{{ $currencySymbol }}{{ number_format($stats['pending_payments'], 0) }}</div>
            <div class="mt-2 pt-2 border-t border-gray-50 flex justify-between text-[11px] text-gray-500">
                <span>Active RMAs: <strong class="text-gray-800">{{ number_format($stats['active_rma_count']) }}</strong></span>
                <span>Cancel Req: <strong class="text-rose-600">{{ number_format($stats['cancel_requests']) }}</strong></span>
            </div>
        </div>

        <!-- Card 5: Inventory & Catalog Health -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-purple-100 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-black uppercase tracking-widest text-purple-600">Stock & Catalog</span>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-purple-50 text-purple-700">
                    {{ number_format($stats['total_products']) }} Active Products
                </span>
            </div>
            <div class="flex items-baseline gap-2.5">
                <div>
                    <span class="text-2xl font-black text-rose-600">{{ number_format($stats['out_of_stock']) }}</span>
                    <span class="text-[10px] font-bold uppercase text-gray-400">OOS</span>
                </div>
                <span class="text-gray-300">|</span>
                <div>
                    <span class="text-2xl font-black text-amber-600">{{ number_format($stats['low_stock']) }}</span>
                    <span class="text-[10px] font-bold uppercase text-gray-400">Low (&le;5)</span>
                </div>
            </div>
            <div class="mt-2 pt-2 border-t border-gray-50 flex justify-between text-[11px] text-gray-500">
                <span>Pending Reviews: <strong class="text-gray-800">{{ number_format($stats['pending_reviews']) }}</strong></span>
                <a href="{{ route('admin.products.index') }}" class="text-purple-700 font-bold hover:underline">Catalog &rarr;</a>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 2. UNIFIED ORDER FULFILLMENT PIPELINE STRIP                         -->
    <!-- =================================================================== -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <h3 class="text-xs font-black uppercase tracking-wider text-gray-500">All-Time Order Status Pipeline</h3>
            </div>
            <a href="{{ Route::has('admin.analysis.index') ? route('admin.analysis.index') : url(config('app.admin_path', 'occ').'/analysis') }}#operations-fulfillment" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                View SLA & Payment Risk Analysis &rarr;
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="p-3.5 rounded-xl border border-amber-100 bg-amber-50/40 hover:bg-amber-50 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-amber-800">Pending</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="text-xl font-black text-gray-900 mt-1">{{ number_format($stats['pending_orders']) }}</div>
            </a>

            <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" class="p-3.5 rounded-xl border border-indigo-100 bg-indigo-50/40 hover:bg-indigo-50 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-indigo-800">Confirmed / Packed</span>
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                </div>
                <div class="text-xl font-black text-gray-900 mt-1">{{ number_format($stats['processing_orders']) }}</div>
            </a>

            <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" class="p-3.5 rounded-xl border border-blue-100 bg-blue-50/40 hover:bg-blue-50 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-blue-800">Shipped</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                </div>
                <div class="text-xl font-black text-gray-900 mt-1">{{ number_format($stats['shipped_orders']) }}</div>
            </a>

            <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" class="p-3.5 rounded-xl border border-emerald-100 bg-emerald-50/40 hover:bg-emerald-50 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-emerald-800">Delivered</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="text-xl font-black text-gray-900 mt-1">{{ number_format($stats['delivered_orders']) }}</div>
            </a>

            <a href="{{ route('admin.returns.index') }}" class="p-3.5 rounded-xl border border-orange-100 bg-orange-50/40 hover:bg-orange-50 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-orange-800">Returned / Refunded</span>
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                </div>
                <div class="text-xl font-black text-gray-900 mt-1">{{ number_format($stats['returned_orders']) }}</div>
            </a>

            <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="p-3.5 rounded-xl border border-rose-100 bg-rose-50/40 hover:bg-rose-50 transition">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-rose-800">Cancelled</span>
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                </div>
                <div class="text-xl font-black text-gray-900 mt-1">{{ number_format($stats['cancelled_orders']) }}</div>
            </a>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 3. SALES TREND & SHIPPING MIX CHARTS                                -->
    <!-- =================================================================== -->
    <div x-data="dashboardCharts()" x-init="initCharts()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sales Summary -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-200 relative">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-5 pb-4 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Sales Performance Trend</h3>
                    <p class="text-xs text-gray-500">Order volume and revenue progression for selected period</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center bg-gray-100 rounded-lg p-0.5 border border-gray-200 text-xs font-bold">
                        <button type="button" @click="salesType = 'amount'; updateSalesChart()" :class="salesType === 'amount' ? 'bg-white shadow-sm text-black' : 'text-gray-500 hover:text-black'" class="px-3 py-1 rounded-md transition">Revenue</button>
                        <button type="button" @click="salesType = 'order'; updateSalesChart()" :class="salesType === 'order' ? 'bg-white shadow-sm text-black' : 'text-gray-500 hover:text-black'" class="px-3 py-1 rounded-md transition">Orders</button>
                    </div>
                    <select x-model="range" @change="fetchData()" class="text-xs font-semibold border-gray-300 rounded-lg py-1.5 pl-2.5 pr-7 bg-gray-50 focus:ring-black focus:border-black">
                        <template x-for="(label, value) in rangeOptions" :key="value">
                            <option :value="value" x-text="label"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="flex items-baseline justify-between mb-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400" x-text="salesType === 'amount' ? 'Period Net Sales' : 'Period Valid Orders'"></span>
                    <div class="text-2xl font-black text-gray-900" x-text="salesTotal">0</div>
                </div>
                <a href="{{ Route::has('admin.analysis.index') ? route('admin.analysis.index') : url(config('app.admin_path', 'occ').'/analysis') }}" class="text-xs font-bold text-emerald-700 hover:underline">
                    View SKU Profit & Discount Breakdown &rarr;
                </a>
            </div>

            <div class="relative h-64 w-full">
                <canvas id="salesChart"></canvas>
                <div x-show="!salesHasData" style="display: none;" class="absolute inset-0 flex items-center justify-center bg-white/80 z-10 backdrop-blur-[1px]">
                    <span class="text-gray-400 text-sm font-medium">No orders recorded in this period.</span>
                </div>
            </div>
        </div>

        <!-- Shipping Overview -->
        <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-gray-200 flex flex-col justify-between relative">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Fulfillment Mix</h3>
                    <p class="text-xs text-gray-500" x-text="rangeOptions[range]"></p>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-xs font-black text-gray-800" x-text="shippingTotal + ' Orders'"></span>
            </div>

            <div class="my-6 flex flex-col sm:flex-row lg:flex-col xl:flex-row items-center justify-center gap-6">
                <div class="relative w-40 h-40 flex-shrink-0">
                    <canvas id="shippingChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Total</span>
                        <span class="text-2xl font-black text-gray-900" x-text="shippingTotal">0</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-1 gap-2.5 text-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#fbbf24]"></span>
                        <span class="text-gray-600 font-medium">Pending:</span>
                        <strong class="text-gray-900 ml-auto" x-text="shippingData[0] || 0"></strong>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#8b5cf6]"></span>
                        <span class="text-gray-600 font-medium">Packed:</span>
                        <strong class="text-gray-900 ml-auto" x-text="shippingData[1] || 0"></strong>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#3b82f6]"></span>
                        <span class="text-gray-600 font-medium">Shipped:</span>
                        <strong class="text-gray-900 ml-auto" x-text="shippingData[2] || 0"></strong>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#10b981]"></span>
                        <span class="text-gray-600 font-medium">Delivered:</span>
                        <strong class="text-gray-900 ml-auto" x-text="shippingData[3] || 0"></strong>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#f43f5e]"></span>
                        <span class="text-gray-600 font-medium">Cancelled:</span>
                        <strong class="text-gray-900 ml-auto" x-text="shippingData[4] || 0"></strong>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#f97316]"></span>
                        <span class="text-gray-600 font-medium">Returned:</span>
                        <strong class="text-gray-900 ml-auto" x-text="shippingData[5] || 0"></strong>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>Cancelled Value (All Time):</span>
                <strong class="text-rose-600">{{ $currencySymbol }}{{ number_format($stats['cancelled_amount'], 0) }}</strong>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 4. RECENT TRANSACTIONS & INVENTORY RESTOCK WATCHLIST                -->
    <!-- =================================================================== -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Recent Orders Table -->
        <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Recent Transactions</h3>
                    <p class="text-xs text-gray-500">Latest orders across Online Storefront and POS</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    View All Orders &rarr;
                </a>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-[11px] uppercase tracking-wider border-b border-gray-100">
                            <th class="px-5 py-3 font-bold">Order #</th>
                            <th class="px-4 py-3 font-bold">Customer</th>
                            <th class="px-4 py-3 font-bold">Channel / Mode</th>
                            <th class="px-4 py-3 font-bold text-right">Amount</th>
                            <th class="px-4 py-3 font-bold">Status</th>
                            <th class="px-5 py-3 font-bold text-right">Placed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @forelse($recent_orders as $order)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-gray-900 hover:text-indigo-600 font-mono">
                                        #{{ $order->order_number }}
                                    </a>
                                    @if($order->coupon_code)
                                        <span class="ml-1.5 px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">
                                            {{ $order->coupon_code }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 font-semibold text-gray-800">
                                    {{ $order->user->name ?? $order->shippingAddress->name ?? $order->customer_name ?? 'Guest Customer' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($order->pos_location_id)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">POS</span>
                                    @elseif(in_array(strtolower($order->payment_method ?? ''), ['cod', 'cash_on_delivery']))
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            {{ ($order->upfront_amount ?? 0) > 0 ? 'Partial COD' : 'COD' }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Prepaid</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right font-bold text-gray-900">
                                    {{ $currencySymbol }}{{ number_format($order->total_amount, 2) }}
                                </td>
                                <td class="px-4 py-3.5">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'processing' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            'shipped' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        ];
                                        $colorClass = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                                    @endphp
                                    <span class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-md border {{ $colorClass }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right text-gray-500 font-medium">
                                    {{ $order->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">
                                    No recent orders found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Column: Low Stock Restock Watchlist & Quick Actions -->
        <div class="space-y-6">
            <!-- Low Stock Watchlist -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Restock Watchlist</h3>
                        <p class="text-[11px] text-gray-500">Active SKUs with lowest stock (&le; 5 units)</p>
                    </div>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-50 text-rose-700">
                        {{ $stats['out_of_stock'] }} OOS
                    </span>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($low_stock_items as $item)
                        @php
                            $variantText = trim(($item->color_name ?? '') . ($item->size_name ? ' / ' . $item->size_name : ''), ' /') ?: 'Standard';
                        @endphp
                        <div class="px-5 py-3 flex items-center justify-between text-xs hover:bg-gray-50">
                            <div class="min-w-0 pr-3">
                                <div class="font-bold text-gray-900 truncate">{{ $item->product_name }}</div>
                                <div class="text-[11px] text-gray-500">
                                    {{ $variantText }}
                                    @if($item->sku_code)
                                        <span class="font-mono text-gray-400">({{ $item->sku_code }})</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="px-2 py-0.5 rounded font-black text-[11px] {{ $item->stock <= 0 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $item->stock }} left
                                </span>
                                <a href="{{ route('admin.products.edit', $item->product_id) }}#skus" class="text-indigo-600 hover:text-indigo-900 font-bold">
                                    Edit &rarr;
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-6 text-center text-xs text-gray-400">
                            All active SKUs have healthy stock (&gt; 5 units).
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Shortcuts -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Quick Shortcuts</h3>
                <div class="grid grid-cols-2 gap-2.5 text-xs font-bold">
                    <a href="{{ route('admin.returns.index') }}" class="p-3 rounded-xl bg-gray-50 hover:bg-black hover:text-white text-gray-800 border border-gray-100 transition flex items-center justify-between">
                        <span>Returns (RMA)</span>
                        <span>&rarr;</span>
                    </a>
                    <a href="{{ route('admin.online-store.custom-widgets.index') }}" class="p-3 rounded-xl bg-gray-50 hover:bg-black hover:text-white text-gray-800 border border-gray-100 transition flex items-center justify-between">
                        <span>Custom Widget</span>
                        <span>&rarr;</span>
                    </a>
                    <a href="{{ route('admin.abandoned-carts.index') }}" class="p-3 rounded-xl bg-gray-50 hover:bg-black hover:text-white text-gray-800 border border-gray-100 transition flex items-center justify-between">
                        <span>Abandoned Carts</span>
                        <span>&rarr;</span>
                    </a>
                    <a href="{{ route('admin.online-store.mnpages.index') }}" class="p-3 rounded-xl bg-gray-50 hover:bg-black hover:text-white text-gray-800 border border-gray-100 transition flex items-center justify-between">
                        <span>Store Pages</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function dashboardCharts() {
    return {
        range: 'this_month',
        salesType: 'amount',
        salesChart: null,
        shippingChart: null,
        salesData: { labels: [], counts: [], amounts: [] },
        shippingData: [0, 0, 0, 0, 0, 0],
        shippingTotal: 0,
        currencySymbol: '{{ $currencySymbol }}',

        rangeOptions: {
            'this_week': 'This Week',
            'this_month': 'This Month',
            'this_quarter': 'This Quarter',
            'this_year': 'This Year',
            'previous_week': 'Previous Week',
            'previous_month': 'Previous Month',
            'previous_quarter': 'Previous Quarter',
            'previous_year': 'Previous Year',
        },

        get salesHasData() {
            if (this.salesType === 'order') {
                return this.salesData.counts && this.salesData.counts.some(v => v > 0);
            }
            return this.salesData.amounts && this.salesData.amounts.some(v => v > 0);
        },

        get salesTotal() {
            if (this.salesType === 'order') {
                let total = this.salesData.counts ? this.salesData.counts.reduce((a, b) => a + b, 0) : 0;
                return new Intl.NumberFormat().format(total) + ' Orders';
            }
            let total = this.salesData.amounts ? this.salesData.amounts.reduce((a, b) => a + b, 0) : 0;
            return this.currencySymbol + new Intl.NumberFormat().format(total);
        },

        initCharts() {
            this.fetchData();
        },

        async fetchData() {
            try {
                const response = await fetch(`{{ route('admin.dashboard.charts') }}?range=${this.range}`);
                const data = await response.json();

                this.salesData = data.sales;
                this.shippingData = data.shipping.data;
                this.shippingTotal = data.shipping.total;

                this.updateSalesChart();
                this.updateShippingChart();
            } catch (error) {
                console.error("Error fetching chart data", error);
            }
        },

        updateSalesChart() {
            const ctx = document.getElementById('salesChart').getContext('2d');
            const data = this.salesType === 'order' ? this.salesData.counts : this.salesData.amounts;

            if (this.salesChart) {
                this.salesChart.destroy();
            }

            this.salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.salesData.labels,
                    datasets: [{
                        label: this.salesType === 'order' ? 'Orders' : 'Amount',
                        data: data,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#10b981',
                        pointBorderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#ffffff',
                            titleColor: '#111827',
                            bodyColor: '#374151',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false,
                            callbacks: {
                                label: (context) => {
                                    let val = context.parsed.y;
                                    if (this.salesType === 'amount') {
                                        return this.currencySymbol + new Intl.NumberFormat().format(val);
                                    }
                                    return new Intl.NumberFormat().format(val) + ' Orders';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 10, family: 'Inter' },
                                color: '#9ca3af',
                                maxTicksLimit: 8,
                                callback: function(val) {
                                    let label = this.getLabelForValue(val);
                                    if (label && label.includes('-')) {
                                        let parts = label.split('-');
                                        if (parts.length === 3) {
                                            const d = new Date(label);
                                            return d.getDate() + ' ' + d.toLocaleString('default', { month: 'short' });
                                        } else if (parts.length === 2) {
                                            const d = new Date(label + '-01');
                                            return d.toLocaleString('default', { month: 'short', year: '2-digit' });
                                        }
                                    }
                                    return label;
                                }
                            }
                        },
                        y: {
                            grid: { color: '#f3f4f6' },
                            ticks: {
                                font: { size: 10, family: 'Inter' },
                                color: '#9ca3af',
                                padding: 8,
                                maxTicksLimit: 6,
                                callback: function(value) {
                                    if (value >= 1000) {
                                        return (value / 1000) + 'k';
                                    }
                                    return value;
                                }
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        },

        updateShippingChart() {
            const ctx = document.getElementById('shippingChart').getContext('2d');

            if (this.shippingChart) {
                this.shippingChart.destroy();
            }

            let plotData = this.shippingData;
            let bgColors = [
                '#fbbf24', // pending
                '#8b5cf6', // packed
                '#3b82f6', // shipped
                '#10b981', // delivered
                '#f43f5e', // cancelled
                '#f97316'  // returned
            ];

            if (this.shippingTotal === 0) {
                plotData = [1];
                bgColors = ['#f3f4f6'];
            }

            this.shippingChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: this.shippingTotal === 0 ? ['No Data'] : ['Pending', 'Packed', 'Shipped', 'Delivered', 'Cancelled', 'Returned'],
                    datasets: [{
                        data: plotData,
                        backgroundColor: bgColors,
                        borderWidth: 0,
                        hoverOffset: this.shippingTotal === 0 ? 0 : 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '74%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: this.shippingTotal > 0,
                            backgroundColor: '#ffffff',
                            titleColor: '#111827',
                            bodyColor: '#374151',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            padding: 10
                        }
                    }
                }
            });
        }
    }
}
</script>
@endsection

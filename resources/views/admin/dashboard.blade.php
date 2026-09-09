@extends('layouts.admin')

@section('header', 'Dashboard')

@section('content')
<div class="space-y-8 pb-10">


    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Revenue Card -->
        <div class="bg-white p-5 rounded-2xl shadow-sm hover:shadow-xl hover:shadow-emerald-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/30 text-white font-bold text-xl font-serif">
                    {{ $currencySymbol }}
                </div>
                <span class="px-2 py-1 text-[10px] font-black uppercase tracking-wider text-emerald-600 bg-emerald-50 rounded-lg">Net</span>
            </div>
            <div class="relative z-10">
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Total Revenue</p>
                <p class="text-2xl font-black text-gray-900 tracking-tight">{{ $currencySymbol }}{{ number_format($stats['revenue']) }}</p>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="bg-white p-5 rounded-2xl shadow-sm hover:shadow-xl hover:shadow-blue-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <span class="px-2 py-1 text-[10px] font-black uppercase tracking-wider text-blue-600 bg-blue-50 rounded-lg">All Time</span>
            </div>
            <div class="relative z-10">
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Total Orders</p>
                <p class="text-2xl font-black text-gray-900 tracking-tight">{{ number_format($stats['total_orders']) }}</p>
            </div>
        </div>

        <!-- Pending Orders Card -->
        <div class="bg-white p-5 rounded-2xl shadow-sm hover:shadow-xl hover:shadow-amber-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-amber-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="px-2 py-1 text-[10px] font-black uppercase tracking-wider text-amber-600 bg-amber-50 rounded-lg relative flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Action Needed
                </span>
            </div>
            <div class="relative z-10">
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Pending Orders</p>
                <p class="text-2xl font-black text-gray-900 tracking-tight">{{ number_format($stats['pending_orders']) }}</p>
            </div>
        </div>

        <!-- Products Card -->
        <div class="bg-white p-5 rounded-2xl shadow-sm hover:shadow-xl hover:shadow-purple-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-purple-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shadow-lg shadow-purple-500/30">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <span class="px-2 py-1 text-[10px] font-black uppercase tracking-wider text-purple-600 bg-purple-50 rounded-lg">Live</span>
            </div>
            <div class="relative z-10">
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Total Products</p>
                <p class="text-2xl font-black text-gray-900 tracking-tight">{{ number_format($stats['total_products']) }}</p>
            </div>
        </div>

    </div>
    
    <!-- Secondary Stats Grid (4 cols) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Return Requests Card -->
        <div class="bg-white p-5 rounded-2xl shadow-sm hover:shadow-xl hover:shadow-red-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-red-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center shadow-lg shadow-red-500/30">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                </div>
            </div>
            <div class="relative z-10">
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Return Requests</p>
                <p class="text-2xl font-black text-gray-900 tracking-tight">{{ number_format($stats['return_requests']) }}</p>
            </div>
        </div>

        <!-- Pending Cancel Requests Card -->
        <div class="bg-white p-5 rounded-2xl shadow-sm hover:shadow-xl hover:shadow-rose-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-rose-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-400 to-red-500 flex items-center justify-center shadow-lg shadow-rose-500/30">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="relative z-10">
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Pending Cancel Requests</p>
                <p class="text-2xl font-black text-gray-900 tracking-tight">{{ number_format($stats['cancel_requests']) }}</p>
            </div>
        </div>

        <!-- Yet to Receive Payments Card -->
        <div class="bg-white p-5 rounded-2xl shadow-sm hover:shadow-xl hover:shadow-cyan-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-cyan-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-teal-500 flex items-center justify-center shadow-lg shadow-cyan-500/30 text-white font-bold text-xl font-serif">
                    {{ $currencySymbol }}
                </div>
                <span class="px-2 py-1 text-[10px] font-black uppercase tracking-wider text-cyan-600 bg-cyan-50 rounded-lg">COD Due</span>
            </div>
            <div class="relative z-10">
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Yet to Receive Payments</p>
                <p class="text-2xl font-black text-gray-900 tracking-tight">{{ $currencySymbol }}{{ number_format($stats['pending_payments']) }}</p>
            </div>
        </div>

        <!-- Out of Stock Items Card -->
        <div class="bg-white p-5 rounded-2xl shadow-sm hover:shadow-xl hover:shadow-gray-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-gray-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center shadow-lg shadow-gray-500/30">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <span class="px-2 py-1 text-[10px] font-black uppercase tracking-wider text-gray-600 bg-gray-100 rounded-lg">SKUs</span>
            </div>
            <div class="relative z-10">
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Out of Stock Items</p>
                <p class="text-2xl font-black text-gray-900 tracking-tight">{{ number_format($stats['out_of_stock']) }}</p>
            </div>
        </div>
    </div>

    <!-- Third Row Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Shipped Orders Card -->
        <div class="bg-white p-5 rounded-2xl shadow-sm hover:shadow-xl hover:shadow-blue-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                </div>
            </div>
            <div class="relative z-10">
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Shipped Orders</p>
                <p class="text-2xl font-black text-gray-900 tracking-tight">{{ number_format($stats['shipped_orders']) }}</p>
            </div>
        </div>

        <!-- Delivered Orders Card -->
        <div class="bg-white p-5 rounded-2xl shadow-sm hover:shadow-xl hover:shadow-green-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-green-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-400 to-emerald-500 flex items-center justify-center shadow-lg shadow-green-500/30">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
            </div>
            <div class="relative z-10">
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Delivered Orders</p>
                <p class="text-2xl font-black text-gray-900 tracking-tight">{{ number_format($stats['delivered_orders']) }}</p>
            </div>
        </div>

        <!-- Returned Orders Card -->
        <div class="bg-white p-5 rounded-2xl shadow-sm hover:shadow-xl hover:shadow-orange-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-orange-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-400 to-amber-500 flex items-center justify-center shadow-lg shadow-orange-500/30">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"></path></svg>
                </div>
            </div>
            <div class="relative z-10">
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Returned Orders</p>
                <p class="text-2xl font-black text-gray-900 tracking-tight">{{ number_format($stats['returned_orders']) }}</p>
            </div>
        </div>

        <!-- Cancelled Orders Card -->
        <div class="bg-white p-5 rounded-2xl shadow-sm hover:shadow-xl hover:shadow-gray-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-gray-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-3 relative z-10">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-gray-400 to-gray-600 flex items-center justify-center shadow-lg shadow-gray-500/30">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="relative z-10">
                <p class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-1">Cancelled Orders</p>
                <p class="text-2xl font-black text-gray-900 tracking-tight">{{ number_format($stats['cancelled_orders']) }}</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div x-data="dashboardCharts()" x-init="initCharts()" class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Sales Summary -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 relative">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-[15px] font-semibold text-gray-700">Sales Summary</h3>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-1.5 text-sm text-gray-500 font-medium hover:text-gray-700">
                        <span x-text="rangeOptions[range]">This Month</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div x-show="open" style="display: none;" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg shadow-gray-200/50 border border-gray-100 py-2 z-50">
                        <template x-for="(label, value) in rangeOptions" :key="value">
                            <button @click="range = value; fetchData(); open = false" 
                                :class="range === value ? 'bg-[#3b82f6] text-white shadow-sm ring-2 ring-[#3b82f6]/30 ring-offset-1 mx-2 rounded-lg' : 'text-gray-600 hover:bg-gray-50 px-4'"
                                class="w-[calc(100%-16px)] mx-auto text-left py-2 text-sm font-medium transition-all mb-0.5 last:mb-0">
                                <span x-text="label" :class="range === value ? 'px-2' : ''"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-between mb-6">
                <div class="text-2xl font-bold text-gray-900" x-text="salesTotal">0</div>
                <div class="flex items-center bg-gray-50 rounded-full p-1 border border-gray-100">
                    <button @click="salesType = 'order'; updateSalesChart()" :class="salesType === 'order' ? 'bg-white shadow-sm text-blue-600 border border-blue-200/50' : 'text-gray-500 hover:text-gray-700 border-transparent'" class="px-5 py-1.5 text-xs font-semibold rounded-full transition-all border">Order</button>
                    <button @click="salesType = 'amount'; updateSalesChart()" :class="salesType === 'amount' ? 'bg-white shadow-sm text-blue-600 border border-blue-200/50' : 'text-gray-500 hover:text-gray-700 border-transparent'" class="px-5 py-1.5 text-xs font-semibold rounded-full transition-all border">Amount</button>
                </div>
            </div>
            
            <div class="relative h-64 w-full">
                <canvas id="salesChart"></canvas>
                <div x-show="!salesHasData" style="display: none;" class="absolute inset-0 flex items-center justify-center bg-white/80 z-10 backdrop-blur-[1px]">
                    <span class="text-gray-500 text-sm font-medium">No data found.</span>
                </div>
            </div>
        </div>

        <!-- Shipping Overview -->
        <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col relative">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-[15px] font-semibold text-gray-700">Shipping Overview</h3>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-1.5 text-sm text-gray-500 font-medium hover:text-gray-700">
                        <span x-text="rangeOptions[range]">This Month</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div x-show="open" style="display: none;" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg shadow-gray-200/50 border border-gray-100 py-2 z-50">
                        <template x-for="(label, value) in rangeOptions" :key="value">
                            <button @click="range = value; fetchData(); open = false" 
                                :class="range === value ? 'bg-[#3b82f6] text-white shadow-sm ring-2 ring-[#3b82f6]/30 ring-offset-1 mx-2 rounded-lg' : 'text-gray-600 hover:bg-gray-50 px-4'"
                                class="w-[calc(100%-16px)] mx-auto text-left py-2 text-sm font-medium transition-all mb-0.5 last:mb-0">
                                <span x-text="label" :class="range === value ? 'px-2' : ''"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex-1 flex flex-col sm:flex-row items-center justify-center gap-8">
                <div class="relative w-40 h-40 flex-shrink-0">
                    <canvas id="shippingChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-[9px] font-bold text-[#3a3541de] uppercase tracking-widest mb-1">All Shippings</span>
                        <span class="text-2xl font-bold text-gray-900" x-text="shippingTotal">0</span>
                    </div>
                </div>
                
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded bg-[#fbbf24]"></span>
                        <span class="text-sm text-[#3a3541de] font-medium">Pending</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded bg-[#8b5cf6]"></span>
                        <span class="text-sm text-[#3a3541de] font-medium">Packed</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded bg-[#3b82f6]"></span>
                        <span class="text-sm text-[#3a3541de] font-medium">Shipped</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded bg-[#10b981]"></span>
                        <span class="text-sm text-[#3a3541de] font-medium">Delivered</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <!-- Recent Orders Table -->
        <div class="xl:col-span-2">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden h-full flex flex-col">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-white relative z-10">
                    <h3 class="text-lg font-black text-gray-900 tracking-tight">Recent Transactions</h3>
                    <a href="{{ route('admin.orders.index') }}" class="group flex items-center gap-1 text-sm font-bold text-blue-600 hover:text-blue-700 transition-colors">
                        View All
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Order ID</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Customer</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Amount</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Status</th>
                                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($recent_orders as $order)
                                <tr class="hover:bg-gray-50/80 transition-colors group">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="font-black text-gray-900 group-hover:text-blue-600 transition-colors">#{{ $order->order_number }}</a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-gray-900 text-sm">{{ $order->user ? $order->user->name : ($order->shipping_address['name'] ?? 'Guest') }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-bold text-gray-900">₹{{ number_format($order->total_amount, 2) }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                'processing' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                'shipped' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                                'delivered' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                'cancelled' => 'bg-red-100 text-red-700 border-red-200',
                                            ];
                                            $colorClass = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                                        @endphp
                                        <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-lg border {{ $colorClass }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 text-xs font-semibold">
                                        {{ $order->created_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center space-y-3">
                                            <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                            </div>
                                            <p class="text-sm font-semibold text-gray-400">No recent orders found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Links -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-gray-50 to-transparent rounded-bl-full pointer-events-none"></div>
                <h3 class="text-lg font-black text-gray-900 tracking-tight mb-6 relative z-10">Quick Actions</h3>
                
                <div class="grid grid-cols-1 gap-4 relative z-10">
                    <a href="{{ route('admin.products.create') }}" class="group p-4 bg-gray-50 rounded-2xl hover:bg-black transition-colors flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-white group-hover:bg-gray-800 shadow-sm flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5 text-gray-700 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 group-hover:text-white transition-colors">Add Product</p>
                            <p class="text-xs text-gray-500 group-hover:text-gray-400 transition-colors">Create a new item</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.products.index') }}" class="group p-4 bg-gray-50 rounded-2xl hover:bg-black transition-colors flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-white group-hover:bg-gray-800 shadow-sm flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5 text-gray-700 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 group-hover:text-white transition-colors">Manage Catalog</p>
                            <p class="text-xs text-gray-500 group-hover:text-gray-400 transition-colors">View and update inventory</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.online-store.mnpages.index') }}" class="group p-4 bg-gray-50 rounded-2xl hover:bg-black transition-colors flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-white group-hover:bg-gray-800 shadow-sm flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5 text-gray-700 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 group-hover:text-white transition-colors">Store Customizer</p>
                            <p class="text-xs text-gray-500 group-hover:text-gray-400 transition-colors">Edit page layouts visually</p>
                        </div>
                    </a>
                </div>
            </div>
            
            <!-- Store Status Widget -->
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl shadow-lg shadow-indigo-500/20 p-6 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-bl-full pointer-events-none blur-md"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_8px_rgba(52,211,153,0.8)]"></span>
                        <h3 class="text-sm font-black uppercase tracking-widest text-indigo-100">Store Status</h3>
                    </div>
                    <p class="text-2xl font-black mb-1">Online & Active</p>
                    <p class="text-indigo-100 text-xs font-medium opacity-80">Your storefront is currently accepting new orders.</p>
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
            salesType: 'order',
            salesChart: null,
            shippingChart: null,
            salesData: { labels: [], counts: [], amounts: [] },
            shippingData: [0, 0, 0, 0],
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
                    return new Intl.NumberFormat().format(total);
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
                            borderColor: '#3b82f6', // blue-500
                            backgroundColor: 'rgba(59, 130, 246, 0.05)',
                            borderWidth: 2,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#3b82f6',
                            pointBorderWidth: 2,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#ffffff',
                                titleColor: '#374151',
                                bodyColor: '#4b5563',
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
                                grid: { display: false, drawBorder: false },
                                ticks: {
                                    font: { size: 10, family: 'Inter' },
                                    color: '#9ca3af',
                                    maxTicksLimit: 6,
                                    callback: function(val, index) {
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
                                grid: {
                                    color: '#f3f4f6',
                                    drawBorder: false,
                                    borderDash: [5, 5]
                                },
                                ticks: {
                                    font: { size: 10, family: 'Inter' },
                                    color: '#9ca3af',
                                    padding: 10,
                                    maxTicksLimit: 6,
                                    callback: function(value) {
                                        if (value >= 1000) {
                                            return (value / 1000) + ' K';
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
                    '#8b5cf6', // packed (processing)
                    '#3b82f6', // shipped
                    '#10b981', // delivered
                ];
                
                if (this.shippingTotal === 0) {
                    plotData = [1];
                    bgColors = ['#f3f4f6']; // Empty gray
                }
                
                this.shippingChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: this.shippingTotal === 0 ? ['No Data'] : ['Pending', 'Packed', 'Shipped', 'Delivered'],
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
                        cutout: '75%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                enabled: this.shippingTotal > 0,
                                backgroundColor: '#ffffff',
                                titleColor: '#374151',
                                bodyColor: '#4b5563',
                                borderColor: '#e5e7eb',
                                borderWidth: 1,
                                padding: 10,
                                displayColors: true,
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label + ': ' + context.parsed;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    }
    </script>
@endsection
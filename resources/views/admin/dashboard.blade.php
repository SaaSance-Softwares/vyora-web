@extends('layouts.admin')

@section('header', 'Dashboard')

@section('content')
<div class="space-y-8 pb-10">
    <!-- Welcome Header -->
    <div class="relative bg-gradient-to-r from-gray-900 via-gray-800 to-black rounded-3xl p-8 overflow-hidden shadow-2xl shadow-gray-900/20">
        <!-- Abstract shapes for background -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-gradient-to-br from-indigo-500/30 to-purple-500/30 blur-3xl mix-blend-screen pointer-events-none"></div>
        <div class="absolute bottom-0 left-20 w-40 h-40 rounded-full bg-gradient-to-tr from-emerald-500/20 to-teal-500/20 blur-2xl mix-blend-screen pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-white tracking-tight mb-2">Welcome back, Admin 👋</h1>
                <p class="text-gray-400 font-medium text-sm max-w-xl leading-relaxed">Here is what's happening with your store today. Review your latest orders, track your revenue, and manage your catalog from one place.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.orders.index') }}" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/10 backdrop-blur-md text-white text-sm font-bold rounded-xl transition-all shadow-lg flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    View Orders
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Revenue Card -->
        <div class="bg-white p-6 rounded-3xl shadow-sm hover:shadow-xl hover:shadow-emerald-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-4 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-emerald-600 bg-emerald-50 rounded-lg">Net</span>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Total Revenue</p>
                <p class="text-3xl font-black text-gray-900 tracking-tight">₹{{ number_format($stats['revenue']) }}</p>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="bg-white p-6 rounded-3xl shadow-sm hover:shadow-xl hover:shadow-blue-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-4 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-blue-600 bg-blue-50 rounded-lg">All Time</span>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Total Orders</p>
                <p class="text-3xl font-black text-gray-900 tracking-tight">{{ number_format($stats['total_orders']) }}</p>
            </div>
        </div>

        <!-- Pending Orders Card -->
        <div class="bg-white p-6 rounded-3xl shadow-sm hover:shadow-xl hover:shadow-amber-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-amber-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-4 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/30">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-amber-600 bg-amber-50 rounded-lg relative flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Action Needed
                </span>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Pending Orders</p>
                <p class="text-3xl font-black text-gray-900 tracking-tight">{{ number_format($stats['pending_orders']) }}</p>
            </div>
        </div>

        <!-- Products Card -->
        <div class="bg-white p-6 rounded-3xl shadow-sm hover:shadow-xl hover:shadow-purple-500/10 hover:-translate-y-1 transition-all duration-300 border border-gray-100 group relative overflow-hidden">
            <div class="absolute right-0 top-0 w-24 h-24 bg-purple-50 rounded-bl-full flex items-start justify-end p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-0"></div>
            <div class="flex items-start justify-between mb-4 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shadow-lg shadow-purple-500/30">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-purple-600 bg-purple-50 rounded-lg">Live</span>
            </div>
            <div class="relative z-10">
                <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Total Products</p>
                <p class="text-3xl font-black text-gray-900 tracking-tight">{{ number_format($stats['total_products']) }}</p>
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
@endsection
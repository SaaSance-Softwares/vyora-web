@extends('layouts.admin')

@section('header', 'Dashboard')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">System Overview</h1>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-500 font-medium">Net Revenue</span>
                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900">₹{{ number_format($stats['revenue']) }}</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-500 font-medium">Total Orders</span>
                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_orders']) }}</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-500 font-medium">Pending Orders</span>
                <svg class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['pending_orders']) }}</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-500 font-medium">Products</span>
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_products']) }}</p>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Orders -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">Recent Orders</h3>
                    <a href="{{ route('admin.orders.index') }}" class="text-xs text-blue-600 font-semibold hover:underline">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase tracking-widest border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4">Order</th>
                                <th class="px-6 py-4">Total</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @forelse($recent_orders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-semibold text-gray-900">#{{ $order->order_number }}</td>
                                    <td class="px-6 py-4">₹{{ number_format($order->total_amount, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-md
                                            {{ $order->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 text-xs">{{ $order->created_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">No recent orders.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="space-y-4">
            <h3 class="font-bold text-gray-900 px-1">Quick Actions</h3>
            <div class="grid grid-cols-1 gap-3">
                <a href="{{ route('admin.products.index') }}" class="p-4 bg-white rounded-lg shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors flex items-center space-x-3">
                    <div class="bg-gray-100 p-2 rounded-md">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">Manage Catalog</p>
                        <p class="text-xs text-gray-500">View and update products</p>
                    </div>
                </a>
                <a href="{{ route('admin.online-store.mnpages.index') }}" class="p-4 bg-white rounded-lg shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors flex items-center space-x-3">
                    <div class="bg-gray-100 p-2 rounded-md">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">Store Customizer</p>
                        <p class="text-xs text-gray-500">Edit page layouts</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
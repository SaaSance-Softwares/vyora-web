@extends('layouts.admin')

@section('title', 'Order Management')

@section('content')
<div class="space-y-6">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Order Management</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage, track, and fulfil all customer orders</p>
        </div>
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <span class="bg-gray-100 px-3 py-1.5 rounded-full font-medium">Total: {{ $stats['all'] ?? 0 }} orders</span>
        </div>
    </div>

    {{-- ===== ALERT ===== --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-medium">
            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ===== STATS CARDS ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
        @php
            $statConfig = [
                'all'        => ['label' => 'All',        'color' => 'bg-gray-900 text-white',          'ring' => ''],
                'pending'    => ['label' => 'Pending',    'color' => 'bg-amber-50 text-amber-700',      'ring' => 'ring-1 ring-amber-200'],
                'processing' => ['label' => 'Processing', 'color' => 'bg-blue-50 text-blue-700',        'ring' => 'ring-1 ring-blue-200'],
                'shipped'    => ['label' => 'Shipped',    'color' => 'bg-indigo-50 text-indigo-700',    'ring' => 'ring-1 ring-indigo-200'],
                'delivered'  => ['label' => 'Delivered',  'color' => 'bg-emerald-50 text-emerald-700',  'ring' => 'ring-1 ring-emerald-200'],
                'cancelled'  => ['label' => 'Cancelled',  'color' => 'bg-rose-50 text-rose-700',        'ring' => 'ring-1 ring-rose-200'],
            ];
        @endphp
        @foreach($statConfig as $key => $cfg)
            <a href="{{ route('admin.orders.index', array_merge(request()->except('status', 'page'), ['status' => $key])) }}"
               class="flex flex-col items-center p-3 rounded-xl {{ $cfg['color'] }} {{ $cfg['ring'] }} transition-all hover:scale-105 {{ request('status', 'all') === $key ? 'ring-2 ring-offset-1 ring-gray-900 scale-105' : '' }}">
                <span class="text-2xl font-black">{{ $stats[$key] ?? 0 }}</span>
                <span class="text-xs font-semibold uppercase tracking-wider opacity-70 mt-0.5">{{ $cfg['label'] }}</span>
            </a>
        @endforeach
    </div>

    {{-- ===== SEARCH & FILTER BAR ===== --}}
    <form method="GET" action="{{ route('admin.orders.index') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
        <div class="flex flex-col lg:flex-row gap-3">
            {{-- Search --}}
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by order #, customer name, email or phone..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
            </div>

            {{-- Payment Method --}}
            <select name="payment_method" class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 bg-white">
                <option value="">All Payment Methods</option>
                <option value="COD" {{ request('payment_method') === 'COD' ? 'selected' : '' }}>COD</option>
                <option value="Prepaid" {{ request('payment_method') === 'Prepaid' ? 'selected' : '' }}>Prepaid / Online</option>
            </select>

            {{-- Date From --}}
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">

            {{-- Date To --}}
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">

            <input type="hidden" name="status" value="{{ request('status', 'all') }}">

            <button type="submit" class="px-5 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-semibold hover:bg-gray-700 transition-colors shrink-0">
                Filter
            </button>
            @if(request()->except('page'))
                <a href="{{ route('admin.orders.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-200 transition-colors shrink-0 flex items-center">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- ===== ORDERS TABLE ===== --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="py-3.5 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="py-3.5 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="py-3.5 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="py-3.5 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Payment</th>
                        <th class="py-3.5 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="py-3.5 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="py-3.5 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Tracking</th>
                        <th class="py-3.5 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($orders as $order)
                        @php
                            $statusConfig = [
                                'pending'    => 'bg-amber-100 text-amber-700',
                                'processing' => 'bg-blue-100 text-blue-700',
                                'shipped'    => 'bg-indigo-100 text-indigo-700',
                                'delivered'  => 'bg-emerald-100 text-emerald-700',
                                'cancelled'  => 'bg-rose-100 text-rose-700',
                                'refunded'   => 'bg-gray-100 text-gray-600',
                            ];
                            $statusClass = $statusConfig[$order->status] ?? 'bg-gray-100 text-gray-600';
                            $payStatusClass = $order->payment_status === 'paid' ? 'text-emerald-600 font-bold' : 'text-amber-600 font-medium';
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            {{-- Order Info --}}
                            <td class="py-4 px-4">
                                <div class="font-mono text-sm font-bold text-gray-900">{{ $order->order_number }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $order->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $order->created_at->format('h:i A') }}</div>
                            </td>

                            {{-- Customer --}}
                            <td class="py-4 px-4">
                                <div class="font-semibold text-gray-900 text-sm">{{ $order->shippingAddress?->name ?? 'Guest' }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $order->shippingAddress?->email }}</div>
                                <div class="text-xs text-gray-400">{{ $order->shippingAddress?->phone }}</div>
                            </td>

                            {{-- Items --}}
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-2">
                                    {{-- Thumbnails --}}
                                    <div class="flex -space-x-2">
                                        @foreach($order->items->take(3) as $item)
                                            <div class="w-9 h-11 rounded-lg border-2 border-white overflow-hidden bg-gray-100 shadow-sm shrink-0">
                                                @if($item->image_url)
                                                    <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <svg class="w-3 h-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <span class="text-xs text-gray-500 font-medium">{{ $order->items_count }} {{ Str::plural('item', $order->items_count) }}</span>
                                </div>
                            </td>

                            {{-- Payment --}}
                            <td class="py-4 px-4">
                                <div class="text-sm font-semibold text-gray-800">{{ $order->payment_method ?? '—' }}</div>
                                <div class="text-xs {{ $payStatusClass }} mt-0.5 uppercase tracking-wide">{{ $order->payment_status }}</div>
                                @if($order->transaction_id)
                                    <div class="text-xs text-gray-400 mt-0.5 font-mono truncate max-w-[100px]" title="{{ $order->transaction_id }}">{{ $order->transaction_id }}</div>
                                @endif
                            </td>

                            {{-- Amount --}}
                            <td class="py-4 px-4">
                                <div class="text-sm font-black text-gray-900">₹{{ number_format($order->total_amount) }}</div>
                                @if($order->discount_amount > 0)
                                    <div class="text-xs text-emerald-600 mt-0.5">-₹{{ number_format($order->discount_amount) }} off</div>
                                @endif
                                @if($order->shipping_amount > 0)
                                    <div class="text-xs text-gray-400 mt-0.5">+₹{{ number_format($order->shipping_amount) }} ship</div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $statusClass }}">
                                    {{ $order->status_label }}
                                </span>
                                @if($order->coupon_code)
                                    <div class="mt-1.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-50 text-purple-700 rounded-full text-[10px] font-bold">
                                            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2h2z"/></svg>
                                            {{ $order->coupon_code }}
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- Tracking --}}
                            <td class="py-4 px-4">
                                @if($order->tracking_url)
                                    <a href="{{ $order->tracking_url }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-800 group">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                        Track
                                    </a>
                                    @if($order->courier_partner)
                                        <div class="text-[10px] text-gray-400 mt-0.5">{{ $order->courier_partner }}</div>
                                    @endif
                                @elseif($order->status === 'shipped')
                                    <span class="text-xs text-amber-500 font-medium">Link Pending</span>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="py-4 px-4">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-semibold hover:bg-gray-700 transition-colors">
                                    View
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-20 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <svg class="w-12 h-12 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p class="font-semibold text-gray-500">No orders found</p>
                                    @if(request()->except('page'))
                                        <a href="{{ route('admin.orders.index') }}" class="text-sm text-blue-600 hover:underline">Clear filters</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="px-4 py-4 border-t border-gray-100 bg-gray-50/40">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
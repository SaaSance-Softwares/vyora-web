@extends('layouts.admin')

@section('title', 'Customer Details')

@section('content')
<div class="space-y-6">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.customers.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-sm text-gray-500">{{ $customer->email }}</span>
                    @if($customer->provider)
                        <span class="px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 text-[10px] font-bold uppercase">{{ $customer->provider }}</span>
                    @else
                        <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 text-[10px] font-bold uppercase">Email Auth</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="text-sm text-gray-500 text-right">
            Registered: <span class="font-medium text-gray-900">{{ $customer->created_at->format('d M Y, h:i A') }}</span>
        </div>
    </div>

    {{-- ===== TOP METRICS ===== --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <div class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Spend</div>
            <div class="text-3xl font-black text-gray-900">₹{{ number_format($customer->total_spent, 2) }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <div class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Orders</div>
            <div class="text-3xl font-black text-gray-900">{{ $customer->orders->count() }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <div class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Avg Order Value</div>
            <div class="text-3xl font-black text-gray-900">₹{{ number_format($customer->average_order_value, 2) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- ===== LEFT COLUMN: DETAILS & ADDRESSES ===== --}}
        <div class="lg:col-span-1 space-y-6">
            
            {{-- Contact Info --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="font-bold text-gray-900">Contact Information</h3>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <div class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Email Address</div>
                        <div class="text-sm text-gray-900 font-medium mt-0.5">{{ $customer->email }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Phone Number</div>
                        <div class="text-sm text-gray-900 font-medium mt-0.5">{{ $customer->phone ?? 'Not provided' }}</div>
                    </div>
                </div>
            </div>

            {{-- Addresses --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900">Saved Addresses</h3>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full">{{ $customer->addresses->count() }}</span>
                </div>
                <div class="p-5 space-y-4">
                    @forelse($customer->addresses as $address)
                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">{{ $address->type ?? 'Address' }}</span>
                                @if($address->is_default)
                                    <span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded">DEFAULT</span>
                                @endif
                            </div>
                            <div class="text-sm font-semibold text-gray-900">{{ $address->name }}</div>
                            @if($address->phone) <div class="text-xs text-gray-500 mb-1">{{ $address->phone }}</div> @endif
                            <div class="text-sm text-gray-600 mt-1">
                                {{ $address->address_line1 }}<br>
                                @if($address->address_line2) {{ $address->address_line2 }}<br> @endif
                                {{ $address->city }}, {{ $address->state }} {{ $address->zip_code }}<br>
                                {{ $address->country }}
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-4">No addresses saved.</p>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ===== RIGHT COLUMN: ORDER TIMELINE ===== --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden h-full">
                <div class="px-5 py-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900">Shopping Timeline</h3>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full">{{ $customer->orders->count() }} Orders</span>
                </div>
                
                <div class="p-0">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/60 border-b border-gray-100">
                            <tr>
                                <th class="py-3 px-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Order</th>
                                <th class="py-3 px-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="py-3 px-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="py-3 px-5 text-xs font-bold text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="py-3 px-5 text-xs font-bold text-gray-500 uppercase tracking-wider"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($customer->orders as $order)
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
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4 px-5">
                                        <div class="font-mono text-sm font-bold text-gray-900">{{ $order->order_number }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ $order->items->count() }} items</div>
                                    </td>
                                    <td class="py-4 px-5 text-sm text-gray-600">
                                        {{ $order->created_at->format('d M Y') }}<br>
                                        <span class="text-xs text-gray-400">{{ $order->created_at->format('h:i A') }}</span>
                                    </td>
                                    <td class="py-4 px-5">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $statusClass }}">
                                            {{ $order->status_label }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-5 text-sm font-black text-gray-900">
                                        ₹{{ number_format($order->total_amount, 2) }}
                                    </td>
                                    <td class="py-4 px-5 text-right">
                                        <a href="{{ route('admin.orders.show', $order) }}"
                                           class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                            View Order &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-sm text-gray-500">
                                        This customer hasn't placed any orders yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

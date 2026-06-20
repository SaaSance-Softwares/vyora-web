@extends('layouts.admin')

@section('title', 'Customer Management')

@section('content')
<div class="space-y-6">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Customer Management</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage and track your registered customers and their shopping history.</p>
        </div>
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <span class="bg-gray-100 px-3 py-1.5 rounded-full font-medium">Total: {{ $customers->total() }} customers</span>
        </div>
    </div>

    {{-- ===== SEARCH & FILTER BAR ===== --}}
    <form method="GET" action="{{ route('admin.customers.index') }}" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
        <div class="flex flex-col lg:flex-row gap-3">
            {{-- Search --}}
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by customer name, email or phone..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
            </div>

            <button type="submit" class="px-5 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-semibold hover:bg-gray-700 transition-colors shrink-0">
                Filter
            </button>
            @if(request('search'))
                <a href="{{ route('admin.customers.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-200 transition-colors shrink-0 flex items-center">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- ===== CUSTOMERS TABLE ===== --}}
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="py-3.5 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="py-3.5 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Auth Provider</th>
                        <th class="py-3.5 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Orders</th>
                        <th class="py-3.5 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Total Spent</th>
                        <th class="py-3.5 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Registered At</th>
                        <th class="py-3.5 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            {{-- Customer Info --}}
                            <td class="py-4 px-4">
                                <div class="font-semibold text-gray-900 text-sm">{{ $customer->name }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $customer->email }}</div>
                                @if($customer->phone)
                                    <div class="text-xs text-gray-400">{{ $customer->phone }}</div>
                                @endif
                            </td>

                            {{-- Auth Provider --}}
                            <td class="py-4 px-4">
                                @if($customer->provider)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-indigo-100 text-indigo-700">
                                        {{ $customer->provider }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide bg-gray-100 text-gray-600">
                                        Email
                                    </span>
                                @endif
                            </td>

                            {{-- Orders Count --}}
                            <td class="py-4 px-4">
                                <span class="font-medium text-gray-800">{{ $customer->orders_count }}</span>
                            </td>

                            {{-- Total Spent --}}
                            <td class="py-4 px-4">
                                <div class="text-sm font-black text-gray-900">₹{{ number_format($customer->orders_sum_total_amount ?? 0, 2) }}</div>
                            </td>

                            {{-- Registered --}}
                            <td class="py-4 px-4">
                                <div class="text-sm text-gray-600">{{ $customer->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $customer->created_at->format('h:i A') }}</div>
                            </td>

                            {{-- Actions --}}
                            <td class="py-4 px-4 text-right">
                                <a href="{{ route('admin.customers.show', $customer) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-semibold hover:bg-gray-700 transition-colors">
                                    Details
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-20 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <svg class="w-12 h-12 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <p class="font-semibold text-gray-500">No customers found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($customers->hasPages())
            <div class="px-4 py-4 border-t border-gray-100 bg-gray-50/40">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('content')
<div class="w-full">
    <!-- Header & Filters -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">POS Analysis</h1>
            <p class="text-sm text-gray-500 mt-1">Top-down performance analysis for physical stores</p>
        </div>
        <form method="GET" action="{{ route('admin.pos-analysis.index') }}" class="flex flex-wrap items-center gap-3 bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
            <div class="flex items-center space-x-2">
                <input type="date" name="start_date" value="{{ $startDateInput }}" class="text-sm border-gray-300 rounded-md focus:ring-black focus:border-black">
                <span class="text-sm text-gray-500 font-medium">to</span>
                <input type="date" name="end_date" value="{{ $endDateInput }}" class="text-sm border-gray-300 rounded-md focus:ring-black focus:border-black">
            </div>
            <div>
                <select name="pos_location_id" class="text-sm border-gray-300 rounded-md focus:ring-black focus:border-black">
                    <option value="all">All Stores</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ $locationId == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-black text-white text-sm font-bold rounded-md hover:bg-gray-800 transition shadow-sm">
                Apply
            </button>
        </form>
    </div>

    <!-- Top Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Total POS Sales</h3>
            <div class="text-3xl font-black text-gray-900">₹{{ number_format($totalSales, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Total POS Orders</h3>
            <div class="text-3xl font-black text-gray-900">{{ number_format($totalOrders) }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">AOV (Per Order)</h3>
            <div class="text-3xl font-black text-gray-900">₹{{ number_format($aov, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">AOV (Per Customer)</h3>
            <div class="text-3xl font-black text-gray-900">₹{{ number_format($aovPerCustomer, 2) }}</div>
        </div>
    </div>

    @if($locationId === 'all')
    <!-- Store Breakdown (Only show if all stores selected) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Store Performance</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Store Name</th>
                        <th class="px-6 py-4 font-semibold">Total Sales</th>
                        <th class="px-6 py-4 font-semibold">Total Orders</th>
                        <th class="px-6 py-4 font-semibold">Unique Customers</th>
                        <th class="px-6 py-4 font-semibold">Avg Order Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($storeStats as $stat)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $stat->store_name }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-green-600">₹{{ number_format($stat->total_sales, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ number_format($stat->orders_count) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ number_format($stat->unique_customers) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">₹{{ number_format($stat->aov, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No store sales data found for this date range.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Size/Color Performance -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Product Variant Performance (Size & Color)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold">Store</th>
                        <th class="px-6 py-4 font-semibold">Product</th>
                        <th class="px-6 py-4 font-semibold">Color</th>
                        <th class="px-6 py-4 font-semibold">Size</th>
                        <th class="px-6 py-4 font-semibold">Net Qty Sold</th>
                        <th class="px-6 py-4 font-semibold">Total Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($variantStats as $vstat)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $vstat->store_name ?? 'Unknown' }}</td>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $vstat->product_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <span class="px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold">
                                {{ $vstat->color_name ?: '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <span class="px-2.5 py-1 bg-gray-100 border border-gray-200 rounded-md text-xs font-semibold">
                                {{ $vstat->size_name ?: '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold {{ $vstat->net_qty_sold > 0 ? 'text-gray-900' : 'text-red-500' }}">{{ $vstat->net_qty_sold }}</td>
                        <td class="px-6 py-4 text-sm text-green-600 font-semibold">₹{{ number_format($vstat->total_revenue, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No variant sales data found for this date range.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

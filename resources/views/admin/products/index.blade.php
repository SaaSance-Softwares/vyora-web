@extends('layouts.admin')

@section('header', 'Products')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Product Catalog</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.upload') }}" class="px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">Bulk Upload</a>
            <a href="{{ route('admin.products.create') }}" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 text-sm font-medium">Add Product</a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-sm text-gray-500">Total Products</p>
            <p class="text-2xl font-bold">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-sm text-gray-500">Active</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-sm text-gray-500">Low Stock</p>
            <p class="text-2xl font-bold text-amber-500">{{ number_format($stats['low_stock']) }}</p>
        </div>
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-sm text-gray-500">Out of Stock</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($stats['out_of_stock']) }}</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <div class="relative w-72">
                <input type="text" id="productSearch" placeholder="Search products..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-black focus:border-black">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
            <div class="text-sm text-gray-500 italic">
                Showing {{ $products->count() }} items
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-4">Product</th>
                        <th class="px-6 py-4">Price Range</th>
                        <th class="px-6 py-4">Inventory</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-gray-100 rounded-md overflow-hidden border border-gray-100 flex-shrink-0">
                                        @if($product->preview_image)
                                            <img src="{{ $product->image_url }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $product->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $product->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($product->skus->isNotEmpty())
                                    ₹{{ number_format($product->skus->min('price'), 0) }} - ₹{{ number_format($product->skus->max('price'), 0) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $product->skus->sum('stock') }} units
                            </td>
                            <td class="px-6 py-4">
                                @if($product->is_active)
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">Active</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-500 text-xs font-semibold rounded">Draft</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="p-2 text-gray-400 hover:text-black">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product?')">
                                        @csrf @method('DELETE')
                                        <button class="p-2 text-gray-400 hover:text-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    document.getElementById('productSearch').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });
</script>
@endsection

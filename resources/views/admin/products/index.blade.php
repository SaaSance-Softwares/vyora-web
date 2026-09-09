@extends('layouts.admin')

@section('header', 'Products')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Product Catalog</h1>
        <div class="flex gap-3">
            <button type="button" onclick="openBulkUpdateModal()" class="px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium flex items-center gap-2">Bulk Update</button>
            <a href="{{ route('admin.products.export') }}" class="px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
                Export CSV
            </a>
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
            <form method="GET" action="{{ route('admin.products.index') }}" class="flex gap-2 w-full max-w-md">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or SKU..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-black focus:border-black">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
                <button type="submit" class="px-4 py-2 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800">Search</button>
                @if(request('search'))
                    <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-200">Clear</a>
                @endif
            </form>
            <div class="text-sm text-gray-500 italic">
                Showing {{ $products->count() }} items
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-4 w-12"><input type="checkbox" id="selectAll" class="rounded border-gray-300 text-black focus:ring-black"></th>
                        <th class="px-6 py-4">Product</th>
                        <th class="px-6 py-4">Price Range</th>
                        <th class="px-6 py-4">Inventory</th>
                        <th class="px-6 py-4">Views</th>
                        <th class="px-6 py-4">Purchases</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4"><input type="checkbox" class="product-checkbox rounded border-gray-300 text-black focus:ring-black" value="{{ $product->id }}"></td>
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
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ number_format($product->view_count) }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ number_format($product->purchase_count) }}
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
                                    <a href="{{ route('admin.products.analytics', $product) }}" title="Product Analytics" class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" title="Edit Product" class="p-2 text-gray-400 hover:text-black transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    @if($product->purchase_count === 0)
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product?')">
                                            @csrf @method('DELETE')
                                            <button title="Delete Product" class="p-2 text-gray-400 hover:text-red-600 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif
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


    <!-- Bulk Update Modal -->
    <div id="bulkUpdateModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-900">Bulk Update <span id="bulk-selected-count" class="text-black ml-1 bg-gray-200 px-2 py-0.5 rounded text-sm">0</span></h3>
                <button type="button" onclick="closeBulkUpdateModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-6 overflow-y-auto flex-1">
                <form id="bulk-update-form" action="{{ route('admin.products.bulk-update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_ids" id="bulk-product-ids">
                    
                    <div class="text-sm text-gray-500 mb-6 bg-blue-50 p-3 rounded border border-blue-100">
                        Leave fields blank (or "Leave Unchanged") to keep the original values for the selected products.
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Size Chart -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Size Chart</label>
                            <select name="size_chart_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                <option value="">Leave Unchanged</option>
                                @foreach($sizeCharts as $sc)
                                    <option value="{{ $sc->id }}">{{ $sc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fit -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fit</label>
                            <select name="fit_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                <option value="">Leave Unchanged</option>
                                @foreach($fits as $fit)
                                    <option value="{{ $fit->id }}">{{ $fit->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fabric -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fabric</label>
                            <select name="fabric_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                <option value="">Leave Unchanged</option>
                                @foreach($fabrics as $fabric)
                                    <option value="{{ $fabric->id }}">{{ $fabric->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Product Type (HSN) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Product Type</label>
                            <select name="product_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                <option value="">Leave Unchanged</option>
                                @foreach($productTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Delivery Timeline -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Delivery Timeline</label>
                            <select name="delivery_timeline_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                <option value="">Leave Unchanged</option>
                                @foreach($deliveryTimelines as $dt)
                                    <option value="{{ $dt->id }}">{{ $dt->name }} ({{ $dt->min_days }}-{{ $dt->max_days }} days)</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tax Class -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tax Class</label>
                            <select name="tax_class" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                <option value="">Leave Unchanged</option>
                                @foreach($taxes as $tax)
                                    <option value="{{ $tax['id'] }}">{{ $tax['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Active Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Active Status</label>
                            <select name="is_active" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                <option value="leave">Leave Unchanged</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <!-- Returnable -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Returnable</label>
                            <select name="is_returnable" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                <option value="leave">Leave Unchanged</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <!-- On Sale -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">On Sale</label>
                            <select name="on_sale" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                <option value="leave">Leave Unchanged</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <!-- QikInk Fulfillment -->
                        @if($qikinkEnabled)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">QikInk Fulfillment</label>
                            <select name="use_qikink" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                <option value="leave">Leave Unchanged</option>
                                <option value="1">Yes (Process via QikInk)</option>
                                <option value="0">No (Manual)</option>
                            </select>
                        </div>
                        @endif
                    </div>

                    <!-- Categories & Collections -->
                    <div class="mt-6 border-t border-gray-100 pt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categories (Multiple)</label>
                            <div class="max-h-48 overflow-y-auto border border-gray-200 rounded p-2 space-y-2 bg-gray-50">
                                @foreach($categories as $cat)
                                    <div class="category-group">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="categories[]" value="{{ $cat->id }}" class="cat-checkbox h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
                                            <span class="ml-2 text-sm text-gray-900 font-bold uppercase">{{ $cat->name }}</span>
                                        </label>
                                        @if($cat->children->isNotEmpty())
                                            <div class="ml-6 mt-1 space-y-2 border-l-2 border-gray-100 pl-3">
                                                @foreach($cat->children as $child)
                                                    <div class="category-group">
                                                        <label class="flex items-center">
                                                            <input type="checkbox" name="categories[]" value="{{ $child->id }}" class="cat-checkbox h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
                                                            <span class="ml-2 text-sm text-gray-800 font-medium">{{ $child->name }}</span>
                                                        </label>
                                                        @if($child->children->isNotEmpty())
                                                            <div class="ml-6 mt-1 space-y-2 border-l-2 border-gray-100 pl-3">
                                                                @foreach($child->children as $grandchild)
                                                                    <label class="flex items-center">
                                                                        <input type="checkbox" name="categories[]" value="{{ $grandchild->id }}" class="cat-checkbox h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
                                                                        <span class="ml-2 text-sm text-gray-700">{{ $grandchild->name }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Collections (Multiple)</label>
                            <div class="max-h-48 overflow-y-auto border border-gray-200 rounded p-2 space-y-2 bg-gray-50">
                                @foreach($collections as $col)
                                    <div class="flex items-center">
                                        <input type="checkbox" name="collections[]" value="{{ $col->id }}" class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
                                        <label class="ml-2 block text-sm text-gray-900">{{ $col->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-3">
                <button type="button" onclick="closeBulkUpdateModal()" class="px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">Cancel</button>
                <button type="button" onclick="submitBulkUpdate()" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 text-sm font-medium">Apply Bulk Update</button>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.product-checkbox');
        
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            });
        }
    });

    function getSelectedProductIds() {
        const checkboxes = document.querySelectorAll('.product-checkbox:checked');
        const ids = [];
        checkboxes.forEach(cb => {
            ids.push(cb.value);
        });
        return ids;
    }

    function openBulkUpdateModal() {
        const ids = getSelectedProductIds();
        if (ids.length === 0) {
            alert('Please select at least one product to update.');
            return;
        }
        
        document.getElementById('bulk-product-ids').value = ids.join(',');
        document.getElementById('bulk-selected-count').innerText = ids.length;
        document.getElementById('bulkUpdateModal').classList.remove('hidden');
    }

    function closeBulkUpdateModal() {
        document.getElementById('bulkUpdateModal').classList.add('hidden');
    }

    function submitBulkUpdate() {
        document.getElementById('bulk-update-form').submit();
    }
</script>
@endsection

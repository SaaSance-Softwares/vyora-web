@extends('layouts.admin')

@section('header', 'Manage Store: ' . $market->name)

@section('content')
<div class="space-y-8 pb-24" x-data="productSelector()">
    
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('admin.pos-markets.index') }}" class="text-sm font-medium text-gray-600 hover:text-black transition-colors">&larr; Back to Markets</a>
        
        <!-- Segmented Control Tabs -->
        <div class="inline-flex bg-gray-100/80 p-1 rounded-lg items-center">
            <a href="{{ route('admin.pos-markets.show', ['slug' => $market->slug, 'tab' => 'manage']) }}" 
               class="px-5 py-1.5 text-sm font-semibold rounded-md transition-all {{ request('tab', 'manage') === 'manage' ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50' }}">
                Manage Products
            </a>
            <a href="{{ route('admin.pos-markets.show', ['slug' => $market->slug, 'tab' => 'add']) }}" 
               class="px-5 py-1.5 text-sm font-semibold rounded-md transition-all {{ request('tab') === 'add' ? 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50' }}">
                Add Products to Store
            </a>
        </div>
    </div>

    @if(request('tab') === 'add')
    {{-- ── ADD PRODUCT (MASTER LIST) ───────────────────── --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Add Products to Store</h3>
                <p class="text-sm text-gray-500">Select a master product to view and assign its variants.</p>
            </div>
            
            <div class="relative w-full md:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    placeholder="Search master products..." 
                    class="w-full border border-gray-300 rounded-md pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black shadow-sm"
                >
            </div>
        </div>
        
        <div class="p-6 bg-gray-50">
            <!-- Visual Product Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 max-h-[500px] overflow-y-auto pr-2">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div 
                        @click="openProduct(product)"
                        class="bg-white border border-gray-200 rounded-lg p-3 cursor-pointer hover:border-black hover:shadow-md transition-all flex flex-col h-full group"
                    >
                        <div class="h-32 w-full bg-gray-100 rounded flex items-center justify-center overflow-hidden mb-3 group-hover:opacity-90 transition-opacity">
                            <template x-if="product.image">
                                <img :src="product.image" class="object-cover w-full h-full">
                            </template>
                            <template x-if="!product.image">
                                <span class="text-gray-400 text-xs">No Image</span>
                            </template>
                        </div>
                        <h4 class="text-xs font-bold text-gray-900 leading-tight mb-2 flex-1" x-text="product.name"></h4>
                        <div class="mt-auto inline-block bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-1 rounded">
                            <span x-text="product.skus.length + ' Variants'"></span>
                        </div>
                    </div>
                </template>
                <div x-show="filteredProducts.length === 0" class="col-span-full py-12 text-center text-gray-500 text-sm bg-white rounded-lg border border-dashed border-gray-300">
                    No products match your search.
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(request('tab', 'manage') === 'manage')
    {{-- ── ASSIGNED PRODUCTS ───────────────────────────── --}}
    <div class="bg-white rounded-lg shadow overflow-hidden" x-data="assignedProducts()">
        <div class="px-6 py-5 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h3 class="text-lg font-semibold text-gray-900 flex-shrink-0">Products Available at {{ $market->name }}</h3>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                {{-- Bulk Actions --}}
                <div x-show="selectedSkus.length > 0" x-transition class="flex items-center gap-2 mr-2">
                    <span class="text-xs font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded" x-text="selectedSkus.length + ' selected'"></span>
                    <button @click="bulkPrintTags" type="button" class="text-sm font-medium bg-black text-white hover:bg-gray-800 px-3 py-1.5 rounded-md transition-colors shadow-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        Print Tags
                    </button>
                    <button @click="bulkDelete" type="button" class="text-sm font-medium bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-900 border border-red-100 px-3 py-1.5 rounded-md transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Remove
                    </button>
                </div>

                <div class="w-full sm:w-64 relative flex-shrink-0">
                    <input 
                        type="text" 
                        x-model="searchAssigned" 
                        placeholder="Search by Product Name or SKU..." 
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-black focus:border-black outline-none"
                    />
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto relative" :class="isDeleting ? 'opacity-50 pointer-events-none' : ''">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left w-12">
                            <input type="checkbox" x-model="selectAll" @change="toggleAll" class="rounded border-gray-300 text-black focus:ring-black h-4 w-4">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Color</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Size</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Barcode</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Online Price</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-green-600 uppercase tracking-wider">Store Price</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($assignedSkus as $sku)
                    <tr class="hover:bg-gray-50 transition" 
                        x-data="{ 
                            editing: false,
                            skuId: '{{ $sku->sku_id }}',
                            skuName: '{{ strtolower(addslashes($sku->name)) }}',
                            shortCode: '{{ strtolower(addslashes($sku->short_code)) }}',
                            barcode: '{{ strtolower(addslashes($sku->barcode ?? '')) }}'
                        }"
                        x-show="searchAssigned === '' || skuName.includes(searchAssigned.toLowerCase()) || shortCode.includes(searchAssigned.toLowerCase()) || barcode.includes(searchAssigned.toLowerCase())"
                    >
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" :value="skuId" x-model="selectedSkus" class="rounded border-gray-300 text-black focus:ring-black h-4 w-4">
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">
                            <div class="flex items-center gap-3">
                                @if($sku->image_url)
                                    <img src="{{ $sku->image_url }}" alt="{{ $sku->name }}" class="w-12 h-12 object-cover rounded shadow-sm border border-gray-200 bg-gray-50">
                                @else
                                    <div class="w-12 h-12 bg-gray-100 rounded shadow-sm border border-gray-200 flex items-center justify-center text-gray-400 text-xs">No Img</div>
                                @endif
                                <span>{{ $sku->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-700 text-sm">{{ $sku->color_name ?? 'Default' }}</td>
                        <td class="px-6 py-4 text-gray-700 text-sm font-semibold">{{ $sku->size_name ?? 'Default' }}</td>
                        <td class="px-6 py-4 text-gray-500 font-mono text-sm">
                            <div class="flex items-center justify-between group gap-2">
                                <div>
                                    <div>{{ $sku->barcode ?? 'N/A' }}</div>
                                    <hr class="my-1 border-gray-200">
                                    <div>{{ $sku->short_code }}</div>
                                </div>
                                <button 
                                    type="button"
                                    @click="$dispatch('open-barcode-modal', { skuName: '{{ addslashes($sku->name) }}', barcode: '{{ $sku->barcode }}', shortCode: '{{ $sku->short_code }}' })"
                                    class="text-gray-400 hover:text-black p-1.5 rounded-md hover:bg-gray-100 transition shadow-sm border border-transparent hover:border-gray-200"
                                    title="View Barcodes"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-400 line-through text-sm">₹{{ $sku->original_price }}</td>
                        
                        <td class="px-6 py-4">
                            <span x-show="!editing" class="font-bold text-green-600">₹{{ $sku->override_price }}</span>
                            <div x-show="editing" style="display: none;">
                                <input form="update-form-{{ $sku->sku_id }}" type="number" name="override_price" value="{{ $sku->override_price }}" step="0.01" class="w-24 border border-gray-300 rounded px-2 py-1 text-sm focus:ring-black">
                            </div>
                        </td>
                        
                        <td class="px-6 py-4">
                            <span x-show="!editing" class="font-medium text-gray-700">{{ $sku->stock }} units</span>
                            <div x-show="editing" style="display: none;">
                                <input form="update-form-{{ $sku->sku_id }}" type="number" name="stock" value="{{ $sku->stock }}" min="0" class="w-20 border border-gray-300 rounded px-2 py-1 text-sm focus:ring-black">
                            </div>
                        </td>
                        
                        <td class="px-6 py-4 text-right">
                            <form id="update-form-{{ $sku->sku_id }}" action="{{ route('admin.pos-markets.updateProduct', [$market->id, $sku->sku_id]) }}" method="POST" class="hidden">
                                @csrf @method('PUT')
                            </form>
                            
                            <form id="remove-form-{{ $sku->sku_id }}" action="{{ route('admin.pos-markets.removeProduct', [$market->id, $sku->sku_id]) }}" method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>

                            <div class="flex items-center justify-end space-x-2">
                                <button x-show="!editing" @click="$dispatch('open-tag-modal', { 
                                    name: '{{ addslashes($sku->name) }}',
                                    size: '{{ $sku->size_name ?? 'S' }}',
                                    price: '{{ $sku->override_price }}',
                                    mrp: '{{ $sku->original_price }}',
                                    barcode: '{{ $sku->barcode }}',
                                    shortCode: '{{ $sku->short_code }}',
                                    fabric: '{{ $sku->fabric_name ?? 'Cotton' }}',
                                    chest: '{{ $sku->chest ?? '--' }}',
                                    length: '{{ $sku->length ?? '--' }}',
                                    chestLabel: '{{ $sku->chest_label ?? 'Chest' }}',
                                    lengthLabel: '{{ $sku->length_label ?? 'Length' }}'
                                })" type="button" class="text-sm text-purple-600 hover:text-purple-900 font-medium bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-md transition-colors">Tag</button>
                                
                                <button x-show="!editing" @click="editing = true" type="button" class="text-sm text-blue-600 hover:text-blue-900 font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors">Edit</button>
                                
                                <button x-show="editing" style="display: none;" form="update-form-{{ $sku->sku_id }}" type="submit" class="text-sm text-green-600 hover:text-green-900 font-medium bg-green-50 hover:bg-green-100 px-3 py-1.5 rounded-md transition-colors">Save</button>
                                <button x-show="editing" style="display: none;" @click="editing = false" type="button" class="text-sm text-gray-600 hover:text-gray-900 font-medium bg-gray-50 hover:bg-gray-100 px-3 py-1.5 rounded-md transition-colors">Cancel</button>

                                <button x-show="!editing" form="remove-form-{{ $sku->sku_id }}" type="submit" class="text-sm text-red-600 hover:text-red-900 font-medium bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors">Remove</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    
                    @if(count($assignedSkus) === 0)
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <span class="text-3xl mb-3 block">📦</span>
                            No products added to this store yet. Select a product above!
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ── VARIANT SELECTION MODAL ─────────────────────── --}}
    <div x-show="showModal" @keydown.escape.window="closeModal()" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Backdrop -->
        <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="closeModal()"></div>

        <!-- Modal Panel (Flex container that fills up to 95vh but shrinks if smaller) -->
        <div x-show="showModal" class="relative bg-white rounded-xl text-left shadow-2xl transform transition-all w-full max-w-[95%] max-h-[95vh] flex flex-col overflow-hidden border border-gray-100">
            
            <template x-if="activeProduct">
                <!-- Inner Form is also a flex column -->
                <form action="{{ route('admin.pos-markets.addProduct', $market->slug) }}" method="POST" @submit="submitSelection($event)" class="flex flex-col flex-1 min-h-0 w-full">
                    @csrf
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-6 py-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gray-100 rounded overflow-hidden">
                                <img :src="activeProduct.image" class="object-cover w-full h-full" x-show="activeProduct.image">
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900" x-text="activeProduct.name"></h3>
                                <p class="text-sm text-gray-500">Select variants to add to store</p>
                            </div>
                        </div>
                        <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-500 p-2">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Bulk Action Bar -->
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4 flex-shrink-0">
                        <div class="flex items-center flex-wrap gap-4">
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-700">Bulk Update Prices:</span>
                                <div class="relative w-28">
                                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-medium">₹</span>
                                    </div>
                                    <input type="number" x-model="bulkPrice" step="0.01" class="w-full border border-gray-300 rounded-md pl-7 pr-3 py-1.5 text-sm focus:ring-1 focus:ring-black focus:border-black" placeholder="Price">
                                </div>
                                <button type="button" @click="applyBulkPrice()" class="bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded-md text-sm font-medium hover:bg-gray-50 transition-colors shadow-sm">
                                    Apply
                                </button>
                            </div>
                            <div class="hidden sm:block w-px h-6 bg-gray-300"></div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-700">Bulk Update Stock:</span>
                                <input type="number" x-model="bulkStock" class="w-24 border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-1 focus:ring-black focus:border-black" placeholder="Qty">
                                <button type="button" @click="applyBulkStock()" class="bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded-md text-sm font-medium hover:bg-gray-50 transition-colors shadow-sm">
                                    Apply
                                </button>
                            </div>
                        </div>
                        <div class="text-sm font-bold text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-full">
                            <span x-text="selectedCount"></span> variants selected
                        </div>
                    </div>

                    <!-- Variants Table Wrapper (Expands to fill remaining height, then scrolls) -->
                    <div class="overflow-y-auto flex-1 min-h-[300px]">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="px-6 py-3 text-left w-12">
                                        <input type="checkbox" @change="toggleAll" class="rounded border-gray-300 text-black focus:ring-black h-4 w-4">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-16">Image</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Color</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Size</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Barcode</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Online Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Store Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Stock Qty</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <template x-for="(sku, index) in activeProduct.skus" :key="sku.id">
                                    <tr class="hover:bg-gray-50 transition-colors" :class="selectedVariants[sku.id] ? 'bg-blue-50/30' : ''">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" x-model="selectedVariants[sku.id]" class="rounded border-gray-300 text-black focus:ring-black h-4 w-4">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="h-10 w-10 flex-shrink-0 bg-gray-100 rounded overflow-hidden">
                                                <template x-if="sku.image">
                                                    <img :src="sku.image" class="h-10 w-10 object-cover">
                                                </template>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="sku.color_name || 'Default'"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900" x-text="sku.size_name || 'Default'"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500"><div x-text="sku.barcode"></div><hr class="my-1 border-gray-200"><div x-text="sku.short_code"></div></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="'₹' + sku.online_price"></td>
                                            <td class="px-6 py-4 whitespace-nowrap w-48">
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 font-medium">₹</span>
                                                    </div>
                                                    <input 
                                                        type="number" 
                                                        step="0.01" 
                                                        x-model="variantPrices[sku.id]" 
                                                        :disabled="!selectedVariants[sku.id]"
                                                        class="w-full border border-gray-300 rounded-md pl-7 pr-3 py-1.5 text-sm focus:ring-1 focus:ring-black focus:border-black disabled:bg-gray-100 disabled:text-gray-400"
                                                    >
                                                </div>
                                                <!-- Hidden inputs for submission -->
                                                <template x-if="selectedVariants[sku.id]">
                                                    <div>
                                                        <input type="hidden" :name="'variants['+index+'][sku_id]'" :value="sku.id">
                                                        <input type="hidden" :name="'variants['+index+'][override_price]'" :value="variantPrices[sku.id]">
                                                        <input type="hidden" :name="'variants['+index+'][stock]'" :value="variantStock[sku.id] || 0">
                                                    </div>
                                                </template>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap w-32">
                                                <input 
                                                    type="number" 
                                                    x-model="variantStock[sku.id]" 
                                                    :disabled="!selectedVariants[sku.id]"
                                                    class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-1 focus:ring-black focus:border-black disabled:bg-gray-100 disabled:text-gray-400"
                                                    min="0"
                                                >
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Modal Footer -->
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-end flex-shrink-0 gap-3">
                            <button type="button" @click="closeModal()" class="bg-white border border-gray-300 text-gray-700 px-6 py-2 rounded-md font-bold hover:bg-gray-50 transition-colors shadow-sm">
                                Cancel
                            </button>
                            <button type="submit" class="bg-black text-white px-8 py-2 rounded-md font-bold hover:bg-gray-800 transition-colors shadow-sm disabled:opacity-50" :disabled="selectedCount === 0">
                                Add Selected to Store
                            </button>
                        </div>
                    </form>
                </template>

            </div>
        </div>
    </div>
</div>
    {{-- ── BARCODE MODAL ─────────────────────────────── --}}
    <div x-data="barcodeModal()" @open-barcode-modal.window="openModal($event.detail)" x-show="isOpen" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <div x-show="isOpen" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="closeModal()"></div>

        <div x-show="isOpen" class="relative bg-white rounded-xl text-left shadow-2xl transform transition-all w-full max-w-md flex flex-col overflow-hidden border border-gray-100">
            
            <div class="bg-white px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900" x-text="skuName"></h3>
                    <p class="text-xs text-gray-500">View QR & Barcode</p>
                </div>
                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-500 p-1.5 rounded-md hover:bg-gray-100 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Tabs -->
            <div class="flex border-b border-gray-200 bg-gray-50/50">
                <button @click="setTab('product')" :class="tab === 'product' ? 'border-black text-black bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="flex-1 py-2.5 text-center border-b-2 font-medium text-sm transition-colors">
                    Product SKU
                </button>
                <button @click="setTab('short')" :class="tab === 'short' ? 'border-black text-black bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="flex-1 py-2.5 text-center border-b-2 font-medium text-sm transition-colors flex items-center justify-center gap-1.5">
                    Short SKU <span class="text-[9px] bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded-sm uppercase tracking-wider font-bold">System</span>
                </button>
            </div>

            <div class="p-6 bg-white flex flex-col items-center justify-center min-h-[300px]">
                
                <div x-show="tab === 'product'" class="w-full flex flex-col items-center space-y-6">
                    <div class="flex flex-col items-center w-full">
                        <div x-ref="qrProduct" class="mx-auto flex justify-center bg-white p-2 border border-gray-100 rounded-lg min-h-[144px]"></div>
                        <p class="mt-4 text-xs font-mono font-medium text-gray-600 bg-gray-100 px-3 py-1 rounded" x-text="barcode || 'N/A'"></p>
                    </div>
                    <div class="w-full h-px bg-gray-100"></div>
                    <div class="flex flex-col items-center w-full px-2">
                        <svg x-ref="bcProduct" class="max-w-full h-auto"></svg>
                    </div>
                </div>

                <div x-show="tab === 'short'" class="w-full flex flex-col items-center space-y-6" style="display: none;">
                    <div class="flex flex-col items-center w-full">
                        <div x-ref="qrShort" class="mx-auto flex justify-center bg-white p-2 border border-gray-100 rounded-lg min-h-[144px]"></div>
                        <p class="mt-4 text-xs font-mono font-medium text-gray-600 bg-gray-100 px-3 py-1 rounded" x-text="shortCode || 'N/A'"></p>
                    </div>
                    <div class="w-full h-px bg-gray-100"></div>
                    <div class="flex flex-col items-center w-full px-2">
                        <svg x-ref="bcShort" class="max-w-full h-auto"></svg>
                    </div>
                </div>

            </div>
        </div>
    </div>
    {{-- ── TAG MODAL ─────────────────────────────── --}}
    <div x-data="tagModal()" @open-tag-modal.window="openModal($event.detail)" x-show="isOpen" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4 sm:p-6" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <div x-show="isOpen" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="closeModal()"></div>

        <div x-show="isOpen" class="relative bg-white rounded-xl text-left shadow-2xl transform transition-all w-full max-w-3xl flex flex-col overflow-hidden border border-gray-100">
            
            <div class="bg-white px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900" x-text="skuName"></h3>
                    <p class="text-xs text-gray-500">Print Tag</p>
                </div>
                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-500 p-1.5 rounded-md hover:bg-gray-100 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex flex-col lg:flex-row h-full">
                <!-- Controls -->
                <div class="w-full lg:w-1/3 bg-gray-50 p-6 border-r border-gray-200 flex flex-col space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Barcode Content</label>
                        <select x-model="skuType" @change="renderCodes" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-black focus:border-black">
                            <option value="product">Product SKU</option>
                            <option value="short">Short SKU</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1 uppercase tracking-wider">Barcode Format</label>
                        <select x-model="barcodeType" @change="renderCodes" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-black focus:border-black">
                            <option value="1D">1D Barcode</option>
                            <option value="2D">2D QR Code</option>
                            <option value="None">None</option>
                        </select>
                    </div>
                    
                    <div class="mt-auto space-y-3 pt-6">
                        
                        <button @click="downloadSVG()" :disabled="isGenerating" :class="isGenerating ? 'opacity-50 cursor-not-allowed' : ''" class="w-full flex items-center justify-center gap-2 bg-indigo-50 border border-indigo-200 text-indigo-700 px-4 py-2 rounded-md font-bold hover:bg-indigo-100 transition-colors shadow-sm">
                            <svg x-show="!isGenerating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span x-text="isGenerating ? 'Generating...' : 'Download SVG'"></span>
                        </button>
                        <button @click="downloadJPG" :disabled="isGenerating" :class="isGenerating ? 'opacity-50 cursor-not-allowed' : ''" class="w-full flex items-center justify-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md font-bold hover:bg-gray-50 transition-colors shadow-sm">
                            <svg x-show="!isGenerating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span x-text="isGenerating ? 'Generating...' : 'Download JPG'"></span>
                        </button>
                        <button @click="downloadPDF" :disabled="isGenerating" :class="isGenerating ? 'opacity-50 cursor-not-allowed bg-gray-600' : 'bg-black hover:bg-gray-800'" class="w-full flex items-center justify-center gap-2 text-white px-4 py-2 rounded-md font-bold transition-colors shadow-sm">
                            <svg x-show="!isGenerating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span x-text="isGenerating ? 'Generating PDF...' : (isBulk ? 'Download All as PDF' : 'Download PDF')"></span>
                        </button>
                    </div>
                </div>

                <!-- Live Tag Preview -->
                <div class="w-full lg:w-2/3 p-6 flex items-center justify-center bg-gray-100 min-h-[450px]">
                    <div x-ref="printArea" class="bg-white border border-gray-300 px-4 text-black font-sans relative mx-auto flex flex-col justify-between overflow-hidden"
                         :style="(printerSize === '48x72' ? 'width: 250px; height: 375px;' : (printerSize === '50x25' ? 'width: 300px; height: 150px;' : 'width: 250px; height: 375px;')) + ` padding-top: ${marginTop ? (isNaN(marginTop) ? marginTop : marginTop + 'px') : '8px'}; padding-bottom: ${marginBottom ? (isNaN(marginBottom) ? marginBottom : marginBottom + 'px') : '8px'};`">
                        
                        {{-- Visual Hole Punch (Ignored during screenshot) --}}
                        <div data-html2canvas-ignore="true" class="absolute left-1/2 transform -translate-x-1/2 w-4 h-4 bg-gray-100 rounded-full border border-gray-300 shadow-inner z-10" 
                             :style="`top: ${holeTopMargin ? (isNaN(holeTopMargin) ? holeTopMargin : holeTopMargin + 'px') : '8px'};`"></div>
                        
                        {{-- Pre-encode images to base64 to avoid ALL CORS/Taint issues with html-to-image --}}
                        @php
                            function getBase64Image($path) {
                                if (!$path) return null;
                                $fullPath = storage_path('app/public/' . $path);
                                if (file_exists($fullPath)) {
                                    $type = pathinfo($fullPath, PATHINFO_EXTENSION);
                                    $data = file_get_contents($fullPath);
                                    return 'data:image/' . $type . ';base64,' . base64_encode($data);
                                }
                                return null;
                            }
                            $logoB64 = getBase64Image($market->tag_main_logo ?? null);
                            $iconB64 = getBase64Image($market->tag_icon ?? null);
                            $washB64 = getBase64Image($market->tag_washing_instruction ?? null);
                        @endphp

                        {{-- Black line --}}
                        <div class="w-full border-t border-black mb-3"></div>

                        {{-- Logo --}}
                        <div class="flex justify-center mb-4 h-8 w-full">
                            @if($logoB64)
                                <img src="{{ $logoB64 }}" class="max-h-full max-w-full object-contain filter grayscale">
                            @else
                                <div class="font-bold text-sm text-center w-full">Logo</div>
                            @endif
                        </div>

                        {{-- Grid: SVG-based mathematical layout for pixel-perfect centering in html2canvas --}}
                        <div style="width:100%; margin-top:4px; margin-bottom:2px;">
                            <svg width="100%" height="72px" viewBox="0 0 250 72" style="border: 0.5px solid black; background: white; font-family: sans-serif;">
                                <!-- Grid Lines -->
                                <line x1="87.5" y1="0" x2="87.5" y2="72" stroke="black" stroke-width="0.5"/>
                                <line x1="87.5" y1="40" x2="250" y2="40" stroke="black" stroke-width="0.5"/>
                                <line x1="152.5" y1="0" x2="152.5" y2="40" stroke="black" stroke-width="0.5"/>
                                <line x1="201.25" y1="0" x2="201.25" y2="40" stroke="black" stroke-width="0.5"/>
                                
                                <!-- Icon -->
                                @if($iconB64)
                                    <image x="4" y="4" width="79.5" height="64" href="{{ $iconB64 }}" preserveAspectRatio="xMidYMid meet" filter="grayscale(100%)" />
                                @else
                                    <text x="43.75" y="36" font-size="11" fill="black" text-anchor="middle" dominant-baseline="middle">Icon</text>
                                @endif

                                <!-- Size -->
                                <text x="120" y="20" font-size="18" font-weight="bold" fill="black" text-anchor="middle" dominant-baseline="middle" x-text="skuSize"></text>

                                <!-- Chest -->
                                <text x="176.875" y="12" font-size="8" fill="black" text-anchor="middle" dominant-baseline="middle" x-text="chestLabel"></text>
                                <text x="176.875" y="28" font-size="16" fill="black" text-anchor="middle" dominant-baseline="middle" x-text="chest"></text>

                                <!-- Length -->
                                <text x="225.625" y="12" font-size="8" fill="black" text-anchor="middle" dominant-baseline="middle" x-text="lengthLabel"></text>
                                <text x="225.625" y="28" font-size="16" fill="black" text-anchor="middle" dominant-baseline="middle" x-text="length"></text>

                                <!-- Fabric -->
                                <g x-show="fabricLines.length === 1">
                                    <text x="168.75" y="58" font-size="13" fill="black" text-anchor="middle" dominant-baseline="middle" x-text="fabricLines[0]"></text>
                                </g>
                                <g x-show="fabricLines.length > 1">
                                    <text x="168.75" y="50" font-size="13" fill="black" text-anchor="middle" dominant-baseline="middle" x-text="fabricLines[0]"></text>
                                    <text x="168.75" y="66" font-size="13" fill="black" text-anchor="middle" dominant-baseline="middle" x-text="fabricLines[1]"></text>
                                </g>
                            </svg>
                        </div>

                        {{-- Product Name --}}
                        <div style="text-align:center; margin-top:0px; margin-bottom:8px; display:flex; justify-content:center; width:100%;">
                            <span style="font-size:11px; line-height:1.3; text-align:center;" x-text="skuName"></span>
                        </div>

                        {{-- Washing Instructions --}}
                        <div style="text-align:center; margin-bottom:10px; display:flex; flex-direction:column; align-items:center; width:100%;">
                            <div style="font-size:8px; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px; text-align:center;">WASHING INSTRUCTIONS</div>
                            <div style="height:24px; text-align:center; display:flex; justify-content:center;">
                                @if($washB64)
                                    <img src="{{ $washB64 }}" style="display:block; max-height:24px; max-width:100%; object-fit:contain; filter:grayscale(100%);">
                                @else
                                    <span style="font-size:10px; color:#000000; font-style:italic;">Washing Icons</span>
                                @endif
                            </div>
                        </div>

                        {{-- Price and Barcode: Flexbox layout instead of table --}}
                        <div style="display:flex; justify-content:space-between; align-items:center; width:100%; margin:4px 0;">
                            {{-- Price Section --}}
                            <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#000000;">
                                <div style="font-size:12px; margin-bottom:4px;">
                                    MRP: ₹<span x-text="mrp"></span>
                                </div>
                                <div style="font-size:11px; margin-bottom:0px;">Offer Price</div>
                                <div style="font-size:22px; line-height:1;">₹<span x-text="price"></span></div>
                            </div>
                            
                            {{-- Barcode Section --}}
                            <div style="width:90px; flex-shrink:0; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                                <div x-show="barcodeType === '2D'" style="width:80px; height:80px; margin:0 auto; text-align:center;">
                                    <div x-ref="qrTag" style="width:100%; height:100%; display:flex; justify-content:center; align-items:center;"></div>
                                </div>
                                <div x-show="barcodeType === '1D'" style="width:80px; height:48px; overflow:hidden; margin:0 auto; display:flex; justify-content:center; align-items:center;">
                                    <svg x-ref="bcTag" style="max-height:100%; max-width:100%;"></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html-to-image@1.11.11/dist/html-to-image.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('tagModal', () => ({
            isOpen: false,
            isGenerating: false,
            isBulk: false,
            skusToPrint: [],
            
            skuName: '',
            skuSize: 'S',
            price: '',
            mrp: '',
            barcode: '',
            shortCode: '',
            fabric: 'Cotton',
            chest: '--',
            length: '--',
            chestLabel: 'Chest',
            lengthLabel: 'Length',
            
            get fabricLines() {
                return this.formatFabricLines(this.fabric);
            },
            
            skuType: 'product',
            barcodeType: '{{ $market->tag_barcode_type ?? "QR" }}',
            
            printerSize: '{{ $market->tag_printer_size ?? "48x72" }}',
            marginTop: '{{ $market->tag_margin_top ?? "10" }}',
            marginBottom: '{{ $market->tag_margin_bottom ?? "10" }}',
            holeTopMargin: '{{ $market->tag_hole_top_margin ?? "8" }}',
            
            // Canvas grid icon preload
            _gridIconImg: null,
            _gridIconUrl: '{{ isset($market->tag_icon) && $market->tag_icon ? asset("storage/".$market->tag_icon) : "" }}',
            
            init() {
                // Preload icon for canvas drawing
                if (this._gridIconUrl) {
                    const img = new Image();
                    img.crossOrigin = 'anonymous';
                    img.onload = () => { this._gridIconImg = img; this.drawGrid(); };
                    img.src = this._gridIconUrl;
                }
            },
            
            openModal(data) {
                if (data.isBulk) {
                    this.isBulk = true;
                    let expanded = [];
                    data.skus.forEach(sku => {
                        let count = parseInt(sku.stock);
                        if (isNaN(count)) count = 1;
                        if (count < 1) count = 1; // Default to 1 if stock is 0 but it was explicitly selected
                        for (let i = 0; i < count; i++) {
                            expanded.push(sku);
                        }
                    });
                    this.skusToPrint = expanded;
                } else {
                    this.isBulk = false;
                    this.skusToPrint = [data];
                }
                this.setCurrentSku(this.skusToPrint[0]);
                this.isOpen = true;
            },
            
            setCurrentSku(data) {
                this.skuName = data.name;
                this.skuSize = data.size;
                this.price = data.price;
                this.mrp = data.mrp;
                this.barcode = data.barcode;
                this.shortCode = data.shortCode;
                this.fabric = data.fabric || 'Cotton';
                this.chest = data.chest || '--';
                this.length = data.length || '--';
                this.chestLabel = data.chestLabel || 'Chest';
                this.lengthLabel = data.lengthLabel || 'Length';
                setTimeout(() => {
                    this.renderCodes();
                    this.drawGrid();
                }, 100);
            },
            
            // Draw the size/chest/length/fabric grid on <canvas> using Canvas 2D API
            // textBaseline:'middle' + textAlign:'center' gives pixel-perfect centering
            drawGrid() {
                const canvas = this.$refs.gridCanvas;
                if (!canvas) return;
                const W = canvas.width;  // 750
                const H = canvas.height; // 216
                const ctx = canvas.getContext('2d');
                const dpr = 3; // canvas is 3x the display size
                
                // Clear with white
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, W, H);
                
                // Layout (in canvas pixels = display px × dpr)
                const iconW    = Math.round(W * 0.35);     // ~262px (35% of width)
                const rightW   = W - iconW;                // ~488px
                const topH     = Math.round(H * 40 / 72); // ~120px (40/72 of height)
                const botH     = H - topH;                 // ~96px
                const sizeW    = Math.round(rightW * 0.40);// ~195px
                const chestW   = Math.round(rightW * 0.30);// ~146px
                const lengthW  = rightW - sizeW - chestW;  // ~147px
                
                // Internal borders (no outer — CSS border on wrapper div handles that)
                ctx.strokeStyle = '#000000';
                ctx.lineWidth = dpr;
                
                // Icon | Right separator
                ctx.beginPath(); ctx.moveTo(iconW, 0); ctx.lineTo(iconW, H); ctx.stroke();
                // Top row | Fabric row separator
                ctx.beginPath(); ctx.moveTo(iconW, topH); ctx.lineTo(W, topH); ctx.stroke();
                // Size | Chest separator
                ctx.beginPath(); ctx.moveTo(iconW + sizeW, 0); ctx.lineTo(iconW + sizeW, topH); ctx.stroke();
                // Chest | Length separator
                ctx.beginPath(); ctx.moveTo(iconW + sizeW + chestW, 0); ctx.lineTo(iconW + sizeW + chestW, topH); ctx.stroke();
                
                // Text rendering: textAlign:'center' + textBaseline:'middle' = guaranteed centering
                ctx.fillStyle = '#000000';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                
                // Cell center Y for top row
                const topCy = topH / 2;
                
                // SIZE (bold, large)
                const sizeCx = iconW + sizeW / 2;
                ctx.font = `bold ${18 * dpr}px Arial, sans-serif`;
                ctx.fillText(this.skuSize || 'S', sizeCx, topCy);
                
                // CHEST label + value (stacked, grouped at center)
                const chestCx = iconW + sizeW + chestW / 2;
                const labelOffset = 9 * dpr;  // distance from center to label
                const valueOffset = 9 * dpr;  // distance from center to value
                ctx.font = `${8 * dpr}px Arial, sans-serif`;
                ctx.fillText(this.chestLabel || 'Chest', chestCx, topCy - labelOffset);
                ctx.font = `600 ${13 * dpr}px Arial, sans-serif`;
                ctx.fillText(this.chest || '--', chestCx, topCy + valueOffset);
                
                // LENGTH label + value
                const lengthCx = iconW + sizeW + chestW + lengthW / 2;
                ctx.font = `${8 * dpr}px Arial, sans-serif`;
                ctx.fillText(this.lengthLabel || 'Length', lengthCx, topCy - labelOffset);
                ctx.font = `600 ${13 * dpr}px Arial, sans-serif`;
                ctx.fillText(this.length || '--', lengthCx, topCy + valueOffset);
                
                // FABRIC (centered in bottom row)
                const fabricCx = iconW + rightW / 2;
                const fabricCy = topH + botH / 2;
                const lines = this.formatFabricLines(this.fabric);
                ctx.font = `bold ${9 * dpr}px Arial, sans-serif`;
                if (lines.length >= 2) {
                    ctx.fillText(lines[0], fabricCx, fabricCy - 7 * dpr);
                    ctx.fillText(lines[1], fabricCx, fabricCy + 7 * dpr);
                } else {
                    ctx.fillText(lines[0] || this.fabric, fabricCx, fabricCy);
                }
                
                // ICON (if preloaded)
                if (this._gridIconImg) {
                    const pad = 8 * dpr;
                    const maxW = iconW - pad * 2;
                    const maxH = H - pad * 2;
                    const ratio = Math.min(maxW / this._gridIconImg.width, maxH / this._gridIconImg.height);
                    const iW = this._gridIconImg.width * ratio;
                    const iH = this._gridIconImg.height * ratio;
                    const iX = (iconW - iW) / 2;
                    const iY = (H - iH) / 2;
                    ctx.filter = 'grayscale(100%)';
                    ctx.drawImage(this._gridIconImg, iX, iY, iW, iH);
                    ctx.filter = 'none';
                }
            },
            
            // Split fabric string into lines for canvas drawing
            formatFabricLines(text) {
                if (!text) return [''];
                // Match percentage-led segments: "90% Cotton", "10% Polyester"
                const parts = text.match(/\d+%[^0-9]*/g);
                if (parts && parts.length >= 2) return parts.map(p => p.trim());
                // Fallback: split at midpoint word
                const words = text.split(' ');
                if (words.length > 2) {
                    const mid = Math.ceil(words.length / 2);
                    return [words.slice(0, mid).join(' '), words.slice(mid).join(' ')];
                }
                return [text];
            },
            
            formatFabric(text) {
                if (!text) return '';
                // Automatically break into a new line before a percentage mix (e.g. "90% Cotton 10% Polyester")
                return text.replace(/\s+(\d+%)/g, '<br>$1');
            },
            
            closeModal() {
                if(this.isGenerating) return;
                this.isOpen = false;
            },
            
            renderCodes() {
                if (this.barcodeType === 'None') return;
                
                const codeToRender = this.skuType === 'product' ? this.barcode : this.shortCode;
                if (!codeToRender || codeToRender === 'N/A') return;
                
                try {
                    if (this.barcodeType === '2D' || this.barcodeType === 'QR') {
                        const qrDiv = this.$refs.qrTag;
                        if (qrDiv && window.QRCode) {
                            qrDiv.innerHTML = '';
                            // Generate high-resolution QR code
                            new QRCode(qrDiv, { text: codeToRender, width: 400, height: 400, colorDark: "#000000", colorLight: "#ffffff", correctLevel : QRCode.CorrectLevel.L });
                            // Force scale down with CSS so html2canvas captures high pixel density
                            const qrEl = qrDiv.querySelector('canvas') || qrDiv.querySelector('img');
                            if (qrEl) {
                                qrEl.style.width = '100%';
                                qrEl.style.height = '100%';
                            }
                        }
                    } else if (this.barcodeType === '1D') {
                        const bcSvg = this.$refs.bcTag;
                        if (bcSvg && window.JsBarcode) {
                            JsBarcode(bcSvg, codeToRender, { format: "CODE128", width: 1.5, height: 35, displayValue: false, margin: 0 });
                        }
                    }
                } catch (e) {
                    console.error("Barcode Render Error:", e);
                }
            },
            
            async downloadJPG() {
                if(!this.$refs.printArea || this.isGenerating) return;
                this.isGenerating = true;
                try {
                    for(let i=0; i < this.skusToPrint.length; i++) {
                        this.setCurrentSku(this.skusToPrint[i]);
                        await new Promise(r => setTimeout(r, 200));
                        
                        const canvas = await html2canvas(this.$refs.printArea, {
                            scale: 3,
                            useCORS: true,
                            allowTaint: true
                        });
                        const cleanName = this.skuName.replace(/[<>:"\/\\|?*]+/g, '').trim();
                        link.download = `${cleanName}.jpg`;
                        link.href = canvas.toDataURL('image/jpeg', 1.0);
                        link.click();
                        
                        if(this.isBulk) await new Promise(r => setTimeout(r, 300));
                    }
                } catch(e) { console.error('JPG Error', e); alert('Could not generate JPG.'); }
                this.isGenerating = false;
            },
            
            async downloadSVG() {
                alert('SVG generation requires html-to-image which is currently disabled for compatibility.');
            },
            
            async downloadPDF() {
                if(!this.$refs.printArea || !window.jspdf || this.isGenerating) return;
                this.isGenerating = true;
                try {
                    const pdfWidth = this.printerSize === '50x25' ? 50 : 48;
                    const pdfHeight = this.printerSize === '50x25' ? 25 : 72;
                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF({
                        orientation: pdfWidth > pdfHeight ? 'landscape' : 'portrait',
                        unit: 'mm',
                        format: [pdfWidth, pdfHeight]
                    });
                    
                    for(let i=0; i < this.skusToPrint.length; i++) {
                        this.setCurrentSku(this.skusToPrint[i]);
                        await new Promise(r => setTimeout(r, 200));
                        
                        const canvas = await html2canvas(this.$refs.printArea, {
                            scale: 3,
                            useCORS: true,
                            allowTaint: true
                        });
                        
                        const imgData = canvas.toDataURL('image/jpeg', 1.0);
                        if(i > 0) pdf.addPage();
                        pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight);
                    }
                    
                    const cleanName = this.skuName.replace(/[<>:"\/\\|?*]+/g, '').trim();
                    const fileName = this.isBulk ? `Bulk_Tags_${Date.now()}.pdf` : `${cleanName}.pdf`;
                    pdf.save(fileName);
                } catch(e) { console.error('PDF Error', e); alert('Could not generate PDF.'); }
                this.isGenerating = false;
            }
        }));
        Alpine.data('barcodeModal', () => ({
            isOpen: false,
            tab: 'product',
            skuName: '',
            barcode: '',
            shortCode: '',
            
            openModal(data) {
                this.skuName = data.skuName;
                this.barcode = data.barcode;
                this.shortCode = data.shortCode;
                this.setTab('product');
                this.isOpen = true;
            },
            
            closeModal() {
                this.isOpen = false;
            },

            setTab(t) {
                this.tab = t;
                setTimeout(() => {
                    this.renderCodes();
                }, 100);
            },

            renderCodes() {
                try {
                    if (this.barcode && this.barcode !== 'N/A') {
                        const qrDiv = this.$refs.qrProduct;
                        if (qrDiv && window.QRCode) {
                            qrDiv.innerHTML = '';
                            new QRCode(qrDiv, { text: this.barcode, width: 140, height: 140, colorDark: "#000000", colorLight: "#ffffff" });
                        }
                        
                        const bcSvg = this.$refs.bcProduct;
                        if (bcSvg && window.JsBarcode) JsBarcode(bcSvg, this.barcode, { format: "CODE128", width: 2, height: 60, displayValue: false });
                    }
                    
                    if (this.shortCode && this.shortCode !== 'N/A') {
                        const qrDiv = this.$refs.qrShort;
                        if (qrDiv && window.QRCode) {
                            qrDiv.innerHTML = '';
                            new QRCode(qrDiv, { text: this.shortCode, width: 140, height: 140, colorDark: "#000000", colorLight: "#ffffff" });
                        }
                        
                        const bcSvg = this.$refs.bcShort;
                        if (bcSvg && window.JsBarcode) JsBarcode(bcSvg, this.shortCode, { format: "CODE128", width: 2, height: 60, displayValue: false });
                    }
                } catch (e) {
                    console.error("Barcode Generation Error:", e);
                }
            }
        }));

        Alpine.data('productSelector', () => ({
            products: [],
            searchQuery: '',
            isLoading: false,
            searchTimeout: null,
            
            showModal: false,
            activeProduct: null,
            
            selectedVariants: {},
            variantPrices: {},
            variantStock: {},
            bulkPrice: '',
            bulkStock: '',
            
            init() {
                this.fetchProducts();
                this.$watch('searchQuery', () => {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => this.fetchProducts(), 300);
                });
            },
            
            fetchProducts() {
                this.isLoading = true;
                const marketSlug = '{{ $market->slug }}';
                const url = `{{ route('admin.pos-markets.searchProducts', $market->slug) }}?q=` + encodeURIComponent(this.searchQuery);
                
                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        this.products = data;
                        this.isLoading = false;
                    })
                    .catch(err => {
                        console.error("Error fetching products:", err);
                        this.isLoading = false;
                    });
            },
            
            get filteredProducts() {
                return this.products;
            },
            
            get selectedCount() {
                return Object.values(this.selectedVariants).filter(Boolean).length;
            },
            
            openProduct(product) {
                this.activeProduct = product;
                this.selectedVariants = {};
                this.variantPrices = {};
                this.variantStock = {};
                this.bulkPrice = '';
                this.bulkStock = '';
                
                if (product && product.skus) {
                    product.skus.forEach(sku => {
                        this.selectedVariants[sku.id] = false;
                        this.variantPrices[sku.id] = sku.online_price;
                        this.variantStock[sku.id] = 0; // Default stock is 0
                    });
                }
                
                this.showModal = true;
            },
            
            closeModal() {
                this.showModal = false;
                setTimeout(() => { this.activeProduct = null; }, 300);
            },
            
            toggleAll(e) {
                const checked = e.target.checked;
                if (this.activeProduct && this.activeProduct.skus) {
                    this.activeProduct.skus.forEach(sku => {
                        this.selectedVariants[sku.id] = checked;
                    });
                }
            },
            
            applyBulkPrice() {
                if (!this.bulkPrice) return;
                Object.keys(this.selectedVariants).forEach(skuId => {
                    if (this.selectedVariants[skuId]) {
                        this.variantPrices[skuId] = this.bulkPrice;
                    }
                });
            },
            
            applyBulkStock() {
                if (this.bulkStock === '' || this.bulkStock < 0) return;
                Object.keys(this.selectedVariants).forEach(skuId => {
                    if (this.selectedVariants[skuId]) {
                        this.variantStock[skuId] = parseInt(this.bulkStock);
                    }
                });
            },
            
            submitSelection(e) {
                if (this.selectedCount === 0) {
                    e.preventDefault();
                    alert('Please select at least one variant to add.');
                }
            }
        }));

        // Prepare master data for bulk print mapping
        window.assignedSkusData = {!! json_encode($assignedSkus->map(function($sku) {
            return [
                'id' => (string) $sku->sku_id,
                'name' => stripslashes($sku->name),
                'size' => $sku->size_name ?? 'S',
                'price' => $sku->override_price,
                'mrp' => $sku->original_price,
                'barcode' => $sku->barcode,
                'shortCode' => $sku->short_code,
                'fabric' => $sku->fabric_name ?? 'Cotton',
                'chest' => $sku->chest ?? '--',
                'length' => $sku->length ?? '--',
                'chestLabel' => $sku->chest_label ?? 'Chest',
                'lengthLabel' => $sku->length_label ?? 'Length',
                'stock' => $sku->stock ?? 1
            ];
        })->toArray()) !!};

        Alpine.data('assignedProducts', () => ({
            searchAssigned: '',
            selectedSkus: [],
            selectAll: false,
            isDeleting: false,
            
            toggleAll() {
                if (this.selectAll) {
                    // Select all visible skus that match search
                    this.selectedSkus = window.assignedSkusData
                        .filter(s => {
                            if (!this.searchAssigned) return true;
                            const term = this.searchAssigned.toLowerCase();
                            return s.name.toLowerCase().includes(term) || 
                                   (s.shortCode && s.shortCode.toLowerCase().includes(term)) || 
                                   (s.barcode && s.barcode.toLowerCase().includes(term));
                        })
                        .map(s => String(s.id));
                } else {
                    this.selectedSkus = [];
                }
            },
            
            bulkPrintTags() {
                if (this.selectedSkus.length === 0) return;
                const skusToPrint = window.assignedSkusData.filter(s => this.selectedSkus.includes(String(s.id)));
                window.dispatchEvent(new CustomEvent('open-tag-modal', { detail: { isBulk: true, skus: skusToPrint } }));
            },

            async bulkDelete() {
                if (this.selectedSkus.length === 0) return;
                if (!confirm(`Are you sure you want to remove ${this.selectedSkus.length} product(s) from this market?`)) return;
                
                this.isDeleting = true;
                try {
                    // Call individual delete routes sequentially
                    for (const skuId of this.selectedSkus) {
                        const form = document.getElementById('remove-form-' + skuId);
                        if (form) {
                            const formData = new FormData(form);
                            await fetch(form.action, {
                                method: 'POST',
                                body: formData,
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            });
                        }
                    }
                    window.location.reload();
                } catch(e) {
                    console.error(e);
                    alert('Error removing some products. Please refresh and check.');
                    this.isDeleting = false;
                }
            }
        }));
    });
</script>
@endpush
@endsection

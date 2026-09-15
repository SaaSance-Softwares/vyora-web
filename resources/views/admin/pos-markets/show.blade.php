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
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Products Available at {{ $market->name }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
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
                    <tr class="hover:bg-gray-50 transition" x-data="{ editing: false }">
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
                        <td class="px-6 py-4 text-gray-500 font-mono text-sm">{{ $sku->barcode ?? 'N/A' }}<hr class="my-1 border-gray-200">{{ $sku->short_code }}</td>
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

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
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
                const url = `/occ/pos-markets/${marketSlug}/search-products?q=` + encodeURIComponent(this.searchQuery);
                
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
    });
</script>
@endpush
@endsection

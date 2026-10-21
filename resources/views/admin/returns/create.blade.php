@extends('layouts.admin')

@section('title', 'Create Return/Exchange')
@section('header', 'Create Return/Exchange')

@section('content')
<div class="space-y-6" x-data="createReturnRequest()">
    
    <!-- Page header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create Return/Exchange Request</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manually create an RMA for a customer</p>
        </div>
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <a href="{{ route('admin.returns.index') }}" class="px-5 py-2.5 bg-gray-50 border border-gray-200 text-gray-600 rounded-lg text-sm font-bold hover:bg-gray-100 transition-colors shadow-sm">Back</a>
        </div>
    </div>

    <!-- Error Alerts -->
    <div x-show="errorMessage" class="mb-4 bg-red-50 text-red-600 p-4 rounded-lg font-medium" x-text="errorMessage" style="display: none;"></div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <!-- Left Column: Search & Order Details -->
        <div class="xl:col-span-2 space-y-6">
            
            <!-- Order Search -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">1. Select Order</h2>
                <div class="relative">
                    <input type="text" x-model="searchQuery" @input.debounce.500ms="searchOrder" 
                           placeholder="Enter Order Number (e.g. ORD-1234)" 
                           class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black pl-10">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                
                <!-- Search Results Dropdown -->
                <div x-show="searchResults.length > 0" class="mt-2 border border-gray-200 rounded-lg max-h-64 overflow-y-auto divide-y divide-gray-100" style="display: none;">
                    <template x-for="order in searchResults" :key="order.id">
                        <button @click="selectOrder(order)" type="button" class="w-full text-left p-3 hover:bg-gray-50 flex justify-between items-center">
                            <div>
                                <span class="font-bold text-gray-800" x-text="order.order_number"></span>
                                <div class="text-sm text-gray-500" x-text="order.user ? order.user.name : 'Guest'"></div>
                            </div>
                            <span class="text-sm bg-gray-100 px-2 py-1 rounded" x-text="order.status"></span>
                        </button>
                    </template>
                </div>
                <div x-show="isSearching" class="mt-2 text-sm text-gray-500" style="display: none;">Searching...</div>
                
                <!-- Selected Order Info -->
                <div x-show="selectedOrder" class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200" style="display: none;">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="font-bold text-gray-900">Selected Order: <span x-text="selectedOrder?.order_number"></span></h3>
                        <button type="button" @click="selectedOrder = null; selectedItems = [];" class="text-sm text-red-600 hover:underline">Change Order</button>
                    </div>
                    <div class="text-sm text-gray-600">
                        Customer: <span class="font-medium" x-text="selectedOrder?.user?.name || 'Guest'"></span><br>
                        Total: <span class="font-medium" x-text="'₹' + selectedOrder?.total_amount"></span>
                    </div>
                </div>
            </div>

            <!-- Items Selection -->
            <div x-show="selectedOrder" class="bg-white shadow-sm rounded-lg border border-gray-200 p-6" style="display: none;">
                <h2 class="text-lg font-bold text-gray-800 mb-4">2. Select Items to <span class="capitalize" x-text="requestType"></span></h2>
                
                                <div class="space-y-4">
                    <template x-for="item in selectedOrder?.items" :key="item.id">
                        <div class="border border-gray-200 rounded-lg p-4" :class="{'bg-gray-50 opacity-75': getAvailableQty(item) === 0}">
                            <div class="flex gap-4">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           :id="'item_' + item.id" 
                                           :value="item.id" 
                                           :disabled="getAvailableQty(item) === 0"
                                           @change="toggleItem(item, $event)" 
                                           class="h-5 w-5 border border-gray-300 rounded text-black focus:ring-black disabled:opacity-50">
                                </div>
                                
                                <!-- Product Image -->
                                <div class="w-16 h-20 rounded-md bg-gray-100 overflow-hidden shrink-0 border border-gray-200">
                                    <template x-if="item.product?.image_url">
                                        <img :src="item.product.image_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!item.product?.image_url">
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <label :for="'item_' + item.id" class="font-bold text-gray-900 cursor-pointer" :class="{'cursor-not-allowed': getAvailableQty(item) === 0}" x-text="item.product?.name || 'Unknown Product'"></label>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                SKU: <span class="font-mono text-gray-700" x-text="item.sku?.sku"></span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-bold text-gray-900">₹<span x-text="item.price"></span></div>
                                            <template x-if="item.sku?.mrp && item.sku.mrp > item.price">
                                                <div class="text-xs text-gray-400 line-through">₹<span x-text="item.sku.mrp"></span></div>
                                            </template>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                        <template x-if="item.sku?.size">
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded font-medium border border-gray-200">Size: <span x-text="item.sku.size.name"></span></span>
                                        </template>
                                        <template x-if="item.sku?.color">
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded font-medium border border-gray-200 flex items-center gap-1.5">
                                                Color: <span x-text="item.sku.color.name"></span>
                                                <span class="w-3 h-3 rounded-full border border-gray-300" :style="`background-color: ${item.sku.color.hex_code}`"></span>
                                            </span>
                                        </template>
                                    </div>

                                    <div class="mt-3 flex items-center gap-3 text-sm">
                                        <div class="text-gray-600 font-medium">
                                            Purchased: <span class="text-gray-900" x-text="item.quantity"></span>
                                        </div>
                                        <div class="text-gray-300">|</div>
                                        <div class="text-blue-600 font-medium">
                                            Available to Request: <span class="text-blue-700" x-text="getAvailableQty(item)"></span>
                                        </div>
                                    </div>
                                    
                                    <!-- Status warning if already locked/returned -->
                                    <template x-if="getAvailableQty(item) < item.quantity">
                                        <div class="mt-2 p-2 bg-amber-50 rounded border border-amber-100 text-xs text-amber-700 flex gap-2">
                                            <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <div>
                                                <span x-text="item.quantity - getAvailableQty(item)"></span> unit(s) are already part of an active/completed Return or Exchange request.
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Quantity & Variant Selection (Visible if selected) -->
                            <template x-if="isSelected(item.id)">
                                <div class="mt-4 p-4 bg-gray-50 rounded-lg space-y-4 border border-gray-200 ml-9">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                        <input type="number" min="1" :max="getAvailableQty(item)" x-model="selectedItems[item.id].qty" class="border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black w-24">
                                    </div>
                                    
                                    <template x-if="requestType === 'exchange'">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Select New Variant (Exchange For)</label>
                                            
                                            <div x-data="{ 
                                                colors: [...new Map((item.product?.skus || []).filter(s => s.color).map(s => [s.color.id, s.color])).values()],
                                                sizes: [...new Map((item.product?.skus || []).filter(s => s.size).map(s => [s.size.id, s.size])).values()],
                                                
                                                checkMatchingSku() {
                                                    let sku = (item.product?.skus || []).find(s => 
                                                        (this.colors.length === 0 || s.color_id == selectedItems[item.id].exchange_color_id) && 
                                                        (this.sizes.length === 0 || s.size_id == selectedItems[item.id].exchange_size_id)
                                                    );
                                                    
                                                    if (sku && (this.colors.length === 0 || selectedItems[item.id].exchange_color_id) && (this.sizes.length === 0 || selectedItems[item.id].exchange_size_id)) {
                                                        selectedItems[item.id].exchange_variant_id = sku.id;
                                                    } else {
                                                        selectedItems[item.id].exchange_variant_id = '';
                                                    }
                                                }
                                            }" x-init="checkMatchingSku()">
                                                
                                                <!-- Colors -->
                                                <template x-if="colors.length > 0">
                                                    <div class="mb-3">
                                                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 block">Color</span>
                                                        <div class="flex flex-wrap gap-2">
                                                            <template x-for="c in colors" :key="c.id">
                                                                <button type="button" 
                                                                        @click="selectedItems[item.id].exchange_color_id = c.id; checkMatchingSku()"
                                                                        class="w-8 h-8 rounded-full border-2 focus:outline-none transition-all relative flex items-center justify-center"
                                                                        :class="selectedItems[item.id].exchange_color_id === c.id ? 'border-gray-900 shadow-sm ring-2 ring-gray-900 ring-offset-1' : 'border-gray-200 hover:border-gray-300'"
                                                                        :style="`background-color: ${c.hex_code}`"
                                                                        :title="c.name">
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                                
                                                <!-- Sizes -->
                                                <template x-if="sizes.length > 0">
                                                    <div class="mb-3">
                                                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 block">Size</span>
                                                        <div class="flex flex-wrap gap-2">
                                                            <template x-for="s in sizes" :key="s.id">
                                                                <button type="button" 
                                                                        @click="selectedItems[item.id].exchange_size_id = s.id; checkMatchingSku()"
                                                                        class="min-w-[3rem] px-3 py-1.5 border rounded-lg text-sm font-medium focus:outline-none transition-colors"
                                                                        :class="selectedItems[item.id].exchange_size_id === s.id ? 'border-gray-900 bg-gray-900 text-white shadow-sm' : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300 hover:bg-gray-50'">
                                                                    <span x-text="s.name"></span>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                                
                                                <!-- Selected SKU Status -->
                                                <div class="mt-2 h-5">
                                                    <template x-if="selectedItems[item.id].exchange_variant_id">
                                                        <span class="text-xs font-semibold text-green-600 flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                            Variant Selected (Stock: <span x-text="(item.product?.skus || []).find(s => s.id === selectedItems[item.id].exchange_variant_id)?.stock"></span>)
                                                        </span>
                                                    </template>
                                                    <template x-if="!selectedItems[item.id].exchange_variant_id && (colors.length > 0 || sizes.length > 0)">
                                                        <span class="text-xs font-semibold text-red-500 animate-pulse">Select options to choose variant</span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

        </div>
        
        <!-- Right Column: Settings & Submit -->
        <div class="space-y-6">
            
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">3. Request Details</h2>
                
                <div class="space-y-4">
                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-medium mb-1">Request Type</label>
                        <select x-model="requestType" class="border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black w-full">
                            <option value="return">Return</option>
                            <option value="exchange">Exchange</option>
                        </select>
                    </div>
                    
                    <!-- Reason -->
                    <div>
                        <label class="block text-sm font-medium mb-1">Reason</label>
                        <select x-model="reason" class="border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black w-full">
                            <option value="">-- Select Reason --</option>
                            <option value="defective">Defective / Damaged</option>
                            <option value="wrong_item">Wrong Item Received</option>
                            <option value="size_issue">Size / Fit Issue</option>
                            <option value="not_needed">No Longer Needed</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Comments -->
                    <div>
                        <label class="block text-sm font-medium mb-1">Admin Comments (Optional)</label>
                        <textarea x-model="comments" class="border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black w-full" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                    
                    <hr class="border-gray-200 my-4">

                    <!-- Fees -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-medium text-gray-900">Fee to Charge (₹)</label>
                            <span class="text-xs text-green-600 font-bold bg-green-50 px-2 py-0.5 rounded uppercase tracking-wider">Override</span>
                        </div>
                        <input type="number" x-model="fee_deducted" class="border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black w-full" min="0" step="0.01">
                        <p class="text-xs text-gray-500 mt-1">Set to 0 to waive fees for this customer.</p>
                    </div>

                    <!-- Target Status -->
                    <div>
                        <label class="block text-sm font-medium mb-1 mt-4">Initial Status</label>
                        <select x-model="status" class="border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black w-full">
                            <option value="approved">Approved (Awaiting Return)</option>
                            <option value="received">Received (Processing)</option>
                            <option value="completed">Completed (In-Store/Immediate)</option>
                            <option value="pending">Pending</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Select "Completed" if processing an in-store return/exchange instantly.</p>
                    </div>

                    <div class="pt-4">
                        <button @click="submitRequest" type="button" :disabled="isSubmitting || selectedItems.length === 0" class="bg-black text-white px-8 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors w-full disabled:opacity-50">
                            <span x-show="!isSubmitting">Create Request</span>
                            <span x-show="isSubmitting">Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('createReturnRequest', () => ({
        searchQuery: '',
        searchResults: [],
        isSearching: false,
        selectedOrder: null,
        
        requestType: 'return',
        reason: '',
        comments: '',
        fee_deducted: 0,
        status: 'approved',
        
        selectedItems: {},
        isSubmitting: false,
        errorMessage: '',

        init() {
            // Watch for type change to reset specific settings
            this.$watch('requestType', value => {
                if(value === 'exchange') {
                    this.fee_deducted = 100; // Standard exchange fee example
                } else {
                    this.fee_deducted = 0; // Returns typically have 0 fee upfront (deducted from refund)
                }
            });
        },

        async searchOrder() {
            if (this.searchQuery.length < 3) {
                this.searchResults = [];
                return;
            }
            this.isSearching = true;
            try {
                const response = await fetch(`/occ/returns/search-order?q=${this.searchQuery}`);
                this.searchResults = await response.json();
            } catch (e) {
                console.error(e);
            } finally {
                this.isSearching = false;
            }
        },

        selectOrder(order) {
            this.selectedOrder = order;
            this.searchResults = [];
            this.searchQuery = '';
            this.selectedItems = {};
        },

        toggleItem(item, event) {
            if (event.target.checked) {
                this.selectedItems[item.id] = {
                    id: item.id,
                    qty: 1,
                    exchange_variant_id: '',
                    exchange_color_id: null,
                    exchange_size_id: null
                };
            } else {
                delete this.selectedItems[item.id];
            }
        },

        isSelected(itemId) {
            return !!this.selectedItems[itemId];
        },

                getAvailableQty(item) {
            let rrequests = this.selectedOrder.return_requests || this.selectedOrder.returnRequests;
            if (!this.selectedOrder || !rrequests) {
                // If relation wasn't loaded, fallback to item.returned_quantity
                return item.quantity - (item.returned_quantity || 0);
            }
            
            let locked = 0;
            rrequests.forEach(rr => {
                if (rr.status !== 'rejected' && rr.status !== 'cancelled') {
                    if (rr.items) {
                        rr.items.forEach(rri => {
                            if (rri.order_item_id === item.id) {
                                locked += rri.quantity;
                            }
                        });
                    }
                }
            });
            
            return Math.max(0, item.quantity - locked);
        },

        async submitRequest() {
            if (!this.selectedOrder) {
                this.errorMessage = "Please select an order.";
                return;
            }
            if (Object.keys(this.selectedItems).length === 0) {
                this.errorMessage = "Please select at least one item.";
                return;
            }
            if (!this.reason) {
                this.errorMessage = "Please select a reason.";
                return;
            }
            if (this.requestType === 'exchange') {
                const missingVariant = Object.values(this.selectedItems).find(i => !i.exchange_variant_id);
                if (missingVariant) {
                    this.errorMessage = "Please select an exchange variant for all selected items.";
                    return;
                }
            }

            this.errorMessage = '';
            this.isSubmitting = true;

            const payload = {
                _token: '{{ csrf_token() }}',
                order_id: this.selectedOrder.id,
                type: this.requestType,
                reason: this.reason,
                comments: this.comments,
                fee_deducted: this.fee_deducted,
                status: this.status,
                items: Object.values(this.selectedItems).map(i => ({
                    order_item_id: i.id,
                    quantity: i.qty,
                    exchange_variant_id: i.exchange_variant_id || null
                }))
            };

            try {
                const response = await fetch('{{ route('admin.returns.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                
                if (response.ok) {
                    window.location.href = `/occ/returns/${data.id}`;
                } else {
                    this.errorMessage = data.message || "An error occurred during submission.";
                }
            } catch (e) {
                this.errorMessage = e.message;
            } finally {
                this.isSubmitting = false;
            }
        }
    }));
});
</script>
@endsection

@extends('layouts.admin')

@section('header', 'Cart Offcanvas & Recommendations')

@section('content')
<div class="w-full mx-auto pb-24">
    <div class="mb-6 mt-4">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Cart Offcanvas Drawer & Cross-Sell</h1>
        <p class="text-sm text-gray-500 mt-1">
            Configure the slide-in shopping bag drawer, pinned savings milestone progress bar, and 1-click recommended products to boost average order value (AOV).
        </p>
    </div>


    <form action="{{ route('admin.online-store.cart-drawer.update') }}" method="POST" id="cart-drawer-form">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left Side: Configuration Controls -->
            <div class="lg:col-span-8 space-y-6">

                <!-- 1. Drawer Basics Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Cart Offcanvas Drawer</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Control whether adding a product opens the interactive slide-in drawer.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="cart_drawer_enabled" value="1" class="sr-only peer" {{ ($settings['cart_drawer_enabled'] ?? '1') === '1' ? 'checked' : '' }} onchange="updatePreview()">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Drawer Header Title</label>
                            <input type="text" name="cart_drawer_title" id="cart_drawer_title" value="{{ $settings['cart_drawer_title'] ?? 'Your Bag' }}" class="block w-full rounded-xl border border-gray-300 py-2.5 px-3.5 focus:ring-black focus:border-black sm:text-sm font-medium" placeholder="Your Bag" oninput="updatePreview()">
                            <p class="text-[11px] text-gray-400 mt-1">Displayed at the top of the slide-in drawer alongside item count.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Savings Milestones in Drawer</label>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-200">
                                <span class="text-xs text-gray-700 font-medium">Show Milestone Progress & Confetti</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="cart_drawer_show_milestones" value="1" class="sr-only peer" {{ ($settings['cart_drawer_show_milestones'] ?? '1') === '1' ? 'checked' : '' }} onchange="updatePreview()">
                                    <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1">Incentivizes buyers to add more items to unlock discounts.</p>
                        </div>
                    </div>
                </div>

                <!-- 2. Recommendations Configuration Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Recommended Products (Cross-Sell)</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Display smart product recommendations inside the drawer with 1-click add.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="cart_drawer_show_recommendations" id="cart_drawer_show_recommendations" value="1" class="sr-only peer" {{ ($settings['cart_drawer_show_recommendations'] ?? '1') === '1' ? 'checked' : '' }} onchange="toggleRecommendations()">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                        </label>
                    </div>

                    <div id="recommendations-settings-body" class="space-y-6 mt-6 {{ ($settings['cart_drawer_show_recommendations'] ?? '1') === '1' ? '' : 'opacity-40 pointer-events-none' }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Section Title</label>
                                <input type="text" name="cart_drawer_recommendations_title" id="cart_drawer_recommendations_title" value="{{ $settings['cart_drawer_recommendations_title'] ?? 'Pairs Well With' }}" class="block w-full rounded-xl border border-gray-300 py-2.5 px-3.5 focus:ring-black focus:border-black sm:text-sm font-medium" placeholder="Pairs Well With" oninput="updatePreview()">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Number of Products to Recommend</label>
                                <input type="number" name="cart_drawer_recommendations_limit" min="1" max="10" value="{{ $settings['cart_drawer_recommendations_limit'] ?? 4 }}" class="block w-full rounded-xl border border-gray-300 py-2.5 px-3.5 focus:ring-black focus:border-black sm:text-sm font-medium">
                                <p class="text-[11px] text-gray-400 mt-1">Between 1 and 10 items (typically 3 or 4).</p>
                            </div>
                        </div>

                        <!-- Rule Selection -->
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-3">Recommendation Rule</label>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <!-- Rule 1: Top sold products -->
                                <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer hover:border-gray-400 transition-all {{ ($settings['cart_drawer_recommendation_rule'] ?? '') === 'top_sold' ? 'border-black bg-gray-50/70 ring-1 ring-black' : 'border-gray-200 bg-white' }}" onclick="selectRule('top_sold')">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <input type="radio" name="cart_drawer_recommendation_rule" value="top_sold" class="h-4 w-4 text-black focus:ring-black border-gray-300" {{ ($settings['cart_drawer_recommendation_rule'] ?? '') === 'top_sold' ? 'checked' : '' }}>
                                            <span class="text-sm font-bold text-gray-900">1. Top Sold Products</span>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">Dynamic</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2 pl-6.5">
                                        Calculates bestsellers dynamically from completed order history, excluding items already in cart.
                                    </p>
                                </label>

                                <!-- Rule 2: Near milestone amount -->
                                <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer hover:border-gray-400 transition-all {{ ($settings['cart_drawer_recommendation_rule'] ?? 'near_milestone') === 'near_milestone' ? 'border-black bg-gray-50/70 ring-1 ring-black' : 'border-gray-200 bg-white' }}" onclick="selectRule('near_milestone')">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <input type="radio" name="cart_drawer_recommendation_rule" value="near_milestone" class="h-4 w-4 text-black focus:ring-black border-gray-300" {{ ($settings['cart_drawer_recommendation_rule'] ?? 'near_milestone') === 'near_milestone' ? 'checked' : '' }}>
                                            <span class="text-sm font-bold text-gray-900">2. Near Next Milestone</span>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-800">Dynamic AOV Boost</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2 pl-6.5">
                                        Recommends products priced close to the gap needed to unlock the next discount milestone tier!
                                    </p>
                                </label>

                                <!-- Rule 3: Product from category -->
                                <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer hover:border-gray-400 transition-all {{ ($settings['cart_drawer_recommendation_rule'] ?? '') === 'category' ? 'border-black bg-gray-50/70 ring-1 ring-black' : 'border-gray-200 bg-white' }}" onclick="selectRule('category')">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <input type="radio" name="cart_drawer_recommendation_rule" value="category" class="h-4 w-4 text-black focus:ring-black border-gray-300" {{ ($settings['cart_drawer_recommendation_rule'] ?? '') === 'category' ? 'checked' : '' }}>
                                            <span class="text-sm font-bold text-gray-900">3. From Category</span>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-100 text-purple-800">Targeted</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2 pl-6.5">
                                        Pick a specific category (e.g. Accessories or Best Sellers) to feature products from.
                                    </p>
                                </label>

                                <!-- Rule 4: Product from collection -->
                                <label class="relative flex flex-col p-4 border rounded-xl cursor-pointer hover:border-gray-400 transition-all {{ ($settings['cart_drawer_recommendation_rule'] ?? '') === 'collection' ? 'border-black bg-gray-50/70 ring-1 ring-black' : 'border-gray-200 bg-white' }}" onclick="selectRule('collection')">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <input type="radio" name="cart_drawer_recommendation_rule" value="collection" class="h-4 w-4 text-black focus:ring-black border-gray-300" {{ ($settings['cart_drawer_recommendation_rule'] ?? '') === 'collection' ? 'checked' : '' }}>
                                            <span class="text-sm font-bold text-gray-900">4. From Collection</span>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800">Curated</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2 pl-6.5">
                                        Feature items from a seasonal, trending, or promotional curated collection.
                                    </p>
                                </label>

                                <!-- Rule 5: Fix product -->
                                <label class="sm:col-span-2 relative flex flex-col p-4 border rounded-xl cursor-pointer hover:border-gray-400 transition-all {{ ($settings['cart_drawer_recommendation_rule'] ?? '') === 'fixed' ? 'border-black bg-gray-50/70 ring-1 ring-black' : 'border-gray-200 bg-white' }}" onclick="selectRule('fixed')">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <input type="radio" name="cart_drawer_recommendation_rule" value="fixed" class="h-4 w-4 text-black focus:ring-black border-gray-300" {{ ($settings['cart_drawer_recommendation_rule'] ?? '') === 'fixed' ? 'checked' : '' }}>
                                            <span class="text-sm font-bold text-gray-900">5. Fixed Specific Products</span>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-800">Manual Pick</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2 pl-6.5">
                                        Handpick exact products to feature in the cart drawer.
                                    </p>
                                </label>
                            </div>
                        </div>

                        <!-- Rule 3 Parameter: Category Dropdown -->
                        <div id="rule-category-container" class="p-4 bg-purple-50/50 rounded-xl border border-purple-100 {{ ($settings['cart_drawer_recommendation_rule'] ?? '') === 'category' ? '' : 'hidden' }}">
                            <label class="block text-sm font-bold text-purple-900 mb-2">Select Target Category</label>
                            <select name="cart_drawer_category_id" class="block w-full rounded-xl border border-purple-200 py-2.5 px-3.5 focus:ring-purple-600 focus:border-purple-600 sm:text-sm bg-white">
                                <option value="">-- Choose Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ ($settings['cart_drawer_category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-purple-700 mt-1.5">Products from this category and its subcategories will be recommended.</p>
                        </div>

                        <!-- Rule 4 Parameter: Collection Dropdown -->
                        <div id="rule-collection-container" class="p-4 bg-amber-50/50 rounded-xl border border-amber-100 {{ ($settings['cart_drawer_recommendation_rule'] ?? '') === 'collection' ? '' : 'hidden' }}">
                            <label class="block text-sm font-bold text-amber-900 mb-2">Select Target Collection</label>
                            <select name="cart_drawer_collection_id" class="block w-full rounded-xl border border-amber-200 py-2.5 px-3.5 focus:ring-amber-600 focus:border-amber-600 sm:text-sm bg-white">
                                <option value="">-- Choose Collection --</option>
                                @foreach($collections as $collection)
                                    <option value="{{ $collection->id }}" {{ ($settings['cart_drawer_collection_id'] ?? '') == $collection->id ? 'selected' : '' }}>
                                        {{ $collection->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-amber-700 mt-1.5">Products assigned to this collection will be recommended.</p>
                        </div>

                        <!-- Rule 5 Parameter: Fixed Product Multi-Select -->
                        <div id="rule-fixed-container" class="p-4 bg-gray-50 rounded-xl border border-gray-200 {{ ($settings['cart_drawer_recommendation_rule'] ?? '') === 'fixed' ? '' : 'hidden' }}">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <label class="block text-sm font-bold text-gray-900">Handpick Specific Products</label>
                                    <p class="text-xs text-gray-500">Search and select items to feature in the cart drawer.</p>
                                </div>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-gray-200 text-gray-800" id="selected-fixed-count">
                                    {{ count($fixedProductIds) }} selected
                                </span>
                            </div>

                            <!-- Search Filter -->
                            <div class="mb-3">
                                <input type="text" id="product-search-input" placeholder="Search products by title..." class="block w-full rounded-xl border border-gray-300 py-2 px-3 text-xs focus:ring-black focus:border-black" oninput="filterProductsList()">
                            </div>

                            <!-- Scrollable Product List -->
                            <div class="max-h-60 overflow-y-auto space-y-1.5 pr-1 border border-gray-200 rounded-xl p-2 bg-white" id="products-selection-list">
                                @forelse($products as $product)
                                    @php $isChecked = in_array($product->id, $fixedProductIds); @endphp
                                    <label class="product-item-row flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 cursor-pointer border {{ $isChecked ? 'border-black/20 bg-gray-50/80' : 'border-transparent' }}">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <input type="checkbox" name="fixed_products[]" value="{{ $product->id }}" class="fixed-prod-checkbox h-4 w-4 text-black focus:ring-black border-gray-300 rounded" {{ $isChecked ? 'checked' : '' }} onchange="updateFixedCount()">
                                            @if($product->preview_image)
                                                <img src="{{ $product->image_url }}" alt="" class="w-8 h-8 rounded object-cover border border-gray-100 shrink-0">
                                            @else
                                                <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-[10px] text-gray-400 shrink-0">IMG</div>
                                            @endif
                                            <div class="truncate">
                                                <div class="text-xs font-semibold text-gray-900 truncate product-name">{{ $product->name }}</div>
                                                <div class="text-[11px] text-gray-500">₹{{ number_format($product->skus->min('price') ?? 0, 2) }}</div>
                                            </div>
                                        </div>
                                    </label>
                                @empty
                                    <div class="p-4 text-center text-xs text-gray-400">No products found in the catalog.</div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Save Action Button -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl shadow-sm text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Settings
                    </button>
                </div>
            </div>

            <!-- Right Side: Live Visual Mockup Preview -->
            <div class="lg:col-span-4">
                <div class="sticky top-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Drawer Wireframe Preview</span>
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-semibold border border-emerald-100 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Live Sync
                        </span>
                    </div>

                    <!-- Offcanvas Mockup Frame -->
                    <div class="bg-white border-2 border-gray-200 rounded-3xl shadow-xl overflow-hidden flex flex-col h-[650px] relative">
                        <!-- Drawer Top Bar -->
                        <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-white z-10 shrink-0">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                <span class="font-extrabold text-sm text-gray-900 tracking-tight" id="preview-drawer-title">{{ $settings['cart_drawer_title'] ?? 'Your Bag' }}</span>
                                <span class="text-[11px] font-bold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">2 items</span>
                            </div>
                            <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 text-xs font-bold">
                                ✕
                            </div>
                        </div>

                        <!-- Scrollable Body -->
                        <div class="flex-1 overflow-y-auto p-4 space-y-4 text-xs">
                            <!-- Milestone Section Mockup -->
                            <div id="preview-milestone-box" class="p-3.5 bg-gradient-to-br from-emerald-50/60 to-white rounded-xl border border-emerald-200/80 shadow-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-emerald-950 text-[11px] flex items-center gap-1">
                                        <span>🎉</span> Add ₹250 more to get 15% OFF!
                                    </span>
                                </div>
                                <div class="w-full bg-emerald-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-emerald-500 h-1.5 rounded-full w-[70%]"></div>
                                </div>
                                <div class="flex justify-between text-[9px] text-gray-500 font-semibold">
                                    <span class="text-emerald-700">✓ Free Ship</span>
                                    <span class="text-emerald-800 font-bold">🎯 15% OFF</span>
                                    <span>25% OFF</span>
                                </div>
                            </div>

                            <!-- Cart Items Mockup -->
                            <div class="space-y-3">
                                <div class="flex gap-3 p-2.5 rounded-xl border border-gray-100 bg-gray-50/50">
                                    <div class="w-12 h-14 bg-gray-200 rounded-lg shrink-0 overflow-hidden flex items-center justify-center text-gray-400 text-[10px]">Item</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-900 truncate text-[11px]">Premium Oversized Tee</div>
                                        <div class="text-[10px] text-gray-400">Black • L</div>
                                        <div class="flex items-center justify-between mt-1.5">
                                            <div class="flex items-center border border-gray-200 rounded bg-white text-[10px] px-1.5 py-0.5 gap-2">
                                                <span>-</span><span class="font-bold">1</span><span>+</span>
                                            </div>
                                            <span class="font-bold text-gray-900">₹999</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recommended Products Mockup -->
                            <div id="preview-recommendations-box" class="pt-2 border-t border-gray-100 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-900 text-[11px]" id="preview-recom-title">{{ $settings['cart_drawer_recommendations_title'] ?? 'Pairs Well With' }}</span>
                                    <span class="text-[9px] font-bold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded" id="preview-recom-badge">Rule: Milestone Gap</span>
                                </div>

                                <div class="p-2.5 rounded-xl border border-dashed border-gray-200 bg-gray-50 flex items-center justify-between gap-3">
                                    <div class="w-10 h-12 bg-white rounded border border-gray-200 shrink-0 flex items-center justify-center text-[9px] text-gray-400">
                                        IMG
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-900 truncate text-[10px]">Canvas Crossbody Bag</div>
                                        <div class="text-[10px] font-bold text-emerald-600">₹299 <span class="line-through text-gray-300 font-normal">₹599</span></div>
                                    </div>
                                    <button type="button" class="px-2.5 py-1 bg-black text-white rounded text-[10px] font-bold shrink-0 hover:bg-gray-800">
                                        + Add
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Sticky Footer Mockup -->
                        <div class="p-4 border-t border-gray-100 bg-white space-y-3 shrink-0">
                            <div class="border border-gray-100 rounded-xl bg-gray-50/80 p-2.5 space-y-1.5">
                                <div class="flex items-center justify-between text-xs cursor-pointer">
                                    <div class="flex items-center gap-1">
                                        <span class="font-bold text-gray-800">Estimate</span>
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                    <span class="font-black text-sm text-gray-900">₹999.00</span>
                                </div>
                            </div>

                            <button type="button" class="w-full py-3 bg-black text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md flex items-center justify-center gap-1.5">
                                <span>Proceed to Checkout</span>
                                <span>→</span>
                            </button>

                            <div class="text-center">
                                <span class="text-[10px] text-gray-500 underline font-medium cursor-pointer">View full bag details</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function selectRule(rule) {
    document.querySelectorAll('input[name="cart_drawer_recommendation_rule"]').forEach(el => {
        el.checked = (el.value === rule);
    });

    // Style active card
    document.querySelectorAll('label[onclick^="selectRule"]').forEach(card => {
        const isCurrent = card.getAttribute('onclick').includes("'" + rule + "'");
        if (isCurrent) {
            card.classList.add('border-black', 'bg-gray-50/70', 'ring-1', 'ring-black');
            card.classList.remove('border-gray-200', 'bg-white');
        } else {
            card.classList.remove('border-black', 'bg-gray-50/70', 'ring-1', 'ring-black');
            card.classList.add('border-gray-200', 'bg-white');
        }
    });

    // Toggle conditional parameters
    const catBox = document.getElementById('rule-category-container');
    const colBox = document.getElementById('rule-collection-container');
    const fixBox = document.getElementById('rule-fixed-container');

    if (catBox) catBox.classList.toggle('hidden', rule !== 'category');
    if (colBox) colBox.classList.toggle('hidden', rule !== 'collection');
    if (fixBox) fixBox.classList.toggle('hidden', rule !== 'fixed');

    // Update preview badge
    const badge = document.getElementById('preview-recom-badge');
    if (badge) {
        const labels = {
            'top_sold': 'Rule: Top Sold',
            'near_milestone': 'Rule: Milestone Gap',
            'category': 'Rule: Category',
            'collection': 'Rule: Collection',
            'fixed': 'Rule: Fixed Products'
        };
        badge.innerText = labels[rule] || rule;
    }
}

function toggleRecommendations() {
    const isChecked = document.getElementById('cart_drawer_show_recommendations').checked;
    const body = document.getElementById('recommendations-settings-body');
    const previewBox = document.getElementById('preview-recommendations-box');

    if (body) {
        if (isChecked) {
            body.classList.remove('opacity-40', 'pointer-events-none');
        } else {
            body.classList.add('opacity-40', 'pointer-events-none');
        }
    }
    if (previewBox) {
        previewBox.style.display = isChecked ? 'block' : 'none';
    }
}

function updatePreview() {
    const title = document.getElementById('cart_drawer_title')?.value || 'Your Bag';
    const previewTitle = document.getElementById('preview-drawer-title');
    if (previewTitle) previewTitle.innerText = title;

    const recomTitle = document.getElementById('cart_drawer_recommendations_title')?.value || 'Pairs Well With';
    const previewRecomTitle = document.getElementById('preview-recom-title');
    if (previewRecomTitle) previewRecomTitle.innerText = recomTitle;

    const showMilestones = document.querySelector('input[name="cart_drawer_show_milestones"]')?.checked;
    const milestoneBox = document.getElementById('preview-milestone-box');
    if (milestoneBox) {
        milestoneBox.style.display = showMilestones ? 'block' : 'none';
    }
}

function updateFixedCount() {
    const count = document.querySelectorAll('.fixed-prod-checkbox:checked').length;
    const badge = document.getElementById('selected-fixed-count');
    if (badge) badge.innerText = count + ' selected';
}

function filterProductsList() {
    const search = document.getElementById('product-search-input')?.value.toLowerCase() || '';
    document.querySelectorAll('.product-item-row').forEach(row => {
        const text = row.querySelector('.product-name')?.innerText.toLowerCase() || '';
        row.style.display = text.includes(search) ? 'flex' : 'none';
    });
}
</script>
@endsection

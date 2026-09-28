@extends('layouts.admin')

@section('header', 'Create Product')

@section('content')
    <div class="pb-24">
        <form id="create-product-form" action="{{ route('admin.products.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="redirect_tab" id="redirect-tab" value="info">

            <!-- Sticky Header for Tabs & Actions -->
            <div class="sticky top-0 z-30 bg-gray-50/80 backdrop-blur-md border-b border-gray-200 -mx-4 px-4 py-2 mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <nav class="flex space-x-6" aria-label="Tabs">
                    <button type="button" class="tab-button border-b-2 border-black text-black py-3 px-1 text-sm font-bold transition-all" data-tab="info">Product Info</button>
                    <button type="button" class="tab-button border-b-2 border-transparent text-gray-400 hover:text-black py-3 px-1 text-sm font-bold transition-all" data-tab="seo">SEO and AEO</button>
                    <button type="button" class="tab-button border-b-2 border-transparent text-gray-400 hover:text-black py-3 px-1 text-sm font-bold transition-all" data-tab="media">Media Gallery</button>
                    <button type="button" class="tab-button border-b-2 border-transparent text-gray-400 hover:text-black py-3 px-1 text-sm font-bold transition-all" data-tab="skus">SKUs & Variants</button>
                    <button type="button" class="tab-button border-b-2 border-transparent text-gray-400 hover:text-black py-3 px-1 text-sm font-bold transition-all" data-tab="organization">Organization</button>
                </nav>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-gray-500 hover:text-black">Cancel</a>
                    <button type="submit" form="create-product-form" class="bg-black text-white px-6 py-2 rounded-lg text-xs font-bold hover:bg-gray-800 transition-all shadow-lg active:scale-95">Create Product</button>
                </div>
            </div>

            <!-- Tab Content: Product Info -->
            <div id="tab-info" class="tab-content flex flex-col gap-6">
                <!-- Basic Info -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Product Name</label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Slug</label>
                            <input type="text" name="slug" value="{{ old('slug') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Brand Name</label>
                                <input type="text" name="brand_name" value="{{ old('brand_name') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Size Chart</label>
                                <select name="size_chart_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                    <option value="">No Size Chart</option>
                                    @foreach($sizeCharts as $chart)
                                        <option value="{{ $chart->id }}" {{ old('size_chart_id') == $chart->id ? 'selected' : '' }}>
                                            {{ $chart->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fit</label>
                                <select name="fit_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                    <option value="">No Fit</option>
                                    @foreach($fits as $fit)
                                        <option value="{{ $fit->id }}" {{ old('fit_id') == $fit->id ? 'selected' : '' }}>
                                            {{ $fit->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Fabric</label>
                                <select name="fabric_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                    <option value="">No Fabric</option>
                                    @foreach($fabrics as $fabric)
                                        <option value="{{ $fabric->id }}" {{ old('fabric_id') == $fabric->id ? 'selected' : '' }}>
                                            {{ $fabric->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Short Description</label>
                            <div id="short-description-editor"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2 bg-white">
                                {!! old('short_description') !!}
                            </div>
                            <textarea name="short_description"
                                class="hidden">{{ old('short_description') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Long Description (HTML Support)</label>
                            <div id="long-description-editor"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2 bg-white">
                                {!! old('long_description') !!}
                            </div>
                            <textarea name="long_description"
                                class="hidden">{{ old('long_description') }}</textarea>
                        </div>
                        </div>
                    </div>

                    <!-- Publishing, SEO Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Publishing -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Publishing</h3>

            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-700">Active Status</span>
                <label class="switch">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                    <span class="slider round"></span>
                </label>
            </div>

            <div class="mb-4">
                <div class="flex items-center justify-between">
                    <span class="text-gray-700">Returnable</span>
                    <input type="checkbox" name="is_returnable" id="is_returnable" value="1" {{ old('is_returnable') ? 'checked' : '' }}
                        class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded" onchange="toggleDays('return_days_container', this.checked)">
                </div>
                <div id="return_days_container" class="{{ old('is_returnable') ? '' : 'hidden' }} mt-2 ml-4">
                    <label class="text-xs text-gray-500 block mb-1">Return window (days)</label>
                    <input type="number" name="return_days" value="{{ old('return_days', 7) }}" class="w-24 border border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm p-1">
                </div>
            </div>

            <div class="mb-4">
                <div class="flex items-center justify-between">
                    <span class="text-gray-700">Exchangeable</span>
                    <input type="checkbox" name="is_exchangeable" id="is_exchangeable" value="1" {{ old('is_exchangeable') ? 'checked' : '' }}
                        class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded" onchange="toggleDays('exchange_days_container', this.checked)">
                </div>
                <div id="exchange_days_container" class="{{ old('is_exchangeable') ? '' : 'hidden' }} mt-2 ml-4">
                    <label class="text-xs text-gray-500 block mb-1">Exchange window (days)</label>
                    <input type="number" name="exchange_days" value="{{ old('exchange_days', 7) }}" class="w-24 border border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm p-1">
                </div>
            </div>

            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-700">On Sale</span>
                <input type="checkbox" name="on_sale" value="1" {{ old('on_sale') ? 'checked' : '' }}
                    class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
            </div>

            <script>
                function toggleDays(containerId, isChecked) {
                    const container = document.getElementById(containerId);
                    if (container) {
                        if (isChecked) {
                            container.classList.remove('hidden');
                        } else {
                            container.classList.add('hidden');
                        }
                    }
                }
            </script>

            @if(\App\Models\ThemeSetting::where('group', 'integration.qikink')->where('key', 'enabled')->value('value') == '1')
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <div>
                    <span class="text-gray-700 block font-bold">QikInk Fulfillment</span>
                    <span class="text-[10px] text-gray-400 block">Process orders via Qikink API</span>
                </div>
                <label class="switch">
                    <input type="checkbox" name="use_qikink" value="1" {{ old('use_qikink') ? 'checked' : '' }}>
                    <span class="slider round"></span>
                </label>
            </div>
            @endif
        </div>
    </div>
</div>
    <!-- End Tab Content: Product Info -->

    <!-- Tab Content: SEO and AEO -->
    <div id="tab-seo" class="tab-content hidden flex flex-col gap-6">
        <!-- SEO -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">SEO & AEO</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-sm font-medium text-gray-700">SEO Title</label>
                        <span id="seo-title-count" class="text-xs text-gray-500">0 / 60</span>
                    </div>
                    <input type="text" name="seo_title" id="seo-title-input"
                        value="{{ old('seo_title') }}" maxlength="60"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                    <p class="text-xs text-gray-500 mt-1">Recommended: 50-60 characters</p>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-sm font-medium text-gray-700">SEO Description</label>
                        <span id="seo-description-count" class="text-xs text-gray-500">0 / 160</span>
                    </div>
                    <textarea name="seo_description" id="seo-description-input" rows="3" maxlength="160"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">{{ old('seo_description', '') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Recommended: 150-160 characters</p>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-sm font-medium text-gray-700">SEO Keywords</label>
                        <span id="seo-keywords-count" class="text-xs text-gray-500">0 keywords</span>
                    </div>
                    <textarea name="seo_keywords" id="seo-keywords-input" rows="2"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">{{ old('seo_keywords') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Separate keywords with commas. Recommended: 5-10 keywords</p>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-sm font-medium text-gray-700">AEO Use Case <span class="text-gray-400 font-normal">(Add natural phrases shoppers ask AI: e.g. "best outfits for the gym", "comfortable streetwear")</span></label>
                    </div>
                    <input type="text" name="use_case" id="use_case"
                        value="{{ old('use_case') }}" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2"
                        placeholder="e.g. gym workouts, oversized streetwear">
                    <p class="text-xs text-gray-500 mt-1">Directly feeds into bots (ChatGPT, Claude) to categorize your product.</p>
                </div>
            </div>
        </div>
        
        @include('admin.partials.faqs-editor')
    </div>
    <!-- End Tab Content: Product Info -->

    <!-- Tab Content: Organization -->
    <div id="tab-organization" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Organization</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Product Type</label>
                    <select name="product_type_id"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                        <option value="">None</option>
                        @foreach($productTypes as $type)
                            <option value="{{ $type->id }}" {{ old('product_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }} (HSN: {{ $type->hsn_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Delivery Timeline</label>
                    <select name="delivery_timeline_id"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                        <option value="">None</option>
                        @foreach($deliveryTimelines as $timeline)
                            <option value="{{ $timeline->id }}" {{ old('delivery_timeline_id', $timeline->is_default ? $timeline->id : null) == $timeline->id ? 'selected' : '' }}>
                                {{ $timeline->min_days }} to {{ $timeline->max_days }} Days
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tax Class</label>
                    <select name="tax_class" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                        <option value="">None (No Tax)</option>
                        @foreach($taxes as $tax)
                            <option value="{{ $tax['id'] }}" {{ old('tax_class') == $tax['id'] ? 'selected' : '' }}>
                                {{ $tax['name'] }} ({{ $tax['rate'] }}%)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Categories</label>
                    <div class="max-h-48 overflow-y-auto border border-gray-200 rounded p-2 space-y-2">
                        @foreach($categories as $category)
                            <div class="category-group">
                                <label class="block items-center">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                        data-id="{{ $category->id }}"
                                        class="cat-checkbox rounded border-gray-300 text-black shadow-sm focus:border-black focus:ring-black">
                                    <span class="ml-2 text-sm text-gray-700 font-bold uppercase tracking-wide">{{ $category->name }}</span>
                                </label>
                                @if($category->children->isNotEmpty())
                                    <div class="ml-6 mt-1 space-y-2 border-l-2 border-gray-100 pl-3">
                                        @foreach($category->children as $child)
                                            <div class="category-group font-medium">
                                                <label class="block items-center">
                                                    <input type="checkbox" name="categories[]" value="{{ $child->id }}"
                                                        data-id="{{ $child->id }}"
                                                        data-parent-id="{{ $category->id }}"
                                                        class="cat-checkbox rounded border-gray-300 text-black shadow-sm focus:border-black focus:ring-black">
                                                    <span class="ml-2 text-sm text-gray-800">{{ $child->name }}</span>
                                                </label>
                                                
                                                @if($child->children && $child->children->isNotEmpty())
                                                    <div class="ml-6 mt-1 space-y-1 border-l-2 border-gray-100 pl-3">
                                                        @foreach($child->children as $subchild)
                                                            <label class="block items-center">
                                                                <input type="checkbox" name="categories[]" value="{{ $subchild->id }}"
                                                                    data-id="{{ $subchild->id }}"
                                                                    data-parent-id="{{ $child->id }}"
                                                                    class="cat-checkbox rounded border-gray-300 text-black shadow-sm focus:border-black focus:ring-black">
                                                                <span class="ml-2 text-sm text-gray-500">{{ $subchild->name }}</span>
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Collections</label>
                    <div class="max-h-48 overflow-y-auto border border-gray-200 rounded p-2 space-y-1">
                        @foreach($collections as $collection)
                            <label class="block items-center">
                                <input type="checkbox" name="collections[]" value="{{ $collection->id }}"
                                    class="rounded border-gray-300 text-black shadow-sm focus:border-black focus:ring-black">
                                <span class="ml-2 text-sm text-gray-700">{{ $collection->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Content: SKUs & Variants -->
    <div id="tab-skus" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Variants & Pricing (SKUs)</h3>
                <div class="flex gap-2">
                    <button type="button" onclick="copyToAll('sku-price')" class="text-xs bg-gray-100 px-3 py-1 rounded hover:bg-gray-200 transition-colors">Sync Price</button>
                    <button type="button" onclick="copyToAll('sku-mrp')" class="text-xs bg-gray-100 px-3 py-1 rounded hover:bg-gray-200 transition-colors">Sync MRP</button>
                    <button type="button" onclick="copyToAll('sku-purchase')" class="text-xs bg-gray-100 px-3 py-1 rounded hover:bg-gray-200 transition-colors text-blue-600 font-medium">Sync Purchase</button>
                    <button type="button" onclick="copyToAll('sku-stock')" class="text-xs bg-gray-100 px-3 py-1 rounded hover:bg-gray-200 transition-colors">Sync Stock</button>
                </div>
            </div>

            <div class="mt-6 border-t pt-4">
                <h4 class="text-sm font-medium text-gray-900 mb-4">Add Variants</h4>
                
                <div id="new-variants-container" class="space-y-4"></div>
                
                <button type="button" onclick="addNewVariantRow()" class="mt-4 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Add Row
                </button>
            </div>
        </div>
    </div>
    <!-- End Tab Content: SKUs & Variants -->

    <!-- Template for new variant row -->
    <template id="new-variant-template">
        <div class="grid grid-cols-12 gap-2 mb-2 px-3 new-variant-row items-center bg-gray-50/50 p-3 rounded-xl border border-gray-100 hover:border-violet-100 transition-all">
            <div class="col-span-2">
                <input type="text" name="new_skus[INDEX][code]" placeholder="SKU CODE" class="w-full border-gray-200 rounded-lg text-xs p-2 font-bold uppercase">
            </div>
            <div class="col-span-2">
                <select name="new_skus[INDEX][color_id]" class="w-full border-gray-200 rounded-lg text-xs p-2 font-bold">
                    <option value="">COLOR</option>
                    @foreach($colors as $color)
                        <option value="{{ $color->id }}">{{ $color->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <input type="text" name="new_skus[INDEX][size]" placeholder="SIZE" class="w-full border-gray-200 rounded-lg text-xs p-2 font-bold uppercase">
            </div>
            <div class="col-span-1">
                <input type="text" name="new_skus[INDEX][design_sku]" placeholder="DESIGN SKU" class="w-full border-gray-200 rounded-lg text-xs p-2 font-mono">
            </div>
            <div class="col-span-1">
                <input type="text" name="new_skus[INDEX][product_sku]" placeholder="PRODUCT SKU" class="w-full border-gray-200 rounded-lg text-xs p-2 font-mono">
            </div>
            <div class="col-span-1">
                <input type="number" step="0.01" name="new_skus[INDEX][price]" placeholder="SP" class="sku-price w-full border-gray-200 rounded-lg text-[10px] p-2 font-bold">
            </div>
            <div class="col-span-1">
                <input type="number" step="0.01" name="new_skus[INDEX][mrp]" placeholder="MRP" class="sku-mrp w-full border-gray-200 rounded-lg text-[10px] p-2 text-gray-500">
            </div>
            <div class="col-span-1">
                <input type="number" step="0.01" name="new_skus[INDEX][purchase_price]" placeholder="Purch" class="sku-purchase w-full border-gray-200 rounded-lg text-[10px] p-2 text-blue-500">
            </div>
            <div class="col-span-1">
                <input type="number" name="new_skus[INDEX][stock]" placeholder="QTY" class="sku-stock w-full border-gray-200 rounded-lg text-xs p-2 font-bold">
            </div>
            <div class="col-span-1 text-right">
                <button type="button" onclick="this.closest('.new-variant-row').remove()" class="p-2 text-red-300 hover:text-red-500 rounded-lg transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
    </template>

    <script>
        let newVariantIndex = 0;
        function copyToAll(className) {
            const first = document.querySelector(`.${className}`);
            if (first) document.querySelectorAll(`.${className}`).forEach(i => i.value = first.value);
        }

        function syncDimensions() {
            copyToAll('sku-width');
            copyToAll('sku-height');
            copyToAll('sku-length');
            copyToAll('sku-weight');
        }

        function addNewVariantRow() {
            const container = document.getElementById('new-variants-container');
            const template = document.getElementById('new-variant-template');
            let html = template.innerHTML.replace(/INDEX/g, newVariantIndex++);
            const div = document.createElement('div');
            div.innerHTML = html;
            container.appendChild(div.firstElementChild);
        }
    </script>

    <!-- Tab Content: Media Gallery -->
    <div id="tab-media" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Master Image</h3>
            
            <div class="flex flex-col md:flex-row gap-8 items-start">
                <!-- Upload Column -->
                <div class="w-full">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Image</label>
                    
                    <div class="drag-drop-zone border-2 border-dashed border-gray-300 rounded-lg p-8 text-center transition-colors hover:border-black hover:bg-gray-50 cursor-pointer h-[200px] flex flex-col justify-center items-center" 
                         id="master-image-dropzone"
                         onclick="document.getElementById('master-image-input').click()">
                        
                        <input type="file" name="preview_image" accept="image/*" class="hidden" id="master-image-input">
                        
                        <div id="master-image-placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-gray-900 font-medium text-lg">Drop your image here</p>
                            <p class="text-gray-500 text-sm mt-1">or click to browse</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Recommended: 800x900px. Used as main thumbnail.</p>
                    @error('preview_image')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm">Media gallery will be available after creating the product and adding SKUs with colors.</p>
        </div>
        
    </div>
    <!-- End Tab Content: Media Gallery -->

    <!-- Compact Save Area (Mobile Floating) -->
    <div class="md:hidden fixed bottom-6 right-6 z-50">
        <button type="submit" form="create-product-form" class="bg-black text-white p-4 rounded-full shadow-2xl active:scale-95 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
        </button>
    </div>
    </form>
    </div>

@endsection



@push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .ql-editor {
            min-height: 150px;
            background-color: white;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab Switching
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            function activateTab(tabName) {
                const redirectInput = document.getElementById('redirect-tab');
                if (redirectInput) redirectInput.value = tabName;

                tabButtons.forEach(btn => {
                    btn.classList.remove('border-black', 'text-black');
                    btn.classList.add('border-transparent', 'text-gray-500');
                    if (btn.dataset.tab === tabName) {
                        btn.classList.remove('border-transparent', 'text-gray-500');
                        btn.classList.add('border-black', 'text-black');
                    }
                });

                tabContents.forEach(content => {
                    if (content.id === `tab-${tabName}`) {
                        content.classList.remove('hidden');
                    } else {
                        content.classList.add('hidden');
                    }
                });
            }

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetTab = button.dataset.tab;
                    activateTab(targetTab);
                    history.pushState(null, null, `#${targetTab}`);
                });
            });

            const hash = window.location.hash.substring(1);
            if (hash && document.querySelector(`.tab-button[data-tab="${hash}"]`)) {
                activateTab(hash);
            } else {
                activateTab('info');
            }

            // Slug formatting and auto-generation
            const nameInput = document.querySelector('input[name="name"]');
            const slugInput = document.querySelector('input[name="slug"]');
            let isSlugEdited = false;

            if (slugInput) {
                slugInput.addEventListener('input', function (e) {
                    isSlugEdited = true;
                    let value = e.target.value;
                    value = value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
                    e.target.value = value;
                });
            }

            if (nameInput && slugInput) {
                nameInput.addEventListener('input', function (e) {
                    if (!isSlugEdited) {
                        let value = e.target.value;
                        value = value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
                        slugInput.value = value;
                    }
                });
            }

            // Quill Editors
            var toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'header': 1 }, { 'header': 2 }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'script': 'sub' }, { 'script': 'super' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'font': [] }],
                [{ 'align': [] }],
                ['clean']
            ];

            var quillShort = new Quill('#short-description-editor', {
                theme: 'snow',
                placeholder: 'Brief summary of the product...',
                modules: { toolbar: toolbarOptions }
            });

            var quillLong = new Quill('#long-description-editor', {
                theme: 'snow',
                placeholder: 'Detailed product description...',
                modules: { toolbar: toolbarOptions }
            });

            // Sync content on form submit
            var form = document.querySelector('#create-product-form');
            form.onsubmit = function () {
                var shortDescInput = document.querySelector('textarea[name="short_description"]');
                var longDescInput = document.querySelector('textarea[name="long_description"]');

                shortDescInput.value = quillShort.root.innerHTML;
                longDescInput.value = quillLong.root.innerHTML;
            };

            // Category Auto-check Parent Logic
            document.querySelectorAll('.cat-checkbox').forEach(chk => {
                chk.addEventListener('change', function() {
                    if (this.checked) {
                        let parentId = this.dataset.parentId;
                        if (parentId) {
                            let parentBox = document.querySelector(`.cat-checkbox[data-id="${parentId}"]`);
                            if (parentBox && !parentBox.checked) {
                                parentBox.checked = true;
                                parentBox.dispatchEvent(new Event('change')); // Trigger event up the chain
                            }
                        }
                    } else {
                        // Uncheck all children if this is unchecked
                        let myId = this.dataset.id;
                        document.querySelectorAll(`.cat-checkbox[data-parent-id="${myId}"]`).forEach(childBox => {
                            if (childBox.checked) {
                                childBox.checked = false;
                                childBox.dispatchEvent(new Event('change')); // Trigger down the chain
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
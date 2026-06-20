@extends('layouts.admin')

@section('header', 'Edit Product')

@section('content')
    <div class="pb-24">
        <form id="edit-product-form" action="{{ route('admin.products.update', $product) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="redirect_tab" id="redirect-tab" value="info">

            <!-- Sticky Header for Tabs & Actions -->
            <div class="sticky top-0 z-30 bg-gray-50/80 backdrop-blur-md border-b border-gray-200 -mx-4 px-4 py-2 mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <nav class="flex space-x-6" aria-label="Tabs">
                    <button type="button" class="tab-button border-b-2 border-black text-black py-3 px-1 text-sm font-bold transition-all" data-tab="info">Product Info</button>
                    <button type="button" class="tab-button border-b-2 border-transparent text-gray-400 hover:text-black py-3 px-1 text-sm font-bold transition-all" data-tab="organization">Organization</button>
                    <button type="button" class="tab-button border-b-2 border-transparent text-gray-400 hover:text-black py-3 px-1 text-sm font-bold transition-all" data-tab="skus">SKUs & Variants</button>
                    <button type="button" class="tab-button border-b-2 border-transparent text-gray-400 hover:text-black py-3 px-1 text-sm font-bold transition-all" data-tab="media">Media Gallery</button>
                    <button type="button" class="tab-button border-b-2 border-transparent text-gray-400 hover:text-black py-3 px-1 text-sm font-bold transition-all" data-tab="shortlinks">Share / Shortlinks</button>
                </nav>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-gray-500 hover:text-black">Cancel</a>
                    <button type="submit" form="edit-product-form" class="bg-black text-white px-6 py-2 rounded-lg text-xs font-bold hover:bg-gray-800 transition-all shadow-lg active:scale-95">Update Product</button>
                </div>
            </div>

            <!-- Tab Content: Product Info -->
            <div id="tab-info" class="tab-content space-y-6">
                <!-- Basic Info -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Product Name</label>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Slug</label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Brand Name</label>
                                <input type="text" name="brand_name" value="{{ old('brand_name', $product->brand_name) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Size Chart</label>
                                <select name="size_chart_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                    <option value="">No Size Chart</option>
                                    @foreach($sizeCharts as $chart)
                                        <option value="{{ $chart->id }}" {{ old('size_chart_id', $product->sizeChart->first()->id ?? '') == $chart->id ? 'selected' : '' }}>
                                            {{ $chart->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Short Description</label>
                            <div id="short-description-editor"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2 bg-white min-h-[100px]">
                                {!! old('short_description', $product->short_description) !!}
                            </div>
                            <textarea name="short_description"
                                class="hidden">{!! old('short_description', $product->short_description) !!}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Long Description (HTML Support)</label>
                            <div id="long-description-editor"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2 bg-white min-h-[200px]">
                                {!! old('long_description', $product->long_description) !!}
                            </div>
                            <textarea name="long_description"
                                class="hidden">{!! old('long_description', $product->long_description) !!}</textarea>
                        </div>
                    </div>
                </div>

                <!-- SEO -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">SEO</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-sm font-medium text-gray-700">SEO Title</label>
                                <span id="seo-title-count" class="text-xs text-gray-500">0 / 60</span>
                            </div>
                            <input type="text" name="seo_title" id="seo-title-input"
                                value="{{ old('seo_title', $product->seo_title) }}" maxlength="60"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-sm font-medium text-gray-700">SEO Description</label>
                                <span id="seo-description-count" class="text-xs text-gray-500">0 / 160</span>
                            </div>
                            <textarea name="seo_description" id="seo-description-input" rows="3" maxlength="160"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">{{ old('seo_description', $product->seo_description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">SEO Keywords</label>
                            <textarea name="seo_keywords" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">{{ old('seo_keywords', $product->seo_keywords) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Organization -->
            <div id="tab-organization" class="tab-content hidden space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Publishing -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Publishing</h3>

                        <div class="flex items-center justify-between mb-4">
                            <span class="text-gray-700">Active Status</span>
                            <label class="switch">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>

                        <div class="flex items-center justify-between mb-4">
                            <span class="text-gray-700">Returnable</span>
                            <input type="checkbox" name="is_returnable" value="1" {{ old('is_returnable', $product->is_returnable) ? 'checked' : '' }}
                                class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
                        </div>

                        <div class="flex items-center justify-between mb-4">
                            <span class="text-gray-700">On Sale</span>
                            <input type="checkbox" name="on_sale" value="1" {{ old('on_sale', $product->on_sale) ? 'checked' : '' }}
                                class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <div>
                                <span class="text-gray-700 block font-bold">QikInk Fulfillment</span>
                                <span class="text-[10px] text-gray-400 block">Process orders via Qikink API</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="use_qikink" value="1" {{ old('use_qikink', $product->use_qikink) ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Organization</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Product Type</label>
                                <select name="product_type_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                    <option value="">None</option>
                                    @foreach($productTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('product_type_id', $product->product_type_id) == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }} (HSN: {{ $type->hsn_code }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tax Class</label>
                                <select name="tax_class" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                    <option value="">None (No Tax)</option>
                                    @foreach($taxes as $tax)
                                        <option value="{{ $tax['id'] }}" {{ old('tax_class', $product->tax_class) == $tax['id'] ? 'selected' : '' }}>
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
                                                    {{ $product->categories->contains($category->id) ? 'checked' : '' }}
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
                                                                    {{ $product->categories->contains($child->id) ? 'checked' : '' }}
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
                                                                                {{ $product->categories->contains($subchild->id) ? 'checked' : '' }}
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
                                                {{ $product->collections->contains($collection->id) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-black shadow-sm focus:border-black focus:ring-black">
                                            <span class="ml-2 text-sm text-gray-700">{{ $collection->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
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
                            <button type="button" onclick="copyToAll('sku-price')" class="text-xs bg-gray-100 px-3 py-1 rounded">Sync Price</button>
                            <button type="button" onclick="copyToAll('sku-mrp')" class="text-xs bg-gray-100 px-3 py-1 rounded">Sync MRP</button>
                            <button type="button" onclick="copyToAll('sku-stock')" class="text-xs bg-gray-100 px-3 py-1 rounded">Sync Stock</button>
                            <button type="button" onclick="syncDimensions()" class="text-xs bg-gray-100 px-3 py-1 rounded">Sync Dims</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU Code</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Design SKU</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product SKU</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price/MRP</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dims (W/H/L/Kg)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($product->skus as $sku)
                                    <tr>
                                        <td class="px-3 py-4">
                                            <input type="text" name="skus[{{ $sku->id }}][code]" value="{{ $sku->code }}" class="block w-full border-gray-300 rounded-md sm:text-xs">
                                        </td>
                                        <td class="px-3 py-4 text-xs">
                                            <div class="flex items-center gap-2 font-medium">
                                                @if(isset($sku->color->hex_code))
                                                    <div class="w-3.5 h-3.5 rounded-full border border-gray-100 shadow-sm shrink-0" style="background-color: {{ $sku->color->hex_code }}"></div>
                                                @endif
                                                {{ $sku->color->name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-3 py-4 text-xs">
                                            {{ $sku->size->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-3 py-4">
                                            <input type="text" name="skus[{{ $sku->id }}][design_sku]" value="{{ $sku->design_sku }}" class="block w-full border-gray-300 rounded-md sm:text-xs font-mono" placeholder="Design SKU">
                                        </td>
                                        <td class="px-3 py-4">
                                            <input type="text" name="skus[{{ $sku->id }}][product_sku]" value="{{ $sku->product_sku }}" class="block w-full border-gray-300 rounded-md sm:text-xs font-mono" placeholder="Product SKU">
                                        </td>
                                        <td class="px-3 py-4">
                                            <div class="flex flex-col gap-1">
                                                <input type="number" step="0.01" name="skus[{{ $sku->id }}][price]" value="{{ $sku->price }}" class="sku-price block w-full border-gray-300 rounded-md sm:text-xs" placeholder="Price">
                                                <input type="number" step="0.01" name="skus[{{ $sku->id }}][mrp]" value="{{ $sku->mrp }}" class="sku-mrp block w-full border-gray-300 rounded-md sm:text-[10px] text-gray-500" placeholder="MRP">
                                            </div>
                                        </td>
                                        <td class="px-3 py-4">
                                            <input type="number" name="skus[{{ $sku->id }}][stock]" value="{{ $sku->stock }}" class="sku-stock block w-full border-gray-300 rounded-md sm:text-xs">
                                        </td>
                                        <td class="px-3 py-4">
                                            <div class="grid grid-cols-2 gap-1 max-w-[120px]">
                                                <input type="number" step="0.01" name="skus[{{ $sku->id }}][width]" value="{{ $sku->width }}" class="sku-width block w-full border-gray-300 rounded-md sm:text-[10px]" placeholder="W">
                                                <input type="number" step="0.01" name="skus[{{ $sku->id }}][height]" value="{{ $sku->height }}" class="sku-height block w-full border-gray-300 rounded-md sm:text-[10px]" placeholder="H">
                                                <input type="number" step="0.01" name="skus[{{ $sku->id }}][length]" value="{{ $sku->length }}" class="sku-length block w-full border-gray-300 rounded-md sm:text-[10px]" placeholder="L">
                                                <input type="number" step="0.01" name="skus[{{ $sku->id }}][weight]" value="{{ $sku->weight }}" class="sku-weight block w-full border-gray-300 rounded-md sm:text-[10px]" placeholder="Kg">
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8 border-t pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-4">Add New Variants</h4>
                        <div id="new-variants-container" class="space-y-4"></div>
                        <button type="button" onclick="addNewVariantRow()" class="mt-4 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Add Row
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Media Gallery -->
            <div id="tab-media" class="tab-content hidden space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Master Preview Image</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                        <div class="relative group aspect-square max-w-[200px] rounded-2xl border border-gray-100 overflow-hidden shadow-sm bg-gray-50 mx-auto md:mx-0" id="master-preview-container">
                            @if($product->preview_image)
                                <img src="{{ $product->image_url }}" id="master-preview-img" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                                <button type="button" onclick="document.getElementById('master-file-input').click()" class="bg-white text-black px-4 py-2 rounded-lg text-xs font-bold shadow-xl">Change Image</button>
                            </div>
                        </div>

                        <div class="dropzone-area border-2 border-dashed border-gray-100 rounded-[2rem] p-10 text-center hover:border-black hover:bg-gray-50 transition-all cursor-pointer group"
                             onclick="document.getElementById('master-file-input').click()"
                             ondragover="handleDragOver(event)" 
                             ondragleave="handleDragLeave(event)" 
                             ondrop="handleMasterDrop(event)">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 bg-white rounded-2xl shadow-sm border border-gray-50 flex items-center justify-center text-gray-400 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                </div>
                                <p class="text-sm font-bold text-gray-900">Master Dropzone</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">Primary Product Thumbnail</p>
                            </div>
                            <input type="file" id="master-file-input" class="hidden" accept="image/*" onchange="if(this.files.length) uploadMasterPreview(this.files[0])">
                        </div>
                    </div>
                </div>

                @if($productParentCategories->count() > 0)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mt-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Category Specific Master Images</h3>
                        <p class="text-xs text-gray-500 mb-6">Optional: Upload a different master image for specific parent categories. If no image is provided, the main master preview image above will be used.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            @foreach($productParentCategories as $parentCat)
                                @php
                                    $catImage = $product->categoryMasterImages->where('category_id', $parentCat->id)->first();
                                @endphp
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                    <h4 class="font-bold text-sm text-gray-900 mb-4 text-center">{{ $parentCat->name }}</h4>
                                    
                                    <div class="flex gap-4 justify-center">
                                        <!-- Image Dropzone -->
                                        <div class="relative group aspect-square rounded-xl border border-dashed border-gray-300 overflow-hidden shadow-sm bg-white dropzone-area cursor-pointer hover:border-black transition-all w-[100px]"
                                             onclick="document.getElementById('cat-master-img-input-{{ $parentCat->id }}').click()"
                                             ondragover="handleDragOver(event)" 
                                             ondragleave="handleDragLeave(event)" 
                                             ondrop="handleCatMasterDrop(event, {{ $parentCat->id }}, 'image')">
                                            @if($catImage && $catImage->image_path)
                                                <img src="{{ $catImage->image_url }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 gap-2 p-2 text-center">
                                                    <span class="text-[9px] font-bold uppercase tracking-wider text-gray-500">Image</span>
                                                </div>
                                            @endif
                                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-all flex flex-col items-center justify-center gap-2">
                                                <span class="bg-white text-black px-3 py-1.5 rounded text-[10px] font-bold shadow-xl">Change</span>
                                                @if($catImage && $catImage->image_path)
                                                    <button type="button" class="bg-red-600 text-white px-3 py-1.5 rounded text-[10px] font-bold shadow-xl hover:bg-red-700 transition-colors" onclick="event.stopPropagation(); deleteCatMasterPreview({{ $parentCat->id }}, 'image')">Delete</button>
                                                @endif
                                            </div>
                                        </div>
                                        <input type="file" id="cat-master-img-input-{{ $parentCat->id }}" class="hidden" accept="image/*" onchange="if(this.files.length) uploadCatMasterPreview(this.files[0], {{ $parentCat->id }}, 'image')">

                                        <!-- Video Dropzone -->
                                        <div class="relative group aspect-square rounded-xl border border-dashed border-gray-300 overflow-hidden shadow-sm bg-white dropzone-area cursor-pointer hover:border-black transition-all w-[100px]"
                                             onclick="document.getElementById('cat-master-vid-input-{{ $parentCat->id }}').click()"
                                             ondragover="handleDragOver(event)" 
                                             ondragleave="handleDragLeave(event)" 
                                             ondrop="handleCatMasterDrop(event, {{ $parentCat->id }}, 'video')">
                                            @if($catImage && $catImage->video_path)
                                                <video src="{{ $catImage->video_url }}" class="w-full h-full object-cover" autoplay loop muted playsinline></video>
                                            @else
                                                <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 gap-2 p-2 text-center">
                                                    <span class="text-[9px] font-bold uppercase tracking-wider text-gray-500">Video</span>
                                                </div>
                                            @endif
                                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-all flex flex-col items-center justify-center gap-2">
                                                <span class="bg-white text-black px-3 py-1.5 rounded text-[10px] font-bold shadow-xl">Change</span>
                                                @if($catImage && $catImage->video_path)
                                                    <button type="button" class="bg-red-600 text-white px-3 py-1.5 rounded text-[10px] font-bold shadow-xl hover:bg-red-700 transition-colors" onclick="event.stopPropagation(); deleteCatMasterPreview({{ $parentCat->id }}, 'video')">Delete</button>
                                                @endif
                                            </div>
                                        </div>
                                        <input type="file" id="cat-master-vid-input-{{ $parentCat->id }}" class="hidden" accept="video/mp4,video/quicktime,video/webm" onchange="if(this.files.length) uploadCatMasterPreview(this.files[0], {{ $parentCat->id }}, 'video')">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @foreach($productColors as $color)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl border border-gray-100 flex items-center justify-center shadow-sm" style="background-color: {{ $color->hex_code }}">
                                    @if(strtolower($color->hex_code) === '#ffffff') <div class="w-full h-full rounded-xl border border-gray-100"></div> @endif
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900">{{ $color->name }} Visuals</h4>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $mediaByColor->get($color->id)?->count() ?? 0 }} Assets Indexed</p>
                                </div>
                            </div>
                        </div>

                        {{-- Visible Dropzone --}}
                        <div class="mb-8 p-10 border-2 border-dashed border-gray-100 rounded-[2rem] text-center hover:border-black hover:bg-gray-50 transition-all cursor-pointer group relative"
                             data-color-id="{{ $color->id }}"
                             onclick="this.nextElementSibling.click()"
                             ondragover="handleDragOver(event)" 
                             ondragleave="handleDragLeave(event)" 
                             ondrop="handleDrop(event)">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 bg-white rounded-2xl shadow-sm border border-gray-50 flex items-center justify-center text-gray-400 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                                </div>
                                <p class="text-sm font-bold text-gray-900">Drop files here or click</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em] italic">Supports JPEG, PNG, MP4</p>
                            </div>
                            <input type="file" multiple class="hidden media-upload-input" data-color-id="{{ $color->id }}" accept="image/*,video/*">
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 media-gallery" id="media-gallery-{{ $color->id }}">
                            @if(isset($mediaByColor[$color->id]))
                                @foreach($mediaByColor[$color->id] as $media)
                                    <div class="relative group aspect-square rounded-2xl border border-gray-100 overflow-hidden shadow-sm bg-gray-50 media-item" data-media-id="{{ $media->id }}">
                                        @if($media->media_type === 'video')
                                            <video src="{{ $media->url }}" class="w-full h-full object-cover"></video>
                                            <div class="absolute top-2 left-2 px-2 py-0.5 bg-black/60 backdrop-blur-md rounded text-[8px] font-black text-white uppercase tracking-widest">Video</div>
                                        @else
                                            <img src="{{ $media->url }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        @endif
                                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-3">
                                            <div class="p-2 bg-white rounded-lg cursor-move drag-handle shadow-lg hover:scale-110 transition-transform">
                                                <svg class="w-4 h-4 text-gray-900" fill="currentColor" viewBox="0 0 24 24"><path d="M11 18c0 1.1-.9 2-2 2s-2-.9-2-2 .9-2 2-2 2 .9 2 2zm-2-8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm6 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0-8c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"></path></svg>
                                            </div>
                                            <button type="button" class="p-2 bg-white text-red-600 rounded-lg delete-media-btn shadow-lg hover:scale-110 transition-transform" data-media-id="{{ $media->id }}" data-product-id="{{ $product->id }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <!-- End Tab Content: Media Gallery -->
        </form>

        <!-- Tab Content: Shortlinks -->
        <div id="tab-shortlinks" class="tab-content hidden mt-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Create New Shortlink -->
                <div class="lg:col-span-4 bg-white rounded-lg shadow p-6 h-fit">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Shortlink</h3>
                    
                    <form action="{{ route('admin.products.shortlinks.store', $product) }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Destination URL <span class="text-red-500">*</span></label>
                                <input type="url" name="actual_link" value="{{ url('/product/' . $product->slug) }}" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                <p class="text-xs text-gray-500 mt-1">Default is the product page.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Custom Short Code (Optional)</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm font-mono">
                                        {{ request()->getHost() }}/s/
                                    </span>
                                    <input type="text" name="short_code" placeholder="e.g. summer-sale"
                                        class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md focus:ring-black focus:border-black sm:text-sm border border-gray-300 font-mono">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Leave blank to auto-generate.</p>
                            </div>

                            <div class="pt-2">
                                <h4 class="text-sm font-medium text-gray-900 mb-3 border-b pb-2">UTM Parameters</h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">utm_source</label>
                                        <input type="text" name="utm_source" placeholder="e.g. instagram"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">utm_medium</label>
                                        <input type="text" name="utm_medium" placeholder="e.g. social"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">utm_campaign</label>
                                        <input type="text" name="utm_campaign" placeholder="e.g. summer_promo"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black sm:text-sm border p-2">
                                    </div>
                                </div>
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="w-full bg-black text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition-all shadow-sm">Generate Shortlink</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Existing Shortlinks -->
                <div class="lg:col-span-8 bg-white rounded-lg shadow p-6 h-fit">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Existing Shortlinks</h3>
                    
                    @if($product->shortlinks && $product->shortlinks->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shortlink</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination / UTMs</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Clicks</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">QR Code</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($product->shortlinks as $link)
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ url('/s/' . $link->short_code) }}" target="_blank" class="text-sm font-medium text-blue-600 hover:underline font-mono">{{ url('/s/' . $link->short_code) }}</a>
                                                    <button onclick="navigator.clipboard.writeText('{{ url('/s/' . $link->short_code) }}'); alert('Copied!')" class="text-gray-400 hover:text-black" title="Copy to clipboard">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                    </button>
                                                </div>
                                                <div class="text-xs text-gray-400 mt-1">Created: {{ $link->created_at->format('M d, Y') }}</div>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="text-sm text-gray-900 truncate max-w-[150px]" title="{{ $link->actual_link }}">{{ $link->actual_link }}</div>
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    @if($link->utm_source) <span class="px-2 py-0.5 bg-gray-100 rounded text-[10px] text-gray-600">src: {{ $link->utm_source }}</span> @endif
                                                    @if($link->utm_medium) <span class="px-2 py-0.5 bg-gray-100 rounded text-[10px] text-gray-600">med: {{ $link->utm_medium }}</span> @endif
                                                    @if($link->utm_campaign) <span class="px-2 py-0.5 bg-gray-100 rounded text-[10px] text-gray-600">cmp: {{ $link->utm_campaign }}</span> @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ number_format($link->click_count) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                <div class="flex flex-col items-center">
                                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode($link->full_actual_link) }}" alt="QR Code" class="w-10 h-10 rounded border border-gray-200">
                                                    <a href="https://api.qrserver.com/v1/create-qr-code/?size=500x500&data={{ urlencode($link->full_actual_link) }}" download="qr_{{ $link->short_code }}.png" target="_blank" class="text-[10px] text-blue-600 hover:underline mt-1">Download</a>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <form action="{{ route('admin.products.shortlinks.destroy', $link) }}" method="POST" onsubmit="return confirm('Delete this shortlink?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500 border border-dashed border-gray-300 rounded-md bg-gray-50">
                            <p class="text-sm font-medium text-gray-900">No shortlinks created yet.</p>
                            <p class="text-sm mt-1">Use the form to generate custom, trackable links.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

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
            <div class="col-span-2 flex flex-col gap-1">
                <input type="number" step="0.01" name="new_skus[INDEX][price]" placeholder="SP" class="sku-price w-full border-gray-200 rounded-lg text-xs p-2 font-bold">
                <input type="number" step="0.01" name="new_skus[INDEX][mrp]" placeholder="MRP" class="sku-mrp w-full border-gray-200 rounded-lg text-[10px] p-2 text-gray-500">
            </div>
            <div class="col-span-2">
                <input type="number" name="new_skus[INDEX][stock]" placeholder="QTY" class="sku-stock w-full border-gray-200 rounded-lg text-xs p-2 font-bold">
            </div>
            <div class="col-span-2 text-right">
                <button type="button" onclick="this.closest('.new-variant-row').remove()" class="p-2 text-red-300 hover:text-red-500 rounded-lg transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
    </template>

    <!-- Compact Save Area (Mobile Floating) -->
    <div class="md:hidden fixed bottom-6 right-6 z-50">
        <button type="submit" form="edit-product-form" class="bg-black text-white p-4 rounded-full shadow-2xl active:scale-95 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
        </button>
    </div>

    <style>
        .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #F3F4F6; transition: .4s; border-radius: 34px; border: 1px solid #E5E7EB; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 2px; bottom: 2px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        input:checked + .slider { background-color: #000; }
        input:checked + .slider:before { transform: translateX(20px); }
    </style>
@endsection

@push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .ql-editor { min-height: 150px; background-color: white; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-format Slug
            const slugInput = document.getElementById('slug');
            if (slugInput) {
                slugInput.addEventListener('input', function(e) {
                    this.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+/, '');
                });
                slugInput.addEventListener('blur', function(e) {
                    this.value = this.value.replace(/-+$/, '');
                });
            }

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
                    content.classList.toggle('hidden', content.id !== `tab-${tabName}`);
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
            activateTab(hash && document.getElementById(`tab-${hash}`) ? hash : 'info');

            // Rich Editors
            const qOptions = { theme: 'snow', modules: { toolbar: [['bold', 'italic'], [{ 'list': 'ordered' }, { 'list': 'bullet' }], ['link', 'clean']] } };
            const qShort = new Quill('#short-description-editor', qOptions);
            const qLong = new Quill('#long-description-editor', qOptions);
            
            const shortText = document.querySelector('textarea[name="short_description"]');
            const longText = document.querySelector('textarea[name="long_description"]');

            document.getElementById('edit-product-form').onsubmit = function () {
                shortText.value = qShort.root.innerHTML;
                longText.value = qLong.root.innerHTML;
            };

            // Batch Upload Helpers
            window.handleDragOver = function(e) {
                e.preventDefault();
                e.currentTarget.classList.add('border-black', 'bg-gray-50');
            };

            window.handleDragLeave = function(e) {
                e.preventDefault();
                e.currentTarget.classList.remove('border-black', 'bg-gray-50');
            };

            window.handleDrop = function(e) {
                e.preventDefault();
                e.currentTarget.classList.remove('border-black', 'bg-gray-50');
                const colorId = e.currentTarget.dataset.colorId;
                if (e.dataTransfer.files.length) uploadFiles(e.dataTransfer.files, colorId);
            };

            window.handleMasterDrop = function(e) {
                e.preventDefault();
                e.currentTarget.classList.remove('border-black', 'bg-gray-50');
                if (e.dataTransfer.files.length) uploadMasterPreview(e.dataTransfer.files[0]);
            };

            window.handleCatMasterDrop = function(e, categoryId, type) {
                e.preventDefault();
                e.currentTarget.classList.remove('border-black', 'bg-gray-50');
                if (e.dataTransfer.files.length) uploadCatMasterPreview(e.dataTransfer.files[0], categoryId, type);
            };

            function createProgressOverlay(container) {
                let overlay = container.querySelector('.progress-overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'progress-overlay absolute inset-0 bg-white/90 backdrop-blur-sm flex flex-col items-center justify-center z-50 transition-opacity';
                    overlay.innerHTML = `
                        <div class="text-[10px] font-bold text-gray-900 mb-2 uppercase tracking-wider progress-text">0%</div>
                        <div class="w-3/4 h-1 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-black transition-all duration-200 w-0 progress-bar"></div>
                        </div>
                    `;
                    container.appendChild(overlay);
                }
                overlay.style.opacity = '1';
                overlay.querySelector('.progress-text').textContent = '0%';
                overlay.querySelector('.progress-bar').style.width = '0%';
                return overlay;
            }

            window.uploadCatMasterPreview = function(file, categoryId, type) {
                // Basic frontend size check (warn if > 50MB)
                if (file.size > 50 * 1024 * 1024) {
                    alert('File is too large. Maximum size is 50MB.');
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);
                formData.append('category_id', categoryId);
                formData.append('type', type);
                
                const inputId = type === 'image' ? 'cat-master-img-input-' : 'cat-master-vid-input-';
                const container = document.getElementById(inputId + categoryId).parentElement;
                const overlay = createProgressOverlay(container);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', "{{ route('admin.products.media.upload-cat-preview', $product->id) }}");
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percent = (e.loaded / e.total) * 100;
                        overlay.querySelector('.progress-text').textContent = Math.round(percent) + '%';
                        overlay.querySelector('.progress-bar').style.width = percent + '%';
                    }
                });

                xhr.onload = function() {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        if (xhr.status === 200 && data.success) {
                            location.reload();
                        } else if (xhr.status === 422) {
                            const errorMsg = data.errors && Object.values(data.errors)[0] ? Object.values(data.errors)[0][0] : data.message;
                            alert('Validation failed: ' + errorMsg);
                            overlay.style.opacity = '0';
                        } else {
                            alert('Upload failed: ' + (data.message || xhr.statusText));
                            overlay.style.opacity = '0';
                        }
                    } catch(err) {
                        console.error('Server response:', xhr.responseText);
                        if (xhr.status === 500) {
                            alert('Server Error 500: Database error or syntax error. Please check your backend terminal, laravel.log, or ensure migrations are run.');
                        } else if (xhr.status === 413) {
                            alert('Payload Too Large: The file exceeds the server PHP upload limit.');
                        } else {
                            alert('Server error (' + xhr.status + '). Check browser console for the response text. It might be an upload size limit or backend crash.');
                        }
                        overlay.style.opacity = '0';
                    }
                };

                xhr.onerror = function() {
                    alert('Network error during upload.');
                    overlay.style.opacity = '0';
                };

                xhr.send(formData);
            };

            window.deleteCatMasterPreview = function(categoryId, type) {
                if (!confirm(`Are you sure you want to delete this ${type}?`)) return;
                
                const inputId = type === 'image' ? 'cat-master-img-input-' : 'cat-master-vid-input-';
                const container = document.getElementById(inputId + categoryId).parentElement;
                container.style.opacity = '0.5';

                fetch("{{ route('admin.products.media.delete-cat-preview', $product->id) }}", {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ category_id: categoryId, type: type })
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Delete failed: ' + (data.message || 'Unknown error'));
                        container.style.opacity = '1';
                    }
                }).catch(err => {
                    alert('Server error deleting media.');
                    container.style.opacity = '1';
                });
            };

            window.uploadMasterPreview = function(file) {
                if (file.size > 50 * 1024 * 1024) {
                    alert('File is too large. Maximum size is 50MB.');
                    return;
                }
                const formData = new FormData();
                formData.append('file', file);
                
                const container = document.getElementById('master-preview-container');
                const overlay = createProgressOverlay(container);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', "{{ route('admin.products.media.upload-preview', $product->id) }}");
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percent = (e.loaded / e.total) * 100;
                        overlay.querySelector('.progress-text').textContent = Math.round(percent) + '%';
                        overlay.querySelector('.progress-bar').style.width = percent + '%';
                    }
                });

                xhr.onload = function() {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        if (xhr.status === 200 && data.success) {
                            const img = document.getElementById('master-preview-img');
                            if (img) img.src = data.url;
                            else location.reload();
                            overlay.style.opacity = '0';
                        } else if (xhr.status === 422) {
                            const errorMsg = data.errors && Object.values(data.errors)[0] ? Object.values(data.errors)[0][0] : data.message;
                            alert('Validation failed: ' + errorMsg);
                            overlay.style.opacity = '0';
                        } else {
                            alert('Upload failed: ' + (data.message || xhr.statusText));
                            overlay.style.opacity = '0';
                        }
                    } catch(err) {
                        console.error('Server response:', xhr.responseText);
                        if (xhr.status === 500) {
                            alert('Server Error 500: Database error or syntax error. Please check your backend terminal, laravel.log, or ensure migrations are run.');
                        } else if (xhr.status === 413) {
                            alert('Payload Too Large: The file exceeds the server PHP upload limit.');
                        } else {
                            alert('Server error (' + xhr.status + '). Check browser console for the response text. It might be an upload size limit or backend crash.');
                        }
                        overlay.style.opacity = '0';
                    }
                };
                
                xhr.onerror = function() {
                    alert('Network error.');
                    overlay.style.opacity = '0';
                };

                xhr.send(formData);
            }

            function uploadFiles(files, colorId) {
                let totalSize = 0;
                Array.from(files).forEach(f => totalSize += f.size);
                if (totalSize > 50 * 1024 * 1024) {
                    alert('Total size of files is too large. Maximum combined size is 50MB.');
                    return;
                }

                const formData = new FormData();
                Array.from(files).forEach(f => formData.append('files[]', f));
                
                const container = document.getElementById(`media-gallery-${colorId}`).parentElement;
                const overlay = createProgressOverlay(container);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', "{{ route('admin.products.media.upload', $product->id) }}?color_id=" + colorId);
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percent = (e.loaded / e.total) * 100;
                        overlay.querySelector('.progress-text').textContent = Math.round(percent) + '%';
                        overlay.querySelector('.progress-bar').style.width = percent + '%';
                    }
                });

                xhr.onload = function() {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        if (xhr.status === 200 && data.success) {
                            location.reload();
                        } else if (xhr.status === 422) {
                            const errorMsg = data.errors && Object.values(data.errors)[0] ? Object.values(data.errors)[0][0] : data.message;
                            alert('Validation failed: ' + errorMsg);
                            overlay.style.opacity = '0';
                        } else {
                            alert('Upload failed: ' + (data.message || xhr.statusText));
                            overlay.style.opacity = '0';
                        }
                    } catch(err) {
                        console.error('Server response:', xhr.responseText);
                        if (xhr.status === 500) {
                            alert('Server Error 500: Database error or syntax error. Please check your backend terminal, laravel.log, or ensure migrations are run.');
                        } else if (xhr.status === 413) {
                            alert('Payload Too Large: The files exceed the server PHP upload limit.');
                        } else {
                            alert('Server error (' + xhr.status + '). Check browser console for the response text. It might be an upload size limit or backend crash.');
                        }
                        overlay.style.opacity = '0';
                    }
                };
                
                xhr.onerror = function() {
                    alert('Network error.');
                    overlay.style.opacity = '0';
                };

                xhr.send(formData);
            }

            document.querySelectorAll('.media-upload-input').forEach(input => {
                input.addEventListener('change', function() {
                    const colorId = this.dataset.colorId;
                    if (this.files.length) uploadFiles(this.files, colorId);
                });
            });

            // Reorder
            document.querySelectorAll('.media-gallery').forEach(el => {
                new Sortable(el, {
                    animation: 250,
                    handle: '.drag-handle',
                    onEnd: function() {
                        const mediaIds = Array.from(el.querySelectorAll('.media-item')).map(i => i.dataset.mediaId);
                        fetch("{{ route('admin.products.media.reorder', $product->id) }}", {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ media_ids: mediaIds })
                        });
                    }
                });
            });

            // Deletion
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.delete-media-btn');
                if (btn && confirm('Permanently remove this asset?')) {
                    let url = "{{ route('admin.products.media.delete', ['product' => 'PROD_ID', 'productImage' => 'MEDIA_ID']) }}".replace('PROD_ID', btn.dataset.productId).replace('MEDIA_ID', btn.dataset.mediaId);
                    fetch(url, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    }).then(r => r.json()).then(d => { if(d.success) btn.closest('.media-item').remove(); });
                }
            });
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
                    }
                });
            });
        });

        let newIndex = 0;
        function addNewVariantRow() {
            const container = document.getElementById('new-variants-container');
            const template = document.getElementById('new-variant-template');
            const div = document.createElement('div');
            div.innerHTML = template.innerHTML.replace(/INDEX/g, newIndex++);
            container.appendChild(div.firstElementChild);
        }

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
    </script>
@endpush
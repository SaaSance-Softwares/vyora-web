@extends('layouts.admin')

@section('header', 'Create Product')

@section('content')
    <div class="pb-24">
        <form id="create-product-form" action="{{ route('admin.products.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="redirect_tab" id="redirect-tab" value="info">

            <!-- Tab Navigation -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                        <button type="button"
                            class="tab-button border-b-2 border-black text-black py-4 px-1 text-sm font-medium"
                            data-tab="info">
                            Product Info
                        </button>
                        <button type="button"
                            class="tab-button border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-4 px-1 text-sm font-medium"
                            data-tab="organization">
                            Organization
                        </button>
                        <button type="button"
                            class="tab-button border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-4 px-1 text-sm font-medium"
                            data-tab="skus">
                            SKUs & Variants
                        </button>
                        <button type="button"
                            class="tab-button border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 py-4 px-1 text-sm font-medium"
                            data-tab="media">
                            Media Gallery
                        </button>
                    </nav>
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

            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-700">Returnable</span>
                <input type="checkbox" name="is_returnable" value="1" {{ old('is_returnable') ? 'checked' : '' }}
                    class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
            </div>

            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-700">On Sale</span>
                <input type="checkbox" name="on_sale" value="1" {{ old('on_sale') ? 'checked' : '' }}
                    class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded">
            </div>

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
            </div>
        </div>
    </div>
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
            <h3 class="text-lg font-medium text-gray-900 mb-4">Variants & Pricing (SKUs)</h3>

            <div class="mt-6 border-t pt-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Add Variants</h4>
                <div class="grid grid-cols-12 gap-2 mb-2 px-3">
                    <div class="col-span-2 text-xs font-medium text-gray-500">SKU</div>
                    <div class="col-span-2 text-xs font-medium text-gray-500">Color</div>
                    <div class="col-span-2 text-xs font-medium text-gray-500">Size</div>
                    <div class="col-span-2 text-xs font-medium text-gray-500">Price</div>
                    <div class="col-span-2 text-xs font-medium text-gray-500">Stock</div>
                    <div class="col-span-2"></div>
                </div>
                <div id="new-variants-container"></div>
                
                <button type="button" onclick="addNewVariantRow()" class="mt-2 flex items-center text-sm text-indigo-600 hover:text-indigo-900 font-medium px-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Add Variant Row
                </button>
            </div>
        </div>
    </div>
    <!-- End Tab Content: SKUs & Variants -->

    <!-- Template for new variant row -->
    <template id="new-variant-template">
        <div class="grid grid-cols-12 gap-2 mb-2 px-3 new-variant-row items-center bg-gray-50 p-2 rounded">
            <div class="col-span-2">
                <input type="text" name="new_skus[INDEX][code]" placeholder="SKU Code" class="w-full border-gray-300 rounded text-xs p-1">
            </div>
            <div class="col-span-2">
                <select name="new_skus[INDEX][color_id]" class="w-full border-gray-300 rounded text-xs p-1">
                    <option value="">Select Color</option>
                    @foreach($colors as $color)
                        <option value="{{ $color->id }}">{{ $color->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <input type="text" name="new_skus[INDEX][size]" placeholder="Size (e.g., S, M, L, A3)" class="w-full border-gray-300 rounded text-xs p-1">
            </div>
            <div class="col-span-2 flex flex-col gap-1">
                <input type="number" step="0.01" name="new_skus[INDEX][price]" placeholder="SP" class="w-full border-gray-300 rounded text-xs p-1">
                <input type="number" step="0.01" name="new_skus[INDEX][mrp]" placeholder="MRP" class="w-full border-gray-300 rounded text-[10px] p-1 text-gray-500">
            </div>
            <div class="col-span-2">
                <input type="number" name="new_skus[INDEX][stock]" placeholder="Stock" class="w-full border-gray-300 rounded text-xs p-1">
            </div>
            <div class="col-span-2 text-right">
                <button type="button" onclick="this.closest('.new-variant-row').remove()" class="text-red-600 hover:text-red-900 text-xs font-medium">Remove</button>
            </div>
        </div>
    </template>

    <script>
        let newVariantIndex = 0;
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
    </form>
    </div>

    <!-- Fixed Bottom Footer Actions -->
    <div
        class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-50 md:pl-64 flex justify-between items-center shadow-lg">
        <button type="submit" form="create-product-form"
            class="bg-black text-white px-6 py-2 rounded-md hover:bg-gray-800 text-sm font-medium transition-colors">
            Create Product
        </button>
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
                    }
                });
            });
        });
    </script>
@endpush
@extends('layouts.admin')

@section('header', 'New Collection')

@section('content')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<div class="w-full">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h1 class="text-xl font-bold">Add Collection</h1>
            <a href="{{ route('admin.collections.index') }}" class="text-sm text-gray-500 hover:text-black font-medium">Cancel</a>
        </div>

        <form action="{{ route('admin.collections.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <div class="flex flex-col gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Collection Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Winter Sale"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black"
                        onkeyup="document.getElementById('slug').value = this.value.toLowerCase().trim().replace(/ /g, '-').replace(/[^\w-]+/g, '');">
                </div>

                <!-- Slug -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required placeholder="winter-sale"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                </div>

                <!-- Description -->
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                    <div id="description-editor" class="w-full border border-gray-300 rounded-lg bg-white" style="height: 200px;">
                        {!! old('description') !!}
                    </div>
                    <textarea name="description" class="hidden">{{ old('description') }}</textarea>
                </div>

                
                <!-- Collection Banner Image -->
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Collection Banner Image (Optional)</label>
                    <div class="mb-3">
                        <img id="banner-image-preview" src="" class="w-64 h-32 object-cover rounded-lg border border-gray-200 hidden">
                    </div>
                    <input type="file" id="banner-image-input" name="banner_image" accept="image/*"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 cursor-pointer">
                    <p class="text-xs text-gray-500 mt-1">Background banner for the frontend page header.</p>
                </div>

<!-- Active Status -->
                <div class="w-full">
                    <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="h-4 w-4 border-gray-300 rounded text-black focus:ring-black">
                        <div>
                            <p class="text-sm font-bold text-gray-900 leading-none">Show in Storefront</p>
                            <p class="text-xs text-gray-500 mt-1">Make this collection visible for customer browsing</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Social SEO Section -->
            <div class="mt-8 pt-6 border-t border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Social SEO Metadata (OG & Twitter)</h3>
                <div class="flex flex-col gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Social Title</label>
                        <input type="text" name="social_title" value="{{ old('social_title') }}" placeholder="e.g. Shop the Winter Collection"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                        <p class="text-xs text-gray-500 mt-1">Leave blank to use the default collection name.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Social Image</label>
                        <input type="file" name="social_image" accept="image/*"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100">
                        <p class="text-xs text-gray-500 mt-1">Recommended size: 1200x630 pixels (for OG/Twitter cards).</p>
                    </div>

                    <div class="w-full">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Social Description</label>
                        <textarea name="social_description" rows="3" placeholder="Description shown on social media shares..."
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">{{ old('social_description') }}</textarea>
                    </div>

                    <!-- Meta Keywords -->
                    <div class="w-full">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Keywords</label>
                        <input type="text" name="meta_keywords" placeholder="Keywords for SEO, comma separated..." value="{{ old('meta_keywords') }}"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                    </div>

                    <!-- AEO Use Case -->
                    <div class="w-full">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">AEO Use Case</label>
                        <input type="text" name="aeo_use_case" placeholder="Use Case (e.g., Summer Collection)" value="{{ old('aeo_use_case') }}"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                    </div>
                </div>
            </div>

            @include('admin.partials.faqs-editor')
            <div class="pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-black text-white px-8 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors">
                    Add Collection
                </button>
            </div>
        </form>
    </div>
</div>



<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bannerInput = document.getElementById('banner-image-input');
        const bannerPreview = document.getElementById('banner-image-preview');
        if (bannerInput && bannerPreview) {
            bannerInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    bannerPreview.src = URL.createObjectURL(file);
                    bannerPreview.classList.remove('hidden');
                }
            });
        }
    });

    var quillDesc = new Quill('#description-editor', {
        theme: 'snow'
    });
    
    // Initialize content if it's there
    var existingContent = document.querySelector('textarea[name="description"]').value;
    if (existingContent && quillDesc.root.innerHTML === '<p><br></p>') {
        // Only set it if quill is empty but textarea has content (just in case)
        quillDesc.root.innerHTML = existingContent;
    }

    var form = document.querySelector('#description-editor').closest('form');
    if (form) {
        form.addEventListener('submit', function() {
            var html = quillDesc.root.innerHTML;
            if (html === '<p><br></p>') html = '';
            document.querySelector('textarea[name="description"]').value = html;
        });
    }
</script>

@endsection
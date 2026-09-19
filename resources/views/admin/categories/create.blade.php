@extends('layouts.admin')

@section('header', 'New Category')

@section('content')
<div class="w-full">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h1 class="text-xl font-bold text-gray-900">Create New Category</h1>
            <a href="{{ route('admin.categories.index') }}" class="text-sm text-gray-500 hover:text-black font-medium">Cancel</a>
        </div>

        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <div class="flex flex-col gap-6">
                <!-- Category Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Category Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                </div>

                <!-- Slug -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Slug *</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" required
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black"
                        placeholder="Leave blank to auto-generate">
                </div>
                <!-- Status -->
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="h-4 w-4 border-gray-300 rounded text-black focus:ring-black">
                        <span class="text-sm font-semibold text-gray-700">Active Category</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">Inactive categories won't be visible to customers.</p>
                </div>

                <!-- Category Image -->
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Category Image</label>
                    <div class="mb-3">
                        <img id="image-preview" src="" class="w-32 h-32 object-cover rounded-lg border border-gray-200 hidden">
                    </div>
                    <input type="file" id="image-input" name="image" accept="image/*"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 cursor-pointer">
                    <p class="text-xs text-gray-500 mt-1">Recommended size: 800x800px</p>
                </div>

                <!-- Category Banner Image -->
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Category Banner Image (Optional)</label>
                    <div class="mb-3">
                        <img id="banner-image-preview" src="" class="w-64 h-32 object-cover rounded-lg border border-gray-200 hidden">
                    </div>
                    <input type="file" id="banner-image-input" name="banner_image" accept="image/*"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 cursor-pointer">
                    <p class="text-xs text-gray-500 mt-1">Background banner for the frontend page header.</p>
                </div>


                <!-- Category Description -->
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Category Description</label>
                    <div id="description-editor" class="w-full border border-gray-300 rounded-lg bg-white" style="height: 200px;">
                        {!! old('description') !!}
                    </div>
                    <textarea name="description" class="hidden">{{ old('description') }}</textarea>
                </div>

                <div class="w-full">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Search Engine Optimization (SEO)</h3>
                </div>

                <!-- Meta Title -->
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title') }}" placeholder="Optimized Page Title"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                </div>

                <!-- Social Image -->
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Social Share Image (OG Image)</label>
                    <div class="mb-3">
                        <img id="social-preview" src="" class="w-32 h-20 object-cover rounded-lg border border-gray-200 hidden">
                    </div>
                    <input type="file" id="social-image-input" name="social_image" accept="image/*"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 cursor-pointer">
                </div>

                <!-- Meta Description -->
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Description</label>
                    <textarea name="meta_description" rows="3" placeholder="Brief description for search engines..."
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">{{ old('meta_description') }}</textarea>
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
                    <input type="text" name="aeo_use_case" placeholder="Use Case (e.g., Casual Wear, Gym Wear)" value="{{ old('aeo_use_case') }}"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                </div>
            </div>

            @include('admin.partials.faqs-editor', ['model' => null])
            
            <div class="pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-black text-white px-8 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors">
                    Create Category
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
            // Image Preview logic
            const imageInput = document.getElementById('image-input');
            const imagePreview = document.getElementById('image-preview');
            
            if (imageInput && imagePreview) {
                imageInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        imagePreview.src = URL.createObjectURL(file);
                        imagePreview.classList.remove('hidden');
                    }
                });
            }
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

            const socialInput = document.getElementById('social-image-input');
            const socialPreview = document.getElementById('social-preview');
            
            if (socialInput && socialPreview) {
                socialInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        socialPreview.src = URL.createObjectURL(file);
                        socialPreview.classList.remove('hidden');
                    }
                });
            }
        const slugInput = document.querySelector('input[name="slug"]');
        if (slugInput) {
            slugInput.addEventListener('input', function(e) {
                let val = e.target.value;
                val = val.toLowerCase();
                val = val.replace(/\s+/g, '-');
                val = val.replace(/-+/g, '-');
                val = val.replace(/[^a-z0-9-]/g, ''); // Optional: remove invalid characters
                e.target.value = val;
            });
            
            // Auto-generate slug from name only on create page if slug is empty
            const nameInput = document.querySelector('input[name="name"]');
            if (nameInput && window.location.pathname.includes('/create')) {
                nameInput.addEventListener('input', function(e) {
                    if (!slugInput.dataset.manuallyEdited) {
                        let val = e.target.value.toLowerCase().replace(/\s+/g, '-').replace(/-+/g, '-').replace(/[^a-z0-9-]/g, '');
                        slugInput.value = val;
                    }
                });
                slugInput.addEventListener('input', function() {
                    slugInput.dataset.manuallyEdited = true;
                });
            }
        }
    });
</script>

<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
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
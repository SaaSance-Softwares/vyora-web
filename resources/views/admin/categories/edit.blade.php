@extends('layouts.admin')

@section('header', 'Edit Category')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h1 class="text-xl font-bold text-gray-900">Edit Category: {{ $category->name }}</h1>
            <a href="{{ route('admin.categories.index') }}" class="text-sm text-gray-500 hover:text-black font-medium">Cancel</a>
        </div>

        <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Category Name</label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                </div>

                <!-- Slug -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Slug</label>
                    <input type="text" id="slug-input" name="slug" value="{{ old('slug', $category->slug) }}" required
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                </div>

                <!-- Parent Category -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Parent Hierarchy</label>
                    <select name="parent_id" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                        <option value="">None (Root Category)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Active Status -->
                <div class="md:col-span-2">
                    <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} class="h-4 w-4 border-gray-300 rounded text-black focus:ring-black">
                        <div>
                            <p class="text-sm font-bold text-gray-900 leading-none">Active Status</p>
                            <p class="text-xs text-gray-500 mt-1">Visible on site storefront</p>
                        </div>
                    </label>
                </div>
            </div>

            <hr class="border-gray-100 my-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Category Image -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Category Image</label>
                    <div class="mb-3">
                        <img id="image-preview" src="{{ $category->image ? asset($category->image) : '' }}" class="w-32 h-32 object-cover rounded-lg border border-gray-200 {{ $category->image ? '' : 'hidden' }}">
                    </div>
                    <input type="file" id="image-input" name="image" accept="image/*"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 cursor-pointer">
                    <p class="text-xs text-gray-500 mt-1">Recommended size: 800x800px</p>
                </div>

                <div class="md:col-span-2">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 mt-4">Search Engine Optimization (SEO)</h3>
                </div>

                <!-- Meta Title -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}" placeholder="Optimized Page Title"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                </div>

                <!-- Social Image -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Social Share Image (OG Image)</label>
                    <div class="mb-3">
                        <img id="social-preview" src="{{ $category->social_image ? asset($category->social_image) : '' }}" class="w-32 h-20 object-cover rounded-lg border border-gray-200 {{ $category->social_image ? '' : 'hidden' }}">
                    </div>
                    <input type="file" id="social-image-input" name="social_image" accept="image/*"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 cursor-pointer">
                </div>

                <!-- Meta Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Description</label>
                    <textarea name="meta_description" rows="3" placeholder="Brief description for search engines..."
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">{{ old('meta_description', $category->meta_description) }}</textarea>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-black text-white px-8 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors">
                    Update Category
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
@endsection
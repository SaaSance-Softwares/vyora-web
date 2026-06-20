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
                    <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" required
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
            <hr class="border-gray-100 my-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Category Image -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Category Image</label>
                    @if($category->image)
                        <div class="mb-3">
                            <img src="{{ asset($category->image) }}" class="w-32 h-32 object-cover rounded-lg border border-gray-200">
                        </div>
                    @endif
                    <input type="file" name="image" accept="image/*"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 cursor-pointer">
                    <p class="text-xs text-gray-500 mt-1">Recommended size: 800x800px</p>
                </div>

                <div class="md:col-span-2">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 mt-4">Search Engine Optimization (SEO)</h3>
                </div>

                <!-- Meta Title -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}" placeholder="Optimized Page Title"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                </div>

                <!-- Social Image -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Social Share Image (OG Image)</label>
                    @if($category->social_image)
                        <div class="mb-3">
                            <img src="{{ asset($category->social_image) }}" class="w-32 h-20 object-cover rounded-lg border border-gray-200">
                        </div>
                    @endif
                    <input type="file" name="social_image" accept="image/*"
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
@endsection
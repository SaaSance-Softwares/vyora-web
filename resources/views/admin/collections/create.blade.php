@extends('layouts.admin')

@section('header', 'New Collection')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h1 class="text-xl font-bold">Add Collection</h1>
            <a href="{{ route('admin.collections.index') }}" class="text-sm text-gray-500 hover:text-black font-medium">Cancel</a>
        </div>

        <form action="{{ route('admin.collections.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="5" placeholder="Brief summary of the collection..."
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">{{ old('description') }}</textarea>
                </div>

                <!-- Active Status -->
                <div class="md:col-span-2">
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Social Description</label>
                        <textarea name="social_description" rows="3" placeholder="Description shown on social media shares..."
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">{{ old('social_description') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-black text-white px-8 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors">
                    Add Collection
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
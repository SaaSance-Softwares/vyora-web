@extends('layouts.admin')

@section('header', 'Edit Category')

@section('content')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<div class="w-full">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h1 class="text-xl font-bold text-gray-900">Edit Category: {{ $category->name }}</h1>
            <a href="{{ route('admin.categories.index') }}" class="text-sm text-gray-500 hover:text-black font-medium">Cancel</a>
        </div>

        <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-6">
                <!-- Category Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Category Name *</label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                </div>

                <!-- Slug -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Slug *</label>
                    <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" required
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                </div>

                <!-- Parent Category (Nested Category) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Parent Category</label>
                    <select name="parent_id" id="parent_id" class="w-full border border-gray-300 rounded-lg py-2.5 px-3 text-sm focus:ring-black focus:border-black bg-white">
                        <option value="">None (Main / Root Category)</option>
                        @foreach($categories as $parentCat)
                            <option value="{{ $parentCat->id }}" {{ (string)old('parent_id', $category->parent_id ?? '') === (string)$parentCat->id ? 'selected' : '' }}>
                                {{ $parentCat->name }}
                            </option>
                            @foreach($parentCat->children ?? [] as $subCat)
                                <option value="{{ $subCat->id }}" {{ (string)old('parent_id', $category->parent_id ?? '') === (string)$subCat->id ? 'selected' : '' }}>
                                    &nbsp;&nbsp;&nbsp;&nbsp;↳ {{ $subCat->name }}
                                </option>
                                @foreach($subCat->children ?? [] as $subSubCat)
                                    <option value="{{ $subSubCat->id }}" {{ (string)old('parent_id', $category->parent_id ?? '') === (string)$subSubCat->id ? 'selected' : '' }}>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;↳ {{ $subSubCat->name }}
                                    </option>
                                @endforeach
                            @endforeach
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Select a parent category if this should be nested as a subcategory (e.g. nest T-Shirt under Women).</p>
                </div>

                <!-- Status -->
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} class="h-4 w-4 border-gray-300 rounded text-black focus:ring-black">
                        <span class="text-sm font-semibold text-gray-700">Active Category</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">Inactive categories won't be visible to customers.</p>
                </div>

                <!-- Category Image -->
                <div class="w-full p-4 border border-gray-200 rounded-xl bg-gray-50/50" x-data="categoryImageManager({
                    initialUrl: '{{ $category->image ? url($category->image) : '' }}',
                    fileInputId: 'image-input'
                })">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <label class="block text-sm font-bold text-gray-800">Category Image</label>
                            <p class="text-xs text-gray-500">Main thumbnail for this category (Recommended: 800x800px)</p>
                        </div>
                        <div>
                            <template x-if="isRemoved">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Marked for deletion
                                </span>
                            </template>
                            <template x-if="hasNewFile">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> New image selected
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Hidden input to submit deletion flag -->
                    <input type="hidden" name="remove_image" :value="isRemoved ? '1' : '0'">

                    <!-- Real file input (hidden, triggered via button) -->
                    <input type="file" id="image-input" name="image" accept="image/*" class="hidden">

                    <div class="flex flex-col sm:flex-row items-start gap-4">
                        <!-- Preview Box -->
                        <div class="relative shrink-0">
                            <!-- Active preview -->
                            <div x-show="currentUrl && !isRemoved" class="relative">
                                <img :src="currentUrl" class="w-32 h-32 object-cover rounded-lg border border-gray-200 shadow-sm bg-white">
                            </div>

                            <!-- Marked for deletion preview -->
                            <div x-show="currentUrl && isRemoved" style="display: none;" class="w-32 h-32 rounded-lg border-2 border-dashed border-red-300 bg-red-50 flex flex-col items-center justify-center text-center p-2">
                                <svg class="w-7 h-7 text-red-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                <span class="text-[11px] font-semibold text-red-600 leading-tight">Will be deleted on save</span>
                            </div>

                            <!-- Empty preview (no image) -->
                            <div x-show="!currentUrl && !isRemoved" style="display: none;" class="w-32 h-32 rounded-lg border-2 border-dashed border-gray-300 bg-white flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-8 h-8 mb-1 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="text-[11px]">No image</span>
                            </div>
                        </div>

                        <!-- Action Controls -->
                        <div class="flex-1 space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Change / Upload Button -->
                                <button type="button" @click="triggerUpload()" class="px-3.5 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-800 hover:bg-gray-50 hover:border-gray-400 transition shadow-sm flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    <span x-text="currentUrl && !isRemoved ? 'Change Image' : 'Upload Image'"></span>
                                </button>

                                <!-- Delete button (visible when there is an image and not marked deleted) -->
                                <button type="button" x-show="(initialUrl || currentUrl) && !isRemoved && !hasNewFile" @click="deleteImage()" class="px-3.5 py-1.5 bg-red-50 border border-red-200 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-100 transition shadow-sm flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    <span>Delete Image</span>
                                </button>

                                <!-- Undo Delete button -->
                                <button type="button" x-show="isRemoved" style="display: none;" @click="undoDelete()" class="px-3.5 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 transition shadow-sm flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2m0 0l-4-4m4 4l4-4"></path></svg>
                                    <span>Undo Delete</span>
                                </button>

                                <!-- Cancel selection button (if newly picked file) -->
                                <button type="button" x-show="hasNewFile" style="display: none;" @click="cancelNewFile()" class="px-3.5 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-50 transition shadow-sm">
                                    Cancel Change
                                </button>
                            </div>

                            <p x-show="hasNewFile" style="display: none;" class="text-xs text-gray-600 font-mono truncate" x-text="'Selected: ' + fileName"></p>
                        </div>
                    </div>
                </div>

                <!-- Category Banner Image -->
                <div class="w-full p-4 border border-gray-200 rounded-xl bg-gray-50/50" x-data="categoryImageManager({
                    initialUrl: '{{ $category->banner_image_url ?? ($category->banner_image ? url($category->banner_image) : '') }}',
                    fileInputId: 'banner-image-input'
                })">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <label class="block text-sm font-bold text-gray-800">Category Banner Image (Optional)</label>
                            <p class="text-xs text-gray-500">Background banner for the frontend category page header</p>
                        </div>
                        <div>
                            <template x-if="isRemoved">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Marked for deletion
                                </span>
                            </template>
                            <template x-if="hasNewFile">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> New banner selected
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Hidden input to submit deletion flag -->
                    <input type="hidden" name="remove_banner_image" :value="isRemoved ? '1' : '0'">

                    <!-- Real file input (hidden, triggered via button) -->
                    <input type="file" id="banner-image-input" name="banner_image" accept="image/*" class="hidden">

                    <div class="flex flex-col sm:flex-row items-start gap-4">
                        <!-- Preview Box -->
                        <div class="relative shrink-0">
                            <!-- Active preview -->
                            <div x-show="currentUrl && !isRemoved" class="relative">
                                <img :src="currentUrl" class="w-64 h-32 object-cover rounded-lg border border-gray-200 shadow-sm bg-white">
                            </div>

                            <!-- Marked for deletion preview -->
                            <div x-show="currentUrl && isRemoved" style="display: none;" class="w-64 h-32 rounded-lg border-2 border-dashed border-red-300 bg-red-50 flex flex-col items-center justify-center text-center p-2">
                                <svg class="w-7 h-7 text-red-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                <span class="text-[11px] font-semibold text-red-600 leading-tight">Banner will be deleted on save</span>
                            </div>

                            <!-- Empty preview (no image) -->
                            <div x-show="!currentUrl && !isRemoved" style="display: none;" class="w-64 h-32 rounded-lg border-2 border-dashed border-gray-300 bg-white flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-8 h-8 mb-1 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="text-[11px]">No banner set</span>
                            </div>
                        </div>

                        <!-- Action Controls -->
                        <div class="flex-1 space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Change / Upload Button -->
                                <button type="button" @click="triggerUpload()" class="px-3.5 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-800 hover:bg-gray-50 hover:border-gray-400 transition shadow-sm flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    <span x-text="currentUrl && !isRemoved ? 'Change Banner' : 'Upload Banner'"></span>
                                </button>

                                <!-- Delete button -->
                                <button type="button" x-show="(initialUrl || currentUrl) && !isRemoved && !hasNewFile" @click="deleteImage()" class="px-3.5 py-1.5 bg-red-50 border border-red-200 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-100 transition shadow-sm flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    <span>Delete Banner</span>
                                </button>

                                <!-- Undo Delete button -->
                                <button type="button" x-show="isRemoved" style="display: none;" @click="undoDelete()" class="px-3.5 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 transition shadow-sm flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2m0 0l-4-4m4 4l4-4"></path></svg>
                                    <span>Undo Delete</span>
                                </button>

                                <!-- Cancel selection button -->
                                <button type="button" x-show="hasNewFile" style="display: none;" @click="cancelNewFile()" class="px-3.5 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-50 transition shadow-sm">
                                    Cancel Change
                                </button>
                            </div>

                            <p x-show="hasNewFile" style="display: none;" class="text-xs text-gray-600 font-mono truncate" x-text="'Selected: ' + fileName"></p>
                        </div>
                    </div>
                </div>

                <!-- Category Description -->
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Category Description</label>
                    <div id="description-editor" class="w-full border border-gray-300 rounded-lg bg-white" style="height: 200px;">
                        {!! old('description', $category->description) !!}
                    </div>
                    <textarea name="description" class="hidden">{{ old('description', $category->description) }}</textarea>
                </div>

                <div class="w-full pt-4 border-t border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Search Engine Optimization (SEO)</h3>
                </div>

                <!-- Meta Title -->
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}" placeholder="Optimized Page Title"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                </div>

                <!-- Meta Description -->
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Description</label>
                    <textarea name="meta_description" rows="3" placeholder="Brief description for search engines..."
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">{{ old('meta_description', $category->meta_description) }}</textarea>
                </div>

                <!-- Meta Keywords -->
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Keywords</label>
                    <input type="text" name="meta_keywords" placeholder="Keywords for SEO, comma separated..." value="{{ old('meta_keywords', $category->meta_keywords) }}"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                </div>

                <!-- Social Image -->
                <div class="w-full p-4 border border-gray-200 rounded-xl bg-gray-50/50" x-data="categoryImageManager({
                    initialUrl: '{{ $category->social_image ? url($category->social_image) : '' }}',
                    fileInputId: 'social-image-input'
                })">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <label class="block text-sm font-bold text-gray-800">Social Share Image (OG Image)</label>
                            <p class="text-xs text-gray-500">Image shown when sharing category link on social media / WhatsApp (Recommended: 1200x630px)</p>
                        </div>
                        <div>
                            <template x-if="isRemoved">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Marked for deletion
                                </span>
                            </template>
                            <template x-if="hasNewFile">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> New image selected
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Hidden input to submit deletion flag -->
                    <input type="hidden" name="remove_social_image" :value="isRemoved ? '1' : '0'">

                    <!-- Real file input (hidden, triggered via button) -->
                    <input type="file" id="social-image-input" name="social_image" accept="image/*" class="hidden">

                    <div class="flex flex-col sm:flex-row items-start gap-4">
                        <!-- Preview Box -->
                        <div class="relative shrink-0">
                            <!-- Active preview -->
                            <div x-show="currentUrl && !isRemoved" class="relative">
                                <img :src="currentUrl" class="w-44 h-24 object-cover rounded-lg border border-gray-200 shadow-sm bg-white">
                            </div>

                            <!-- Marked for deletion preview -->
                            <div x-show="currentUrl && isRemoved" style="display: none;" class="w-44 h-24 rounded-lg border-2 border-dashed border-red-300 bg-red-50 flex flex-col items-center justify-center text-center p-2">
                                <svg class="w-7 h-7 text-red-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                <span class="text-[11px] font-semibold text-red-600 leading-tight">Will be deleted on save</span>
                            </div>

                            <!-- Empty preview (no image) -->
                            <div x-show="!currentUrl && !isRemoved" style="display: none;" class="w-44 h-24 rounded-lg border-2 border-dashed border-gray-300 bg-white flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-8 h-8 mb-1 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="text-[11px]">No image set</span>
                            </div>
                        </div>

                        <!-- Action Controls -->
                        <div class="flex-1 space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Change / Upload Button -->
                                <button type="button" @click="triggerUpload()" class="px-3.5 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-800 hover:bg-gray-50 hover:border-gray-400 transition shadow-sm flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    <span x-text="currentUrl && !isRemoved ? 'Change Image' : 'Upload Image'"></span>
                                </button>

                                <!-- Delete button -->
                                <button type="button" x-show="(initialUrl || currentUrl) && !isRemoved && !hasNewFile" @click="deleteImage()" class="px-3.5 py-1.5 bg-red-50 border border-red-200 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-100 transition shadow-sm flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    <span>Delete Image</span>
                                </button>

                                <!-- Undo Delete button -->
                                <button type="button" x-show="isRemoved" style="display: none;" @click="undoDelete()" class="px-3.5 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 transition shadow-sm flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2m0 0l-4-4m4 4l4-4"></path></svg>
                                    <span>Undo Delete</span>
                                </button>

                                <!-- Cancel selection button -->
                                <button type="button" x-show="hasNewFile" style="display: none;" @click="cancelNewFile()" class="px-3.5 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-50 transition shadow-sm">
                                    Cancel Change
                                </button>
                            </div>

                            <p x-show="hasNewFile" style="display: none;" class="text-xs text-gray-600 font-mono truncate" x-text="'Selected: ' + fileName"></p>
                        </div>
                    </div>
                </div>

                <!-- AEO Use Case -->
                <div class="w-full">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">AEO Use Case</label>
                    <input type="text" name="aeo_use_case" placeholder="Use Case (e.g., Casual Wear, Gym Wear)" value="{{ old('aeo_use_case', $category->aeo_use_case) }}"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                </div>
            </div>

            @include('admin.partials.faqs-editor', ['model' => $category])
            
            <div class="pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-black text-white px-8 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors">
                    Update Category
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function categoryImageManager(config) {
        return {
            initialUrl: config.initialUrl || '',
            currentUrl: config.initialUrl || '',
            isRemoved: false,
            hasNewFile: false,
            fileName: '',
            
            init() {
                const input = document.getElementById(config.fileInputId);
                if (input) {
                    input.addEventListener('change', (e) => {
                        const file = e.target.files && e.target.files[0];
                        if (file) {
                            this.currentUrl = URL.createObjectURL(file);
                            this.hasNewFile = true;
                            this.isRemoved = false;
                            this.fileName = file.name;
                        }
                    });
                }
            },

            triggerUpload() {
                const input = document.getElementById(config.fileInputId);
                if (input) input.click();
            },

            deleteImage() {
                this.isRemoved = true;
                this.hasNewFile = false;
                this.fileName = '';
                const input = document.getElementById(config.fileInputId);
                if (input) input.value = '';
            },

            undoDelete() {
                this.isRemoved = false;
                this.currentUrl = this.initialUrl;
            },

            cancelNewFile() {
                this.hasNewFile = false;
                this.fileName = '';
                const input = document.getElementById(config.fileInputId);
                if (input) input.value = '';
                this.currentUrl = this.initialUrl;
                this.isRemoved = false;
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        const slugInput = document.querySelector('input[name="slug"]');
        if (slugInput) {
            slugInput.addEventListener('input', function(e) {
                let val = e.target.value;
                val = val.toLowerCase();
                val = val.replace(/\s+/g, '-');
                val = val.replace(/-+/g, '-');
                val = val.replace(/[^a-z0-9-]/g, '');
                e.target.value = val;
            });
        }
    });
</script>

<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    var quillDesc = new Quill('#description-editor', {
        theme: 'snow'
    });
    
    var existingContent = document.querySelector('textarea[name="description"]').value;
    if (existingContent && quillDesc.root.innerHTML === '<p><br></p>') {
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
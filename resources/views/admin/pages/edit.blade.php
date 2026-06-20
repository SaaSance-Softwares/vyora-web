@extends('layouts.admin')

@section('header', 'Edit Page')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h1 class="text-xl font-bold">Page Settings: {{ $mnpage->title }}</h1>
            <a href="{{ route('admin.online-store.mnpages.index') }}" class="text-sm text-gray-500 hover:text-black font-medium">Cancel</a>
        </div>

        <form action="{{ route('admin.online-store.mnpages.update', $mnpage) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-8">
            @csrf @method('PUT')
            
            <input type="hidden" name="content" value="{{ json_encode($mnpage->content) }}">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Info -->
                <div class="space-y-6">
                    <h3 class="font-bold text-gray-900 border-b pb-2">Identification</h3>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Page Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $mnpage->title) }}" required class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">URL Slug</label>
                        <div class="flex rounded-lg shadow-sm">
                            <span id="slug_prefix" class="px-4 inline-flex items-center min-w-fit rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-sm text-gray-500 transition-all">
                                {{ $mnpage->is_home ? url('/') . '/' : url('/p') . '/' }}
                            </span>
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $mnpage->slug) }}" required class="w-full border border-gray-300 rounded-r-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                        </div>
                        <div class="mt-2 text-right">
                            <a href="{{ url($mnpage->slug) }}" target="_blank" id="preview_link" class="text-sm font-medium text-blue-600 hover:text-blue-800 flex items-center justify-end gap-1 transition-colors">
                                Open Page in New Tab
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                            </a>
                        </div>
                    </div>

                    <h3 class="font-bold text-gray-900 border-b pb-2 pt-4">Search Optimization (SEO)</h3>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $mnpage->meta_title) }}" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Social Share Image (OG Image)</label>
                        <div class="mb-3 {{ $mnpage->meta_image ? '' : 'hidden' }}" id="meta_image_preview_container">
                            <img src="{{ $mnpage->meta_image ? asset($mnpage->meta_image) : '' }}" id="meta_image_preview" class="w-32 h-20 object-cover rounded-lg border border-gray-200">
                        </div>
                        <input type="file" name="meta_image" id="meta_image_input" accept="image/*" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Description</label>
                        <textarea name="meta_description" rows="4" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">{{ old('meta_description', $mnpage->meta_description) }}</textarea>
                    </div>
                </div>

                <!-- Settings -->
                <div class="space-y-6">
                    <h3 class="font-bold text-gray-900 border-b pb-2">Status & Layout</h3>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $mnpage->is_active) ? 'checked' : '' }} class="h-4 w-4 border-gray-300 rounded text-black focus:ring-black">
                            <span class="text-sm font-bold text-gray-900">Active Stage (Visible)</span>
                        </label>

                        <label class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 {{ $hasOtherHomePage ? 'opacity-60' : 'cursor-pointer' }}">
                            <input type="hidden" name="is_home" value="0">
                            <input type="checkbox" name="is_home" value="1" {{ old('is_home', $mnpage->is_home) ? 'checked' : '' }} {{ $hasOtherHomePage ? 'disabled' : '' }} class="mt-0.5 h-4 w-4 border-gray-300 rounded text-black focus:ring-black disabled:opacity-50">
                            <div>
                                <span class="text-sm font-bold text-gray-900 block">Primary Home Page</span>
                                @if($hasOtherHomePage)
                                    <span class="text-xs text-red-500 block mt-0.5 font-medium">Another page is currently set as the Primary Home Page.</span>
                                @endif
                            </div>
                        </label>
                    </div>

                    <div class="pt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Display Mode</label>
                        <select name="layout" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                            <option value="default" {{ $mnpage->layout === 'default' ? 'selected' : '' }}>System Default</option>
                            <option value="contained" {{ $mnpage->layout === 'contained' ? 'selected' : '' }}>Contained</option>
                            <option value="fluid" {{ $mnpage->layout === 'fluid' ? 'selected' : '' }}>Edge-to-Edge (Fluid)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-black text-white px-8 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors">
                    Update Page
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function updatePreviewLink(slug) {
        let isHome = document.querySelector('input[name="is_home"]:checked') ? true : false;
        let linkElement = document.getElementById('preview_link');
        let prefixElement = document.getElementById('slug_prefix');
        
        if (prefixElement) {
            prefixElement.innerText = isHome ? "{{ url('/') }}/" : "{{ url('/p') }}/";
        }
        
        if (linkElement) {
            linkElement.href = isHome ? "{{ url('/') }}" : ("{{ url('/p') }}/" + slug);
        }
    }

    document.getElementById('title').addEventListener('input', function () {
        let slug = this.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        document.getElementById('slug').value = slug;
        updatePreviewLink(slug);
    });

    document.getElementById('slug').addEventListener('input', function () {
        let slug = this.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        updatePreviewLink(slug);
    });

    let isHomeCheckbox = document.querySelector('input[name="is_home"][value="1"]');
    if (isHomeCheckbox) {
        isHomeCheckbox.addEventListener('change', function() {
            let slug = document.getElementById('slug').value;
            updatePreviewLink(slug);
        });
    }

    // Initialize on load
    updatePreviewLink(document.getElementById('slug').value);

    document.getElementById('meta_image_input').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            let reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('meta_image_preview').src = event.target.result;
                document.getElementById('meta_image_preview_container').classList.remove('hidden');
            }
            reader.readAsDataURL(e.target.files[0]);
        }
    });
</script>
@endpush
@endsection
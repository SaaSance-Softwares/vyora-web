@extends('layouts.admin')

@section('header', 'New Customise Page')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h1 class="text-xl font-bold">Add New Design Page</h1>
            <a href="{{ route('admin.online-store.mnpages.index') }}" class="text-sm text-gray-500 hover:text-black font-medium">Cancel</a>
        </div>

        <form action="{{ route('admin.online-store.mnpages.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Page Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="e.g. Summer Collection"
                        class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                </div>

                <!-- Slug -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">URL Slug</label>
                    <div class="flex rounded-lg shadow-sm">
                        <span id="slug_prefix" class="px-4 inline-flex items-center min-w-fit rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-sm text-gray-500 transition-all">
                            {{ url('/p') }}/
                        </span>
                        <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required placeholder="summer-collection"
                            class="w-full border border-gray-300 rounded-r-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                    </div>
                </div>

                <!-- SEO -->
                <div class="md:col-span-2 space-y-4">
                    <h3 class="font-bold text-gray-900 border-b pb-2 pt-4">Search Optimization</h3>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title') }}" placeholder="Defaults to Page Title" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Social Share Image (OG Image)</label>
                        <div class="mb-3 hidden" id="meta_image_preview_container">
                            <img src="" id="meta_image_preview" class="w-32 h-20 object-cover rounded-lg border border-gray-200">
                        </div>
                        <input type="file" name="meta_image" id="meta_image_input" accept="image/*" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Meta Description</label>
                        <textarea name="meta_description" rows="3" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">{{ old('meta_description') }}</textarea>
                    </div>
                </div>

                <!-- Settings -->
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t">
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 border-gray-300 rounded text-black focus:ring-black">
                            <span class="text-sm font-bold text-gray-900">Active Stage</span>
                        </label>
                        <label class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200 {{ $hasOtherHomePage ? 'opacity-60' : 'cursor-pointer' }}">
                            <input type="checkbox" name="is_home" value="1" {{ $hasOtherHomePage ? 'disabled' : '' }} class="mt-0.5 h-4 w-4 border-gray-300 rounded text-black focus:ring-black disabled:opacity-50">
                            <div>
                                <span class="text-sm font-bold text-gray-900 block">Set as Homepage</span>
                                @if($hasOtherHomePage)
                                    <span class="text-xs text-red-500 block mt-0.5 font-medium">Another page is currently set as the Primary Home Page.</span>
                                @endif
                            </div>
                        </label>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Layout Mode</label>
                        <select name="layout" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                            <option value="default">Global Default</option>
                            <option value="contained">Contained</option>
                            <option value="fluid">Fluid</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-black text-white px-8 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors">
                    Create Page
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function updateSlugPrefix() {
        let isHome = document.querySelector('input[name="is_home"]:checked') ? true : false;
        let prefixElement = document.getElementById('slug_prefix');
        if (prefixElement) {
            prefixElement.innerText = isHome ? "{{ url('/') }}/" : "{{ url('/p') }}/";
        }
    }

    document.getElementById('title').addEventListener('input', function () {
        let slug = this.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        document.getElementById('slug').value = slug;
    });

    let isHomeCheckbox = document.querySelector('input[name="is_home"][value="1"]');
    if (isHomeCheckbox) {
        isHomeCheckbox.addEventListener('change', updateSlugPrefix);
    }
    
    // Initialize
    updateSlugPrefix();

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
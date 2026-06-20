@extends('layouts.admin')

@section('header', 'Edit Collection')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h1 class="text-xl font-bold">Edit Collection: {{ $collection->name }}</h1>
            <a href="{{ route('admin.collections.index') }}" class="text-sm text-gray-500 hover:text-black">Cancel</a>
        </div>

        <form id="edit-collection-form" action="{{ route('admin.collections.update', $collection) }}" method="POST" class="p-6 space-y-8">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Info -->
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-900 border-b pb-2">General Information</h3>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Collection Name</label>
                        <input type="text" name="name" value="{{ old('name', $collection->name) }}" required class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">URL Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $collection->slug) }}" required class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="5" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black">{{ old('description', $collection->description) }}</textarea>
                    </div>
                </div>

                <!-- Products & Visibility -->
                <div class="space-y-6">
                    <div>
                        <h3 class="font-bold text-gray-900 border-b pb-2 mb-4">Storefront Visibility</h3>
                        <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $collection->is_active) ? 'checked' : '' }} class="h-4 w-4 border-gray-300 rounded text-black focus:ring-black">
                            <span class="text-sm font-bold text-gray-900">Show in Storefront</span>
                        </label>
                    </div>

                    <div>
                        <h3 class="font-bold text-gray-900 border-b pb-2 mb-4">Product Selection</h3>
                        <div class="relative mb-4">
                            <input type="text" id="product-search" placeholder="Search products..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-black focus:border-black">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <div id="search-results" class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg mt-1 shadow-lg max-h-60 overflow-y-auto hidden"></div>
                        </div>

                        <div id="selected-products" class="space-y-2 max-h-80 overflow-y-auto p-1">
                            @foreach($collection->products as $product)
                                <div class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-lg shadow-sm" data-id="{{ $product->id }}">
                                    <div class="flex items-center gap-3">
                                        <input type="hidden" name="products[]" value="{{ $product->id }}">
                                        <div class="w-10 h-10 bg-gray-50 rounded overflow-hidden">
                                            @if($product->preview_image)
                                                <img src="/{{ $product->preview_image }}" class="w-full h-full object-cover">
                                            @endif
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $product->name }}</span>
                                    </div>
                                    <button type="button" onclick="this.closest('div').remove()" class="text-red-400 hover:text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-black text-white px-8 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors">
                    Save Collection
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('product-search');
        const resultsContainer = document.getElementById('search-results');
        const selectedContainer = document.getElementById('selected-products');
        let timer;

        searchInput.addEventListener('input', function() {
            clearTimeout(timer);
            const query = this.value;
            if (query.length < 2) { resultsContainer.classList.add('hidden'); return; }

            timer = setTimeout(() => {
                fetch(`{{ route('admin.products.search') }}?query=${encodeURIComponent(query)}`)
                    .then(r => r.json()).then(products => {
                        resultsContainer.innerHTML = '';
                        if (products.length > 0) {
                            products.forEach(p => {
                                if (selectedContainer.querySelector(`[data-id="${p.id}"]`)) return;
                                const div = document.createElement('div');
                                div.className = 'p-3 hover:bg-gray-50 cursor-pointer text-sm font-medium flex items-center gap-3';
                                div.innerHTML = `<div class="w-8 h-8 bg-gray-100 rounded overflow-hidden">${p.image_url ? `<img src="${p.image_url}" class="w-full h-full object-cover">` : ''}</div><span>${p.name}</span>`;
                                div.onclick = () => {
                                    const row = document.createElement('div');
                                    row.className = 'flex items-center justify-between p-3 bg-white border border-gray-100 rounded-lg shadow-sm';
                                    row.dataset.id = p.id;
                                    row.innerHTML = `<div class="flex items-center gap-3"><input type="hidden" name="products[]" value="${p.id}"><div class="w-10 h-10 bg-gray-50 rounded overflow-hidden">${p.image_url ? `<img src="${p.image_url}" class="w-full h-full object-cover">` : ''}</div><span class="text-sm font-medium text-gray-900">${p.name}</span></div><button type="button" onclick="this.closest('div').remove()" class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>`;
                                    selectedContainer.appendChild(row);
                                    searchInput.value = '';
                                    resultsContainer.classList.add('hidden');
                                };
                                resultsContainer.appendChild(div);
                            });
                            resultsContainer.classList.remove('hidden');
                        } else { resultsContainer.classList.add('hidden'); }
                    });
            }, 300);
        });

        document.addEventListener('click', e => { if (!searchInput.contains(e.target)) resultsContainer.classList.add('hidden'); });
    });
</script>
@endpush
@endsection
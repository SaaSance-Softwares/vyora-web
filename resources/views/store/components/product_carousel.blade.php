@php
    $limit = $data['limit'] ?? 8;
    $type = $data['collection'] ?? 'new_arrivals';
    
    $query = \App\Models\Product::where('is_active', true);

    if ($type == 'new_arrivals') {
        $query->latest();
    } elseif ($type == 'best_sellers') {
        $query->inRandomOrder(); // Placeholder for actual sales data
    } elseif ($type == 'featured') {
        $query->where('on_sale', true); // Placeholder using on_sale
    }

    $products = $query->take($limit)->get();
@endphp

@if($products->count() > 0)
<div class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(!empty($data['title']))
            <h2 class="text-3xl font-extrabold font-heading text-gray-900 mb-8 text-center">{{ $data['title'] }}</h2>
        @endif

        <div class="relative group">
            <div class="flex overflow-x-auto space-x-6 pb-4 hide-scrollbar snap-x snap-mandatory" id="carousel-{{ Str::random(8) }}">
                @foreach($products as $product)
                    <div class="flex-none w-64 snap-center">
                        <div class="group relative">
                            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-md bg-gray-200 group-hover:opacity-75 lg:aspect-none lg:h-80">
                                @if($product->preview_image)
                                    <img src="{{ Storage::url($product->preview_image) }}" alt="{{ $product->name }}" class="h-full w-full object-cover object-center lg:h-full lg:w-full">
                                @else
                                    <div class="h-full w-full flex items-center justify-center bg-gray-100 text-gray-400">No Image</div>
                                @endif
                                
                                @if($product->on_sale)
                                    <span class="absolute top-2 left-2 bg-accent text-white text-xs px-2 py-1 rounded">SALE</span>
                                @endif
                            </div>
                            <div class="mt-4 flex justify-between">
                                <div>
                                    <h3 class="text-sm font-medium text-gray-700">
                                        <a href="#">
                                            <span aria-hidden="true" class="absolute inset-0"></span>
                                            {{ $product->name }}
                                        </a>
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">{{ $product->productType->name ?? 'Product' }}</p>
                                </div>
                                {{-- Price placeholder - assumes pricing logic exists or fetches from SKUs --}}
                                <p class="text-sm font-medium text-gray-900">
                                    {{-- ${{ number_format($product->skus->first()->price ?? 0, 2) }} --}}
                                    View
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endif

@extends('layouts.admin')

@section('header', 'Categories')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Category Hierarchy</h1>
        <a href="{{ route('admin.categories.create') }}" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 text-sm font-medium flex items-center gap-2">
            <span>+</span> New Category
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Total Categories</p>
            <p class="text-2xl font-bold">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Active</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Root Items</p>
            <p class="text-2xl font-bold text-blue-600">{{ number_format($stats['root']) }}</p>
        </div>
    </div>

    <!-- Tree View -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="mb-4 border-b border-gray-100 pb-4 flex justify-between items-center">
            <h3 class="text-sm font-bold text-gray-900 italic">Structure Visualization</h3>
            <span class="text-[10px] text-gray-400 font-medium italic">Drag to reorder hierarchy</span>
        </div>

        <ul class="space-y-3 sortable-list" id="root-categories" data-parent-id="">
            @foreach($categories as $category)
                @include('admin.categories.item', ['category' => $category, 'level' => 0])
            @endforeach
        </ul>

        @if($categories->isEmpty())
            <div class="py-12 text-center text-gray-400 italic">No categories defined yet.</div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initSortable() {
            var nestedSortables = [].slice.call(document.querySelectorAll('.sortable-list'));
            for (var i = 0; i < nestedSortables.length; i++) {
                new Sortable(nestedSortables[i], {
                    group: 'nested',
                    animation: 250,
                    handle: '.drag-handle',
                    onEnd: function () {
                        saveOrder();
                    }
                });
            }
        }

        initSortable();

        function getOrder(rootElement) {
            let result = [];
            Array.from(rootElement.children).forEach(li => {
                if (li.tagName === 'LI') {
                    let item = { id: li.dataset.id };
                    let childUl = li.querySelector('ul.sortable-list');
                    if (childUl) item.children = getOrder(childUl);
                    result.push(item);
                }
            });
            return result;
        }

        function saveOrder() {
            const order = getOrder(document.getElementById('root-categories'));
            fetch('{{ route('admin.categories.reorder') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ categories: order })
            });
        }
    });
</script>
@endpush
@endsection
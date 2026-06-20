<li data-id="{{ $category->id }}" class="group/item">
    <div class="flex items-center gap-4 py-3 px-4 bg-white border border-gray-100 rounded-lg shadow-sm hover:shadow-md hover:border-gray-300 transition-all">
        <div class="drag-handle cursor-move p-1 text-gray-300 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
        </div>
        
        <div class="flex-1 flex items-center justify-between">
            <div>
                <span class="text-sm font-bold text-gray-900">{{ $category->name }}</span>
                <span class="text-[10px] text-gray-400 font-mono ml-2 uppercase tracking-widest">/{{ $category->slug }}</span>
                @if(!$category->is_active)
                    <span class="ml-3 px-2 py-0.5 rounded-full bg-gray-100 text-gray-400 text-[8px] font-black uppercase tracking-widest border border-gray-200">Inactive</span>
                @else
                    <span class="ml-3 px-2 py-0.5 rounded-full bg-green-50 text-green-600 text-[8px] font-black uppercase tracking-widest border border-green-100 italic">Active</span>
                @endif
            </div>
            
            <div class="flex items-center gap-2 opacity-0 group-hover/item:opacity-100 transition-opacity">
                <a href="{{ route('admin.categories.edit', $category) }}" class="p-1.5 text-gray-400 hover:text-black transition-colors rounded-md bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </a>
                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category?')">
                    @csrf
                    @method('DELETE')
                    <button class="p-1.5 text-gray-400 hover:text-red-600 transition-colors rounded-md bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if($category->children->count() > 0)
        <ul class="sortable-list mt-3 ml-12 space-y-3" data-parent-id="{{ $category->id }}">
            @foreach($category->children as $child)
                @include('admin.categories.item', ['category' => $child, 'level' => $level + 1])
            @endforeach
        </ul>
    @endif
</li>
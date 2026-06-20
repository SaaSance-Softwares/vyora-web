@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Search Queries</h1>
            <p class="text-sm text-gray-500 mt-1">Monitor what your customers are searching for on your store.</p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" onclick="document.getElementById('deleteModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-red-600 text-sm font-semibold rounded-lg hover:bg-red-50 focus:ring-4 focus:ring-red-100 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                Delete by Date
            </button>
            <form action="{{ route('admin.online-store.marketing.search-queries.export') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white text-sm font-semibold rounded-lg hover:bg-gray-800 focus:ring-4 focus:ring-gray-200 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Export CSV
                </button>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500">
                <thead class="bg-gray-50/50 text-xs uppercase font-semibold text-gray-400 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Search Query</th>
                        <th class="px-6 py-4 text-center">Search Count</th>
                        <th class="px-6 py-4 text-center">Results Found</th>
                        <th class="px-6 py-4 text-right">Last Searched</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($queries as $query)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-900">{{ $query->query }}</span>
                            @if($query->normalized_query)
                                <span class="block text-xs text-gray-400 mt-0.5 font-mono">Matched: {{ $query->normalized_query }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                {{ $query->count }}x
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($query->results_count > 0)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                    {{ $query->results_count }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700">
                                    0
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-gray-400 whitespace-nowrap">
                            {{ $query->updated_at->format('M d, Y h:i A') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 mb-4">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900">No searches yet</h3>
                            <p class="text-sm text-gray-500 mt-1">When customers search for products, they will appear here.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($queries->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $queries->links() }}
        </div>
        @endif
    </div>

</div>

{{-- Delete Modal --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden" onclick="event.stopPropagation()">
        <form action="{{ route('admin.online-store.marketing.search-queries.deleteByDate') }}" method="POST">
            @csrf
            @method('DELETE')
            
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Delete Search Queries</h3>
                <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-500">Select a date range to permanently delete old search queries. This action cannot be undone.</p>
                
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" required class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" required class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                </div>
            </div>
            
            <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                    Delete Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

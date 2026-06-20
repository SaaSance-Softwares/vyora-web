@extends('layouts.admin')

@section('header', 'Collections')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Store Collections</h1>
        <a href="{{ route('admin.collections.create') }}" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 text-sm font-medium flex items-center gap-2">
            <span>+</span> Add Collection
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Total Collections</p>
            <p class="text-2xl font-bold">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Active Status</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($stats['active']) }}</p>
        </div>
    </div>

    <!-- List table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-4">Collection Title</th>
                        <th class="px-6 py-4">URL Slug</th>
                        <th class="px-6 py-4 text-center">Visibility</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm italic font-medium">
                    @forelse($collections as $collection)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 not-italic">{{ $collection->name }}</div>
                                <div class="text-[10px] text-gray-400 mt-0.5">{{ Str::limit($collection->description, 50) ?: 'No description' }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-500 font-mono text-xs">/{{ $collection->slug }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($collection->is_active)
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-bold uppercase rounded">Visible</span>
                                @else
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-400 text-[10px] font-bold uppercase rounded">Hidden</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.collections.edit', $collection) }}" class="p-2 text-gray-400 hover:text-black">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <form action="{{ route('admin.collections.destroy', $collection) }}" method="POST" onsubmit="return confirm('Archive this collection?')">
                                        @csrf @method('DELETE')
                                        <button class="p-2 text-gray-400 hover:text-red-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">No collections found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
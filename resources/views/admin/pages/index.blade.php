@extends('layouts.admin')

@section('header', 'Store Pages')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Storefront Customizer</h1>
        <a href="{{ route('admin.online-store.mnpages.create') }}" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 text-sm font-medium flex items-center gap-2">
            <span>+</span> Create New Page
        </a>
    </div>

    <!-- Page List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-4">Page Title</th>
                        <th class="px-6 py-4">URL Slug</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm font-medium">
                    @forelse($pages as $page)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $page->title }}</div>
                                @if($page->is_home)
                                    <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded font-bold uppercase">Home Page</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 font-mono text-xs">
                                        @if($page->is_home)
                                            /
                                        @else
                                            /p/{{ $page->slug }}
                                        @endif
                                    </span>
                                    <a href="{{ $page->is_home ? url('/') : url('/p/' . $page->slug) }}" target="_blank" class="text-gray-400 hover:text-blue-600 transition-colors" title="View Page">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($page->is_active)
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-bold uppercase rounded">Active</span>
                                @else
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-400 text-[10px] font-bold uppercase rounded">Draft</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.online-store.mnpages.design', $page) }}" target="_blank"
                                        class="px-3 py-1 bg-black text-white rounded text-[10px] font-bold uppercase hover:bg-gray-800 transition-colors">
                                        Design
                                    </a>
                                    <a href="{{ route('admin.online-store.mnpages.edit', $page) }}" class="text-gray-400 hover:text-black">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    @if(!$page->is_home)
                                        <form action="{{ route('admin.online-store.mnpages.destroy', $page) }}" method="POST" onsubmit="return confirm('Delete this page?')">
                                            @csrf @method('DELETE')
                                            <button class="text-gray-400 hover:text-red-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">No pages created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
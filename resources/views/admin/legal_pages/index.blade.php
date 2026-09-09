@extends('layouts.admin')

@section('title', 'DPDP & Legal Pages')
@section('header', 'DPDP Mandate & Legal Pages')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Legal & Policy Pages</h1>
        <p class="text-sm text-gray-500 mt-1">Manage DPDP compliance policies and custom legal pages.</p>
    </div>
    <a href="{{ route('admin.legal-pages.create') }}" class="px-4 py-2 bg-black text-white text-sm font-bold rounded-lg hover:bg-gray-800 transition-colors shadow-sm">
        + Create Custom Page
    </a>
</div>

<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Page Title</th>
                    <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Last Modified</th>
                    <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($pages as $page)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="p-4">
                        <div class="font-semibold text-gray-900">{{ $page->title }}</div>
                        <div class="text-xs text-gray-500 mt-0.5 font-mono">/policy/{{ $page->slug }}</div>
                    </td>
                    <td class="p-4">
                        @if($page->is_mandatory)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700 uppercase tracking-wider">DPDP Mandate</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600 uppercase tracking-wider">Custom</span>
                        @endif
                    </td>
                    <td class="p-4">
                        @if($page->is_published)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wider">Published</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 uppercase tracking-wider">Draft</span>
                        @endif
                    </td>
                    <td class="p-4">
                        <div class="text-sm font-medium text-gray-900">{{ $page->updated_at->format('d M Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $page->updated_at->format('h:i A') }}</div>
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.legal-pages.logs', $page) }}" class="px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-md transition-colors" title="View Edit History">
                                History
                            </a>
                            <a href="{{ route('admin.legal-pages.edit', $page) }}" class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                                Edit
                            </a>
                            @if(!$page->is_mandatory)
                                <form action="{{ route('admin.legal-pages.destroy', $page) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this custom page?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition-colors">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                
                @if($pages->isEmpty())
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-500 text-sm">
                        No pages found. Run the seeder to populate DPDP mandates.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection

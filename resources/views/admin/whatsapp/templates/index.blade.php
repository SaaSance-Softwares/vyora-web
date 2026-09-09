@extends('admin.whatsapp.layout')

@section('whatsapp_content')
<div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden">
    
    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
        <div>
            <h2 class="text-lg font-bold text-gray-900">Message Templates</h2>
            <p class="text-sm text-gray-500">Sync and manage your approved templates from Meta.</p>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('admin.whatsapp.templates.sync') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-colors shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Sync from Meta
                </button>
            </form>
            <a href="{{ route('admin.whatsapp.templates.create') }}" class="px-4 py-2 bg-[#25D366] text-white text-sm font-bold rounded-xl hover:bg-[#1DA851] transition-colors shadow-sm shadow-[#25D366]/20">
                + Create Template
            </a>
        </div>
    </div>

    @if(session('error'))
    <div class="px-6 py-4 bg-red-50 text-red-600 text-sm font-bold border-b border-red-100">
        {{ session('error') }}
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-500">
            <thead class="bg-white text-xs font-black uppercase tracking-widest text-gray-400 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4">Template Name</th>
                    <th class="px-6 py-4">Category</th>
                    <th class="px-6 py-4">Language</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($templates as $template)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-bold text-gray-900">{{ $template->name }}</span>
                            <div class="text-xs text-gray-400 mt-1 line-clamp-1 max-w-md">
                                @php
                                    $bodyComponent = collect($template->components)->firstWhere('type', 'BODY');
                                    echo $bodyComponent['text'] ?? 'No text body';
                                @endphp
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 bg-gray-100 text-gray-600 text-[10px] font-black uppercase tracking-wider rounded-md">
                                {{ $template->category }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-600">
                            {{ $template->language }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($template->status === 'APPROVED')
                                <span class="px-2.5 py-1 bg-[#25D366]/10 text-[#1DA851] text-[10px] font-black uppercase tracking-wider rounded-md border border-[#25D366]/20">
                                    Approved
                                </span>
                            @elseif($template->status === 'PENDING')
                                <span class="px-2.5 py-1 bg-yellow-100 text-yellow-700 text-[10px] font-black uppercase tracking-wider rounded-md border border-yellow-200">
                                    Pending
                                </span>
                            @else
                                <span class="px-2.5 py-1 bg-red-100 text-red-600 text-[10px] font-black uppercase tracking-wider rounded-md border border-red-200">
                                    {{ $template->status }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.whatsapp.templates.edit', $template) }}" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit Template">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form action="{{ route('admin.whatsapp.templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this template from Meta?');" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete from Meta">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900 mb-1">No Templates Found</h3>
                            <p class="text-sm text-gray-500">Sync templates from your Meta Business account.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

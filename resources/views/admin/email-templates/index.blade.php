@extends('layouts.admin')

@section('header', 'Email Templates')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Manage Email templates for automated notifications.</p>
        <a href="{{ route('admin.email-templates.create') }}" class="px-4 py-2 bg-black text-white text-sm font-bold rounded-xl hover:bg-gray-800 transition-colors shadow-sm">
            + Create Template
        </a>
    </div>

    @if(session('success'))
    <div class="mb-6 px-6 py-4 bg-green-50 text-green-600 text-sm font-bold border-b border-green-100 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500">
                <thead class="bg-gray-50/50 text-xs font-black uppercase tracking-widest text-gray-400 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Template Name</th>
                        <th class="px-6 py-4">Subject Preview</th>
                        <th class="px-6 py-4">Status Linked</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($templates as $template)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-900">{{ $template->name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900 font-medium truncate max-w-md">
                                    {{ Str::limit($template->subject, 60) }}
                                </div>
                                <div class="text-gray-500 text-xs mt-1 truncate max-w-md">
                                    {{ Str::limit(strip_tags($template->body), 80) }}
                                </div>
                                @if(count($template->variables) > 0)
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach($template->variables as $var)
                                            <span class="px-1.5 py-0.5 bg-gray-100 text-gray-600 text-[10px] font-mono rounded">
                                                {{ '{' . $var . '}' }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($template->orderStatuses->count() > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($template->orderStatuses as $status)
                                            <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs font-bold rounded flex items-center gap-1 border border-gray-200">
                                                @if($status->color)
                                                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $status->color }};"></span>
                                                @endif
                                                {{ $status->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs italic">Not linked</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.email-templates.edit', $template) }}" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <form action="{{ route('admin.email-templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this template?');" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    <p class="text-base font-bold text-gray-900">No Email templates found</p>
                                    <p class="text-sm mt-1">Get started by creating a new template.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">
        {{ $templates->links() }}
    </div>
</div>
@endsection

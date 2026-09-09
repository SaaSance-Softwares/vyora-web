@extends('layouts.admin')

@section('title', 'Page Edit History')
@section('header', 'History: ' . $legalPage->title)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.legal-pages.index') }}" class="text-sm font-semibold text-gray-500 hover:text-black flex items-center gap-1 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Pages
    </a>
</div>

<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Edit History</h3>
            <p class="text-sm text-gray-500 mt-1">Showing all modifications for <strong>{{ $legalPage->title }}</strong></p>
        </div>
        <a href="{{ route('admin.legal-pages.edit', $legalPage) }}" class="px-4 py-2 bg-black text-white text-sm font-bold rounded-lg hover:bg-gray-800 transition-colors shadow-sm">
            Edit Page
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider w-48">Date & Time</th>
                    <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">User</th>
                    <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                    <th class="p-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-right">Preview Content</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($logs as $log)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="p-4">
                        <div class="text-sm font-medium text-gray-900">{{ $log->created_at->format('d M Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $log->created_at->format('h:i:s A') }}</div>
                    </td>
                    <td class="p-4">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                                {{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}
                            </div>
                            <span class="text-sm text-gray-900 font-medium">{{ $log->user->name ?? 'System' }}</span>
                        </div>
                    </td>
                    <td class="p-4">
                        @if($log->action === 'created')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wider">Created</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 uppercase tracking-wider">Updated</span>
                        @endif
                    </td>
                    <td class="p-4 text-right">
                        <button onclick="document.getElementById('modal-{{ $log->id }}').classList.remove('hidden')" class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                            View Snapshot
                        </button>
                    </td>
                </tr>
                @endforeach
                
                @if($logs->isEmpty())
                <tr>
                    <td colspan="4" class="p-8 text-center text-gray-500 text-sm">
                        No history found for this page.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Modals for Snapshots -->
@foreach($logs as $log)
<div id="modal-{{ $log->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('modal-{{ $log->id }}').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Snapshot: {{ $log->created_at->format('d M Y h:i:s A') }}
                        </h3>
                        <div class="mt-4 border border-gray-200 rounded-lg p-6 bg-gray-50 max-h-[60vh] overflow-y-auto prose max-w-none text-sm">
                            {!! $log->content_snapshot !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('modal-{{ $log->id }}').classList.add('hidden')">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

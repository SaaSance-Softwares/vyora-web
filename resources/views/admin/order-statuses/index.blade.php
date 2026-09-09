@extends('layouts.admin')

@section('header', 'Order Statuses')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Manage order statuses and link automated notification templates.</p>
        <a href="{{ route('admin.order-statuses.create') }}" class="px-4 py-2 bg-black text-white text-sm font-bold rounded-xl hover:bg-gray-800 transition-colors shadow-sm">
            + Create Status
        </a>
    </div>

    @if(session('success'))
    <div class="mb-6 px-6 py-4 bg-green-50 text-green-600 text-sm font-bold border-b border-green-100 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 px-6 py-4 bg-red-50 text-red-600 text-sm font-bold border-b border-red-100 rounded-xl">
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-500">
                <thead class="bg-gray-50/50 text-xs font-black uppercase tracking-widest text-gray-400 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 w-16">Sort</th>
                        <th class="px-6 py-4">Status Name</th>
                        <th class="px-6 py-4">Color</th>
                        <th class="px-6 py-4">Linked Templates</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($statuses as $status)
                    <tr class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-4 text-gray-400 font-medium">
                            {{ $status->sort_order }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-900 flex items-center space-x-2">
                                <span>{{ $status->name }}</span>
                                @if($status->is_system)
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-[10px] rounded-md font-bold uppercase tracking-wider">System</span>
                                @endif
                                @if($status->fulfillment_type === 'QikInk')
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] rounded-md font-bold uppercase tracking-wider">QikInk</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <span class="w-4 h-4 rounded-full shadow-inner" style="background-color: {{ $status->color }}"></span>
                                <span class="text-xs font-mono text-gray-500">{{ $status->color }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col space-y-1">
                                @if($status->emailTemplate)
                                    <span class="inline-flex items-center space-x-1 text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded-md font-medium w-max border border-blue-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        <span>{{ $status->emailTemplate->name }}</span>
                                    </span>
                                @endif
                                
                                @if($status->smsTemplate)
                                    <span class="inline-flex items-center space-x-1 text-xs text-green-600 bg-green-50 px-2 py-1 rounded-md font-medium w-max border border-green-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        <span>{{ $status->smsTemplate->name }}</span>
                                    </span>
                                @endif

                                @if($status->whatsappTemplate)
                                    <span class="inline-flex items-center space-x-1 text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md font-medium w-max border border-emerald-100">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                        <span>{{ $status->whatsappTemplate->name }}</span>
                                    </span>
                                @endif

                                @if(!$status->emailTemplate && !$status->smsTemplate && !$status->whatsappTemplate)
                                    <span class="text-xs text-gray-400 italic">No templates linked</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('admin.order-statuses.edit', $status) }}" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                @if(!$status->is_system)
                                    <form action="{{ route('admin.order-statuses.destroy', $status) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this status?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p class="text-base font-bold text-gray-900">No order statuses found</p>
                                <p class="text-sm mt-1">Get started by creating a new status.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

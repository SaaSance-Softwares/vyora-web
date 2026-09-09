@extends('layouts.admin')
@section('header', 'Email Subscribers')
@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Newsletter Subscribers</h1>
            <p class="text-sm text-gray-400 mt-0.5">Manage users who opted in to your marketing newsletter.</p>
        </div>
        <a href="{{ route('admin.newsletter-subscribers.export') }}" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 text-sm font-bold flex items-center gap-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
            Export to CSV
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($subscribers->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">IP Address</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Subscribed At</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm font-medium">
                        @foreach($subscribers as $sub)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $sub->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500">
                                    {{ $sub->ip_address ?? '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $sub->status === 'subscribed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $sub->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500">
                                    {{ $sub->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('admin.newsletter-subscribers.destroy', $sub->id) }}" method="POST" onsubmit="return confirm('Delete this subscriber?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-red-500 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($subscribers->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $subscribers->links() }}
                </div>
            @endif
        @else
            <div class="p-12 text-center text-gray-400 italic">
                No subscribers found.
            </div>
        @endif
    </div>
</div>
@endsection

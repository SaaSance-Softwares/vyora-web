@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Delivery Timelines</h1>
            <p class="text-sm text-gray-500 mt-1">Manage delivery timelines available in your store.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add New Timeline Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Add New Timeline</h2>
                <form action="{{ route('admin.online-store.delivery-timelines.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Min Days *</label>
                            <input type="number" name="min_days" placeholder="e.g. 4" required min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-black focus:border-black outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Max Days *</label>
                            <input type="number" name="max_days" placeholder="e.g. 5" required min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-black focus:border-black outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Internal Note (Optional)</label>
                        <textarea name="internal_note" rows="2" placeholder="e.g. Standard delivery partner" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-black focus:border-black outline-none transition-all"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Show to User (Optional)</label>
                        <textarea name="show_to_user" rows="2" placeholder="e.g. Your order will arrive within 4-5 working days." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-black focus:border-black outline-none transition-all"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-black text-white rounded-lg px-4 py-2.5 text-sm font-bold uppercase tracking-wider hover:bg-gray-800 transition-colors">
                        Add Timeline
                    </button>
                </form>
            </div>
        </div>

        <!-- Timelines List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-[10px] uppercase tracking-wider text-gray-500">
                            <th class="p-4 font-bold">Timeline Details</th>
                            <th class="p-4 font-bold w-32">Status</th>
                            <th class="p-4 font-bold text-right w-24">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($timelines as $timeline)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="p-4">
                                    <div class="text-sm font-bold text-gray-900">{{ $timeline->min_days }} to {{ $timeline->max_days }} Days</div>
                                    @if($timeline->show_to_user)
                                        <div class="text-xs text-gray-600 mt-1"><span class="font-semibold text-gray-400">User text:</span> {{ $timeline->show_to_user }}</div>
                                    @endif
                                    @if($timeline->internal_note)
                                        <div class="text-xs text-gray-400 mt-0.5"><span class="font-semibold">Internal:</span> {{ $timeline->internal_note }}</div>
                                    @endif
                                </td>
                                <td class="p-4 align-middle">
                                    @if($timeline->is_default)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-green-100 text-green-700">
                                            Default
                                        </span>
                                    @else
                                        <form action="{{ route('admin.online-store.delivery-timelines.set-default', $timeline) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-xs font-medium text-blue-600 hover:text-blue-800 underline transition-colors">
                                                Set Default
                                            </button>
                                        </form>
                                    @endif
                                </td>
                                <td class="p-4 align-middle text-right flex justify-end items-center space-x-2">
                                    <a href="{{ route('admin.online-store.delivery-timelines.edit', $timeline) }}" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <form action="{{ route('admin.online-store.delivery-timelines.destroy', $timeline) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this timeline?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-8 text-center text-sm text-gray-500">
                                    No delivery timelines created yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

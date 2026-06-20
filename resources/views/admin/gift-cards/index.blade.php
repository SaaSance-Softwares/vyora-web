@extends('layouts.admin')
@section('header', 'Gift Cards')
@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gift Card Denominations</h1>
            <p class="text-sm text-gray-400 mt-0.5">Each denomination can be purchased by unlimited users — each gets a unique code.</p>
        </div>
        <a href="{{ route('admin.online-store.gift-cards.create') }}" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 text-sm font-bold flex items-center gap-2">
            <span>+</span> Add Denomination
        </a>
    </div>

    {{-- Stats Row --}}
    @php
        $totalIssued   = \App\Models\GiftCard::count();
        $activeIssued  = \App\Models\GiftCard::whereIn('status', ['active', 'partially_used'])->count();
        $totalRevenue  = \App\Models\GiftCard::whereNotNull('purchased_by')->sum('amount');
        $totalRedeemed = \App\Models\GiftCard::sum('used_amount');
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Denominations</p>
            <p class="text-2xl font-black text-gray-900 mt-1">{{ $templates->total() }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Cards Issued</p>
            <p class="text-2xl font-black text-green-600 mt-1">{{ $totalIssued }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Total Revenue</p>
            <p class="text-2xl font-black text-gray-900 mt-1">₹{{ number_format($totalRevenue, 0) }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Total Redeemed</p>
            <p class="text-2xl font-black text-indigo-600 mt-1">₹{{ number_format($totalRedeemed, 0) }}</p>
        </div>
    </div>

    {{-- Templates Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h2 class="text-xs font-black uppercase tracking-widest text-gray-500">Storefront Denominations</h2>
            <span class="text-[10px] text-gray-400 font-medium">One denomination → unlimited unique purchases</span>
        </div>
        @if($templates->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-4">Name / Denomination</th>
                            <th class="px-6 py-4">Validity</th>
                            <th class="px-6 py-4 text-center">Cards Sold</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm font-medium">
                        @foreach($templates as $template)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-black text-gray-900 text-base">₹{{ number_format($template->amount, 0) }}</div>
                                    @if($template->name)
                                        <div class="text-[11px] text-gray-400 mt-0.5">{{ $template->name }}</div>
                                    @endif
                                    @if($template->description)
                                        <div class="text-[11px] text-gray-400 italic mt-0.5">{{ Str::limit($template->description, 60) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($template->validity_days)
                                        <span class="text-xs text-gray-700 font-bold">{{ $template->validity_days }} days</span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">No Expiry</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-lg font-black text-gray-900">{{ $template->issued_cards_count }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <form action="{{ route('admin.online-store.gift-cards.toggle', $template->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[10px] font-black uppercase px-3 py-1 rounded-full {{ $template->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }} transition-colors">
                                            {{ $template->is_active ? 'Live' : 'Hidden' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-right flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.online-store.gift-cards.show', $template->id) }}"
                                        class="text-xs font-bold text-blue-600 hover:underline">View</a>
                                    @if($template->issued_cards_count === 0)
                                        <form action="{{ route('admin.online-store.gift-cards.destroy', $template->id) }}" method="POST" onsubmit="return confirm('Delete this denomination?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs font-bold text-red-500 hover:underline">Delete</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($templates->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $templates->links() }}
                </div>
            @endif
        @else
            <div class="p-12 text-center text-gray-400 italic">
                No gift card denominations yet.
                <a href="{{ route('admin.online-store.gift-cards.create') }}" class="text-black underline ml-1">Create one.</a>
            </div>
        @endif
    </div>

    {{-- Recent Issued Cards --}}
    @if($recentCards->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="text-xs font-black uppercase tracking-widest text-gray-500">Recently Issued Cards</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-4">Card #</th>
                        <th class="px-6 py-4">From Template</th>
                        <th class="px-6 py-4">Purchased By</th>
                        <th class="px-6 py-4">Assigned To</th>
                        <th class="px-6 py-4 text-right">Value</th>
                        <th class="px-6 py-4 text-right">Remaining</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm font-medium">
                    @foreach($recentCards as $card)
                        @php $badge = $card->status_badge; @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-mono font-bold text-gray-900 text-xs tracking-widest">{{ $card->card_number }}</div>
                                <div class="text-[10px] text-gray-400 mt-0.5">{{ $card->created_at->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-600">
                                {{ $card->template?->displayName() ?? ($card->type === 'direct' ? 'Direct (Admin)' : '—') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($card->purchaser)
                                    <div class="font-semibold text-gray-800 text-xs">{{ $card->purchaser->name }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $card->purchaser->email }}</div>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($card->recipient)
                                    <div class="font-semibold text-gray-800 text-xs">{{ $card->recipient->name }}</div>
                                @else
                                    <span class="text-gray-400 text-xs italic">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-gray-900">₹{{ number_format($card->amount, 0) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold {{ $card->remaining_amount > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                    ₹{{ number_format($card->remaining_amount, 0) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.online-store.gift-cards.cards.show', $card->id) }}"
                                    class="text-xs font-bold text-blue-600 hover:underline">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

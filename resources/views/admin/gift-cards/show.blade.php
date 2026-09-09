@extends('layouts.admin')
@section('header', 'Gift Card Denomination Details')
@section('content')
<div class="space-y-6 max-w-5xl">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.online-store.gift-cards.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 font-mono tracking-widest">{{ $giftCard->name ?? '₹' . number_format($giftCard->amount, 0) . ' Gift Card' }}</h1>
                <p class="text-xs text-gray-400 mt-0.5">Created {{ $giftCard->created_at->format('d M Y, h:i A') }}</p>
            </div>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-black {{ $giftCard->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
            {{ $giftCard->is_active ? 'Live on Store' : 'Hidden' }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Core Details --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-5">📄 Template Details</h2>
                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Amount</p>
                        <p class="text-2xl font-black text-gray-900 mt-0.5">₹{{ number_format($giftCard->amount, 0) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Name</p>
                        <p class="font-semibold text-gray-800 mt-0.5">{{ $giftCard->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Validity</p>
                        <p class="font-semibold text-gray-800 mt-0.5">{{ $giftCard->validity_days ? $giftCard->validity_days . ' days' : 'No Expiry' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Created By</p>
                        <p class="font-semibold text-gray-800 mt-0.5">{{ $giftCard->creator?->name ?? '—' }}</p>
                    </div>
                    @if($giftCard->description)
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Description</p>
                            <p class="text-sm text-gray-600 italic mt-0.5">{{ $giftCard->description }}</p>
                        </div>
                    @endif
                </div>

                <div class="mt-6 pt-6 border-t border-gray-100">
                    <form action="{{ route('admin.online-store.gift-cards.toggle', $giftCard->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2 bg-gray-100 text-gray-700 text-xs font-black rounded-lg hover:bg-gray-200 transition-all">
                            {{ $giftCard->is_active ? 'Hide from Store' : 'Publish to Store' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- RIGHT: Issued Cards --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h2 class="text-xs font-black uppercase tracking-widest text-gray-500">Issued Cards ({{ $issuedCards->total() }})</h2>
                </div>
                
                @if($issuedCards->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase">
                                <tr>
                                    <th class="px-6 py-4">Card #</th>
                                    <th class="px-6 py-4">Purchaser</th>
                                    <th class="px-6 py-4">Assigned To</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm font-medium">
                                @foreach($issuedCards as $card)
                                    @php $badge = $card->status_badge; @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-mono font-bold text-gray-900 text-xs tracking-widest">{{ $card->card_number }}</div>
                                            <div class="text-[10px] text-gray-400 mt-0.5">{{ $card->created_at->format('d M Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($card->purchaser)
                                                <div class="font-semibold text-gray-800 text-xs">{{ $card->purchaser->name }}</div>
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
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $badge['class'] }}">
                                                {{ $badge['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('admin.online-store.gift-cards.cards.show', $card->id) }}" class="text-xs font-bold text-blue-600 hover:underline">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($issuedCards->hasPages())
                        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                            {{ $issuedCards->links() }}
                        </div>
                    @endif
                @else
                    <div class="p-12 text-center text-gray-400 italic">
                        No cards have been issued from this template yet.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

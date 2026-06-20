@extends('layouts.admin')
@section('header', 'Gift Card Details')
@section('content')
<div class="space-y-6 max-w-5xl">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.online-store.gift-cards.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 font-mono tracking-widest">{{ $giftCard->card_number }}</h1>
                <p class="text-xs text-gray-400 mt-0.5">Created {{ $giftCard->created_at->format('d M Y, h:i A') }}</p>
            </div>
        </div>
        @php $badge = $giftCard->status_badge; @endphp
        <span class="px-3 py-1 rounded-full text-xs font-black {{ $badge['class'] }}">{{ $badge['label'] }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Core Details + Financial --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Core Details --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-5">📄 Core Details</h2>
                <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Card Number</p>
                        <p class="font-mono font-bold text-gray-900 mt-0.5">{{ $giftCard->card_number }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Type</p>
                        <p class="font-semibold text-gray-800 mt-0.5">{{ $giftCard->type === 'direct' ? 'Direct (Admin Created)' : 'Purchasable' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Created By</p>
                        <p class="font-semibold text-gray-800 mt-0.5">{{ $giftCard->creator?->name ?? '—' }}</p>
                        <p class="text-[10px] text-gray-400">{{ $giftCard->creator?->email }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Purchased By</p>
                        <p class="font-semibold text-gray-800 mt-0.5">{{ $giftCard->purchaser?->name ?? '—' }}</p>
                        <p class="text-[10px] text-gray-400">{{ $giftCard->purchaser?->email }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Assigned To</p>
                        @if($giftCard->recipient)
                            <p class="font-semibold text-gray-800 mt-0.5">{{ $giftCard->recipient->name }}</p>
                            <p class="text-[10px] text-gray-400">{{ $giftCard->recipient->email }}</p>
                        @else
                            <p class="text-gray-400 italic mt-0.5 text-xs">Unassigned</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Expires</p>
                        <p class="font-semibold text-gray-800 mt-0.5">{{ $giftCard->expires_at?->format('d M Y') ?? 'No Expiry' }}</p>
                    </div>
                </div>
            </div>

            {{-- Financial Tracking --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-5">💰 Financial Tracking</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Total Amount</p>
                        <p class="text-2xl font-black text-gray-900 mt-1">₹{{ number_format($giftCard->amount, 0) }}</p>
                    </div>
                    <div class="text-center p-4 bg-red-50 rounded-xl">
                        <p class="text-[10px] font-black uppercase tracking-widest text-red-400">Amount Used</p>
                        <p class="text-2xl font-black text-red-600 mt-1">₹{{ number_format($giftCard->used_amount, 0) }}</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 rounded-xl">
                        <p class="text-[10px] font-black uppercase tracking-widest text-green-500">Remaining</p>
                        <p class="text-2xl font-black text-green-600 mt-1">₹{{ number_format($giftCard->remaining_amount, 0) }}</p>
                    </div>
                </div>
                @if($giftCard->amount > 0)
                    <div class="mt-4">
                        <div class="flex justify-between text-[10px] text-gray-400 mb-1">
                            <span>Used</span>
                            <span>{{ round(($giftCard->used_amount / $giftCard->amount) * 100) }}%</span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-green-400 to-green-600 rounded-full transition-all"
                                style="width: {{ min(100, round(($giftCard->used_amount / $giftCard->amount) * 100)) }}%"></div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Transaction History --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-5">📊 Transaction History</h2>
                @if($giftCard->transactions->count() > 0)
                    <div class="space-y-3">
                        @foreach($giftCard->transactions as $txn)
                            <div class="flex items-start gap-4 p-3 rounded-xl bg-gray-50 border border-gray-100">
                                <div class="shrink-0">
                                    @if($txn->type === 'redemption')
                                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-sm">💸</div>
                                    @elseif($txn->type === 'creation')
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-sm">✨</div>
                                    @elseif($txn->type === 'assignment')
                                        <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-sm">👤</div>
                                    @elseif($txn->type === 'purchase')
                                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-sm">🛒</div>
                                    @elseif($txn->type === 'withdrawal')
                                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-sm">⬆️</div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <p class="text-sm font-semibold text-gray-800">{{ $txn->type_label }}</p>
                                        @if($txn->amount_used > 0)
                                            <span class="text-sm font-bold {{ $txn->type === 'redemption' || $txn->type === 'withdrawal' ? 'text-red-600' : 'text-green-600' }}">
                                                {{ $txn->type === 'redemption' || $txn->type === 'withdrawal' ? '−' : '' }}₹{{ number_format($txn->amount_used, 0) }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $txn->description }}</p>
                                    <div class="flex items-center gap-3 mt-1 text-[10px] text-gray-400">
                                        <span>{{ $txn->transaction_date->format('d M Y, h:i A') }}</span>
                                        @if($txn->order_id)
                                            <span>• Order #{{ $txn->order_id }}</span>
                                        @endif
                                        @if($txn->performer)
                                            <span>• {{ $txn->performer->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic text-center py-6">No transactions yet.</p>
                @endif
            </div>
        </div>

        {{-- RIGHT: Admin Actions --}}
        <div class="space-y-4">
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-5">⚡ Admin Actions</h2>

                @if(in_array($giftCard->status, ['active', 'partially_used', 'assigned']) && $giftCard->remaining_amount > 0)
                    <div class="p-4 bg-red-50 border border-red-100 rounded-xl mb-4">
                        <p class="text-xs font-bold text-red-700 mb-1">Withdraw Remaining Balance</p>
                        <p class="text-xs text-red-500 mb-3">This will move ₹{{ number_format($giftCard->remaining_amount, 0) }} out of the card and mark it as Withdrawn. This cannot be undone.</p>
                        <form action="{{ route('admin.online-store.gift-cards.withdraw', $giftCard->id) }}" method="POST"
                            onsubmit="return confirm('Withdraw ₹{{ $giftCard->remaining_amount }} from this card? This cannot be undone.')">
                            @csrf
                            <button type="submit" class="w-full py-2 bg-red-600 text-white text-xs font-black rounded-lg hover:bg-red-700 transition-all">
                                Withdraw ₹{{ number_format($giftCard->remaining_amount, 0) }}
                            </button>
                        </form>
                    </div>
                @elseif($giftCard->status === 'withdrawn')
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl">
                        <p class="text-xs text-gray-500 text-center">This card has been withdrawn.</p>
                    </div>
                @elseif($giftCard->status === 'used')
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl">
                        <p class="text-xs text-gray-500 text-center">This card has been fully used.</p>
                    </div>
                @else
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl">
                        <p class="text-xs text-gray-500 text-center italic">No actions available for this card's current state.</p>
                    </div>
                @endif
            </div>

            {{-- Card Code (encrypted display) --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-3">🔑 Redemption Code</h2>
                <p class="text-[10px] text-gray-400 mb-3">Reveal to assist a user with their redemption code. Do not share publicly.</p>
                <div class="relative">
                    <input type="password" value="{{ $giftCard->plain_code }}" id="codeField" readonly
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs font-mono text-gray-800 bg-gray-50 focus:outline-none">
                    <button type="button" onclick="toggleCode()"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-gray-400 hover:text-gray-700 font-medium">
                        Reveal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
function toggleCode() {
    const f = document.getElementById('codeField');
    f.type = f.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
@endsection

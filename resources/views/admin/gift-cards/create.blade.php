@extends('layouts.admin')
@section('header', 'Create Gift Card')
@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.online-store.gift-cards.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Create Gift Card</h1>
    </div>

    <div class="flex gap-2 p-1 bg-gray-100 rounded-lg w-fit mb-6">
        <button type="button" onclick="switchType('template')" id="btn-template" class="px-4 py-2 text-sm font-bold rounded-md transition-all bg-white text-black shadow-sm">🛒 Storefront Denomination</button>
        <button type="button" onclick="switchType('direct')" id="btn-direct" class="px-4 py-2 text-sm font-bold rounded-md transition-all text-gray-500 hover:text-gray-800">🎁 Direct Gift to User</button>
    </div>

    <form action="{{ route('admin.online-store.gift-cards.store') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="type" id="cardType" value="template">

        {{-- Amount --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-4">Gift Card Value</h2>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Amount (₹)</label>
            <div class="relative max-w-xs">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">₹</span>
                <input type="number" name="amount" value="{{ old('amount') }}" min="1" step="1"
                    class="w-full border border-gray-200 rounded-lg pl-7 pr-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all"
                    placeholder="e.g. 500" required>
            </div>
            @error('amount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Storefront template options --}}
        <div id="templateSection" class="bg-white rounded-lg border border-gray-200 p-6 space-y-5">
            <div>
                <h2 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-1">Storefront Settings</h2>
                <p class="text-xs text-gray-400">This denomination will appear on the Gift Cards page. Any number of users can purchase it — each purchase generates a unique redemption code.</p>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Display Name <span class="text-gray-300 font-normal normal-case">(optional)</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Classic ₹500 Gift Card"
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-gray-900 transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Short Description <span class="text-gray-300 font-normal normal-case">(optional)</span></label>
                <textarea name="description" rows="2" placeholder="e.g. Valid on all products. Shareable with anyone."
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-gray-900 transition-all resize-none">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Validity (Days) <span class="text-gray-300 font-normal normal-case">(leave blank = no expiry)</span></label>
                <input type="number" name="validity_days" value="{{ old('validity_days') }}" min="1"
                    class="w-48 border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-gray-900 transition-all"
                    placeholder="e.g. 365">
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs text-gray-500 bg-gray-50 rounded-lg p-4">
                <div>✓ Unlimited purchases per denomination</div>
                <div>✓ Each purchase = unique encrypted code</div>
                <div>✓ Buyer can gift it via WhatsApp / Email / Link</div>
                <div>✓ Recipient can redeem at checkout</div>
            </div>
        </div>

        {{-- Direct gift section --}}
        <div id="directSection" class="hidden bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Assign To User</h2>
            <p class="text-xs text-gray-400 mb-4">Card activates immediately for this user and is not listed on the storefront.</p>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Select User</label>
            <select name="assigned_to" id="assignedUser" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-gray-900 transition-all">
                <option value="">— Select a user —</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} — {{ $user->email }}{{ $user->phone ? ' (' . $user->phone . ')' : '' }}
                    </option>
                @endforeach
            </select>
            @error('assigned_to') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            <div class="flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-lg p-3 mt-4">
                <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <p class="text-xs text-amber-700">The assigned user <strong>cannot be deleted</strong> while they hold an active gift card.</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-black text-white text-sm font-bold rounded-lg hover:bg-gray-800 transition-all">Create Gift Card</button>
            <a href="{{ route('admin.online-store.gift-cards.index') }}" class="px-4 py-2.5 border border-gray-300 text-sm font-bold rounded-lg text-gray-600 hover:bg-gray-50 transition-all">Cancel</a>
        </div>
    </form>
</div>
@push('scripts')
<script>
function switchType(t) {
    document.getElementById('cardType').value = t;
    const tpl = document.getElementById('templateSection');
    const dir = document.getElementById('directSection');
    const au  = document.getElementById('assignedUser');
    const btnT = document.getElementById('btn-template');
    const btnD = document.getElementById('btn-direct');
    const active   = 'px-4 py-2 text-sm font-bold rounded-md transition-all bg-white text-black shadow-sm';
    const inactive = 'px-4 py-2 text-sm font-bold rounded-md transition-all text-gray-500 hover:text-gray-800';
    if (t === 'template') {
        tpl.classList.remove('hidden'); dir.classList.add('hidden');
        au.required = false; btnT.className = active; btnD.className = inactive;
    } else {
        tpl.classList.add('hidden'); dir.classList.remove('hidden');
        au.required = true; btnD.className = active; btnT.className = inactive;
    }
}
</script>
@endpush
@endsection

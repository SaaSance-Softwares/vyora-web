@extends('layouts.admin')
@section('header', 'Edit Gift Card Template')
@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.online-store.gift-cards.index') }}" class="text-gray-400 hover:text-gray-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Edit Gift Card Template</h1>
    </div>

    <form action="{{ route('admin.online-store.gift-cards.update', $giftCard) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Amount --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-4">Gift Card Value</h2>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Amount (₹)</label>
            <div class="relative max-w-xs">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold">₹</span>
                <input type="number" name="amount" value="{{ old('amount', $giftCard->amount) }}" min="1" step="1"
                    class="w-full border border-gray-200 rounded-lg pl-7 pr-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-all"
                    placeholder="e.g. 500" required>
            </div>
            @error('amount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Storefront template options --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6 space-y-5">
            <div>
                <h2 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-1">Storefront Settings</h2>
                <p class="text-xs text-gray-400">Update the display options for the storefront.</p>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Display Name <span class="text-gray-300 font-normal normal-case">(optional)</span></label>
                <input type="text" name="name" value="{{ old('name', $giftCard->name) }}" placeholder="e.g. Classic ₹500 Gift Card"
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-gray-900 transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Short Description <span class="text-gray-300 font-normal normal-case">(optional)</span></label>
                <textarea name="description" rows="2" placeholder="e.g. Valid on all products. Shareable with anyone."
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-gray-900 transition-all resize-none">{{ old('description', $giftCard->description) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Validity (Days) <span class="text-gray-300 font-normal normal-case">(leave blank = no expiry)</span></label>
                <input type="number" name="validity_days" value="{{ old('validity_days', $giftCard->validity_days) }}" min="1"
                    class="w-48 border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-gray-900 transition-all"
                    placeholder="e.g. 365">
            </div>

            {{-- Background Image --}}
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Card Background Image <span class="text-gray-300 font-normal normal-case">(optional)</span></label>
                <div class="flex items-start gap-4">
                    <label for="bg_image_input" class="flex flex-col items-center justify-center w-48 h-28 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-gray-400 transition-all bg-gray-50 relative overflow-hidden">
                        <img id="bg_image_preview" src="{{ $giftCard->background_image ? url($giftCard->background_image) : '' }}" alt="" class="absolute inset-0 w-full h-full object-cover {{ $giftCard->background_image ? '' : 'hidden' }} rounded-xl">
                        <div id="bg_image_placeholder" class="flex flex-col items-center justify-center gap-1 z-10 {{ $giftCard->background_image ? 'hidden' : '' }}">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Upload Image</span>
                        </div>
                        <input type="file" id="bg_image_input" name="background_image" accept="image/*" class="hidden" onchange="previewBgImage(this)">
                    </label>
                    <div class="text-xs text-gray-400 leading-relaxed pt-2">
                        <p>Supported: JPG, PNG, WebP, GIF</p>
                        <p>Max size: 5 MB</p>
                        <p class="mt-2 text-gray-500">If no image is set, the card will use the automatic premium color theme based on amount.</p>
                        @if($giftCard->background_image)
                            <p class="mt-2 text-emerald-600 font-bold">Image currently uploaded.</p>
                        @endif
                    </div>
                </div>
                @error('background_image') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-black text-white text-sm font-bold rounded-lg hover:bg-gray-800 transition-all">Save Changes</button>
            <a href="{{ route('admin.online-store.gift-cards.index') }}" class="px-4 py-2.5 border border-gray-300 text-sm font-bold rounded-lg text-gray-600 hover:bg-gray-50 transition-all">Cancel</a>
        </div>
    </form>
</div>
@push('scripts')
<script>
function previewBgImage(input) {
    const preview = document.getElementById('bg_image_preview');
    const placeholder = document.getElementById('bg_image_placeholder');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection

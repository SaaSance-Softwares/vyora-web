@extends('layouts.admin')

@section('header', 'PDP Page Design')

@section('content')
<div class="w-full">
    {{-- Breadcrumbs & Header --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">PDP Page Design</h1>
                <p class="mt-2 text-gray-500 font-medium">Customize the layout and visual elements of your Product Detail Pages.</p>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.online-store.pdp-settings.update') }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="space-y-8">
            {{-- ⚡ Mega Deal Card Customization --}}
            <div class="bg-white/80 backdrop-blur-lg shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl border border-gray-100 p-8 transition-all hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] mb-8">
                <div class="mb-8 border-b border-gray-100 pb-5">
                    <h3 class="text-xl font-bold text-gray-900 flex items-center">
                        <div class="bg-violet-50 p-2 rounded-lg mr-3 shadow-sm">
                            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                        Mega Deal Card (PDP)
                    </h3>
                    <p class="mt-2 text-sm text-gray-500 ml-11">Customize the coupon highlight card shown on every product page below the price.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                    {{-- Controls --}}
                    <div class="space-y-6">

                        {{-- Label --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Card Label</label>
                            <div class="group relative rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 focus-within:ring-2 focus-within:ring-inset focus-within:ring-violet-500 transition-all bg-white overflow-hidden">
                                <input type="text" name="mega_deal_label"
                                    id="mega_deal_label"
                                    value="{{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_label')->first()->value ?? 'Mega Deal' }}"
                                    class="block w-full border-0 py-2.5 px-4 text-gray-900 focus:ring-0 sm:text-sm bg-transparent"
                                    placeholder="e.g. Mega Deal, Hot Offer, Flash Sale"
                                    oninput="updatePreview()">
                            </div>
                            <p class="mt-1.5 text-xs text-gray-400">The title text shown on the card next to the icon.</p>
                        </div>

                        {{-- Icon --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Icon / Emoji</label>
                            <div class="group relative rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 focus-within:ring-2 focus-within:ring-inset focus-within:ring-violet-500 transition-all bg-white overflow-hidden">
                                <input type="text" name="mega_deal_icon"
                                    id="mega_deal_icon"
                                    value="{{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_icon')->first()->value ?? '⚡' }}"
                                    class="block w-full border-0 py-2.5 px-4 text-gray-900 focus:ring-0 sm:text-sm bg-transparent"
                                    placeholder="e.g. ⚡ 🔥 🎁 💥"
                                    oninput="updatePreview()">
                            </div>
                            <p class="mt-1.5 text-xs text-gray-400">Paste any emoji or short symbol to use as the icon.</p>
                        </div>

                        {{-- Badge Text --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Badge Text</label>
                            <div class="group relative rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 focus-within:ring-2 focus-within:ring-inset focus-within:ring-violet-500 transition-all bg-white overflow-hidden">
                                <input type="text" name="mega_deal_badge"
                                    id="mega_deal_badge"
                                    value="{{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_badge')->first()->value ?? 'Limited' }}"
                                    class="block w-full border-0 py-2.5 px-4 text-gray-900 focus:ring-0 sm:text-sm bg-transparent"
                                    placeholder="e.g. Limited, For You, Exclusive"
                                    oninput="updatePreview()">
                            </div>
                            <p class="mt-1.5 text-xs text-gray-400">Small pill badge shown on the right of the header row.</p>
                        </div>

                        {{-- Colors Row --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Background (from)</label>
                                <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                    <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                        <input type="color" name="mega_deal_bg_from" id="mega_deal_bg_from_color"
                                            value="{{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_bg_from')->first()->value ?? '#4f46e5' }}"
                                            class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                            oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updatePreview();">
                                    </div>
                                    <input type="text"
                                        value="{{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_bg_from')->first()->value ?? '#4f46e5' }}"
                                        class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updatePreview();">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Background (to)</label>
                                <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                    <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                        <input type="color" name="mega_deal_bg_to" id="mega_deal_bg_to_color"
                                            value="{{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_bg_to')->first()->value ?? '#7c3aed' }}"
                                            class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                            oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updatePreview();">
                                    </div>
                                    <input type="text"
                                        value="{{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_bg_to')->first()->value ?? '#7c3aed' }}"
                                        class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updatePreview();">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Text Color</label>
                                <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                    <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                        <input type="color" name="mega_deal_text_color" id="mega_deal_text_color_color"
                                            value="{{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_text_color')->first()->value ?? '#ffffff' }}"
                                            class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                            oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updatePreview();">
                                    </div>
                                    <input type="text"
                                        value="{{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_text_color')->first()->value ?? '#ffffff' }}"
                                        class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updatePreview();">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Sub-text Color</label>
                                <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                    <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                        <input type="color" name="mega_deal_subtext_color" id="mega_deal_subtext_color_color"
                                            value="{{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_subtext_color')->first()->value ?? '#c7d2fe' }}"
                                            class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                            oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updatePreview();">
                                    </div>
                                    <input type="text"
                                        value="{{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_subtext_color')->first()->value ?? '#c7d2fe' }}"
                                        class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updatePreview();">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Live Preview --}}
                    <div class="flex flex-col">
                        <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-4">Live Preview</p>
                        <div class="flex-1 flex items-center">
                            <div id="mega-deal-preview"
                                class="relative overflow-hidden rounded-xl px-4 py-3 w-full shadow-lg"
                                style="background: linear-gradient(to right, {{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_bg_from')->first()->value ?? '#4f46e5' }}, {{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_bg_to')->first()->value ?? '#7c3aed' }});">
                                
                                {{-- Badge in Preview --}}
                                <div id="preview-badge-container"
                                    class="absolute top-0 right-0 px-2 py-0.5 rounded-bl-lg bg-black/10 backdrop-blur-md border-l border-b border-white/10 {{ ($badgeValue = ($settings->get('mega_deal', collect())->where('key', 'mega_deal_badge')->first()->value ?? 'Limited')) ? '' : 'hidden' }}">
                                    <span id="preview-badge-text" style="color: {{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_text_color')->first()->value ?? '#ffffff' }}"
                                        class="text-[8px] font-black uppercase tracking-tighter opacity-80">{{ $badgeValue }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span id="preview-icon" class="text-base">{{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_icon')->first()->value ?? '⚡' }}</span>
                                        <div>
                                            <div class="flex items-baseline gap-1.5 flex-wrap">
                                                <span id="preview-label"
                                                    style="color: {{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_text_color')->first()->value ?? '#ffffff' }}; opacity: 0.7;"
                                                    class="text-[10px] font-black uppercase tracking-[0.15em]">{{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_label')->first()->value ?? 'Mega Deal' }}</span>
                                                <span id="preview-text" style="color: {{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_text_color')->first()->value ?? '#ffffff' }}"
                                                    class="text-sm font-black">Get at ₹719</span>
                                            </div>
                                            <p id="preview-subtext"
                                                style="color: {{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_subtext_color')->first()->value ?? '#c7d2fe' }}"
                                                class="text-[10px] font-semibold">10% off (₹80 saved) · ✨ Pre-Applied</p>
                                        </div>
                                    </div>
                                    <div class="shrink-0 flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 border"
                                        style="background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.2);">
                                        <span id="preview-code" style="color: {{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_text_color')->first()->value ?? '#ffffff' }}"
                                            class="text-[11px] font-black tracking-widest font-mono uppercase">SAVE10</span>
                                        <span id="preview-badge-inline"
                                            style="color: {{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_subtext_color')->first()->value ?? '#c7d2fe' }}"
                                            class="text-[9px]">⎘</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4 pb-4">
            <button type="submit"
                class="inline-flex items-center justify-center bg-gray-900 hover:bg-black text-white px-8 py-3.5 rounded-xl font-semibold shadow-[0_4px_14px_0_rgb(0,0,0,0.25)] hover:shadow-[0_6px_20px_rgba(0,0,0,0.23)] hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900">
                <svg class="w-5 h-5 mr-2 -ml-1 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save PDP Settings
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function updatePreview() {
        try {
            // Get values
            const label = document.getElementById('mega_deal_label')?.value || 'Mega Deal';
            const icon = document.getElementById('mega_deal_icon')?.value || '⚡';
            const badgeTextValue = document.getElementById('mega_deal_badge')?.value || '';
            const bgFrom = document.getElementById('mega_deal_bg_from_color')?.value || '#4f46e5';
            const bgTo = document.getElementById('mega_deal_bg_to_color')?.value || '#7c3aed';
            const textColor = document.getElementById('mega_deal_text_color_color')?.value || '#ffffff';
            const subtextColor = document.getElementById('mega_deal_subtext_color_color')?.value || '#c7d2fe';

            // Get elements
            const preview = document.getElementById('mega-deal-preview');
            const previewIcon = document.getElementById('preview-icon');
            const previewLabel = document.getElementById('preview-label');
            const previewText = document.getElementById('preview-text');
            const previewSubtext = document.getElementById('preview-subtext');
            const previewCode = document.getElementById('preview-code');
            const previewBadgeInline = document.getElementById('preview-badge-inline');
            const previewBadgeContainer = document.getElementById('preview-badge-container');
            const previewBadgeText = document.getElementById('preview-badge-text');

            // Update styles & content
            if (preview) preview.style.background = `linear-gradient(to right, ${bgFrom}, ${bgTo})`;
            if (previewIcon) previewIcon.innerText = icon;
            if (previewLabel) {
                previewLabel.innerText = label;
                previewLabel.style.color = textColor;
            }
            if (previewText) previewText.style.color = textColor;
            if (previewCode) previewCode.style.color = textColor;
            if (previewSubtext) previewSubtext.style.color = subtextColor;
            if (previewBadgeInline) previewBadgeInline.style.color = subtextColor;
            
            if (previewBadgeContainer && previewBadgeText) {
                if (badgeTextValue) {
                    previewBadgeContainer.classList.remove('hidden');
                    previewBadgeText.innerText = badgeTextValue;
                    previewBadgeText.style.color = textColor;
                } else {
                    previewBadgeContainer.classList.add('hidden');
                }
            }
        } catch (err) {
            console.error("Preview Update Error:", err);
        }
    }

    // Initialize preview on load
    window.addEventListener('load', updatePreview);

    // Global listener for the section for 100% reliability
    document.addEventListener('input', function(e) {
        if (e.target && (e.target.id?.startsWith('mega_deal_') || e.target.name?.startsWith('mega_deal_'))) {
            updatePreview();
        }
    });
</script>
@endpush
@endsection

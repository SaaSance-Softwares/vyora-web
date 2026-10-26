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
                                
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span id="preview-icon" class="text-xl leading-none">
                                            @if($iconValue = $settings->get('mega_deal', collect())->where('key', 'mega_deal_icon')->first()->value)
                                                @if(str_starts_with($iconValue, 'http'))
                                                    <img src="{{ $iconValue }}" class="h-6 w-auto object-contain" />
                                                @else
                                                    {{ $iconValue }}
                                                @endif
                                            @else
                                                ⚡
                                            @endif
                                        </span>
                                        <div>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span id="preview-badge-text"
                                                    style="color: {{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_text_color')->first()->value ?? '#ffffff' }}"
                                                    class="text-lg font-bold">{{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_badge')->first()->value ?? 'Get at' }}</span>
                                                <span id="preview-text" style="color: {{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_text_color')->first()->value ?? '#ffffff' }}"
                                                    class="text-lg font-extrabold">₹1,229</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="shrink-0">
                                        <span class="inline-block px-3 py-1.5 rounded-lg text-white text-xs font-bold whitespace-nowrap bg-[#2ecc71] shadow-sm">
                                            Extra ₹283 Off
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center justify-between">
                                    <span id="preview-subtext" style="color: {{ $settings->get('mega_deal', collect())->where('key', 'mega_deal_text_color')->first()->value ?? '#ffffff' }}; opacity: 0.8;" class="text-xs font-medium">With Pre-Applyed Coupon</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 📈 Price History Modal Customization --}}
        <div class="bg-white/80 backdrop-blur-lg shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl border border-gray-100 p-8 transition-all hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] mb-8">
            <div class="mb-8 border-b border-gray-100 pb-5">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <div class="bg-blue-50 p-2 rounded-lg mr-3 shadow-sm">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
                    </div>
                    Price History Modal
                </h3>
                <p class="mt-2 text-sm text-gray-500 ml-11">Customize the styling and layout of the Price History modal on the Product Detail Page.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                {{-- Controls --}}
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Modal Color</label>
                            <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                    <input type="color" name="price_history_bg_color" id="price_history_bg_color_input"
                                        value="{{ $settings->get('pdp', collect())->where('key', 'price_history_bg_color')->first()->value ?? '#ffffff' }}"
                                        class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updateHistoryPreview();">
                                </div>
                                <input type="text"
                                    value="{{ $settings->get('pdp', collect())->where('key', 'price_history_bg_color')->first()->value ?? '#ffffff' }}"
                                    class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                    oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updateHistoryPreview();">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Text Color</label>
                            <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                    <input type="color" name="price_history_text_color" id="price_history_text_color_input"
                                        value="{{ $settings->get('pdp', collect())->where('key', 'price_history_text_color')->first()->value ?? '#18181b' }}"
                                        class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updateHistoryPreview();">
                                </div>
                                <input type="text"
                                    value="{{ $settings->get('pdp', collect())->where('key', 'price_history_text_color')->first()->value ?? '#18181b' }}"
                                    class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                    oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updateHistoryPreview();">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Sales Price Line Color</label>
                            <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                    <input type="color" name="price_history_sales_color" id="price_history_sales_color_input"
                                        value="{{ $settings->get('pdp', collect())->where('key', 'price_history_sales_color')->first()->value ?? '#10b981' }}"
                                        class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updateHistoryPreview();">
                                </div>
                                <input type="text"
                                    value="{{ $settings->get('pdp', collect())->where('key', 'price_history_sales_color')->first()->value ?? '#10b981' }}"
                                    class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                    oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updateHistoryPreview();">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">MRP Line Color</label>
                            <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                    <input type="color" name="price_history_mrp_color" id="price_history_mrp_color_input"
                                        value="{{ $settings->get('pdp', collect())->where('key', 'price_history_mrp_color')->first()->value ?? '#ef4444' }}"
                                        class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updateHistoryPreview();">
                                </div>
                                <input type="text"
                                    value="{{ $settings->get('pdp', collect())->where('key', 'price_history_mrp_color')->first()->value ?? '#ef4444' }}"
                                    class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                    oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updateHistoryPreview();">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Show Trend Arrows (Red/Green)</label>
                        <select name="price_history_show_trend" id="price_history_show_trend" onchange="updateHistoryPreview()"
                            class="block w-full rounded-xl border-0 py-2.5 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-violet-500 sm:text-sm">
                            <option value="1" {{ ($settings->get('pdp', collect())->where('key', 'price_history_show_trend')->first()->value ?? '1') == '1' ? 'selected' : '' }}>Yes (Show Up/Down arrows)</option>
                            <option value="0" {{ ($settings->get('pdp', collect())->where('key', 'price_history_show_trend')->first()->value ?? '1') == '0' ? 'selected' : '' }}>No (Hide arrows)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Graph Position</label>
                        <select name="price_history_graph_position" id="price_history_graph_position" onchange="updateHistoryPreview()"
                            class="block w-full rounded-xl border-0 py-2.5 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-violet-500 sm:text-sm">
                            <option value="top" {{ ($settings->get('pdp', collect())->where('key', 'price_history_graph_position')->first()->value ?? 'top') == 'top' ? 'selected' : '' }}>Top (Graph above table)</option>
                            <option value="bottom" {{ ($settings->get('pdp', collect())->where('key', 'price_history_graph_position')->first()->value ?? 'top') == 'bottom' ? 'selected' : '' }}>Bottom (Graph below table)</option>
                        </select>
                    </div>
                </div>

                {{-- Live Preview --}}
                <div class="flex flex-col">
                    <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-4">Live Preview</p>
                    <div class="flex-1 flex items-center justify-center bg-gray-100 rounded-xl p-4 overflow-hidden relative" style="min-height: 400px;">
                        {{-- Modal Mockup --}}
                        <div id="preview-history-modal" class="rounded-2xl shadow-xl w-full max-w-sm flex flex-col border border-black/5" style="background-color: #ffffff;">
                            <div class="p-4 flex justify-between items-center border-b border-black/10">
                                <h3 class="text-sm font-semibold" id="preview-history-title" style="color: #18181b;">Price History (Last 30 Days)</h3>
                                <button class="p-1 rounded-full bg-gray-100"><svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                            </div>
                            
                            <div class="p-4 flex flex-col gap-4" id="preview-history-body">
                                {{-- Graph Mockup --}}
                                <div id="preview-history-graph" class="h-32 w-full relative">
                                    <svg viewBox="0 0 100 40" class="w-full h-full preserve-3d" preserveAspectRatio="none">
                                        {{-- MRP Line --}}
                                        <path id="preview-history-mrp-line" d="M0,10 Q25,10 50,20 T100,5" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" />
                                        {{-- Sales Line --}}
                                        <path id="preview-history-sales-line" d="M0,25 Q25,25 50,30 T100,20" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" />
                                    </svg>
                                </div>

                                {{-- Table Mockup --}}
                                <div id="preview-history-table" class="overflow-x-auto text-xs">
                                    <table class="w-full text-left">
                                        <thead>
                                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.2);">
                                                <th class="py-2" style="color: inherit; opacity: 0.7;">Date</th>
                                                <th class="py-2 text-right" style="color: inherit; opacity: 0.7;">MRP</th>
                                                <th class="py-2 text-right" style="color: inherit; opacity: 0.7;">Sales Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.1);">
                                                <td class="py-2">Sep 18</td>
                                                <td class="py-2 text-right">₹800 <span class="preview-trend-arrow ml-1 text-red-500">↑</span></td>
                                                <td class="py-2 font-medium text-right">₹740 <span class="preview-trend-arrow ml-1 text-red-500">↑</span></td>
                                            </tr>
                                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.1);">
                                                <td class="py-2">Sep 09</td>
                                                <td class="py-2 text-right">₹700</td>
                                                <td class="py-2 font-medium text-right">₹700</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 🎯 Savings Milestones Card Customization --}}
        <div class="bg-white/80 backdrop-blur-lg shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-2xl border border-gray-100 p-8 transition-all hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] mb-8">
            <div class="mb-8 border-b border-gray-100 pb-5">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <div class="bg-emerald-50 p-2 rounded-lg mr-3 shadow-sm">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    Savings Milestones (Cart & Checkout)
                </h3>
                <p class="mt-2 text-sm text-gray-500 ml-11">Customize the title, background colors, progress bar, checkpoint circles, and discount badges for the milestone savings timeline.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                {{-- Controls --}}
                <div class="space-y-6">
                    {{-- 1. Custom Name / Title --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Section Title / Custom Name</label>
                        <div class="group relative rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 focus-within:ring-2 focus-within:ring-inset focus-within:ring-emerald-500 transition-all bg-white overflow-hidden">
                            <input type="text" name="milestone_title" id="milestone_title"
                                value="{{ $allSettings['milestone_title'] ?? 'Savings Milestones' }}"
                                class="block w-full border-0 py-2.5 px-4 text-gray-900 focus:ring-0 sm:text-sm bg-transparent"
                                placeholder="e.g. Savings Milestones, Tier Rewards, Spend More Save More"
                                oninput="updateMilestonePreview()">
                        </div>
                        <p class="mt-1.5 text-xs text-gray-400">Header label shown above the milestone progress bar.</p>
                    </div>

                    {{-- 2. Section Background Type (Single vs Gradient) --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Background Style</label>
                        <select name="milestone_bg_type" id="milestone_bg_type" onchange="toggleMilestoneBgType(); updateMilestonePreview();"
                            class="block w-full rounded-xl border-0 py-2.5 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-emerald-500 sm:text-sm bg-white">
                            <option value="single" {{ ($allSettings['milestone_bg_type'] ?? 'single') === 'single' ? 'selected' : '' }}>Single Color</option>
                            <option value="gradient" {{ ($allSettings['milestone_bg_type'] ?? 'single') === 'gradient' ? 'selected' : '' }}>Gradient with Distance Color</option>
                        </select>
                    </div>

                    {{-- Single Color Picker (shown when single) --}}
                    <div id="milestone_single_bg_group" class="{{ ($allSettings['milestone_bg_type'] ?? 'single') === 'gradient' ? 'hidden' : '' }}">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Card Background Color</label>
                        <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                            <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                <input type="color" name="milestone_bg_color" id="milestone_bg_color"
                                    value="{{ $allSettings['milestone_bg_color'] ?? '#ffffff' }}"
                                    class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                    oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updateMilestonePreview();">
                            </div>
                            <input type="text"
                                value="{{ $allSettings['milestone_bg_color'] ?? '#ffffff' }}"
                                class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updateMilestonePreview();">
                        </div>
                    </div>

                    {{-- Gradient Colors (shown when gradient) --}}
                    <div id="milestone_gradient_bg_group" class="space-y-4 {{ ($allSettings['milestone_bg_type'] ?? 'single') === 'gradient' ? '' : 'hidden' }}">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Gradient Start (From)</label>
                                <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                    <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                        <input type="color" name="milestone_bg_from" id="milestone_bg_from"
                                            value="{{ $allSettings['milestone_bg_from'] ?? '#ffffff' }}"
                                            class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                            oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updateMilestonePreview();">
                                    </div>
                                    <input type="text"
                                        value="{{ $allSettings['milestone_bg_from'] ?? '#ffffff' }}"
                                        class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updateMilestonePreview();">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Gradient Distance / End (To)</label>
                                <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                    <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                        <input type="color" name="milestone_bg_to" id="milestone_bg_to"
                                            value="{{ $allSettings['milestone_bg_to'] ?? '#f0fdf4' }}"
                                            class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                            oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updateMilestonePreview();">
                                    </div>
                                    <input type="text"
                                        value="{{ $allSettings['milestone_bg_to'] ?? '#f0fdf4' }}"
                                        class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updateMilestonePreview();">
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Gradient Direction</label>
                            <select name="milestone_bg_direction" id="milestone_bg_direction" onchange="updateMilestonePreview()"
                                class="block w-full rounded-xl border-0 py-2.5 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-emerald-500 sm:text-sm bg-white">
                                <option value="to right" {{ ($allSettings['milestone_bg_direction'] ?? 'to bottom right') === 'to right' ? 'selected' : '' }}>Horizontal (Left to Right →)</option>
                                <option value="to bottom" {{ ($allSettings['milestone_bg_direction'] ?? 'to bottom right') === 'to bottom' ? 'selected' : '' }}>Vertical (Top to Bottom ↓)</option>
                                <option value="to bottom right" {{ ($allSettings['milestone_bg_direction'] ?? 'to bottom right') === 'to bottom right' ? 'selected' : '' }}>Diagonal (Top-Left to Bottom-Right ↘)</option>
                                <option value="to top right" {{ ($allSettings['milestone_bg_direction'] ?? 'to bottom right') === 'to top right' ? 'selected' : '' }}>Diagonal (Bottom-Left to Top-Right ↗)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Card Border Color --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Card Border Color</label>
                        <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                            <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                <input type="color" name="milestone_border_color" id="milestone_border_color"
                                    value="{{ $allSettings['milestone_border_color'] ?? '#f3f4f6' }}"
                                    class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                    oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updateMilestonePreview();">
                            </div>
                            <input type="text"
                                value="{{ $allSettings['milestone_border_color'] ?? '#f3f4f6' }}"
                                class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updateMilestonePreview();">
                        </div>
                    </div>

                    {{-- 3. Progress Bar Colors --}}
                    <div class="border-t border-gray-100 pt-5">
                        <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-4">Progress Bar Styling</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Progress Fill Color</label>
                                <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                    <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                        <input type="color" name="milestone_progress_color" id="milestone_progress_color"
                                            value="{{ $allSettings['milestone_progress_color'] ?? '#10b981' }}"
                                            class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                            oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updateMilestonePreview();">
                                    </div>
                                    <input type="text"
                                        value="{{ $allSettings['milestone_progress_color'] ?? '#10b981' }}"
                                        class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updateMilestonePreview();">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Track Background Color</label>
                                <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                    <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                        <input type="color" name="milestone_track_color" id="milestone_track_color"
                                            value="{{ $allSettings['milestone_track_color'] ?? '#f3f4f6' }}"
                                            class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                            oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updateMilestonePreview();">
                                    </div>
                                    <input type="text"
                                        value="{{ $allSettings['milestone_track_color'] ?? '#f3f4f6' }}"
                                        class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updateMilestonePreview();">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Checkpoint Circles --}}
                    <div class="border-t border-gray-100 pt-5">
                        <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-4">Checkpoint Circle Styling</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Checkbox (Unlocked) Color</label>
                                <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                    <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                        <input type="color" name="milestone_unlocked_circle_color" id="milestone_unlocked_circle_color"
                                            value="{{ $allSettings['milestone_unlocked_circle_color'] ?? '#059669' }}"
                                            class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                            oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updateMilestonePreview();">
                                    </div>
                                    <input type="text"
                                        value="{{ $allSettings['milestone_unlocked_circle_color'] ?? '#059669' }}"
                                        class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updateMilestonePreview();">
                                </div>
                                <p class="mt-1 text-xs text-gray-400">Background of completed/unlocked node.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Target Circle Color</label>
                                <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                    <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                        <input type="color" name="milestone_target_circle_color" id="milestone_target_circle_color"
                                            value="{{ $allSettings['milestone_target_circle_color'] ?? '#10b981' }}"
                                            class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                            oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updateMilestonePreview();">
                                    </div>
                                    <input type="text"
                                        value="{{ $allSettings['milestone_target_circle_color'] ?? '#10b981' }}"
                                        class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updateMilestonePreview();">
                                </div>
                                <p class="mt-1 text-xs text-gray-400">Ring & inner dot of the upcoming milestone.</p>
                            </div>
                        </div>
                    </div>

                    {{-- 5. xx% Off Badge Styling --}}
                    <div class="border-t border-gray-100 pt-5">
                        <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-4">Discount Badge (xx% OFF) Styling</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Unlocked Badge Background</label>
                                <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                    <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                        <input type="color" name="milestone_badge_bg" id="milestone_badge_bg"
                                            value="{{ $allSettings['milestone_badge_bg'] ?? '#ecfdf5' }}"
                                            class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                            oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updateMilestonePreview();">
                                    </div>
                                    <input type="text"
                                        value="{{ $allSettings['milestone_badge_bg'] ?? '#ecfdf5' }}"
                                        class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updateMilestonePreview();">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Unlocked Badge Text Color</label>
                                <div class="rounded-xl shadow-sm ring-1 ring-inset ring-gray-200 bg-white flex items-center p-1.5">
                                    <div class="h-9 w-10 shrink-0 rounded-lg overflow-hidden border border-gray-200 ring-1 ring-black/5">
                                        <input type="color" name="milestone_badge_text_color" id="milestone_badge_text_color"
                                            value="{{ $allSettings['milestone_badge_text_color'] ?? '#047857' }}"
                                            class="h-16 w-16 -m-3 cursor-pointer border-0 p-0"
                                            oninput="this.closest('.p-1.5').querySelector('input[type=text]').value = this.value; updateMilestonePreview();">
                                    </div>
                                    <input type="text"
                                        value="{{ $allSettings['milestone_badge_text_color'] ?? '#047857' }}"
                                        class="block w-full border-0 py-1.5 pl-3 text-gray-900 focus:ring-0 sm:text-xs uppercase font-mono bg-transparent"
                                        oninput="this.closest('.p-1.5').querySelector('input[type=color]').value = this.value; updateMilestonePreview();">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Live Preview --}}
                <div class="flex flex-col">
                    <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-4">Live Preview</p>
                    <div class="flex-1 flex items-center justify-center bg-gray-50/70 rounded-2xl p-6 border border-gray-100">
                        <div id="preview-milestone-card" class="w-full max-w-md rounded-2xl p-5 shadow-xs transition-all border">
                            {{-- Header --}}
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="flex h-4 w-4 items-center justify-center rounded-full bg-gray-900 text-white">
                                        <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                    </span>
                                    <span id="preview-milestone-title-text" class="text-[11px] font-bold text-gray-700 uppercase tracking-widest">
                                        Savings Milestones
                                    </span>
                                </div>
                            </div>

                            {{-- Dynamic Status --}}
                            <div class="mb-4">
                                <p class="text-xs text-gray-600 font-medium leading-relaxed">
                                    <span id="preview-milestone-unlocked-status" class="font-semibold text-emerald-700">✓ 15% OFF unlocked! </span>
                                    Add <strong class="font-bold text-gray-900">₹1,000</strong> more for <strong class="font-bold text-gray-900">20% OFF</strong>
                                </p>
                            </div>

                            {{-- Progress Track & Checkpoints: Perfectly center-aligned on vertical axis --}}
                            <div class="relative h-6 flex items-center mb-9 px-1">
                                {{-- Background Track --}}
                                <div id="preview-milestone-track" class="h-1.5 w-full rounded-full overflow-hidden">
                                    <div id="preview-milestone-fill" class="h-full rounded-full transition-all duration-300" style="width: 55%;"></div>
                                </div>

                                {{-- Node 1: Unlocked --}}
                                <div class="absolute top-0 h-6 flex items-center justify-center -translate-x-1/2 select-none" style="left: 14%;">
                                    <div class="preview-unlocked-node flex h-6 w-6 items-center justify-center rounded-full border-2 border-white shadow-xs">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div class="absolute top-7 flex flex-col items-center pointer-events-none whitespace-nowrap">
                                        <span class="text-[11px] font-semibold text-gray-900 leading-tight">₹2,999</span>
                                        <span class="preview-unlocked-badge text-[9px] font-bold tracking-tight px-1.5 py-0.5 rounded mt-1 border">10% OFF</span>
                                    </div>
                                </div>

                                {{-- Node 2: Unlocked & Current --}}
                                <div class="absolute top-0 h-6 flex items-center justify-center -translate-x-1/2 select-none" style="left: 50%;">
                                    <div class="preview-unlocked-node flex h-6 w-6 items-center justify-center rounded-full border-2 border-white shadow-xs">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div class="absolute top-7 flex flex-col items-center pointer-events-none whitespace-nowrap">
                                        <span class="text-[11px] font-semibold text-gray-900 leading-tight">₹3,000</span>
                                        <span class="preview-unlocked-badge text-[9px] font-bold tracking-tight px-1.5 py-0.5 rounded mt-1 border">15% OFF</span>
                                    </div>
                                </div>

                                {{-- Node 3: Target --}}
                                <div class="absolute top-0 h-6 flex items-center justify-center -translate-x-1/2 select-none" style="left: 86%;">
                                    <div id="preview-target-node" class="flex h-6 w-6 items-center justify-center rounded-full border-2 bg-white shadow-xs">
                                        <span id="preview-target-dot" class="h-2 w-2 rounded-full"></span>
                                    </div>
                                    <div class="absolute top-7 flex flex-col items-center pointer-events-none whitespace-nowrap">
                                        <span class="text-[11px] font-semibold text-gray-800 leading-tight">₹4,000</span>
                                        <span class="text-[9px] font-bold tracking-tight px-1.5 py-0.5 rounded mt-1 border bg-gray-50 text-gray-700 border-gray-200">20% OFF</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Add Items Row: Just above Code auto-applied --}}
                            <div class="mt-6 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                                <span class="text-gray-600 font-medium">
                                    Add <strong class="font-bold text-gray-900">₹1,000</strong> more for <strong class="font-bold text-gray-900">20% OFF</strong>
                                </span>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-gray-900 hover:text-black underline underline-offset-2">
                                    Add Items →
                                </span>
                            </div>

                            {{-- Bottom Status Confirmation --}}
                            <div class="pt-2.5 border-t border-gray-50 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2 text-gray-600">
                                    <span id="preview-bottom-dot" class="flex h-1.5 w-1.5 rounded-full"></span>
                                    <span>Code <span class="font-mono font-semibold text-gray-900">Milestone15</span> auto-applied</span>
                                </div>
                                <span id="preview-bottom-badge" class="preview-unlocked-badge text-[11px] font-bold px-2 py-0.5 rounded-full border">
                                    15% OFF
                                </span>
                            </div>
                                </span>
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
            const icon = document.getElementById('mega_deal_icon')?.value || '⚡';
            const badgeTextValue = document.getElementById('mega_deal_badge')?.value || 'Get at';
            const bgFrom = document.getElementById('mega_deal_bg_from_color')?.value || '#4f46e5';
            const bgTo = document.getElementById('mega_deal_bg_to_color')?.value || '#7c3aed';
            const textColor = document.getElementById('mega_deal_text_color_color')?.value || '#ffffff';

            // Get elements
            const preview = document.getElementById('mega-deal-preview');
            const previewIcon = document.getElementById('preview-icon');
            const previewText = document.getElementById('preview-text');
            const previewBadgeText = document.getElementById('preview-badge-text');
            const previewSubtext = document.getElementById('preview-subtext');

            // Update styles & content
            if (preview) preview.style.background = `linear-gradient(to right, ${bgFrom}, ${bgTo})`;
            if (previewIcon) {
                if (icon.startsWith('http')) {
                    previewIcon.innerHTML = `<img src="${icon}" class="h-6 w-auto object-contain" />`;
                } else {
                    previewIcon.innerText = icon;
                }
            }
            if (previewText) previewText.style.color = textColor;
            if (previewBadgeText) {
                previewBadgeText.innerText = badgeTextValue;
                previewBadgeText.style.color = textColor;
            }
            if (previewSubtext) previewSubtext.style.color = textColor;
        } catch (err) {
            console.error("Preview Update Error:", err);
        }
    }

    // Initialize preview on load
    window.addEventListener('load', function() {
        updatePreview();
        updateHistoryPreview();
    });

    function updateHistoryPreview() {
        try {
            const bgColor = document.getElementById('price_history_bg_color_input')?.value || '#ffffff';
            const textColor = document.getElementById('price_history_text_color_input')?.value || '#18181b';
            const mrpColor = document.getElementById('price_history_mrp_color_input')?.value || '#ef4444';
            const salesColor = document.getElementById('price_history_sales_color_input')?.value || '#10b981';
            const showTrend = document.getElementById('price_history_show_trend')?.value || '1';
            const position = document.getElementById('price_history_graph_position')?.value || 'top';

            const modal = document.getElementById('preview-history-modal');
            const title = document.getElementById('preview-history-title');
            const mrpLine = document.getElementById('preview-history-mrp-line');
            const salesLine = document.getElementById('preview-history-sales-line');
            const table = document.getElementById('preview-history-table');
            const body = document.getElementById('preview-history-body');
            const graph = document.getElementById('preview-history-graph');
            const arrows = document.querySelectorAll('.preview-trend-arrow');
            const tableRows = document.querySelectorAll('#preview-history-table tr');

            if (modal) modal.style.backgroundColor = bgColor;
            if (title) title.style.color = textColor;
            if (table) table.style.color = textColor;
            if (mrpLine) mrpLine.style.stroke = mrpColor;
            if (salesLine) salesLine.style.stroke = salesColor;

            // Make the table borders match the text color (light opacity)
            tableRows.forEach((row, i) => {
                if (i === 0) row.style.borderBottom = `1px solid ${textColor}33`; // 20% opacity for header
                else row.style.borderBottom = `1px solid ${textColor}1A`; // 10% opacity for body
            });

            arrows.forEach(el => {
                el.style.display = showTrend === '1' ? 'inline' : 'none';
            });

            if (position === 'bottom') {
                body.style.flexDirection = 'column-reverse';
            } else {
                body.style.flexDirection = 'column';
            }
        } catch (err) {
            console.error("History Preview Error:", err);
        }
    }

    function toggleMilestoneBgType() {
        const bgType = document.getElementById('milestone_bg_type')?.value || 'single';
        const singleGroup = document.getElementById('milestone_single_bg_group');
        const gradientGroup = document.getElementById('milestone_gradient_bg_group');
        if (bgType === 'gradient') {
            singleGroup?.classList.add('hidden');
            gradientGroup?.classList.remove('hidden');
        } else {
            singleGroup?.classList.remove('hidden');
            gradientGroup?.classList.add('hidden');
        }
    }

    function updateMilestonePreview() {
        try {
            const title = document.getElementById('milestone_title')?.value || 'Savings Milestones';
            const bgType = document.getElementById('milestone_bg_type')?.value || 'single';
            const bgColor = document.getElementById('milestone_bg_color')?.value || '#ffffff';
            const bgFrom = document.getElementById('milestone_bg_from')?.value || '#ffffff';
            const bgTo = document.getElementById('milestone_bg_to')?.value || '#f0fdf4';
            const bgDirection = document.getElementById('milestone_bg_direction')?.value || 'to bottom right';
            const borderColor = document.getElementById('milestone_border_color')?.value || '#f3f4f6';

            const progressColor = document.getElementById('milestone_progress_color')?.value || '#10b981';
            const trackColor = document.getElementById('milestone_track_color')?.value || '#f3f4f6';

            const unlockedCircleColor = document.getElementById('milestone_unlocked_circle_color')?.value || '#059669';
            const targetCircleColor = document.getElementById('milestone_target_circle_color')?.value || '#10b981';

            const badgeBg = document.getElementById('milestone_badge_bg')?.value || '#ecfdf5';
            const badgeTextColor = document.getElementById('milestone_badge_text_color')?.value || '#047857';

            // Card container
            const card = document.getElementById('preview-milestone-card');
            if (card) {
                card.style.background = bgType === 'gradient'
                    ? `linear-gradient(${bgDirection}, ${bgFrom}, ${bgTo})`
                    : bgColor;
                card.style.borderColor = borderColor;
            }

            // Title
            const titleEl = document.getElementById('preview-milestone-title-text');
            if (titleEl) titleEl.innerText = title;

            // Track & Fill
            const track = document.getElementById('preview-milestone-track');
            if (track) track.style.backgroundColor = trackColor;

            const fill = document.getElementById('preview-milestone-fill');
            if (fill) fill.style.backgroundColor = progressColor;

            // Checkpoint Nodes (unlocked)
            document.querySelectorAll('.preview-unlocked-node').forEach(node => {
                node.style.backgroundColor = unlockedCircleColor;
            });

            // Target node & dot
            const targetNode = document.getElementById('preview-target-node');
            if (targetNode) targetNode.style.borderColor = targetCircleColor;

            const targetDot = document.getElementById('preview-target-dot');
            if (targetDot) targetDot.style.backgroundColor = targetCircleColor;

            // Badges
            document.querySelectorAll('.preview-unlocked-badge').forEach(badge => {
                badge.style.backgroundColor = badgeBg;
                badge.style.color = badgeTextColor;
                badge.style.borderColor = `${badgeTextColor}40`;
            });

            // Bottom indicators
            const bottomDot = document.getElementById('preview-bottom-dot');
            if (bottomDot) bottomDot.style.backgroundColor = progressColor;

            const unlockedStatus = document.getElementById('preview-milestone-unlocked-status');
            if (unlockedStatus) unlockedStatus.style.color = badgeTextColor;

        } catch (err) {
            console.error("Milestone Preview Error:", err);
        }
    }

    // Initialize all previews on load
    window.addEventListener('load', function() {
        updatePreview();
        updateHistoryPreview();
        toggleMilestoneBgType();
        updateMilestonePreview();
    });

    // Global listener for live inputs
    document.addEventListener('input', function(e) {
        if (!e.target) return;
        if (e.target.id?.startsWith('mega_deal_') || e.target.name?.startsWith('mega_deal_')) {
            updatePreview();
        }
        if (e.target.id?.startsWith('price_history_') || e.target.name?.startsWith('price_history_')) {
            updateHistoryPreview();
        }
        if (e.target.id?.startsWith('milestone_') || e.target.name?.startsWith('milestone_')) {
            updateMilestonePreview();
        }
    });
</script>
@endpush
@endsection

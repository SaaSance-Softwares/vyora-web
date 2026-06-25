@extends('layouts.admin')

@section('header', 'Product Analytics')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.products.index') }}" class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center shrink-0 hover:bg-gray-50 transition-colors shadow-sm">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div class="flex items-center gap-4">
                @if($product->preview_image)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-12 h-12 rounded-lg object-cover border border-gray-200">
                @else
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-gray-900">{{ $product->name }}</h1>
                    <p class="text-sm text-gray-500 font-medium">SKU: {{ $product->slug }}</p>
                </div>
            </div>
        </div>
        <a href="{{ route('admin.products.edit', $product) }}" class="px-5 py-2.5 bg-white border border-gray-200 hover:border-gray-900 hover:bg-gray-50 text-gray-700 text-sm font-bold rounded-xl transition-all shadow-sm">
            Edit Product
        </a>
    </div>

    {{-- Primary KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Total Views --}}
        <div class="bg-white p-6 border border-gray-200 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="absolute right-0 top-0 w-16 h-16 bg-blue-50 rounded-bl-full flex items-start justify-end p-3 opacity-50">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </div>
            <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Total Views</p>
            <p class="text-3xl font-black text-gray-900">{{ number_format($viewCount) }}</p>
        </div>

        {{-- Total Purchases --}}
        <div class="bg-white p-6 border border-gray-200 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="absolute right-0 top-0 w-16 h-16 bg-emerald-50 rounded-bl-full flex items-start justify-end p-3 opacity-50">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Purchases</p>
            <p class="text-3xl font-black text-emerald-600">{{ number_format($purchaseCount) }}</p>
        </div>

        {{-- Conversion Rate --}}
        <div class="bg-white p-6 border border-gray-200 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="absolute right-0 top-0 w-16 h-16 bg-purple-50 rounded-bl-full flex items-start justify-end p-3 opacity-50">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Conversion Rate</p>
            <p class="text-3xl font-black text-gray-900">{{ $conversionRate }}%</p>
        </div>

        {{-- Total Revenue --}}
        <div class="bg-white p-6 border border-gray-200 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="absolute right-0 top-0 w-16 h-16 bg-amber-50 rounded-bl-full flex items-start justify-end p-3 opacity-50">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Net Revenue</p>
            <p class="text-3xl font-black text-gray-900">₹{{ number_format($revenue, 2) }}</p>
        </div>
    </div>

    {{-- Secondary KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        {{-- Returns --}}
        <div class="bg-white p-6 border border-gray-200 rounded-2xl shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Return & Cancellations</p>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl font-black text-red-500">{{ number_format($returnCount) }}</p>
                    <p class="text-sm font-semibold text-gray-500">items returned</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Return Rate</p>
                <p class="text-xl font-black text-gray-900">{{ $returnRate }}%</p>
            </div>
        </div>

        {{-- Insights Box --}}
        <div class="bg-gray-50 p-6 border border-gray-200 rounded-2xl shadow-sm">
            <h3 class="text-sm font-black uppercase tracking-widest text-gray-900 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Product Insights
            </h3>
            <ul class="space-y-3 text-sm text-gray-600 font-medium leading-relaxed">
                <li class="flex gap-2">
                    <span class="text-emerald-500 shrink-0">●</span>
                    @if($conversionRate > 5)
                        This product has a healthy conversion rate above industry average.
                    @else
                        Conversion rate is below 5%. Consider updating product images or description to improve sales.
                    @endif
                </li>
                <li class="flex gap-2">
                    <span class="text-amber-500 shrink-0">●</span>
                    @if($returnRate > 10)
                        High return rate detected! Check recent reviews to identify sizing or quality issues.
                    @else
                        Return rate is within a manageable threshold.
                    @endif
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection

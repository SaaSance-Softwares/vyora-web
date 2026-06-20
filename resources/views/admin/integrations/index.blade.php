@extends('layouts.admin')
@section('header', 'Integrations')

@section('content')
<div class="space-y-8">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Integrations</h1>
        <p class="text-sm text-gray-400 mt-1">Connect your store to third-party payment, email, and accounting services.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($integrations as $slug => $data)
        @php
            $isActive = $data['status'] === 'active';
            $isSoon   = $data['status'] === 'soon';
            $isEnabled = $data['enabled'] ?? false;
            $mode      = $data['mode'] ?? 'test';
        @endphp

        @if($isActive)
        <a href="{{ route('admin.online-store.integrations.show', $slug) }}"
            class="group relative bg-white border border-gray-200 rounded-2xl p-6 hover:border-gray-900 hover:shadow-lg transition-all duration-200 block">
        @else
        <div class="group relative bg-white border border-gray-200 rounded-2xl p-6 opacity-60 cursor-not-allowed">
        @endif

            @if($isSoon)
            <div class="absolute top-4 right-4">
                <span class="text-[10px] font-black uppercase tracking-widest bg-gray-100 text-gray-400 px-2 py-0.5 rounded-full">Coming Soon</span>
            </div>
            @endif

            @if($isActive && $isEnabled)
            <div class="absolute top-4 right-4">
                <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full {{ $mode === 'live' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $mode === 'live' ? '● Live' : '● Test' }}
                </span>
            </div>
            @endif

            <div class="flex items-center gap-4 mb-4">
                {{-- Icon --}}
                @if($slug === 'razorpay')
                <div class="w-14 h-14 rounded-xl bg-[#072654] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <svg class="w-8 h-8" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 32L20 8l12 24H8z" fill="#3395FF" opacity=".9"/>
                        <path d="M14 24l6-12 6 12h-12z" fill="white" opacity=".5"/>
                    </svg>
                </div>
                @elseif($slug === 'smtp')
                <div class="w-14 h-14 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                @elseif($slug === 'whatsapp')
                <div class="w-14 h-14 rounded-xl bg-[#e8faf0] flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.457L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.37 9.864-9.799.002-2.623-1.023-5.086-2.885-6.948C16.59 2.016 14.1 1.01 11.999 1.01c-5.438 0-9.863 4.372-9.867 9.802-.001 1.73.457 3.41 1.32 4.905l-1.002 3.66 3.755-.985zm12.39-5.421c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                    </svg>
                </div>
                @elseif($slug === 'google-analytics')
                <div class="w-14 h-14 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-[#E37400]" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-5h2v5zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/>
                    </svg>
                </div>
                @elseif($slug === 'meta-pixel')
                <div class="w-14 h-14 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-[#0668E1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                </div>
                @elseif($slug === 'bing-webmaster')
                <div class="w-14 h-14 rounded-xl bg-teal-50 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-[#008373]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                @elseif($slug === 'google-search-console')
                <div class="w-14 h-14 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"/>
                    </svg>
                </div>
                @elseif($slug === 'google-merchant')
                <div class="w-14 h-14 rounded-xl bg-yellow-50 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                @elseif($slug === 'ondc')
                <div class="w-14 h-14 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                @elseif($slug === 'social-login')
                <div class="w-14 h-14 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                @elseif($slug === 'twilio')
                <div class="w-14 h-14 rounded-xl bg-pink-50 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-[#F22F46]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                @elseif($slug === 'slack')
                <div class="w-14 h-14 rounded-xl bg-violet-50 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-[#4A154B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </div>
                @else
                <div class="w-14 h-14 rounded-xl bg-gray-50 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                @endif

                <div>
                    <h3 class="font-bold text-gray-900 text-base">{{ $data['name'] }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $data['description'] }}</p>
                </div>
            </div>

            @if($isActive)
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <span class="text-xs font-semibold {{ $isEnabled ? 'text-green-600' : 'text-gray-400' }}">
                    {{ $isEnabled ? 'Connected' : 'Not configured' }}
                </span>
                <span class="text-xs font-bold text-gray-400 group-hover:text-gray-900 transition-colors flex items-center gap-1">
                    Configure
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            </div>
            @endif

        @if($isActive)
        </a>
        @else
        </div>
        @endif
        @endforeach
    </div>
</div>
@endsection

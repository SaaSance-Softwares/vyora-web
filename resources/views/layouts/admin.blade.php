<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} Admin</title>
    
    @php
        $faviconUrl = \Illuminate\Support\Facades\Cache::remember('site_favicon', 86400, function() {
            return \App\Models\ThemeSetting::where('group', 'logos')->where('key', 'favicon')->value('value');
        });
    @endphp
    @if($faviconUrl)
        <link rel="icon" href="{{ asset($faviconUrl) }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
    </style>
    @stack('styles')
</head>

<body class="bg-gray-100 antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col fixed h-full z-40">
            <div class="p-6 border-b border-gray-100 flex-shrink-0">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                    <div class="bg-black p-2 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight">Admin</span>
                </a>
            </div>

            <nav class="flex-1 overflow-y-auto p-4 space-y-1 custom-scrollbar">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span class="text-sm">Dashboard</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-3 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Inventory</p>
                </div>

                <a href="{{ route('admin.products.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.products.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span class="text-sm">Products</span>
                </a>

                <a href="{{ route('admin.categories.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.categories.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    <span class="text-sm">Categories</span>
                </a>

                <a href="{{ route('admin.collections.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.collections.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span class="text-sm">Collections</span>
                </a>

                <a href="{{ route('admin.attributes.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.attributes.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    <span class="text-sm">Attributes</span>
                </a>

                <a href="{{ route('admin.size-charts.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.size-charts.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="text-sm">Size Charts</span>
                </a>

                <a href="{{ route('admin.upload') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.upload') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    <span class="text-sm">Bulk Upload</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-3 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Marketing & Sales</p>
                </div>

                <a href="{{ route('admin.customers.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.customers.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="text-sm">Customers</span>
                </a>



                <a href="{{ route('admin.orders.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.orders.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span class="text-sm">Orders</span>
                </a>

                <a href="{{ route('admin.reviews.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.reviews.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                    <span class="text-sm">Reviews</span>
                </a>

                <a href="{{ route('admin.online-store.coupons.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.online-store.coupons.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                    <span class="text-sm">Coupons</span>
                </a>

                <a href="{{ route('admin.online-store.gift-cards.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.online-store.gift-cards.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7M9 1v2m6-2v2"></path></svg>
                    <span class="text-sm">Gift Cards</span>
                </a>

                @if(\App\Models\ThemeSetting::where('group', 'integration.whatsapp')->where('key', 'enabled')->value('value') === '1')
                <a href="{{ route('admin.whatsapp.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.whatsapp.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"></path></svg>
                    <span class="text-sm">WhatsApp Chat</span>
                </a>
                @endif

                <a href="{{ route('admin.online-store.marketing.search-queries.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.online-store.marketing.search-queries.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <span class="text-sm">Search Queries</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-3 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Customise Store</p>
                </div>

                <a href="{{ route('admin.online-store.mnpages.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.online-store.mnpages.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span class="text-sm">Pages</span>
                </a>

                <a href="{{ route('admin.online-store.product-card-settings.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.online-store.product-card-settings.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path></svg>
                    <span class="text-sm">Product Card</span>
                </a>

                <a href="{{ route('admin.online-store.pdp-settings.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.online-store.pdp-settings.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    <span class="text-sm">PDP Settings</span>
                </a>

                <a href="{{ route('admin.online-store.navbar-settings.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.online-store.navbar-settings.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    <span class="text-sm">Menu / Nav Bar</span>
                </a>

                <a href="{{ route('admin.online-store.policy-settings.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.online-store.policy-settings.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="text-sm">Policies</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-3 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Settings</p>
                </div>

                <a href="{{ route('admin.online-store.general-settings.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.online-store.general-settings.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="text-sm">Store Settings</span>
                </a>

                <a href="{{ route('admin.online-store.delivery-pins.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.online-store.delivery-pins.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="text-sm">Delivery PINs</span>
                </a>

                <a href="{{ route('admin.online-store.auth-settings.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.online-store.auth-settings.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 21a11.955 11.955 0 01-9.618-7.016m19.236 0a11.955 11.955 0 00-19.236 0M12 11V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <span class="text-sm">Auth Settings</span>
                </a>

                <a href="{{ route('admin.online-store.tax-shipping.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.online-store.tax-shipping.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    <span class="text-sm">Tax & Shipping</span>
                </a>

                <a href="{{ route('admin.online-store.integrations.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.online-store.integrations.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path></svg>
                    <span class="text-sm">Integrations</span>
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-3 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Admin Setting</p>
                </div>

                <a href="{{ route('admin.settings.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.settings.index') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span class="text-sm">Admin Setting</span>
                </a>

                <a href="{{ route('admin.settings.users') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.settings.users') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="text-sm">Admin User Setting</span>
                </a>

                <a href="{{ route('admin.settings.vyora') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.settings.vyora') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm">Project Vyora</span>
                </a>

                <a href="{{ route('admin.settings.update.index') }}" class="flex items-center space-x-3 p-3 rounded-lg {{ request()->routeIs('admin.settings.update.*') ? 'bg-gray-100 text-black font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span class="text-sm">System Updates</span>
                </a>
            </nav>

            <div class="p-4 border-t border-gray-100 flex-shrink-0">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 p-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span class="text-sm font-medium">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 ml-64 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 sticky top-0 z-30">
                <h2 class="text-lg font-bold text-gray-800 truncate">@yield('header')</h2>
                <div class="flex items-center space-x-5">
                    @if(isset($globalUpdateAvailable) && $globalUpdateAvailable)
                    <a href="{{ route('admin.settings.update.index') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors border border-emerald-200 shadow-sm active:scale-95">
                        <span class="flex h-2 w-2 relative">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        Update Available
                    </a>
                    @endif

                    @if(\App\Models\ThemeSetting::where('group', 'integration.whatsapp')->where('key', 'enabled')->value('value') === '1')
                    <a href="{{ route('admin.whatsapp.index') }}" x-data="{ unread: 0, poll() { fetch('{{ route('admin.whatsapp.unread-count') }}').then(r => r.json()).then(d => this.unread = d.count); setTimeout(() => this.poll(), 5000); } }" x-init="poll()" class="relative hidden sm:inline-flex items-center justify-center w-9 h-9 text-gray-500 hover:text-[#1DA851] hover:bg-[#25D366]/10 rounded-full transition-colors active:scale-95" title="WhatsApp Messages">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"></path></svg>
                        <span x-show="unread > 0" x-transition x-text="unread" style="display: none;" class="absolute top-0 right-0 transform translate-x-1/4 -translate-y-1/4 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow border-2 border-white"></span>
                    </a>
                    @endif

                    <a href="{{ url('/') }}" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors border border-slate-200 shadow-sm active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        Visit Store
                    </a>
                    <div class="flex items-center space-x-3 border-l border-gray-200 pl-5">
                        <span class="text-sm text-gray-700 font-semibold hidden sm:block">{{ auth()->user()->name ?? 'Admin' }}</span>
                        <div class="w-8 h-8 bg-black rounded-full flex items-center justify-center text-white text-xs font-bold shadow-sm">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
                    </div>
                </div>
            </header>

            <main class="p-8">
                @if(session('success'))
                    <div class="mb-8 p-4 rounded-lg bg-green-50 border border-green-100 flex items-start text-green-800">
                        <svg class="w-5 h-5 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-8 p-4 rounded-lg bg-red-50 border border-red-100 flex items-start text-red-800">
                        <svg class="w-5 h-5 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg shadow-sm">
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="font-bold text-red-700 text-sm">Action Required</span>
                        </div>
                        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarNav = document.querySelector('aside nav');
            if (sidebarNav) {
                // Restore the exact scroll position if saved
                const savedScrollTop = sessionStorage.getItem('sidebar-scroll');
                if (savedScrollTop !== null) {
                    sidebarNav.scrollTop = parseInt(savedScrollTop, 10);
                } else {
                    // Fallback for first-time visits: scroll active item into view
                    const activeLink = sidebarNav.querySelector('a.bg-gray-100');
                    if (activeLink) {
                        activeLink.scrollIntoView({ block: 'nearest', behavior: 'instant' });
                    }
                }

                // Listen to scroll events and save the current position in sessionStorage
                sidebarNav.addEventListener('scroll', function() {
                    sessionStorage.setItem('sidebar-scroll', sidebarNav.scrollTop);
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
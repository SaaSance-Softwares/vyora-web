@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-white border border-gray-200 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
            <svg class="w-6 h-6 text-[#4285F4]" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.01 22c-5.52 0-10-4.48-10-10s4.48-10 10-10 10 4.48 10 10-4.48 10-10 10zM12 4C7.59 4 4 7.59 4 12s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8zm-2 13H8v-2h2v2zm5 0h-2v-2h2v2zm-2.5-4H9.5v-6h4v6z" />
            </svg>
        </div>
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">{{ $integration['name'] }}</h1>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest rounded-full border border-blue-100">
                    Shopping
                </span>
            </div>
            <p class="text-sm text-gray-500 font-medium">{{ $integration['description'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Option 1: File --}}
            <div class="bg-white border border-blue-200 rounded-2xl overflow-hidden shadow-sm relative ring-1 ring-blue-50">
                <div class="absolute top-0 right-0 bg-blue-500 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-bl-xl">
                    Recommended
                </div>
                <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-3 bg-blue-50/50">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Add products from a file</h2>
                        <p class="text-xs text-gray-500">Create a file that contains all your product details (title, description, price, and more).</p>
                    </div>
                </div>
                
                <div class="p-6">
                    <p class="text-sm text-gray-600 mb-4">
                        Your store automatically generates a live, auto-updating product feed. 
                        Simply copy this link and paste it into Google Merchant Center using the <strong>Scheduled Fetch</strong> option.
                    </p>
                    
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ url('/google-merchant-feed.xml') }}" class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-blue-500 rounded-xl px-4 py-3 text-sm font-medium text-gray-900" id="feedUrl">
                        <button onclick="navigator.clipboard.writeText(document.getElementById('feedUrl').value); alert('URL Copied!')" class="shrink-0 px-4 py-3 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-bold rounded-xl transition-all border border-blue-200">
                            Copy Link
                        </button>
                    </div>
                </div>
            </div>

            {{-- Option 2: API --}}
            <div class="bg-gray-50 border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Add products using API</h2>
                        <p class="text-xs text-gray-500">Use the Merchant API to upload a large number of products or if you plan to make frequent changes.</p>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex gap-3">
                        <svg class="w-5 h-5 text-yellow-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div>
                            <h4 class="text-sm font-bold text-yellow-800">Coming Soon</h4>
                            <p class="text-sm text-yellow-700 mt-1">This API method requires technical knowledge (Google Cloud Projects, Service Account JSON Keys, etc.). <strong>This option will be available in a future release of Vyora.</strong> For now, please use the <strong>File link</strong> above to sync your products instantly.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <a href="{{ route('admin.online-store.integrations.index') }}" class="px-5 py-3 border border-gray-200 text-sm font-bold rounded-xl text-gray-600 hover:bg-gray-50 transition-all">
                    &larr; Back to Integrations
                </a>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">
            {{-- Instructions --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-4">Setup Instructions</h3>
                <ol class="space-y-4">
                    <li class="flex items-start gap-3">
                        <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">1</span>
                        <span class="text-xs text-gray-600 leading-relaxed">
                            Log in to <strong>Google Merchant Center Next</strong>.
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">2</span>
                        <span class="text-xs text-gray-600 leading-relaxed">
                            Click the <strong>Settings Gear</strong> icon and go to <strong>Data sources</strong>.
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">3</span>
                        <span class="text-xs text-gray-600 leading-relaxed">
                            Click <strong>Add product source</strong> and select <strong>Add products from a file</strong>.
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">4</span>
                        <span class="text-xs text-gray-600 leading-relaxed">
                            Choose <strong>Scheduled fetch</strong> and paste your store's XML link.
                        </span>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

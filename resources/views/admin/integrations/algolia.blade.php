@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-white border border-gray-200 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
            <svg class="w-6 h-6 text-blue-500" viewBox="0 0 24 24" fill="currentColor">
                <!-- A simple magnifying glass for Algolia -->
                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            </svg>
        </div>
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">{{ $integration['name'] }}</h1>
                <span class="px-2.5 py-1 bg-green-50 text-green-700 text-[10px] font-black uppercase tracking-widest rounded-full border border-green-100">
                    Search Engine
                </span>
            </div>
            <p class="text-sm text-gray-500 font-medium">{{ $integration['description'] }}</p>
        </div>
    </div>

    <form action="{{ route('admin.online-store.integrations.update', $slug) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Form --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Status --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-gray-900 mb-1">Enable Integration</h2>
                            <p class="text-sm text-gray-500">Turn on Algolia search for your storefront</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="enabled" value="1" class="sr-only peer" {{ $saved['enabled'] ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>

                {{-- API Credentials --}}
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-base font-bold text-gray-900">API Credentials</h2>
                        <span class="text-xs font-semibold text-gray-400">Algolia Dashboard → API Keys</span>
                    </div>
                    
                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Application ID</label>
                            <input type="text" name="app_id" value="{{ old('app_id', $saved['app_id'] ?? '') }}" required
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="e.g. latency">
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Admin API Key</label>
                            <div class="relative">
                                <input type="password" id="adminApiKey" name="admin_api_key" value="{{ old('admin_api_key', $saved['admin_api_key'] ?? '') }}" required
                                    class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400 pr-12"
                                    placeholder="Enter your Admin API Key">
                                <button type="button" onclick="toggleSecret()" class="absolute inset-y-0 right-0 px-4 text-gray-400 hover:text-gray-600">
                                    <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Ensure this is the Admin API key, not the Search-only key, as we need to index products.</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                        <div id="testResult" class="text-sm font-semibold hidden"></div>
                        <button type="button" id="testBtn" onclick="testConnection()" class="flex items-center gap-2 text-sm font-bold text-gray-700 hover:text-blue-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Test Connection
                        </button>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-blue-200">
                        Save Configuration
                    </button>
                    <a href="{{ route('admin.online-store.integrations.index') }}" class="px-5 py-3 border border-gray-200 text-sm font-bold rounded-xl text-gray-600 hover:bg-gray-50 transition-all">
                        Cancel
                    </a>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-5">
                {{-- How it Works --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-4">How it works</h3>
                    <ol class="space-y-3">
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">1</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                <a href="https://dashboard.algolia.com" target="_blank" class="font-bold text-blue-600 hover:underline">Get API keys from Algolia</a>
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">2</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Paste your App ID and Admin API Key here.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">3</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Run the initial product sync command (via CLI) or allow the system to sync automatically when saving products.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">4</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Enable the integration to swap Vyora search with Algolia.
                            </span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function toggleSecret() {
    const input = document.getElementById('adminApiKey');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>`;
    } else {
        input.type = 'password';
        icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
    }
}

async function testConnection() {
    const btn    = document.getElementById('testBtn');
    const result = document.getElementById('testResult');
    btn.disabled = true;
    btn.innerHTML = `<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Testing...`;
    result.className = 'text-sm font-semibold hidden';

    try {
        const res  = await fetch('{{ route('admin.online-store.integrations.algolia.test') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        });
        const data = await res.json();
        result.className = `text-sm font-semibold flex items-center gap-2 ${data.success ? 'text-green-600' : 'text-red-600'}`;
        result.innerHTML = `<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${data.success ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'}"/></svg> ${data.message}`;
    } catch (e) {
        result.className = 'text-sm font-semibold text-red-600 flex items-center gap-2';
        result.innerHTML = '✗ Network error. Please try again.';
    }

    btn.disabled = false;
    btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> Test Connection`;
}
</script>
@endpush
@endsection

@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-white border border-gray-200 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">{{ $integration['name'] }}</h1>
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-100">
                    Fulfillment Partner
                </span>
            </div>
            <p class="text-sm text-gray-500 font-medium">{{ $integration['description'] }}</p>
        </div>
    </div>


    <form action="{{ route('admin.online-store.integrations.update', 'qikink') }}" method="POST" id="qikinkForm">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Form --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Status --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-gray-900 mb-1">Enable Integration</h2>
                            <p class="text-sm text-gray-500">Automatically process orders for Qikink-enabled products</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="enabled" value="1" class="sr-only peer" {{ !empty($saved['enabled']) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>
                </div>

                {{-- Tracking ID / API Credentials --}}
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Environment</h4>
                            <p class="text-xs text-gray-500 mt-1">Use Live for real orders, Sandbox for testing.</p>
                        </div>
                        <div class="flex bg-gray-100 p-1 rounded-xl">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="mode" value="test" class="peer sr-only" {{ ($saved['mode'] ?? 'test') === 'test' ? 'checked' : '' }}>
                                <span class="block px-4 py-1.5 text-xs font-bold rounded-lg peer-checked:bg-white peer-checked:text-emerald-700 peer-checked:shadow-sm text-gray-500 transition-all">Sandbox</span>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="mode" value="live" class="peer sr-only" {{ ($saved['mode'] ?? 'test') === 'live' ? 'checked' : '' }}>
                                <span class="block px-4 py-1.5 text-xs font-bold rounded-lg peer-checked:bg-white peer-checked:text-emerald-700 peer-checked:shadow-sm text-gray-500 transition-all">Live</span>
                            </label>
                        </div>
                    </div>
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-base font-bold text-gray-900">API Credentials</h2>
                        <a href="https://dashboard.qikink.com/integration/api" target="_blank" class="text-xs font-semibold text-gray-400 hover:text-emerald-600 transition-colors">Qikink Dashboard →</a>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        {{-- Client ID --}}
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Client ID</label>
                            <input type="text" name="client_id" id="clientId" value="{{ $saved['client_id'] ?? '' }}" required
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-emerald-500 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="Your Client ID">
                        </div>

                        {{-- Client Secret --}}
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Client Secret</label>
                            <div class="relative">
                                <input type="password" name="client_secret" id="clientSecret" value="{{ $saved['client_secret'] ?? '' }}" required
                                    class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-emerald-500 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400 pr-12"
                                    placeholder="••••••••••••••••••••">
                                <button type="button" onclick="toggleSecret()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 transition-colors">
                                    <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Stored encrypted in the database. Leave unchanged to keep existing secret.</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                        <div id="testResult" class="text-sm font-semibold hidden"></div>
                        <button type="button" id="testBtn" onclick="testConnection()" class="flex items-center gap-2 text-sm font-bold text-gray-700 hover:text-emerald-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Test Connection
                        </button>
                    </div>
                </div>

                @if(($saved['mode'] ?? 'test') === 'test')
                <div class="bg-blue-50 border border-blue-100 rounded-2xl overflow-hidden mb-6">
                    <div class="px-6 py-5 flex items-start justify-between gap-4">
                        <div>
                            <h4 class="text-sm font-bold text-blue-900">Qikink Approval Required</h4>
                            <p class="text-xs text-blue-700 mt-1">Send a test order to Qikink to prove your integration works. Send the details below to their support to get your Live API keys.</p>
                            
                            <div id="testOrderResult" class="hidden mt-4 space-y-3 w-full">
                                <div class="text-sm font-bold text-green-700 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span id="testOrderMsg"></span>
                                </div>
                                <div class="bg-gray-900 rounded-xl p-4 overflow-x-auto">
                                    <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-2">Request Payload Sent</p>
                                    <pre id="testOrderPayload" class="text-emerald-400 text-xs font-mono"></pre>
                                </div>
                                <div class="bg-gray-900 rounded-xl p-4 overflow-x-auto">
                                    <p class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-2">Response from Qikink</p>
                                    <pre id="testOrderResponse" class="text-blue-400 text-xs font-mono"></pre>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="testOrderBtn" onclick="sendTestOrder()" class="shrink-0 flex items-center gap-2 text-sm font-bold text-blue-700 hover:text-white hover:bg-blue-600 transition-colors bg-blue-100 px-5 py-2.5 rounded-xl">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            Send Test Order
                        </button>
                    </div>
                </div>
                @endif

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-8 py-3 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-emerald-200">
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
                                Get <a href="https://dashboard.qikink.com/integration/api" target="_blank" class="font-bold text-emerald-600 hover:underline">Sandbox API keys</a> from the Qikink Dashboard.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">2</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Choose <b>Sandbox</b> mode, insert your keys, and click <b>Send Test Order</b> below. Send the result to Qikink Support to get approved.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">3</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Once approved, get your <b>Live API Keys</b> from Qikink, insert them here, and switch the mode to <b>Live</b>.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">4</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                To respect Qikink's <b>30 requests/minute</b> API limit, orders are sent in batches in the background. Tracking statuses are synced hourly.
                            </span>
                        </li>
                        <li class="flex items-start gap-3 mt-4 p-3 bg-amber-50 rounded-xl border border-amber-100">
                            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-xs text-amber-900 leading-relaxed font-medium">
                                <b>Required:</b> For this integration to work automatically, you must configure your server's Cron Job. <a href="/occ/settings/cron" class="text-emerald-600 hover:underline font-bold">View Cron Setup Instructions &rarr;</a>
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
    const input = document.getElementById('clientSecret');
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
        const res  = await fetch('{{ route('admin.online-store.integrations.qikink.test') }}', {
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

async function sendTestOrder() {
    const btn = document.getElementById('testOrderBtn');
    const container = document.getElementById('testOrderResult');
    const msg = document.getElementById('testOrderMsg');
    const payloadPre = document.getElementById('testOrderPayload');
    const responsePre = document.getElementById('testOrderResponse');
    
    btn.disabled = true;
    btn.innerHTML = `<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Sending...`;
    
    try {
        const res = await fetch('{{ route('admin.online-store.integrations.qikink.test-order') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
        });
        const data = await res.json();
        
        container.classList.remove('hidden');
        msg.innerText = data.message;
        msg.className = data.success ? 'text-green-700 font-bold' : 'text-red-600 font-bold';
        
        if (data.payload) {
            payloadPre.innerText = data.payload;
        }
        if (data.response) {
            responsePre.innerText = data.response;
        }
        
    } catch (e) {
        alert('Network error. Please try again.');
    }
    
    btn.disabled = false;
    btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg> Send Test Order`;
}
</script>
@endpush
@endsection

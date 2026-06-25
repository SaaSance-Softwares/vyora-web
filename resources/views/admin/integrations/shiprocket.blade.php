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

    @if(session('success'))
    <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm font-semibold">
        <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('admin.online-store.integrations.update', 'shiprocket') }}" method="POST" id="shiprocketForm">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Form --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Status --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-gray-900 mb-1">Enable Integration</h2>
                            <p class="text-sm text-gray-500">Automatically connect with Shiprocket for order shipping</p>
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
                        <h2 class="text-base font-bold text-gray-900">API Credentials</h2>
                        <a href="https://app.shiprocket.in/api-user" target="_blank" class="text-xs font-semibold text-gray-400 hover:text-emerald-600 transition-colors">Shiprocket Dashboard →</a>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        {{-- Shiprocket Email --}}
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Shiprocket Email</label>
                            <input type="email" name="shiprocket_email" id="shiprocketEmail" value="{{ $saved['shiprocket_email'] ?? '' }}" required
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-emerald-500 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="Email address for API user">
                        </div>

                        {{-- Shiprocket Password --}}
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Shiprocket Password</label>
                            <div class="relative">
                                <input type="password" name="shiprocket_password" id="shiprocketPassword" value="{{ $saved['shiprocket_password'] ?? '' }}" required
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
                                Get your API Email and Password from <a href="https://app.shiprocket.in/api-user" target="_blank" class="font-bold text-emerald-600 hover:underline">Shiprocket Dashboard</a>
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">2</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Enter credentials here and click 'Test Connection'.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">3</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Manually send orders to Shiprocket from the Order Details page. Tracking numbers will automatically sync!
                            </span>
                        </li>
                    </ol>
                </div>
                {{-- Webhook URL --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5 mt-5">
                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-4">Webhook Configuration</h3>
                    <p class="text-xs text-gray-600 mb-4 leading-relaxed">
                        To receive automated tracking updates (shipped, delivered, etc.), add this URL in your Shiprocket Dashboard under <strong>Settings → API → Webhooks</strong>.
                    </p>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2">Your Webhook URL</label>
                    <div class="flex mb-4">
                        <input type="text" readonly value="{{ url('/api/webhooks/shiprocket') }}" class="w-full bg-gray-50 border border-gray-200 rounded-l-lg px-3 py-2 text-xs font-mono text-gray-800" id="webhookUrl">
                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('webhookUrl').value); alert('Copied!')" class="px-4 py-2 bg-gray-100 border border-l-0 border-gray-200 rounded-r-lg text-xs font-bold text-gray-600 hover:bg-gray-200 transition-colors">
                            Copy
                        </button>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-3 text-xs text-emerald-800">
                        <p class="font-bold mb-1">Event Type:</p>
                        <p>Ensure you select <strong>AWB Status Update</strong> or Tracking event.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function toggleSecret() {
    const input = document.getElementById('shiprocketPassword');
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
        const res  = await fetch('{{ route('admin.online-store.integrations.shiprocket.test') }}', {
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

@extends('layouts.admin')

@section('title', 'SaaSance Push Relay - Settings')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-gray-900 border border-gray-800 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
            <img src="https://saasance.com/vyora-admin-icon.png" alt="SaaSance Push Relay" class="w-7 h-7 object-contain" />
        </div>
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">{{ $integration['name'] }}</h1>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest rounded-full border border-blue-100">
                    Push Notifications
                </span>
            </div>
            <p class="text-sm text-gray-500 font-medium">{{ $integration['description'] }}</p>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl font-medium text-sm flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
                            <p class="text-sm text-gray-500">Allow Vyora to securely send mobile app notifications via SaaSance Relay</p>
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
                        <a href="https://saasance.com/project/vyora" target="_blank" class="text-xs font-semibold text-gray-400 hover:text-blue-600 transition-colors">SaaSance Dashboard →</a>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        {{-- API Key --}}
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">API Key</label>
                            <input type="text" name="saasance_api_key" value="{{ $saved['saasance_api_key'] ? '************************************' . substr($saved['saasance_api_key'], -4) : '' }}" class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400" placeholder="Paste your 40-character API Key">
                            <p class="mt-2 text-xs text-gray-500">Stored encrypted in the database. Leave unchanged to keep existing secret.</p>
                        </div>
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
                                Log in to your dashboard at <a href="https://saasance.com/project/vyora" target="_blank" class="font-bold text-blue-600 hover:underline">SaaSance.com</a>.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">2</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Navigate to the <strong>Project Vyora</strong> section in the sidebar.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">3</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Enter your store's domain name (e.g. <code>business-name.in</code>) and click <strong>Generate API Key</strong>.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">4</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Copy the generated 40-character API key and paste it on the left.
                            </span>
                        </li>
                    </ol>
                </div>
                </div>
                
                {{-- Test Connection --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-4">Test Integration</h3>
                    <p class="text-xs text-gray-600 leading-relaxed mb-4">
                        Send a test push notification directly to your device (make sure you are logged into the app).
                    </p>
                    <button type="button" onclick="testSaasancePush()" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-900 hover:bg-black text-white text-sm font-bold rounded-xl transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        Send Test Notification
                    </button>
                    <div id="test-result" class="mt-3 text-sm font-medium hidden"></div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function testSaasancePush() {
        const resultDiv = document.getElementById('test-result');
        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Sending...';
        btn.disabled = true;
        resultDiv.classList.add('hidden');

        fetch("{{ route('admin.online-store.integrations.saasance-push.test') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            resultDiv.classList.remove('hidden', 'text-green-600', 'text-red-600');
            resultDiv.classList.add(data.success ? 'text-green-600' : 'text-red-600');
            resultDiv.innerHTML = data.message;
            btn.innerHTML = originalText;
            btn.disabled = false;
        })
        .catch(error => {
            resultDiv.classList.remove('hidden', 'text-green-600');
            resultDiv.classList.add('text-red-600');
            resultDiv.innerHTML = 'An error occurred while testing.';
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>
@endpush
@endsection

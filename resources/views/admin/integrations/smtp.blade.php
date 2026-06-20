@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-white border border-gray-200 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
            <svg class="w-6 h-6 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">{{ $integration['name'] }}</h1>
                <span class="px-2.5 py-1 bg-gray-100 text-gray-800 text-[10px] font-black uppercase tracking-widest rounded-full border border-gray-200">
                    Email
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
                            <p class="text-sm text-gray-500">Send transactional emails via a custom SMTP server</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="enabled" value="1" class="sr-only peer" {{ $saved['enabled'] ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-gray-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gray-900"></div>
                        </label>
                    </div>
                </div>

                {{-- Quick Prefill --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <h2 class="text-base font-bold text-gray-900 mb-4">Quick Setup</h2>
                    <p class="text-sm text-gray-500 mb-4">Click below to auto-fill SMTP details for popular providers.</p>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" onclick="prefillSMTP('smtp.gmail.com', '587', 'tls')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold rounded-lg transition-colors border border-gray-200 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500" viewBox="0 0 24 24" fill="currentColor"><path d="M21.35 11.1h-9.17v2.73h6.51c-.33 3.81-3.5 5.44-6.5 5.44C8.36 19.27 5 16.25 5 12c0-4.1 3.2-7.27 7.2-7.27 3.09 0 4.9 1.97 4.9 1.97L19 4.72S16.56 2 12.1 2C6.42 2 2.03 6.8 2.03 12c0 5.05 4.13 10 10.22 10 5.35 0 9.25-3.67 9.25-9.09 0-1.15-.15-1.81-.15-1.81z"/></svg>
                            Google (Gmail / Workspace)
                        </button>
                        <button type="button" onclick="prefillSMTP('smtp.zoho.in', '465', 'ssl')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold rounded-lg transition-colors border border-gray-200 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.5 14H8v-1.5l5.5-6.5H8V6.5h8.5V8l-5.5 6.5h5.5V16z"/></svg>
                            Zoho Mail
                        </button>
                        <button type="button" onclick="prefillSMTP('smtp.sendgrid.net', '587', 'tls')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-bold rounded-lg transition-colors border border-gray-200 flex items-center gap-2">
                            <svg class="w-4 h-4 text-cyan-500" viewBox="0 0 24 24" fill="currentColor"><path d="M22 6.5l-9.5-5-9.5 5v11l9.5 5 9.5-5v-11z"/></svg>
                            SendGrid
                        </button>
                    </div>
                </div>

                {{-- SMTP Configuration --}}
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="text-base font-bold text-gray-900">Server Configuration</h2>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">SMTP Host</label>
                                <input type="text" id="smtp_host" name="smtp_host" value="{{ old('smtp_host', $saved['smtp_host'] ?? '') }}" required
                                    class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-gray-900 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                    placeholder="e.g., smtp.gmail.com">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Port</label>
                                    <input type="text" id="smtp_port" name="smtp_port" value="{{ old('smtp_port', $saved['smtp_port'] ?? '') }}" required
                                        class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-gray-900 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                        placeholder="587, 465">
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Encryption</label>
                                    <select id="smtp_encryption" name="smtp_encryption" class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-gray-900 rounded-xl px-4 py-3 text-sm font-medium text-gray-900">
                                        <option value="">None</option>
                                        <option value="tls" {{ ($saved['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS</option>
                                        <option value="ssl" {{ ($saved['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Username / Email</label>
                            <input type="text" id="smtp_username" name="smtp_username" value="{{ old('smtp_username', $saved['smtp_username'] ?? '') }}" required
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-gray-900 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="youremail@domain.com">
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Password / App Password</label>
                            <input type="password" id="smtp_password" name="smtp_password" value="{{ $saved['smtp_password'] ? '********' : '' }}" 
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-gray-900 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="{{ $saved['smtp_password'] ? 'Leave blank to keep existing password' : 'SMTP Password' }}">
                            <p class="mt-2 text-xs text-gray-500">If using Google or Zoho, use an <strong>App Password</strong> rather than your primary password.</p>
                        </div>
                    </div>
                </div>

                {{-- Sender Configuration --}}
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="text-base font-bold text-gray-900">Sender Details</h2>
                    </div>
                    
                    <div class="p-6 space-y-6 grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                        <div class="space-y-0">
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">From Name</label>
                            <input type="text" id="smtp_from_name" name="smtp_from_name" value="{{ old('smtp_from_name', $saved['smtp_from_name'] ?? '') }}" required
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-gray-900 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="e.g., Dope Style Support">
                        </div>
                        <div class="space-y-0">
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">From Email</label>
                            <input type="email" id="smtp_from_address" name="smtp_from_address" value="{{ old('smtp_from_address', $saved['smtp_from_address'] ?? '') }}" required
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-gray-900 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="support@domain.com">
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-8 py-3 bg-gray-900 hover:bg-black text-white text-sm font-bold rounded-xl transition-all shadow-sm">
                        Save Configuration
                    </button>
                    <a href="{{ route('admin.online-store.integrations.index') }}" class="px-5 py-3 border border-gray-200 text-sm font-bold rounded-xl text-gray-600 hover:bg-gray-50 transition-all">
                        Cancel
                    </a>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-5">
                {{-- Info --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-4">Note on Passwords</h3>
                    <div class="text-sm text-gray-600 leading-relaxed space-y-3">
                        <p>Most popular email providers (like Google Workspace and Zoho) require <strong>App Passwords</strong> instead of your regular account password due to Two-Factor Authentication (2FA).</p>
                        <p>Generate an App Password in your email provider's security settings and paste it here.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function prefillSMTP(host, port, encryption) {
        document.getElementById('smtp_host').value = host;
        document.getElementById('smtp_port').value = port;
        document.getElementById('smtp_encryption').value = encryption;
    }
</script>
@endsection

@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-[#25D366]/10 rounded-xl flex items-center justify-center shrink-0 border border-[#25D366]/20">
            <svg class="w-6 h-6 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
        </div>
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">{{ $integration['name'] }}</h1>
                <span class="px-2.5 py-1 bg-[#25D366]/10 text-[#25D366] text-[10px] font-black uppercase tracking-widest rounded-full border border-[#25D366]/20">
                    Marketing & Chat
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
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-gray-900 mb-1">Enable Integration</h2>
                            <p class="text-sm text-gray-500">Activate WhatsApp notifications and Admin Chat.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="enabled" value="1" class="sr-only peer" {{ $saved['enabled'] ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#25D366]/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#25D366]"></div>
                        </label>
                    </div>
                </div>

                {{-- API Credentials --}}
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-base font-bold text-gray-900">Meta App Credentials</h2>
                        <span class="text-xs font-semibold text-gray-400">developers.facebook.com</span>
                    </div>
                    
                    <div class="p-6 space-y-5">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Access Token</label>
                            <input type="password" name="whatsapp_access_token" value="{{ old('whatsapp_access_token', $saved['whatsapp_access_token'] ?? '') }}" 
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="Permanent Access Token">
                            <p class="mt-2 text-xs text-gray-500">A permanent system user access token with `whatsapp_business_messaging` permissions.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Phone Number ID</label>
                            <input type="text" name="whatsapp_phone_id" value="{{ old('whatsapp_phone_id', $saved['whatsapp_phone_id'] ?? '') }}" 
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="e.g. 104561238901">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">WhatsApp Business Account ID</label>
                            <input type="text" name="whatsapp_business_account_id" value="{{ old('whatsapp_business_account_id', $saved['whatsapp_business_account_id'] ?? '') }}" 
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="e.g. 103982424912">
                            <p class="mt-2 text-xs text-gray-500">Required for syncing and creating Templates.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Webhook Verify Token</label>
                            <input type="password" name="whatsapp_webhook_verify_token" value="{{ old('whatsapp_webhook_verify_token', $saved['whatsapp_webhook_verify_token'] ?? '') }}" 
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="Your custom secret token">
                            <p class="mt-2 text-xs text-gray-500">The secret string you define in Meta Developer console to verify webhook delivery.</p>
                        </div>
                    </div>
                </div>

                {{-- Message Templates --}}
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-base font-bold text-gray-900">Automated Template IDs</h2>
                        <span class="text-xs font-semibold text-gray-400">Triggered Events</span>
                    </div>
                    
                    <div class="p-6 space-y-5">
                        <p class="text-sm text-gray-600 mb-4">
                            Enter the exact <strong>Template Name</strong> as approved in your WhatsApp Manager for each event. Leave blank to disable an event.
                        </p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Order Confirmed</label>
                                <input type="text" name="whatsapp_template_confirmed" value="{{ old('whatsapp_template_confirmed', $saved['whatsapp_template_confirmed'] ?? '') }}" 
                                    class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm font-medium text-gray-900"
                                    placeholder="e.g. order_confirmed_v1">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Order Shipped</label>
                                <input type="text" name="whatsapp_template_shipped" value="{{ old('whatsapp_template_shipped', $saved['whatsapp_template_shipped'] ?? '') }}" 
                                    class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm font-medium text-gray-900"
                                    placeholder="e.g. order_shipped_v1">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Order Cancelled</label>
                                <input type="text" name="whatsapp_template_cancelled" value="{{ old('whatsapp_template_cancelled', $saved['whatsapp_template_cancelled'] ?? '') }}" 
                                    class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm font-medium text-gray-900"
                                    placeholder="e.g. order_cancelled_v1">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Account Created</label>
                                <input type="text" name="whatsapp_template_account_created" value="{{ old('whatsapp_template_account_created', $saved['whatsapp_template_account_created'] ?? '') }}" 
                                    class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm font-medium text-gray-900"
                                    placeholder="e.g. welcome_user">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Password Updated</label>
                                <input type="text" name="whatsapp_template_password_updated" value="{{ old('whatsapp_template_password_updated', $saved['whatsapp_template_password_updated'] ?? '') }}" 
                                    class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm font-medium text-gray-900"
                                    placeholder="e.g. security_alert_pass">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Abandoned Cart</label>
                                <input type="text" name="whatsapp_template_abandoned_cart" value="{{ old('whatsapp_template_abandoned_cart', $saved['whatsapp_template_abandoned_cart'] ?? '') }}" 
                                    class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm font-medium text-gray-900"
                                    placeholder="e.g. abandoned_cart_reminder">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-8 py-3 bg-[#25D366] hover:bg-[#1DA851] text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-[#25D366]/30">
                        Save Configuration
                    </button>
                    <a href="{{ route('admin.online-store.integrations.index') }}" class="px-5 py-3 border border-gray-200 text-sm font-bold rounded-xl text-gray-600 hover:bg-gray-50 transition-all">
                        Cancel
                    </a>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-5">
                {{-- Webhook URL --}}
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-base font-bold text-gray-900">Webhook Configuration</h2>
                        <span class="text-xs font-semibold text-gray-400">Incoming Messages</span>
                    </div>
                    
                    <div class="p-6 space-y-5">
                        <p class="text-sm text-gray-600">
                            To receive incoming messages in the Admin Chat, configure this URL in your Meta App Dashboard under <strong>WhatsApp > Configuration</strong>.
                        </p>

                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex gap-4 mt-2">
                            <svg class="w-6 h-6 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-bold mb-1">Crucial Final Steps in Meta Dashboard:</p>
                                <ul class="list-disc list-inside space-y-1 ml-1 text-blue-700">
                                    <li>Ensure this URL uses <strong>https://</strong> (not http://).</li>
                                    <li>After verifying, you <strong>MUST</strong> click the "Manage" button under Webhook Fields and check the box to subscribe to <strong>messages</strong>. If you don't do this, incoming messages will not work!</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Your Webhook Callback URL</label>
                            <div class="flex">
                                <input type="text" readonly value="{{ url('/api/webhooks/whatsapp') }}" class="w-full bg-gray-50 border border-gray-200 rounded-l-lg px-3 py-2 text-xs font-mono text-gray-800" id="webhookUrl">
                                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('webhookUrl').value); alert('Copied!')" class="px-4 py-2 bg-gray-100 border border-l-0 border-gray-200 rounded-r-lg text-xs font-bold text-gray-600 hover:bg-gray-200 transition-colors">
                                    Copy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Features Info --}}
                <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5">
                    <h3 class="text-xs font-black uppercase tracking-widest text-blue-800 mb-3">Capabilities</h3>
                    <ul class="space-y-2 text-xs text-blue-900/80 font-medium">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Automated Order Updates
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Live 2-way Admin Chat
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Template Syncing
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

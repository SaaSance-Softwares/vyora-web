@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-white border border-gray-200 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
            <svg class="w-6 h-6 text-red-500" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm3 11h-2v2c0 .55-.45 1-1 1s-1-.45-1-1v-2H9c-.55 0-1-.45-1-1s.45-1 1-1h2v-2c0-.55.45-1 1-1s1 .45 1 1v2h2c.55 0 1 .45 1 1s-.45 1-1 1z" />
            </svg>
        </div>
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">{{ $integration['name'] }}</h1>
                <span class="px-2.5 py-1 bg-red-50 text-red-700 text-[10px] font-black uppercase tracking-widest rounded-full border border-red-100">
                    SMS
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
                            <p class="text-sm text-gray-500">Send automatic SMS updates to your customers</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="enabled" value="1" class="sr-only peer" {{ $saved['enabled'] ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                        </label>
                    </div>
                </div>

                {{-- Twilio Configuration --}}
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-base font-bold text-gray-900">API Credentials</h2>
                        <span class="text-xs font-semibold text-gray-400">Twilio Console → Account Info</span>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Account SID</label>
                            <input type="text" name="twilio_sid" value="{{ old('twilio_sid', $saved['twilio_sid'] ?? '') }}" required
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-red-500 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Auth Token</label>
                            <input type="password" name="twilio_auth_token" value="{{ $saved['twilio_auth_token'] ? '********' : '' }}" 
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-red-500 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="{{ $saved['twilio_auth_token'] ? 'Leave blank to keep existing token' : 'Your Twilio Auth Token' }}">
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Twilio Phone Number</label>
                            <input type="text" name="twilio_phone_number" value="{{ old('twilio_phone_number', $saved['twilio_phone_number'] ?? '') }}" required
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-red-500 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="+1234567890">
                            <p class="mt-2 text-xs text-gray-500">Must include the country code (e.g., +1 for US, +91 for India).</p>
                        </div>
                    </div>
                </div>

                {{-- SMS Templates --}}
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-base font-bold text-gray-900">Message Templates</h2>
                        <span class="text-xs font-semibold text-gray-400">Variables: {name}, {order_id}, {tracking_url}, {items}</span>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Order Confirmed SMS</label>
                            <textarea name="twilio_template_confirmed" rows="2" 
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-red-500 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="Hi {name}, your order #{order_id} for {items} is confirmed!">{{ old('twilio_template_confirmed', !empty($saved['twilio_template_confirmed']) ? $saved['twilio_template_confirmed'] : 'Hi {name}, your order #{order_id} for {items} has been confirmed. Thank you for shopping with us!') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Order Shipped SMS</label>
                            <textarea name="twilio_template_shipped" rows="2" 
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-red-500 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="Hi {name}, your order #{order_id} has shipped! Track here: {tracking_url}">{{ old('twilio_template_shipped', !empty($saved['twilio_template_shipped']) ? $saved['twilio_template_shipped'] : 'Hi {name}, your order #{order_id} has been shipped! Track here: {tracking_url}') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Order Cancelled SMS</label>
                            <textarea name="twilio_template_cancelled" rows="2" 
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-red-500 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="Hi {name}, your order #{order_id} has been cancelled.">{{ old('twilio_template_cancelled', !empty($saved['twilio_template_cancelled']) ? $saved['twilio_template_cancelled'] : 'Hi {name}, your order #{order_id} has been cancelled. If you have questions, please contact support.') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-8 py-3 bg-red-500 hover:bg-red-600 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-red-200">
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
                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-4">Setup Guide</h3>
                    <ol class="space-y-3">
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">1</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Log in to your Twilio Console.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">2</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Under Account Info, copy your <strong>Account SID</strong> and <strong>Auth Token</strong>.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">3</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                To find your phone number, navigate to <strong>Phone Numbers &rarr; Manage &rarr; Active numbers</strong> in the Twilio sidebar. Copy the number exactly as shown (including the + country code). If you don't have one, click <strong>Buy a Number</strong>.
                            </span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

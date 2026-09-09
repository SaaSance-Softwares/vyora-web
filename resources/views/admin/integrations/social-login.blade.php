@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-white border border-gray-200 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/></svg>
        </div>
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">{{ $integration['name'] }}</h1>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest rounded-full border border-blue-100">
                    Authentication
                </span>
            </div>
            <p class="text-sm text-gray-500 font-medium">{{ $integration['description'] }}</p>
        </div>
    </div>


    <form action="{{ route('admin.online-store.integrations.update', $slug) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Settings --}}
            <div class="lg:col-span-2 space-y-6">

                @php
                    $providers = [
                        'google' => ['name' => 'Google Login', 'icon' => 'https://www.svgrepo.com/show/303108/google-icon-logo.svg', 'console' => 'Google Cloud Console', 'url' => 'https://console.cloud.google.com/'],
                        'facebook' => ['name' => 'Facebook Login', 'icon' => 'https://www.svgrepo.com/show/303114/facebook-3-logo.svg', 'console' => 'Meta App Dashboard', 'url' => 'https://developers.facebook.com/apps'],
                        'apple' => ['name' => 'Apple Login', 'icon' => 'https://www.svgrepo.com/show/511330/apple-173.svg', 'console' => 'Apple Developer Portal', 'url' => 'https://developer.apple.com/account/resources/identifiers/list'],
                        'github' => ['name' => 'GitHub Login', 'icon' => 'https://www.svgrepo.com/show/512317/github-142.svg', 'console' => 'GitHub Developer Settings', 'url' => 'https://github.com/settings/developers'],
                        'snapchat' => ['name' => 'Snapchat Login', 'icon' => 'https://developers.snap.com/img/logo-full.svg', 'console' => 'Snap Kit Developer Portal', 'url' => 'https://kit.snapchat.com/portal/'],
                    ];
                @endphp

                @foreach ($providers as $provider => $details)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 sm:p-8 flex items-center justify-between border-b border-gray-100">
                        <div class="flex items-center gap-4">
                            <img src="{{ $details['icon'] }}" class="w-8 h-8" alt="{{ $details['name'] }}">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">{{ $details['name'] }}</h2>
                                <p class="text-sm text-gray-500 mt-1">Configure credentials from <a href="{{ $details['url'] }}" target="_blank" class="text-blue-600 hover:underline">{{ $details['console'] }}</a></p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="{{ $provider }}_enabled" value="1" class="sr-only peer" {{ $saved[$provider]['enabled'] ? 'checked' : '' }}>
                            <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-500"></div>
                        </label>
                    </div>

                    <div class="p-6 sm:p-8 space-y-6">
                        @if ($provider === 'apple')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Service ID (Client ID) <span class="text-red-500">*</span></label>
                                <input type="text" name="apple_client_id" value="{{ old('apple_client_id', $saved['apple']['client_id'] ?? '') }}"
                                    class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-black rounded-xl px-4 py-3 text-sm font-medium text-gray-900"
                                    placeholder="e.g. com.yourdomain.webapp">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Team ID <span class="text-red-500">*</span></label>
                                <input type="text" name="apple_team_id" value="{{ old('apple_team_id', $saved['apple']['team_id'] ?? '') }}"
                                    class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-black rounded-xl px-4 py-3 text-sm font-medium text-gray-900"
                                    placeholder="e.g. ABCD123456">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Key ID <span class="text-red-500">*</span></label>
                            <input type="text" name="apple_key_id" value="{{ old('apple_key_id', $saved['apple']['key_id'] ?? '') }}"
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-black rounded-xl px-4 py-3 text-sm font-medium text-gray-900"
                                placeholder="e.g. 1A2B3C4D5E">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Private Key <span class="text-red-500">*</span></label>
                            <textarea name="apple_private_key" rows="6"
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-black rounded-xl px-4 py-3 text-sm font-mono text-gray-900 placeholder:text-gray-400"
                                placeholder="-----BEGIN PRIVATE KEY-----&#10;...&#10;-----END PRIVATE KEY-----">{{ old('apple_private_key', $saved['apple']['private_key'] ? '••••••••••••••••••••••••••••••••' : '') }}</textarea>
                            <p class="text-xs text-gray-500 mt-2">Paste the entire contents of your .p8 file here.</p>
                        </div>
                        @php
                            $host = preg_replace('/^www\./', '', request()->getHost());
                        @endphp
                        <div class="bg-gray-100 p-3 rounded-lg border border-gray-200 text-xs text-gray-600 space-y-2">
                            <div><strong>Valid Callback URLs (Add BOTH to developer portal):</strong></div>
                            <code class="block bg-white p-2 rounded border border-gray-200">https://{{ $host }}/auth/apple/callback</code>
                            <code class="block bg-white p-2 rounded border border-gray-200">https://www.{{ $host }}/auth/apple/callback</code>
                        </div>
                        @else
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Client ID <span class="text-red-500">*</span></label>
                            <input type="text" name="{{ $provider }}_client_id" value="{{ old("{$provider}_client_id", $saved[$provider]['client_id'] ?? '') }}"
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-black rounded-xl px-4 py-3 text-sm font-medium text-gray-900"
                                placeholder="Client ID">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Client Secret <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" name="{{ $provider }}_client_secret" value="{{ old("{$provider}_client_secret", $saved[$provider]['client_secret'] ?? '') }}"
                                    class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-black rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400 pr-12"
                                    placeholder="Client Secret">
                            </div>
                        </div>
                        @php
                            $host = preg_replace('/^www\./', '', request()->getHost());
                        @endphp
                        <div class="bg-gray-100 p-3 rounded-lg border border-gray-200 text-xs text-gray-600 space-y-2">
                            <div><strong>Valid Callback URLs (Add BOTH to developer portal):</strong></div>
                            <code class="block bg-white p-2 rounded border border-gray-200" style="user-select: all;">https://{{ $host }}/auth/{{ $provider }}/callback</code>
                            <code class="block bg-white p-2 rounded border border-gray-200" style="user-select: all;">https://www.{{ $host }}/auth/{{ $provider }}/callback</code>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-8 py-3 bg-black hover:bg-gray-800 text-white text-sm font-bold rounded-xl transition-all shadow-sm">
                        Save Configuration
                    </button>
                    <a href="{{ route('admin.online-store.integrations.index') }}" class="px-5 py-3 border border-gray-200 text-sm font-bold rounded-xl text-gray-600 hover:bg-gray-50 transition-all">
                        Cancel
                    </a>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-5">
                {{-- Setup Guide --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-4">How to Setup</h3>
                    <ol class="space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">1</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Choose which providers you want to offer your customers and click their respective dashboard links.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">2</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Create an OAuth App in the provider's developer console.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">3</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Enter the provided <strong>Callback URLs</strong> shown below each form into the "Authorized redirect URIs" field on the provider.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">4</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Copy the Client ID and Client Secret generated by the provider back here, enable the integration, and click Save.
                            </span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

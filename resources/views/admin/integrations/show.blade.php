@extends('layouts.admin')

@section('header', $integration['name'] . ' Integration')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.online-store.integrations.index') }}" class="text-sm text-gray-500 hover:text-black transition-colors flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to Integrations
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden max-w-3xl">
    <div class="p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-1">Configure {{ $integration['name'] }}</h3>
        <p class="text-sm text-gray-500 mb-6">{{ $integration['description'] }}</p>
        
        <form action="{{ route('admin.online-store.integrations.update', $slug) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <!-- Enable Toggle -->
                <label class="flex items-center gap-3 cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" name="enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                    </div>
                    <span class="text-sm font-semibold text-gray-900">Enable Integration</span>
                </label>

                <!-- API Keys fields placeholder -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">API Key / Client ID</label>
                    <input type="text" name="api_key" class="w-full border border-gray-300 rounded-lg px-4 py-2" placeholder="Enter key" />
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Secret Key</label>
                    <input type="password" name="secret_key" class="w-full border border-gray-300 rounded-lg px-4 py-2" placeholder="Enter secret" />
                </div>
            </div>
            
            <div class="mt-8 border-t border-gray-100 pt-6">
                <button type="submit" class="bg-black text-white px-6 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors">
                    Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-white border border-gray-200 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
            <img src="{{ asset('vyora-asset/integration/zoho/zohobooks.webp') }}" alt="Zoho Books" class="w-7 h-7 object-contain" />
        </div>
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-black tracking-tight text-gray-900">{{ $integration['name'] }}</h1>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-[10px] font-black uppercase tracking-widest rounded-full border border-blue-100">
                    Accounting
                </span>
            </div>
            <p class="text-sm text-gray-500 font-medium">{{ $integration['description'] }}</p>
        </div>
    </div>


    <form action="{{ route('admin.online-store.integrations.update', 'zoho-books') }}" method="POST" id="zohoForm">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Form --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Status --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-bold text-gray-900 mb-1">Enable Integration</h2>
                            <p class="text-sm text-gray-500">Sync your store's data with Zoho Books</p>
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
                        <h2 class="text-base font-bold text-gray-900">OAuth Credentials</h2>
                        <a href="https://api-console.zoho.com/" target="_blank" class="text-xs font-semibold text-gray-400 hover:text-blue-600 transition-colors">Zoho API Console →</a>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        {{-- Data Center --}}
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Data Center</label>
                            <select name="data_center" class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 rounded-xl px-4 py-3 text-sm font-medium text-gray-900">
                                <option value=".com" {{ $saved['zoho_data_center'] === '.com' ? 'selected' : '' }}>United States (.com)</option>
                                <option value=".in" {{ $saved['zoho_data_center'] === '.in' ? 'selected' : '' }}>India (.in)</option>
                                <option value=".eu" {{ $saved['zoho_data_center'] === '.eu' ? 'selected' : '' }}>Europe (.eu)</option>
                                <option value=".com.au" {{ $saved['zoho_data_center'] === '.com.au' ? 'selected' : '' }}>Australia (.com.au)</option>
                                <option value=".com.cn" {{ $saved['zoho_data_center'] === '.com.cn' ? 'selected' : '' }}>China (.com.cn)</option>
                            </select>
                        </div>

                        {{-- Client ID --}}
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Client ID</label>
                            <input type="text" name="client_id" value="{{ $saved['client_id'] }}" required
                                class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                placeholder="1000.XXXXXXXXXXXXXXXXXXXXX">
                        </div>

                        {{-- Client Secret --}}
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Client Secret</label>
                            <div class="relative">
                                <input type="password" name="client_secret" id="clientSecret" value="{{ $saved['client_secret'] }}" required
                                    class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400 pr-12"
                                    placeholder="••••••••••••••••••••">
                            </div>
                        </div>
                    </div>

                    @if(!empty($saved['client_id']) && !empty($saved['zoho_refresh_token']))
                        <div class="bg-green-50 px-6 py-4 border-t border-green-100 flex items-center justify-between">
                            <span class="text-sm font-semibold text-green-700 flex items-center gap-2">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Zoho Books is securely connected!
                            </span>
                            <a href="{{ route('admin.online-store.integrations.zoho-books.redirect') }}" class="text-xs font-bold text-gray-500 hover:text-gray-800 bg-white px-3 py-1.5 rounded border border-gray-200 transition-all">
                                Reconnect (Refresh Token)
                            </a>
                        </div>
                    @else
                        <div class="bg-yellow-50 px-6 py-4 border-t border-yellow-100 flex items-center justify-between">
                            <span class="text-sm font-semibold text-yellow-700 flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                Not connected yet.
                            </span>
                            @if(!empty($saved['client_id']))
                                <a href="{{ route('admin.online-store.integrations.zoho-books.redirect') }}" class="text-sm font-bold text-blue-600 hover:text-blue-700 bg-white px-3 py-1.5 rounded-lg border border-blue-200 shadow-sm transition-all">
                                    Connect via OAuth
                                </a>
                            @else
                                <span class="text-xs text-yellow-600 font-medium">Save Client ID first</span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Sync Settings --}}
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="text-base font-bold text-gray-900">Synchronization Rules</h2>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        {{-- Sync Type --}}
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Order Sync Type</label>
                            <p class="text-[11px] text-gray-500 mb-3">Choose how Vyora orders should be created in Zoho Books.</p>
                            <div class="flex gap-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="sync_type" value="sales_order" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-600" {{ $saved['zoho_sync_type'] === 'sales_order' ? 'checked' : '' }}>
                                    <span class="text-sm font-bold text-gray-900">Sales Order</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="sync_type" value="invoice" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-600" {{ $saved['zoho_sync_type'] === 'invoice' ? 'checked' : '' }}>
                                    <span class="text-sm font-bold text-gray-900">Invoice</span>
                                </label>
                            </div>
                        </div>

                        <hr class="border-gray-100">

                        {{-- Inventory Sync --}}
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Inventory Sync</label>
                            <p class="text-[11px] text-gray-500 mb-3">Choose whether to just push data to Zoho, or sync stock levels bidirectionally.</p>
                            <div class="flex gap-6">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="inventory_sync" value="1-way" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-600" {{ $saved['zoho_inventory_sync'] === '1-way' ? 'checked' : '' }}>
                                    <span class="text-sm font-bold text-gray-900">Push Only (1-Way)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="inventory_sync" value="2-way" class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-600" {{ $saved['zoho_inventory_sync'] === '2-way' ? 'checked' : '' }}>
                                    <span class="text-sm font-bold text-gray-900">Sync Stock (2-Way)</span>
                                </label>
                            </div>
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
                    <ol class="space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">1</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Go to the <a href="https://api-console.zoho.com/" target="_blank" class="font-bold text-blue-600 hover:underline">Zoho API Console</a> and click <b>Server-based Applications</b>.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">2</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Set <b>Client Name</b> to "Vyora Integration" and <b>Homepage URL</b> to your store's root domain.
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">3</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Copy and paste this exact <b>Authorized Redirect URI</b>:<br>
                                <code class="text-[10px] bg-gray-100 px-1.5 py-1 rounded block mt-1 break-all border border-gray-200">{{ route('admin.online-store.integrations.zoho-books.callback') }}</code>
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">4</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                <b>Data Center:</b> Look at your Zoho URL (e.g., if it says <code>zoho.in</code>, choose India <code>.in</code>).
                            </span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-gray-900 text-white text-[10px] font-black flex items-center justify-center shrink-0 mt-0.5">5</span>
                            <span class="text-xs text-gray-600 leading-relaxed">
                                Paste the Client ID and Secret here, save the form, then click <b>Connect via OAuth</b>.
                            </span>
                        </li>
                    </ol>
                </div>

                @if(!empty($saved['zoho_refresh_token']))
                {{-- Sync Past Orders --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5">
                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-3">Sync Past Data</h3>
                    <p class="text-[11px] text-gray-500 mb-4">Push all previous orders, customers, and products to Zoho Books.</p>
                    
                    <button type="button" id="syncPastBtn" onclick="syncPastOrders()" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-sm font-bold rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Sync Past Orders
                    </button>
                    <div id="syncResult" class="hidden mt-3 text-xs font-medium text-center"></div>
                </div>
                @endif
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
async function syncPastOrders() {
    if (!confirm('Are you sure you want to sync all past orders to Zoho Books? This process will run in the background.')) return;

    const btn = document.getElementById('syncPastBtn');
    const result = document.getElementById('syncResult');
    
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = `<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Starting Sync...`;
    
    try {
        const res = await fetch('{{ route('admin.online-store.integrations.zoho-books.sync-past') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        });
        const data = await res.json();
        
        result.classList.remove('hidden');
        if (data.success) {
            result.className = 'mt-3 text-xs font-semibold text-green-600 text-center';
            result.textContent = data.message;
        } else {
            result.className = 'mt-3 text-xs font-semibold text-red-600 text-center';
            result.textContent = data.message;
        }
    } catch (e) {
        result.classList.remove('hidden');
        result.className = 'mt-3 text-xs font-semibold text-red-600 text-center';
        result.textContent = 'Network error. Could not start sync.';
    }

    btn.disabled = false;
    btn.innerHTML = originalText;
}
</script>
@endpush
@endsection

@extends('layouts.admin')

@section('header', 'Point of Sale Settings')

@section('content')
<form action="{{ route('admin.pos-settings.update') }}" method="POST">
    @csrf

    <div class="space-y-8 pb-24" x-data="{ 
        enabled: {{ $posEnabled == '1' ? 'true' : 'false' }},
        showDisableModal: false,
        advanced: false,
        togglePos() {
            if (this.enabled) {
                this.showDisableModal = true;
            } else {
                this.enabled = true;
            }
        },
        confirmDisable() {
            this.enabled = false;
            this.showDisableModal = false;
        }
    }">
        
        {{-- ── ENABLE POS (MASTER SWITCH) ────────────────────────────────── --}}
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-black">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Point of Sale (POS) System</h3>
                    <p class="text-sm text-gray-500">Enable or disable the entire offline POS engine for your physical retail stores.</p>
                </div>
                
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="pos_enabled" value="0">
                    <input type="checkbox" name="pos_enabled" value="1" :checked="enabled" class="sr-only peer" @click.prevent="togglePos()">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                </label>
            </div>
        </div>

        {{-- ── COMMAND CENTER (QUICK LINKS) ─────────────────────────── --}}
        <div x-show="enabled" x-transition class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-1">Command Center</h3>
            <p class="text-sm text-gray-500 mb-6">Quick shortcuts to manage your physical retail operations.</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="/{{ $posUrl }}" target="_blank" class="flex flex-col items-center justify-center p-5 bg-gray-50 hover:bg-gray-100 hover:border-black border border-gray-200 rounded-xl transition text-center group">
                    <span class="text-3xl mb-3 group-hover:scale-110 transition-transform">💻</span>
                    <span class="font-bold text-gray-900 text-sm mb-1">Launch Terminal</span>
                    <span class="text-xs text-gray-500">Open cash register</span>
                </a>
                
                <a href="{{ route('admin.pos-markets.index') }}" class="flex flex-col items-center justify-center p-5 bg-gray-50 hover:bg-gray-100 hover:border-black border border-gray-200 rounded-xl transition text-center group">
                    <span class="text-3xl mb-3 group-hover:scale-110 transition-transform">🏬</span>
                    <span class="font-bold text-gray-900 text-sm mb-1">Manage Stores</span>
                    <span class="text-xs text-gray-500">Locations & receipts</span>
                </a>

                <a href="{{ route('admin.orders.index', ['source' => 'pos']) }}" class="flex flex-col items-center justify-center p-5 bg-gray-50 hover:bg-gray-100 hover:border-black border border-gray-200 rounded-xl transition text-center group">
                    <span class="text-3xl mb-3 group-hover:scale-110 transition-transform">🛍️</span>
                    <span class="font-bold text-gray-900 text-sm mb-1">POS Orders</span>
                    <span class="text-xs text-gray-500">View offline sales</span>
                </a>

                <a href="{{ route('admin.settings.users') }}" class="flex flex-col items-center justify-center p-5 bg-gray-50 hover:bg-gray-100 hover:border-black border border-gray-200 rounded-xl transition text-center group">
                    <span class="text-3xl mb-3 group-hover:scale-110 transition-transform">👥</span>
                    <span class="font-bold text-gray-900 text-sm mb-1">POS Cashiers</span>
                    <span class="text-xs text-gray-500">Manage staff access</span>
                </a>
            </div>
        </div>

        {{-- ── GLOBAL SETTINGS ─────────────────────────── --}}
        <div x-show="enabled" x-transition class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Global Configurations</h3>
                <p class="text-sm text-gray-500">Set default behaviors for offline transactions.</p>
            </div>
            
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50/50">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Default POS Order Status</label>
                    <p class="text-xs text-gray-500 mb-3">When a sale is processed on the POS terminal, it will be assigned this status. Any email/WhatsApp templates attached to this status will be dispatched.</p>
                    <select name="default_pos_status" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        <option value="">-- System Default --</option>
                        @foreach($orderStatuses as $status)
                            <option value="{{ $status->id }}" {{ $defaultPosStatus == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Default Guest Name</label>
                    <p class="text-xs text-gray-500 mb-3">If a cashier enters a phone number but forgets to enter the customer's name, this default name will be used on their digital receipt.</p>
                    <input type="text" name="default_pos_guest_name" value="{{ $defaultPosGuestName }}" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" placeholder="e.g. POS Customer">
                </div>
            </div>
        </div>

        {{-- ── ADVANCED SETTINGS (DANGER ZONE) ─────────────────────────── --}}
        <div x-show="enabled" x-transition class="bg-white rounded-lg shadow overflow-hidden border border-red-100">
            <button type="button" @click="advanced = !advanced" class="w-full px-6 py-4 flex items-center justify-between bg-red-50 hover:bg-red-100 transition-colors">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span class="font-bold text-red-800 text-sm">Advanced Settings (Danger Zone)</span>
                </div>
                <svg :class="{'rotate-180': advanced}" class="w-5 h-5 text-red-600 transform transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>
            
            <div x-show="advanced"  class="p-6 border-t border-red-100">
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded text-red-800 text-sm mb-6">
                    <strong>Warning:</strong> Do not change the POS URL unless absolutely necessary. Changing this will break Progressive Web App (PWA) caches on active tablets and may cause login loops until cashiers perform a hard refresh.
                </div>

                <div class="mb-4 w-full">
                    <label class="block text-sm font-bold text-gray-700 mb-1">POS Custom URL</label>
                    <div class="flex rounded-md shadow-sm">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                            {{ config('app.url') }}/
                        </span>
                        <input type="text" name="pos_url" value="{{ $posUrl }}" class="flex-1 block w-full rounded-none rounded-r-md text-sm border border-gray-300 focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500 px-3 py-2" placeholder="e.g. pos" required>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── CUSTOM MODAL WARNING ──────────────────────── --}}
        <div x-show="showDisableModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showDisableModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="showDisableModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showDisableModal" class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-bold text-gray-900">Disable Point of Sale?</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600 leading-relaxed">
                                        <strong class="text-red-600">WARNING:</strong> Turning off Point of Sale will revoke access for all offline stores. They will instantly lose the ability to process walk-in sales. Are you absolutely sure?
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse sm:gap-2">
                        <button type="button" @click="confirmDisable()" class="w-full inline-flex justify-center rounded-md shadow-sm px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">Yes, Turn Off POS</button>
                        <button type="button" @click="showDisableModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-100 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SAVE BAR ──────────────────────────────────── --}}
    <div class="fixed bottom-0 left-0 right-0 md:left-64 bg-white border-t border-gray-200 p-4 flex justify-end z-40 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
        <button type="submit" class="bg-black text-white px-8 py-2.5 rounded-lg hover:bg-gray-800 text-sm font-bold transition shadow-lg">
            Save POS Settings
        </button>
    </div>
</form>
@endsection

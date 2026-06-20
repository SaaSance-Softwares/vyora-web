@extends('layouts.admin')

@section('header', 'Delivery PIN Settings')

@section('content')
<form action="{{ route('admin.online-store.delivery-pins.update') }}" method="POST">
    @csrf

    <div class="space-y-8 pb-32">
        {{-- ── RESTRICTION LOGIC EXPLANATION ──────────────── --}}
        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-4">How Shipping PIN Restrictions Work</h3>
            <div class="space-y-3 text-sm text-gray-600 leading-relaxed">
                <div class="flex items-start gap-3">
                    <span class="font-bold text-black min-w-[120px]">Allowed PINs:</span>
                    <p>If this list is empty, shipping is open to all locations. If populated, shipping is <span class="font-bold text-black italic text-underline">ONLY</span> possible for these PINs.</p>
                </div>
                <div class="flex items-start gap-3">
                    <span class="font-bold text-black min-w-[120px]">Excluded PINs:</span>
                    <p>Pins listed here will <span class="font-bold text-red-600 italic">ALWAYS</span> be blocked, preventing shipping regardless of any whitelist settings.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- ── ALLOWED PINS ──────────────────────────────── --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-2 h-8 bg-green-500 rounded-full"></div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Allowed PIN Codes</h3>
                        <p class="text-sm text-gray-500">Whitelist specific zones for delivery.</p>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Enter PINs (Separated by comma or new line)</label>
                    <textarea name="allowed_pins" rows="15"
                        class="w-full border border-gray-300 rounded-md px-4 py-3 text-sm font-medium text-gray-900 focus:outline-none focus:ring-1 focus:ring-black placeholder:text-gray-400"
                        placeholder="e.g. 110001, 110002, 400001">{{ $allowedPins }}</textarea>
                </div>
                
                <p class="mt-4 text-[11px] text-gray-400 font-medium italic italic uppercase tracking-wider">Empty list means "Ship to All Locations"</p>
            </div>

            {{-- ── EXCLUDED PINS ─────────────────────────────── --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-2 h-8 bg-red-500 rounded-full"></div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Excluded PIN Codes</h3>
                        <p class="text-sm text-gray-500">Blacklist specific zones from delivery.</p>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Enter PINs (Separated by comma or new line)</label>
                    <textarea name="excluded_pins" rows="15"
                        class="w-full border border-gray-300 rounded-md px-4 py-3 text-sm font-medium text-gray-900 focus:outline-none focus:ring-1 focus:ring-black placeholder:text-gray-400"
                        placeholder="e.g. 700001, 800001, 900001">{{ $excludedPins }}</textarea>
                </div>

                <p class="mt-4 text-[11px] text-gray-400 font-medium italic italic uppercase tracking-wider">PINs here take precedence over Allowed PINs</p>
            </div>
        </div>
    </div>

    {{-- ── STICKY SAVE BAR ──────────────────────────────── --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-50 md:pl-64 flex items-center justify-between shadow-lg">
        <p class="text-sm text-gray-500">Shipping restrictions apply globally to all products.</p>
        <button type="submit"
            class="bg-black text-white px-8 py-2.5 rounded-md hover:bg-gray-800 text-sm font-bold transition-all active:scale-95 shadow-lg shadow-black/10">
            Save Delivery PINs
        </button>
    </div>
</form>
@endsection

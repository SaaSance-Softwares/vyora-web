@extends('layouts.admin')

@section('header', 'Tax and Shipping Settings')

@section('content')
<div class="w-full">
    <form action="{{ route('admin.online-store.tax-shipping.update') }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="flex border-b border-gray-100" id="tabs">
                <button type="button" onclick="switchTab('tax')" id="tab-btn-tax" class="flex-1 py-4 text-sm font-bold border-b-2 border-black text-black bg-gray-50 transition-colors">
                    Tax Settings
                </button>
                <button type="button" onclick="switchTab('shipping')" id="tab-btn-shipping" class="flex-1 py-4 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-900 transition-colors">
                    Shipping Rules
                </button>
            </div>

            <!-- Tax Tab Content -->
            <div id="tab-content-tax" class="p-6">
                <h3 class="text-base font-bold text-gray-900 mb-1">Global Tax Configuration</h3>
                <p class="text-sm text-gray-500 mb-6">Configure how your store handles taxes.</p>
                
                <div class="mb-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_tax_enabled" value="0">
                        <input type="checkbox" name="is_tax_enabled" value="1" {{ ($settings['is_tax_enabled'] ?? '1') == '1' ? 'checked' : '' }} onchange="toggleTaxSettings(this.checked)" class="w-5 h-5 text-black border-gray-300 rounded focus:ring-black">
                        <span class="text-sm font-bold text-gray-900">Enable Taxes</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-7">If disabled, taxes will not be calculated or shown during checkout.</p>
                </div>

                <div id="tax-settings-container" style="display: {{ ($settings['is_tax_enabled'] ?? '1') == '1' ? 'block' : 'none' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tax Display Name (e.g. GST, VAT)</label>
                            <input type="text" name="tax_label" value="{{ $settings['tax_label'] ?? 'Tax' }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5" placeholder="Tax">
                            <p class="text-xs text-gray-500 mt-1">This is what customers will see at checkout.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Store Tax Number (Optional)</label>
                            <input type="text" name="store_tax_number" value="{{ $settings['store_tax_number'] ?? '' }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5" placeholder="e.g. 22AAAAA0000A1Z5">
                            <p class="text-xs text-gray-500 mt-1">Your registered business tax ID (for invoices).</p>
                        </div>
                    </div>

                    <div class="mb-8">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Are prices inclusive or exclusive of tax?</label>
                        <select name="tax_inclusion" class="w-full sm:w-1/2 border border-gray-300 rounded-lg px-4 py-2.5">
                            <option value="include" {{ ($settings['tax_inclusion'] ?? '') === 'include' ? 'selected' : '' }}>Prices include tax</option>
                            <option value="exclude" {{ ($settings['tax_inclusion'] ?? '') === 'exclude' ? 'selected' : '' }}>Prices exclude tax</option>
                        </select>
                    </div>

                    <div class="mb-8">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="show_tax_in_cart_checkout" value="0">
                            <input type="checkbox" name="show_tax_in_cart_checkout" value="1" {{ ($settings['show_tax_in_cart_checkout'] ?? '1') == '1' ? 'checked' : '' }} class="w-5 h-5 text-black border-gray-300 rounded focus:ring-black">
                            <span class="text-sm font-bold text-gray-900">Show Tax in Cart & Checkout</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-7">If disabled, the separate tax row will be hidden from the order summary on the frontend.</p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Shipping Tax Rate (%)</label>
                        <input type="number" step="0.01" name="shipping_tax_rate" value="{{ $settings['shipping_tax_rate'] ?? '18' }}" class="w-full sm:w-1/3 border border-gray-300 rounded-lg px-4 py-2.5" placeholder="e.g. 18">
                        <p class="text-xs text-gray-500 mt-1">The tax rate applied to shipping charges.</p>
                    </div>

                    <hr class="border-gray-100 mb-6">

                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Tax Rates</h3>
                            <p class="text-sm text-gray-500">Define the taxes available to assign to your products.</p>
                        </div>
                        <button type="button" onclick="addTaxRow()" class="bg-gray-100 text-gray-800 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-200 transition-colors">
                            + Add Tax Rate
                        </button>
                    </div>

                <div class="space-y-4" id="tax-container">
                    @foreach($settings['taxes'] as $index => $tax)
                    <div class="flex items-center gap-4 tax-row" data-id="{{ $tax['id'] }}">
                        <input type="hidden" name="taxes[{{ $index }}][id]" value="{{ $tax['id'] }}">
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tax Name</label>
                            <input type="text" name="taxes[{{ $index }}][name]" value="{{ $tax['name'] }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="e.g. GST 5%">
                        </div>
                        <div class="w-32">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Rate (%)</label>
                            <input type="number" step="0.01" name="taxes[{{ $index }}][rate]" value="{{ $tax['rate'] }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="5.0">
                        </div>
                        <div class="w-10 pt-5">
                            <button type="button" onclick="this.closest('.tax-row').remove()" class="text-gray-400 hover:text-red-500 transition-colors p-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                </div>
            </div>

            <!-- Shipping Tab Content -->
            <div id="tab-content-shipping" class="p-6 hidden">
                <h3 class="text-base font-bold text-gray-900 mb-1">Shipping Rules</h3>
                <p class="text-sm text-gray-500 mb-6">Configure shipping logic for Prepaid and Cash on Delivery (COD) orders.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Prepaid -->
                    <div class="border border-gray-200 rounded-xl p-5 bg-gray-50">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            <h4 class="font-bold text-gray-900">Prepaid Orders</h4>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Shipping Type</label>
                                <select name="shipping_rules[prepaid][type]" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" onchange="toggleShippingFields('prepaid', this.value)">
                                    <option value="free" {{ ($settings['shipping_rules']['prepaid']['type'] ?? '') === 'free' ? 'selected' : '' }}>Free Shipping</option>
                                    <option value="flat" {{ ($settings['shipping_rules']['prepaid']['type'] ?? '') === 'flat' ? 'selected' : '' }}>Flat Rate</option>
                                    <option value="tiered" {{ ($settings['shipping_rules']['prepaid']['type'] ?? '') === 'tiered' ? 'selected' : '' }}>Order Value Based (Tiered)</option>
                                </select>
                            </div>

                            <div class="flat-fields-prepaid" style="display: {{ ($settings['shipping_rules']['prepaid']['type'] ?? '') === 'flat' ? 'block' : 'none' }}">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Shipping Fee (₹)</label>
                                <input type="number" name="shipping_rules[prepaid][fee]" value="{{ $settings['shipping_rules']['prepaid']['fee'] ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            </div>

                            <div class="tiered-fields-prepaid border border-gray-100 bg-white p-3 rounded-lg" style="display: {{ ($settings['shipping_rules']['prepaid']['type'] ?? '') === 'tiered' ? 'block' : 'none' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-semibold text-gray-600">Tier Rules (Up to Order Value -> Fee)</label>
                                    <button type="button" onclick="addTierRow('prepaid')" class="text-xs bg-gray-200 px-2 py-1 rounded hover:bg-gray-300">+ Add Tier</button>
                                </div>
                                <div id="prepaid-tiers-container" class="space-y-2">
                                    @php $prepaidTiers = $settings['shipping_rules']['prepaid']['tiers'] ?? []; @endphp
                                    @if(empty($prepaidTiers))
                                        <div class="flex items-center gap-3 tier-row">
                                            <div class="flex items-center gap-2 flex-1">
                                                <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Up to ₹</span>
                                                <input type="number" name="shipping_rules[prepaid][tiers][0][up_to]" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm" placeholder="e.g. 599">
                                            </div>
                                            <div class="flex items-center gap-2 flex-1">
                                                <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Fee ₹</span>
                                                <input type="number" name="shipping_rules[prepaid][tiers][0][fee]" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm" placeholder="e.g. 80">
                                            </div>
                                            <button type="button" onclick="this.closest('.tier-row').remove()" class="text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 w-7 h-7 flex items-center justify-center rounded transition-colors shrink-0">✕</button>
                                        </div>
                                    @else
                                        @foreach($prepaidTiers as $i => $tier)
                                        <div class="flex items-center gap-3 tier-row">
                                            <div class="flex items-center gap-2 flex-1">
                                                <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Up to ₹</span>
                                                <input type="number" name="shipping_rules[prepaid][tiers][{{$i}}][up_to]" value="{{ $tier['up_to'] ?? '' }}" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm" placeholder="e.g. 599">
                                            </div>
                                            <div class="flex items-center gap-2 flex-1">
                                                <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Fee ₹</span>
                                                <input type="number" name="shipping_rules[prepaid][tiers][{{$i}}][fee]" value="{{ $tier['fee'] ?? '' }}" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm" placeholder="e.g. 80">
                                            </div>
                                            <button type="button" onclick="this.closest('.tier-row').remove()" class="text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 w-7 h-7 flex items-center justify-center rounded transition-colors shrink-0">✕</button>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                                <p class="text-[10px] text-gray-400 mt-2">Example: "Up to 599" -> "Fee 80", then "Up to 99999" -> "Fee 0" (Free above 599).</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Prepaid Discount (Optional)</label>
                                <div class="flex gap-2">
                                    <select name="shipping_rules[prepaid][discount_type]" class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-1/3">
                                        <option value="none" {{ ($settings['shipping_rules']['prepaid']['discount_type'] ?? 'none') === 'none' ? 'selected' : '' }}>None</option>
                                        <option value="percent" {{ ($settings['shipping_rules']['prepaid']['discount_type'] ?? '') === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                                        <option value="flat" {{ ($settings['shipping_rules']['prepaid']['discount_type'] ?? '') === 'flat' ? 'selected' : '' }}>Flat Amount (₹)</option>
                                    </select>
                                    <input type="number" step="0.01" name="shipping_rules[prepaid][discount_value]" value="{{ $settings['shipping_rules']['prepaid']['discount_value'] ?? '' }}" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Discount value">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Description / Notes</label>
                                <textarea name="shipping_rules[prepaid][notes]" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="e.g. Free shipping on all prepaid orders">{{ $settings['shipping_rules']['prepaid']['notes'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- COD -->
                    <div class="border border-gray-200 rounded-xl p-5 bg-gray-50">
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <h4 class="font-bold text-gray-900">COD Orders</h4>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Shipping Type</label>
                                <select name="shipping_rules[cod][type]" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" onchange="toggleShippingFields('cod', this.value)">
                                    <option value="free" {{ ($settings['shipping_rules']['cod']['type'] ?? '') === 'free' ? 'selected' : '' }}>Free Shipping</option>
                                    <option value="flat" {{ ($settings['shipping_rules']['cod']['type'] ?? '') === 'flat' ? 'selected' : '' }}>Flat Rate</option>
                                    <option value="tiered" {{ ($settings['shipping_rules']['cod']['type'] ?? '') === 'tiered' ? 'selected' : '' }}>Order Value Based (Tiered)</option>
                                </select>
                            </div>

                            <div class="flat-fields-cod" style="display: {{ ($settings['shipping_rules']['cod']['type'] ?? '') === 'flat' ? 'block' : 'none' }}">
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Shipping Fee (₹)</label>
                                <input type="number" name="shipping_rules[cod][fee]" value="{{ $settings['shipping_rules']['cod']['fee'] ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            </div>

                            <div class="tiered-fields-cod border border-gray-100 bg-white p-3 rounded-lg" style="display: {{ ($settings['shipping_rules']['cod']['type'] ?? '') === 'tiered' ? 'block' : 'none' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-semibold text-gray-600">Tier Rules (Up to Order Value -> Fee)</label>
                                    <button type="button" onclick="addTierRow('cod')" class="text-xs bg-gray-200 px-2 py-1 rounded hover:bg-gray-300">+ Add Tier</button>
                                </div>
                                <div id="cod-tiers-container" class="space-y-2">
                                    @php $codTiers = $settings['shipping_rules']['cod']['tiers'] ?? []; @endphp
                                    @if(empty($codTiers))
                                        <div class="flex items-center gap-3 tier-row">
                                            <div class="flex items-center gap-2 flex-1">
                                                <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Up to ₹</span>
                                                <input type="number" name="shipping_rules[cod][tiers][0][up_to]" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm" placeholder="e.g. 599">
                                            </div>
                                            <div class="flex items-center gap-2 flex-1">
                                                <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Fee ₹</span>
                                                <input type="number" name="shipping_rules[cod][tiers][0][fee]" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm" placeholder="e.g. 80">
                                            </div>
                                            <button type="button" onclick="this.closest('.tier-row').remove()" class="text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 w-7 h-7 flex items-center justify-center rounded transition-colors shrink-0">✕</button>
                                        </div>
                                    @else
                                        @foreach($codTiers as $i => $tier)
                                        <div class="flex items-center gap-3 tier-row">
                                            <div class="flex items-center gap-2 flex-1">
                                                <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Up to ₹</span>
                                                <input type="number" name="shipping_rules[cod][tiers][{{$i}}][up_to]" value="{{ $tier['up_to'] ?? '' }}" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm" placeholder="e.g. 599">
                                            </div>
                                            <div class="flex items-center gap-2 flex-1">
                                                <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Fee ₹</span>
                                                <input type="number" name="shipping_rules[cod][tiers][{{$i}}][fee]" value="{{ $tier['fee'] ?? '' }}" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm" placeholder="e.g. 80">
                                            </div>
                                            <button type="button" onclick="this.closest('.tier-row').remove()" class="text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 w-7 h-7 flex items-center justify-center rounded transition-colors shrink-0">✕</button>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                                <p class="text-[10px] text-gray-400 mt-2">Example: "Up to 599" -> "Fee 80", then "Up to 999" -> "Fee 100".</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Description / Notes</label>
                                <textarea name="shipping_rules[cod][notes]" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="e.g. ₹50 additional for COD orders">{{ $settings['shipping_rules']['cod']['notes'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 border-t border-gray-200 p-5 flex justify-end">
                <button type="submit" class="bg-black text-white px-8 py-2.5 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors">
                    Save Configuration
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function switchTab(tab) {
        localStorage.setItem('activeTaxShippingTab', tab);
        
        document.getElementById('tab-content-tax').classList.toggle('hidden', tab !== 'tax');
        document.getElementById('tab-content-shipping').classList.toggle('hidden', tab !== 'shipping');
        
        document.getElementById('tab-btn-tax').className = tab === 'tax' 
            ? 'flex-1 py-4 text-sm font-bold border-b-2 border-black text-black bg-gray-50 transition-colors'
            : 'flex-1 py-4 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-900 transition-colors';
            
        document.getElementById('tab-btn-shipping').className = tab === 'shipping' 
            ? 'flex-1 py-4 text-sm font-bold border-b-2 border-black text-black bg-gray-50 transition-colors'
            : 'flex-1 py-4 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-900 transition-colors';
    }

    document.addEventListener('DOMContentLoaded', () => {
        const activeTab = localStorage.getItem('activeTaxShippingTab') || 'tax';
        switchTab(activeTab);
    });

    function toggleTaxSettings(enabled) {
        document.getElementById('tax-settings-container').style.display = enabled ? 'block' : 'none';
    }

    let taxIndex = {{ count($settings['taxes'] ?? []) }};
    function addTaxRow() {
        const id = 't_' + Math.random().toString(36).substring(7);
        const html = `
            <div class="flex items-center gap-4 tax-row" data-id="${id}">
                <input type="hidden" name="taxes[${taxIndex}][id]" value="${id}">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tax Name</label>
                    <input type="text" name="taxes[${taxIndex}][name]" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="e.g. GST 5%">
                </div>
                <div class="w-32">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Rate (%)</label>
                    <input type="number" step="0.01" name="taxes[${taxIndex}][rate]" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="5.0">
                </div>
                <div class="w-10 pt-5">
                    <button type="button" onclick="this.closest('.tax-row').remove()" class="text-gray-400 hover:text-red-500 transition-colors p-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
        `;
        document.getElementById('tax-container').insertAdjacentHTML('beforeend', html);
        taxIndex++;
    }

    function toggleShippingFields(method, value) {
        const flatFields = document.querySelector('.flat-fields-' + method);
        const tieredFields = document.querySelector('.tiered-fields-' + method);
        
        if (value === 'free') {
            flatFields.style.display = 'none';
            tieredFields.style.display = 'none';
        } else if (value === 'flat') {
            flatFields.style.display = 'block';
            tieredFields.style.display = 'none';
        } else if (value === 'tiered') {
            flatFields.style.display = 'none';
            tieredFields.style.display = 'block';
        }
    }

    let tierIndex = 999;
    function addTierRow(method) {
        const container = document.getElementById(method + '-tiers-container');
        const html = `
            <div class="flex items-center gap-3 tier-row">
                <div class="flex items-center gap-2 flex-1">
                    <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Up to ₹</span>
                    <input type="number" name="shipping_rules[${method}][tiers][${tierIndex}][up_to]" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm" placeholder="e.g. 599">
                </div>
                <div class="flex items-center gap-2 flex-1">
                    <span class="text-xs text-gray-500 font-medium whitespace-nowrap">Fee ₹</span>
                    <input type="number" name="shipping_rules[${method}][tiers][${tierIndex}][fee]" class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm" placeholder="e.g. 80">
                </div>
                <button type="button" onclick="this.closest('.tier-row').remove()" class="text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 w-7 h-7 flex items-center justify-center rounded transition-colors shrink-0">✕</button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        tierIndex++;
    }
</script>
@endpush
@endsection

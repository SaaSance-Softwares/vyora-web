@extends('layouts.admin')

@section('header', 'Edit Coupon')

@section('content')
<div class="max-w-5xl mx-auto pb-24">
    <form action="{{ route('admin.online-store.coupons.update', $coupon->id) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')
        
        <div class="flex justify-between items-center bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div>
                <h1 class="text-xl font-bold">Edit Campaign: {{ $coupon->code }}</h1>
                <p class="text-sm text-gray-500">Modify discount rules and eligibility criteria.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.online-store.coupons.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-bold">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-black text-white rounded-lg text-sm font-bold hover:bg-gray-800">Update Coupon</button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <!-- Basic Info -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="font-bold border-b pb-4 mb-6">Coupon Identification</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Coupon Code</label>
                            <input type="text" name="code" value="{{ old('code', $coupon->code) }}" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono uppercase focus:ring-black">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Campaign Name</label>
                            <input type="text" name="name" value="{{ old('name', $coupon->name) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="e.g. Winter Sale 2026">
                        </div>
                    </div>
                </div>

                <!-- Discount Logic -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="font-bold border-b pb-4 mb-6">Discount Rules</h3>
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Type</label>
                                <select name="type" id="type" onchange="toggleType()" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                                    <option value="percentage" {{ $coupon->type == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="fixed" {{ $coupon->type == 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                                    <option value="free_shipping" {{ $coupon->type == 'free_shipping' ? 'selected' : '' }}>Free Shipping</option>
                                    <option value="bogo" {{ $coupon->type == 'bogo' ? 'selected' : '' }}>Buy X Get Y (BOGO)</option>
                                </select>
                            </div>
                            <div id="val-wrap" class="{{ in_array($coupon->type, ['bogo', 'free_shipping']) ? 'hidden' : '' }}">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Value</label>
                                <input type="number" step="0.01" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', $coupon->discount_amount) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            </div>
                        </div>

                        {{-- Upto / Max Discount Cap (only for Percentage type) --}}
                        <div id="upto-wrap" class="{{ $coupon->type == 'percentage' ? '' : 'hidden' }} p-4 bg-amber-50 rounded-lg border border-amber-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-xs font-bold uppercase text-amber-700">Upto / Max Discount Cap</h4>
                                    <p class="text-[11px] text-amber-600 mt-0.5">Limit the maximum discount granted. e.g. <span class="font-bold">50% OFF upto ₹80</span></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <label class="block text-[10px] font-bold uppercase mb-1 text-amber-700">Max Discount Amount (₹)</label>
                                    <input type="number" step="0.01" min="0" name="max_discount_amount" id="max_discount_amount"
                                        placeholder="e.g. 80"
                                        value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}"
                                        oninput="updatePreview()"
                                        class="w-full border border-amber-300 bg-white rounded px-3 py-2 text-sm placeholder-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                </div>
                                <div class="text-amber-400 pt-5 text-lg font-bold">→</div>
                                <div class="flex-1 pt-5">
                                    <div id="upto-badge" class="text-sm font-bold text-amber-700 bg-amber-100 border border-amber-200 rounded px-3 py-2 text-center">
                                        {{ $coupon->max_discount_amount ? 'Upto ₹' . $coupon->max_discount_amount : 'Max cap not set' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BOGO Details -->
                        <div id="bogo-wrap" class="{{ $coupon->type == 'bogo' ? '' : 'hidden' }} p-4 bg-gray-50 rounded border border-gray-200 space-y-4">
                            <h4 class="text-xs font-bold uppercase text-gray-400">BOGO Configuration</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase mb-1">Buy Qty (X)</label>
                                    <input type="number" name="bogo_buy_qty" value="{{ old('bogo_buy_qty', $coupon->bogo_buy_qty) }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase mb-1">Get Qty (Y)</label>
                                    <input type="number" name="bogo_get_qty" value="{{ old('bogo_get_qty', $coupon->bogo_get_qty) }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase mb-1">Max Disc (₹)</label>
                                    <input type="number" step="0.01" name="bogo_max_discount" value="{{ old('bogo_max_discount', $coupon->bogo_max_discount) }}" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Criteria -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="font-bold border-b pb-4 mb-6">Eligibility & Restrictions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Min Spend (₹)</label>
                                <input type="number" step="0.01" name="min_cart_value" value="{{ old('min_cart_value', $coupon->min_cart_value) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Min Item Qty</label>
                                <input type="number" name="min_item_quantity" value="{{ old('min_item_quantity', $coupon->min_item_quantity) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            </div>
                        </div>
                        <div class="space-y-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="exclude_sale_items" value="1" {{ $coupon->exclude_sale_items ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300">
                                <span class="text-sm font-bold">Exclude Sale Items</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="first_time_users_only" value="1" {{ $coupon->first_time_users_only ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300">
                                <span class="text-sm font-bold">New Customers Only</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="can_combine" value="1" {{ $coupon->can_combine ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300">
                                <span class="text-sm font-bold">Allow Stacking</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats & Limits -->
            <div class="space-y-8">
                <div class="bg-black rounded-lg shadow-lg p-6 text-white space-y-6">
                    <h3 class="font-bold border-b border-gray-800 pb-4 uppercase text-xs tracking-widest">Visibility</h3>
                    <label class="flex items-center justify-between cursor-pointer">
                        <span class="font-bold text-sm">Status</span>
                        <div class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ $coupon->is_active ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:bg-green-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                        </div>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_default_magic" value="1" {{ $coupon->is_default_magic ? 'checked' : '' }} class="h-4 w-4 rounded bg-gray-800 border-0">
                        <span class="text-xs font-bold">Auto-Apply (Magic)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="show_on_product_page" value="1" {{ $coupon->show_on_product_page ? 'checked' : '' }} class="h-4 w-4 rounded bg-gray-800 border-0">
                        <span class="text-xs font-bold">Show on PDP</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="show_on_checkout_page" value="1" {{ $coupon->show_on_checkout_page ? 'checked' : '' }} class="h-4 w-4 rounded bg-gray-800 border-0">
                        <span class="text-xs font-bold">Show on Checkout</span>
                    </label>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
                    <h3 class="font-bold border-b pb-4 uppercase text-xs tracking-widest text-gray-400">Usage Data</h3>
                    <div class="text-center p-4 bg-gray-50 rounded border border-gray-100">
                        <div class="text-2xl font-black text-gray-900">{{ $coupon->times_used }}</div>
                        <div class="text-[10px] font-bold uppercase text-gray-400">Total Redemptions</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Usage Limit</label>
                        <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Per User Limit</label>
                        <input type="number" name="usage_limit_per_user" value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-bold">
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
                    <h3 class="font-bold border-b pb-4 uppercase text-xs tracking-widest text-gray-400">Duration</h3>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Start Date</label>
                        <input type="datetime-local" name="starts_at" value="{{ $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '' }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">End Date</label>
                        <input type="datetime-local" name="expires_at" value="{{ $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '' }}" class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-bold">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function toggleType() {
        const t = document.getElementById('type').value;
        const b = document.getElementById('bogo-wrap');
        const v = document.getElementById('val-wrap');
        const upto = document.getElementById('upto-wrap');
        b.classList.toggle('hidden', t !== 'bogo');
        v.classList.toggle('hidden', t === 'bogo' || t === 'free_shipping');
        upto.classList.toggle('hidden', t !== 'percentage');
        updatePreview();
    }
    function updatePreview() {
        const pct = document.getElementById('discount_amount')?.value;
        const cap = document.getElementById('max_discount_amount')?.value;
        const badge = document.getElementById('upto-badge');
        if (!badge) return;
        if (pct && cap) {
            badge.textContent = pct + '% OFF upto ₹' + cap;
            badge.className = 'text-sm font-bold text-green-700 bg-green-100 border border-green-200 rounded px-3 py-2 text-center';
        } else if (pct) {
            badge.textContent = pct + '% OFF (no cap)';
            badge.className = 'text-sm font-bold text-amber-700 bg-amber-100 border border-amber-200 rounded px-3 py-2 text-center';
        } else {
            badge.textContent = 'Max cap not set';
            badge.className = 'text-sm font-bold text-amber-700 bg-amber-100 border border-amber-200 rounded px-3 py-2 text-center';
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        const da = document.getElementById('discount_amount');
        if (da) da.addEventListener('input', updatePreview);
        updatePreview(); // Initialize badge with existing values
    });
</script>
@endsection

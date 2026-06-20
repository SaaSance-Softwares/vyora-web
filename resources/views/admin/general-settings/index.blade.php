@extends('layouts.admin')

@section('header', 'General Settings')

@section('content')
<form action="{{ route('admin.online-store.general-settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="space-y-8 pb-24">
        {{-- ── STORE INFORMATION ─────────────────────────── --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Store Information</h3>
            <p class="text-sm text-gray-500 mb-6">Basic details about your online store.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                    <input type="text" name="store_name" value="{{ $settings['store_name'] }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Legal Business Name</label>
                    <input type="text" name="business_name" value="{{ $settings['business_name'] ?? '' }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Store Email (System/Sender)</label>
                    <input type="email" name="store_email" value="{{ $settings['store_email'] }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tax ID / GST Number</label>
                    <input type="text" name="tax_id" value="{{ $settings['tax_id'] ?? '' }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Store Description (SEO/Footer)</label>
                    <textarea name="store_description" rows="2"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">{{ $settings['store_description'] ?? '' }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Store Address</label>
                    <textarea name="store_address" rows="3"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">{{ $settings['store_address'] }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── CUSTOMER SUPPORT & CONTACTS ───────────────── --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Customer Support</h3>
            <p class="text-sm text-gray-500 mb-6">Contact channels displayed to your customers.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Email</label>
                    <input type="email" name="support_email" value="{{ $settings['support_email'] ?? '' }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Phone</label>
                    <input type="text" name="support_phone" value="{{ $settings['support_phone'] }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" value="{{ $settings['whatsapp_number'] ?? '' }}"
                        placeholder="e.g. +91 9876543210"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Support Hours</label>
                    <input type="text" name="customer_support_hours" value="{{ $settings['customer_support_hours'] ?? '' }}"
                        placeholder="e.g. Mon-Fri 9AM - 6PM"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
            </div>
        </div>

        {{-- ── SOCIAL MEDIA LINKS ────────────────────────── --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Social Media Links</h3>
            <p class="text-sm text-gray-500 mb-6">Connect your social accounts to display icons in the footer.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Instagram URL</label>
                    <input type="url" name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}"
                        placeholder="https://instagram.com/..."
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Facebook URL</label>
                    <input type="url" name="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}"
                        placeholder="https://facebook.com/..."
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Twitter (X) URL</label>
                    <input type="url" name="social_twitter" value="{{ $settings['social_twitter'] ?? '' }}"
                        placeholder="https://twitter.com/..."
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">YouTube URL</label>
                    <input type="url" name="social_youtube" value="{{ $settings['social_youtube'] ?? '' }}"
                        placeholder="https://youtube.com/..."
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">TikTok URL</label>
                    <input type="url" name="social_tiktok" value="{{ $settings['social_tiktok'] ?? '' }}"
                        placeholder="https://tiktok.com/..."
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pinterest URL</label>
                    <input type="url" name="social_pinterest" value="{{ $settings['social_pinterest'] ?? '' }}"
                        placeholder="https://pinterest.com/..."
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
            </div>
        </div>

        {{-- ── LOCALIZATION ──────────────────────────────── --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Localization</h3>
            <p class="text-sm text-gray-500 mb-6">Set your store's default currency and time preferences.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Currency</label>
                    <select name="default_currency"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        @foreach($currencies as $code => $name)
                            <option value="{{ $code }}" {{ $settings['default_currency'] == $code ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency Symbol</label>
                    <input type="text" name="currency_symbol" value="{{ $settings['currency_symbol'] }}"
                        placeholder="e.g. ₹, $"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Time Zone</label>
                    <select name="time_zone"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        @foreach($timezones as $tz)
                            <option value="{{ $tz['id'] }}" {{ $settings['time_zone'] == $tz['id'] ? 'selected' : '' }}>
                                {{ $tz['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Format</label>
                    <select name="date_format"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        <option value="d/m/Y" {{ $settings['date_format'] == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY (e.g. 28/03/2026)</option>
                        <option value="m/d/Y" {{ $settings['date_format'] == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY (e.g. 03/28/2026)</option>
                        <option value="Y-m-d" {{ $settings['date_format'] == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD (e.g. 2026-03-28)</option>
                        <option value="M j, Y" {{ $settings['date_format'] == 'M j, Y' ? 'selected' : '' }}>Month DD, YYYY (e.g. Mar 28, 2026)</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- ── STANDARDS & UNITS ─────────────────────────── --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Standards & Format</h3>
            <p class="text-sm text-gray-500 mb-6">Define the measurement systems for your products.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Weight Unit</label>
                    <select name="weight_unit"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        <option value="kg" {{ $settings['weight_unit'] == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                        <option value="g" {{ $settings['weight_unit'] == 'g' ? 'selected' : '' }}>Grams (g)</option>
                        <option value="lb" {{ $settings['weight_unit'] == 'lb' ? 'selected' : '' }}>Pounds (lb)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Length Unit</label>
                    <select name="length_unit"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        <option value="cm" {{ $settings['length_unit'] == 'cm' ? 'selected' : '' }}>Centimeters (cm)</option>
                        <option value="m" {{ $settings['length_unit'] == 'm' ? 'selected' : '' }}>Meters (m)</option>
                        <option value="in" {{ $settings['length_unit'] == 'in' ? 'selected' : '' }}>Inches (in)</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- ── BRAND ASSETS (LOGOS) ──────────────────────── --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Brand Assets</h3>
            <p class="text-sm text-gray-500 mb-6">Upload your store's main logo and favicon.</p>

            <div class="space-y-6">
                <!-- Main Logo -->
                <div class="flex items-center gap-6 p-4 bg-gray-50 rounded-lg">
                    <div class="w-24 h-24 bg-white border rounded flex items-center justify-center overflow-hidden" id="preview-container-main_logo">
                        @if($logo = ($themeSettings->get('logos', collect())->where('key', 'main_logo')->first() ?? null))
                            <img src="{{ asset($logo->value) }}" class="max-w-full max-h-full object-contain" id="img-main_logo">
                        @else
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-[10px] text-gray-400 font-bold uppercase">No Logo</span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Main Logo</label>
                        <input type="file" name="logos[main_logo]" onchange="previewImage(this, 'main_logo')" class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:font-medium file:bg-gray-100 hover:file:bg-gray-200">
                    </div>
                </div>

                <!-- Favicon -->
                <div class="flex items-center gap-6 p-4 bg-gray-50 rounded-lg">
                    <div class="w-12 h-12 bg-white border rounded flex items-center justify-center overflow-hidden" id="preview-container-favicon">
                        @if($favicon = ($themeSettings->get('logos', collect())->where('key', 'favicon')->first() ?? null))
                            <img src="{{ asset($favicon->value) }}" class="max-w-full max-h-full object-contain" id="img-favicon">
                        @else
                            <span class="text-[10px] text-gray-400 font-bold uppercase">Fav</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Favicon</label>
                        <input type="file" name="logos[favicon]" onchange="previewImage(this, 'favicon')" class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:font-medium file:bg-gray-100 hover:file:bg-gray-200">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── VISUAL IDENTITY (COLORS) ──────────────────── --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Visual Identity: Colors</h3>
            <p class="text-sm text-gray-500 mb-6">Set your primary, secondary, and accent colors.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php
                    $primary = $themeSettings->get('colors', collect())->where('key', 'primary_color')->first()->value ?? '#000000';
                    $secondary = $themeSettings->get('colors', collect())->where('key', 'secondary_color')->first()->value ?? '#ffffff';
                    $accent = $themeSettings->get('colors', collect())->where('key', 'accent_color')->first()->value ?? '#3b82f6';
                @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Primary Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="primary_color" value="{{ $primary }}" class="h-10 w-10 border border-gray-300 rounded cursor-pointer">
                        <input type="text" value="{{ $primary }}" class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm font-mono uppercase bg-gray-50" readonly>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Secondary Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="secondary_color" value="{{ $secondary }}" class="h-10 w-10 border border-gray-300 rounded cursor-pointer">
                        <input type="text" value="{{ $secondary }}" class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm font-mono uppercase bg-gray-50" readonly>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Accent Color</label>
                    <div class="flex items-center gap-2">
                        <input type="color" name="accent_color" value="{{ $accent }}" class="h-10 w-10 border border-gray-300 rounded cursor-pointer">
                        <input type="text" value="{{ $accent }}" class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm font-mono uppercase bg-gray-50" readonly>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── TYPOGRAPHY ────────────────────────────────── --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">Typography</h3>
            <p class="text-sm text-gray-500 mb-6">Choose fonts for headings and body text.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Heading Font</label>
                    <select name="heading_font" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        @foreach($googleFonts as $font)
                            <option value="{{ $font }}" {{ ($themeSettings->get('typography', collect())->where('key', 'heading_font')->first()->value ?? 'Inter') == $font ? 'selected' : '' }}>{{ $font }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Body Font</label>
                    <select name="body_font" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        @foreach($googleFonts as $font)
                            <option value="{{ $font }}" {{ ($themeSettings->get('typography', collect())->where('key', 'body_font')->first()->value ?? 'Open Sans') == $font ? 'selected' : '' }}>{{ $font }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STICKY SAVE BAR ──────────────────────────────── --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-50 md:pl-64 flex items-center justify-between shadow-lg">
        <p class="text-sm text-gray-500">Global store settings will be applied immediately.</p>
        <button type="submit"
            class="bg-black text-white px-6 py-2 rounded-md hover:bg-gray-800 text-sm font-medium transition-colors">
            Save General Settings
        </button>
    </div>
</form>
@push('scripts')
<script>
    function previewImage(input, type) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const container = document.getElementById(`preview-container-${type}`);
                let img = document.getElementById(`img-${type}`);
                
                if (!img) {
                    container.innerHTML = `<img src="${e.target.result}" class="max-w-full max-h-full object-contain" id="img-${type}">`;
                } else {
                    img.src = e.target.result;
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
@endsection

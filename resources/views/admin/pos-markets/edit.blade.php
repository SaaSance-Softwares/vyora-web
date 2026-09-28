@extends('layouts.admin')

@section('header', 'Edit Store: ' . $location->name)

@section('content')
<div class="w-full space-y-6 pb-24">
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('admin.pos-markets.index') }}" class="text-sm font-medium text-gray-600 hover:text-black transition-colors">&larr; Back to Markets</a>
    </div>

    <form action="{{ route('admin.pos-markets.update', $location->slug) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-lg shadow" x-data="{ 
            type: '{{ $location->type }}', 
            name: '{{ addslashes($location->name) }}', 
            slug: '{{ $location->slug }}', 
            manuallyEditedSlug: true,
            receipt_header: {{ json_encode($location->receipt_header ?? '') }},
            receipt_footer: {{ json_encode($location->receipt_footer ?? 'Thank you for shopping with us!') }},
            receipt_printer_size: '{{ $location->receipt_printer_size ?? '80mm' }}',
            receipt_barcode_type: '{{ $location->receipt_barcode_type ?? 'QR' }}',
            operating_hours: '{{ addslashes($location->operating_hours ?? '') }}',
            tag_printer_size: '{{ $location->tag_printer_size ?? '48x72' }}',
            tag_custom_size_w: '{{ $location->tag_custom_size_w ?? '' }}',
            tag_custom_size_h: '{{ $location->tag_custom_size_h ?? '' }}',
            tag_margin_top: '{{ $location->tag_margin_top ?? '10' }}',
            tag_margin_bottom: '{{ $location->tag_margin_bottom ?? '10' }}',
            tag_hole_top_margin: '{{ $location->tag_hole_top_margin ?? '8' }}',
            tag_barcode_type: '{{ $location->tag_barcode_type ?? 'QR' }}',
            tag_template: '{{ $location->tag_template ?? 'default' }}',
            tag_main_logo_preview: '{{ isset($location->tag_main_logo) && $location->tag_main_logo ? asset('storage/'.$location->tag_main_logo) : '' }}',
            tag_icon_preview: '{{ isset($location->tag_icon) && $location->tag_icon ? asset('storage/'.$location->tag_icon) : '' }}',
            tag_washing_preview: '{{ isset($location->tag_washing_instruction) && $location->tag_washing_instruction ? asset('storage/'.$location->tag_washing_instruction) : '' }}'
        }">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Edit Store Details</h3>
            </div>
            
                        <div class="p-6 space-y-8">

                {{-- 1. Basic Information --}}
                <div class="pb-6 border-b border-gray-200">
                    <h4 class="text-base font-medium text-gray-900 mb-4">1. Basic Information</h4>
                    <div class="grid grid-cols-1  gap-6">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                            <input type="text" name="name" x-model="name" @input="if(!manuallyEditedSlug) slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '')" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                        </div>
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700 mb-1">System Slug</label>
                            <input type="text" name="slug" x-model="slug" @input="manuallyEditedSlug = true" required class="w-full bg-gray-50 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            <p class="text-xs text-gray-500 mt-1">This is used for URLs and system records. Be careful when changing an existing slug.</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select name="type" x-model="type" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                <option value="store">Permanent Store</option>
                                <option value="temporary">Temporary Stall / Pop-up</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-6 ">
                            <!-- Status -->
                            <div class="flex flex-col justify-center">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" {{ $location->is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-700">Active</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Storefront Setup --}}
                <div class="pb-6 border-b border-gray-200">
                    <h4 class="text-base font-medium text-gray-900 mb-4">2. Storefront Setup</h4>
                    
                    <div class="mb-6 flex flex-col justify-center">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Show in Store Page</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="show_in_store" value="0">
                            <input type="checkbox" name="show_in_store" value="1" {{ ($location->show_in_store ?? 1) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Visible on Public Storefront</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1  gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Store Image</label>
                            @if(isset($location->store_image) && $location->store_image)
                                <div class="mb-3">
                                    <img src="{{ asset('storage/' . $location->store_image) }}" alt="Store Image" class="h-32 rounded-lg object-cover border border-gray-200 shadow-sm">
                                </div>
                            @endif
                            <input type="file" name="store_image" accept="image/*" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black text-sm px-3 py-2 bg-white">
                            <p class="text-xs text-gray-500 mt-1">Recommended: 800x600px. Leave empty to keep current.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Google Maps Link</label>
                            <input type="url" name="map_link" value="{{ $location->map_link ?? '' }}" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black text-sm px-3 py-2" placeholder="https://maps.google.com/...">
                        </div>
                    </div>
                </div>

                {{-- 3. Dates & Operating Hours --}}
                <div class="pb-6 border-b border-gray-200">
                    <h4 class="text-base font-medium text-gray-900 mb-4">3. Dates & Operating Hours</h4>
                    
                    <div x-show="type === 'temporary'" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm">
                        <div class="col-span-full">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Temporary Stall Duration</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" name="start_date" value="{{ $location->start_date ? \Carbon\Carbon::parse($location->start_date)->format('Y-m-d') : '' }}" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" name="end_date" value="{{ $location->end_date ? \Carbon\Carbon::parse($location->end_date)->format('Y-m-d') : '' }}" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Open Time</label>
                            <input type="time" name="open_time" value="{{ $location->open_time ? \Carbon\Carbon::parse($location->open_time)->format('H:i') : '' }}" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Close Time</label>
                            <input type="time" name="close_time" value="{{ $location->close_time ? \Carbon\Carbon::parse($location->close_time)->format('H:i') : '' }}" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                        </div>
                    </div>
                </div>

                {{-- 4. Business & Contact Details --}}
                <div class="pb-6 border-b border-gray-200">
                    <h4 class="text-base font-medium text-gray-900 mb-4">4. Business & Contact Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">GST Number</label>
                            <input type="text" name="gst_number" value="{{ $location->gst_number ?? '' }}" placeholder="e.g. 22AAAAA0000A1Z5" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black uppercase" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                            <input type="text" name="contact_phone" value="{{ $location->contact_phone ?? '' }}" placeholder="e.g. +91 9876543210" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                            <input type="email" name="contact_email" value="{{ $location->contact_email ?? '' }}" placeholder="store@example.com" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                        </div>
                    </div>
                </div>

                {{-- 5. Location Details --}}
                <div class="pb-6 border-b border-gray-200">
                    <h4 class="text-base font-medium text-gray-900 mb-4">5. Location Details</h4>
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 1</label>
                                <input type="text" name="address" value="{{ $location->address }}" placeholder="123 Street Name" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 2</label>
                                <input type="text" name="address_line_2" value="{{ $location->address_line_2 ?? '' }}" placeholder="Suite, Unit, Floor, etc." class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                                <input type="text" name="pincode" value="{{ $location->pincode }}" placeholder="110001" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                <input type="text" name="city" value="{{ $location->city }}" placeholder="New Delhi" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                                <input type="text" name="district" value="{{ $location->district ?? '' }}" placeholder="New Delhi" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                <input type="text" name="state" value="{{ $location->state }}" placeholder="Delhi" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                <input type="text" name="country" value="{{ $location->country ?? 'India' }}" placeholder="India" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 6. Receipt Settings & Preview --}}
                <div class="pt-2">
                    <h4 class="text-base font-medium text-gray-900 mb-6">6. Bill Printing Settings</h4>

                    <div class="flex flex-col lg:flex-row gap-8">
                        {{-- Left Side: Settings Form --}}
                        <div class="w-full lg:w-1/2 space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Printer Size</label>
                                <select name="receipt_printer_size" x-model="receipt_printer_size" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                    <option value="80mm">Standard Receipt (80mm)</option>
                                    <option value="58mm">Compact Receipt (58mm)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Header Text (Above Items)</label>
                                <textarea name="receipt_header" x-model="receipt_header" rows="3" placeholder="Welcome to our store!" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Footer Text (Below Totals)</label>
                                <textarea name="receipt_footer" x-model="receipt_footer" rows="3" placeholder="Thank you for shopping with us!" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bottom Barcode (For Returns)</label>
                                <select name="receipt_barcode_type" x-model="receipt_barcode_type" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                    <option value="QR">2D QR Code</option>
                                    <option value="1D">1D Barcode</option>
                                    <option value="None">None</option>
                                </select>
                            </div>
                        </div>

                        {{-- Right Side: Live Thermal Preview --}}
                        <div class="w-full lg:w-1/2 bg-gray-100 p-6 rounded-lg flex flex-col items-center justify-center border border-gray-200">
                            <p class="text-xs font-bold text-gray-500 mb-4 uppercase tracking-widest bg-white px-3 py-1 rounded-full border border-gray-200">Live Thermal Preview</p>
                            
                            <div class="bg-white shadow-lg p-6 text-black font-mono transition-all duration-300 relative mx-auto"
                                 :style="receipt_printer_size === '58mm' ? 'width: 280px; min-height: 400px;' : 'width: 380px; min-height: 400px;'">
                                
                                {{-- Zigzag Top --}}
                                <div class="absolute top-0 left-0 right-0 h-2 -mt-2 opacity-50 flex overflow-hidden">
                                    @for($i = 0; $i < 30; $i++)
                                        <div class="w-3 h-3 bg-white rotate-45 transform -translate-y-2 translate-x-1 shrink-0"></div>
                                    @endfor
                                </div>
                                
                                {{-- Receipt Content --}}
                                <div class="text-center mb-6 border-b border-dashed border-gray-400 pb-4">
                                    @if(isset($mainLogoUrl) && $mainLogoUrl)
                                        <div class="flex justify-center mb-3">
                                            <img src="{{ $mainLogoUrl }}" alt="Logo" class="max-h-12" style="filter: grayscale(100%);">
                                        </div>
                                    @endif
                                    <h1 class="text-xl font-bold uppercase mb-2 leading-none" x-text="name || 'Store Name'"></h1>
                                    <p class="text-xs whitespace-pre-wrap leading-tight text-gray-700" x-show="receipt_header" x-text="receipt_header"></p>
                                    <p class="text-[11px] mt-3 text-gray-500">Date: {{ now()->format('d/m/Y H:i') }}</p>
                                    <p class="text-[11px] text-gray-500">Receipt #: POS-123456</p>
                                </div>

                                <div class="mb-4">
                                    <table class="w-full text-xs">
                                        <thead>
                                            <tr class="border-b border-dashed border-gray-400">
                                                <th class="text-left pb-2 text-gray-600 font-medium">Item</th>
                                                <th class="text-center pb-2 text-gray-600 font-medium">Qty</th>
                                                <th class="text-right pb-2 text-gray-600 font-medium">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="py-2 pr-2 font-medium">Example Product</td>
                                                <td class="text-center py-2">1</td>
                                                <td class="text-right py-2 font-medium">₹499.00</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 pr-2 font-medium">Test Item 2</td>
                                                <td class="text-center py-2">2</td>
                                                <td class="text-right py-2 font-medium">₹1000.00</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="border-t border-dashed border-gray-400 pt-3 mb-6 space-y-1.5">
                                    <div class="flex justify-between font-bold text-[15px] mt-2 pt-2 border-t border-dashed border-gray-400">
                                        <span>TOTAL:</span>
                                        <span>₹1499.00</span>
                                    </div>
                                </div>

                                <div class="text-center pt-2 border-t border-dashed border-gray-400">
                                    <p class="text-[11px] whitespace-pre-wrap mb-5 text-gray-600" x-show="receipt_footer" x-text="receipt_footer"></p>
                                    
                                    <div x-show="receipt_barcode_type === 'QR'" class="flex justify-center mb-2">
                                        <div class="w-24 h-24 border-[3px] border-black p-1 flex items-center justify-center relative">
                                            <div class="w-full h-full bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTMgM2g4djhIM3YtOGptMiAydjRoNHYtNEg1em0xNCAwdi0yaC04djhoOHYtNGgtMnYtMmgyeXpNMyAxM2g4djhIM3YtOGptMiAydjRoNHYtNEg1em0xNiAySDE1djRINDUydi0yaDR2LTJoMnY0aDJ2LTR6bS0yLTJoLTR2MmgydjIuNWgzdi00LjVoLTF6IiBmaWxsPSJjdXJyZW50Q29sb3IiLz48L3N2Zz4=')] opacity-80"></div>
                                        </div>
                                    </div>
                                    
                                    <div x-show="receipt_barcode_type === '1D'" class="flex justify-center mb-2">
                                        <div class="w-48 h-12 bg-[repeating-linear-gradient(90deg,#000,#000_3px,transparent_3px,transparent_6px,#000_6px,#000_8px,transparent_8px,transparent_11px)] opacity-90"></div>
                                    </div>
                                    
                                    <p x-show="receipt_barcode_type !== 'None'" class="text-[10px] tracking-widest mt-1 text-gray-500">POS-123456</p>
                                </div>

                                {{-- Zigzag Bottom --}}
                                <div class="absolute bottom-0 left-0 right-0 h-2 -mb-2 opacity-50 flex overflow-hidden">
                                    @for($i = 0; $i < 30; $i++)
                                        <div class="w-3 h-3 bg-white rotate-45 transform translate-y-2 translate-x-1 shrink-0"></div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 7. Tag Settings & Preview --}}
                <div class="pt-2 mt-6 border-t border-gray-200">
                    <h4 class="text-base font-medium text-gray-900 mb-6 mt-6">7. Tag Settings</h4>

                    <div class="flex flex-col lg:flex-row gap-8">
                        {{-- Left Side: Settings Form --}}
                        <div class="w-full lg:w-1/2 space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Printer Size</label>
                                <select name="tag_printer_size" x-model="tag_printer_size" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                    <option value="48x72">48x72 (widthxheight)</option>
                                    <option value="50x25">50mmx25mm (widthxheight)</option>
                                    <option value="custom">Custom Size</option>
                                </select>
                            </div>

                            <div x-show="tag_printer_size === 'custom'" class="flex gap-4">
                                <div class="w-1/2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Width (mm/inch)</label>
                                    <input type="text" name="tag_custom_size_w" x-model="tag_custom_size_w" placeholder="e.g. 50mm" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                </div>
                                <div class="w-1/2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Height (mm/inch)</label>
                                    <input type="text" name="tag_custom_size_h" x-model="tag_custom_size_h" placeholder="e.g. 80mm" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="w-1/3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Top Margin</label>
                                    <input type="text" name="tag_margin_top" x-model="tag_margin_top" placeholder="10px or 2mm" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                </div>
                                <div class="w-1/3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Bottom Margin</label>
                                    <input type="text" name="tag_margin_bottom" x-model="tag_margin_bottom" placeholder="10px or 2mm" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                </div>
                                <div class="w-1/3">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Hole Top Margin</label>
                                    <input type="text" name="tag_hole_top_margin" x-model="tag_hole_top_margin" placeholder="8px" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Main Logo</label>
                                <div class="flex items-center gap-4">
                                    <input type="file" name="tag_main_logo" accept="image/*" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => tag_main_logo_preview = e.target.result; reader.readAsDataURL(file); }">
                                    <div x-show="tag_main_logo_preview" class="flex flex-col items-center gap-1">
                                        <img :src="tag_main_logo_preview" class="h-10 w-auto border border-gray-200 rounded p-1 object-contain bg-white">
                                        <button type="button" @click="tag_main_logo_preview = ''; document.querySelector('input[name=tag_main_logo]').value = '';" class="text-xs text-red-600 hover:underline">Remove</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                                <div class="flex items-center gap-4">
                                    <input type="file" name="tag_icon" accept="image/*" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => tag_icon_preview = e.target.result; reader.readAsDataURL(file); }">
                                    <div x-show="tag_icon_preview" class="flex flex-col items-center gap-1">
                                        <img :src="tag_icon_preview" class="h-10 w-auto border border-gray-200 rounded p-1 object-contain bg-white">
                                        <button type="button" @click="tag_icon_preview = ''; document.querySelector('input[name=tag_icon]').value = '';" class="text-xs text-red-600 hover:underline">Remove</button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Washing Instruction Image</label>
                                <div class="flex items-center gap-4">
                                    <input type="file" name="tag_washing_instruction" accept="image/*" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" @change="const file = $event.target.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => tag_washing_preview = e.target.result; reader.readAsDataURL(file); }">
                                    <div x-show="tag_washing_preview" class="flex flex-col items-center gap-1">
                                        <img :src="tag_washing_preview" class="h-10 w-auto border border-gray-200 rounded p-1 object-contain bg-white">
                                        <button type="button" @click="tag_washing_preview = ''; document.querySelector('input[name=tag_washing_instruction]').value = '';" class="text-xs text-red-600 hover:underline">Remove</button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                                <select name="tag_barcode_type" x-model="tag_barcode_type" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                    <option value="1D">1D Barcode</option>
                                    <option value="2D">2D QR Code</option>
                                    <option value="None">None</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Select Template</label>
                                <select name="tag_template" x-model="tag_template" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                    <option value="default">Default Template</option>
                                </select>
                            </div>
                        </div>

                        {{-- Right Side: Live Tag Preview --}}
                        <div class="w-full lg:w-1/2 bg-gray-100 p-6 rounded-lg flex flex-col items-center justify-center border border-gray-200">
                            <p class="text-xs font-bold text-gray-500 mb-4 uppercase tracking-widest bg-white px-3 py-1 rounded-full border border-gray-200">Live Tag Preview</p>
                            
                            <div class="bg-white border border-gray-300 px-4 text-black font-sans transition-all duration-300 relative mx-auto flex flex-col justify-between overflow-hidden"
                                 :style="(tag_printer_size === '48x72' ? 'width: 250px; height: 375px;' : (tag_printer_size === '50x25' ? 'width: 300px; height: 150px;' : 'width: 250px; height: 375px;')) + ` padding-top: ${tag_margin_top ? (isNaN(tag_margin_top) ? tag_margin_top : tag_margin_top + 'px') : '8px'}; padding-bottom: ${tag_margin_bottom ? (isNaN(tag_margin_bottom) ? tag_margin_bottom : tag_margin_bottom + 'px') : '8px'};`">
                                
                                {{-- Visual Hole Punch (Preview Only) --}}
                                <div class="absolute left-1/2 transform -translate-x-1/2 w-4 h-4 bg-gray-100 rounded-full border border-gray-300 shadow-inner z-10" 
                                     :style="`top: ${tag_hole_top_margin ? (isNaN(tag_hole_top_margin) ? tag_hole_top_margin : tag_hole_top_margin + 'px') : '8px'};`" title="Physical Tag Hole"></div>
                                
                                {{-- Black line --}}
                                <div class="w-full border-t border-black mb-3"></div>

                                {{-- Logo --}}
                                <div class="flex justify-center mb-4 h-8 w-full">
                                    <img x-show="tag_main_logo_preview" :src="tag_main_logo_preview" alt="Logo" class="max-h-full max-w-full object-contain filter grayscale">
                                    <div x-show="!tag_main_logo_preview" class="font-bold text-sm">Logo</div>
                                </div>

                                {{-- Grid Area --}}
                                <div class="w-full border border-black flex mb-1 mt-1 bg-white">
                                    {{-- Left Box for Icon --}}
                                    <div class="w-[35%] border-r border-black flex items-center justify-center p-2 min-h-[70px]">
                                        <img x-show="tag_icon_preview" :src="tag_icon_preview" alt="Icon" class="max-h-full max-w-full object-contain filter grayscale mx-auto">
                                        <div x-show="!tag_icon_preview" class="font-bold text-xs w-full text-center">{icon}</div>
                                    </div>
                                    
                                    {{-- Right Side --}}
                                    <div class="w-[65%] flex flex-col min-h-[70px]">
                                        {{-- Top Row --}}
                                        <div class="flex border-b border-black flex-1">
                                            {{-- Size --}}
                                            <div class="w-[40%] border-r border-black flex items-center py-1">
                                                <div class="w-full text-center font-bold text-xl tracking-tight leading-tight">S</div>
                                            </div>
                                            {{-- Chest --}}
                                            <div class="w-[30%] border-r border-black flex items-center py-1">
                                                <div class="w-full text-center">
                                                    <div class="text-[8px] mb-0.5 leading-tight">Chest</div>
                                                    <div class="text-sm font-semibold leading-tight">38</div>
                                                </div>
                                            </div>
                                            {{-- Length --}}
                                            <div class="w-[30%] flex items-center py-1">
                                                <div class="w-full text-center">
                                                    <div class="text-[8px] mb-0.5 leading-tight">Length</div>
                                                    <div class="text-sm font-semibold leading-tight">28</div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Bottom Row --}}
                                        <div class="flex flex-1 items-center py-1">
                                            <div class="w-full text-center font-bold text-[9px] leading-tight">{Fabric Name}</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Product Name --}}
                                <div class="text-center mb-3 mt-1">
                                    <p class="text-[11px] leading-tight">{Product name}</p>
                                </div>

                                {{-- Washing Instructions --}}
                                <div class="text-center mb-4">
                                    <div class="text-[8px] uppercase mb-1">WASHING INSTRUCTIONS</div>
                                    <div class="flex justify-center h-6 w-full text-center">
                                        <img x-show="tag_washing_preview" :src="tag_washing_preview" alt="Washing" class="max-h-full max-w-full object-contain filter grayscale mx-auto">
                                        <div x-show="!tag_washing_preview" class="text-[10px] text-gray-400 w-full flex items-center justify-center italic text-center">Washing Icons</div>
                                    </div>
                                </div>

                                {{-- Price and Barcode --}}
                                <div class="flex mt-2 mb-1 w-full items-center min-h-[60px]">
                                    {{-- Price Column --}}
                                    <div class="w-[calc(100%-90px)] flex flex-col justify-center items-center">
                                        <div class="px-1 mb-0.5 w-full flex justify-center text-center">
                                            <div class="relative inline-block">
                                                <span class="text-[12px] text-gray-800 font-medium">MRP: ₹{xxxx}</span>
                                                <div class="absolute left-0 w-full h-[1px] bg-black bottom-[4px]"></div>
                                            </div>
                                        </div>
                                        <div class="text-[11px] text-gray-600 mb-0.5 w-full text-center">Offer Price</div>
                                        <div class="font-bold text-xl leading-none w-full text-center mt-0.5">₹{xxx}</div>
                                    </div>
                                    
                                    {{-- QR Code Column --}}
                                    <div class="w-[90px] flex flex-col items-center justify-center text-center">
                                        <div x-show="tag_barcode_type === '2D'" class="w-[80px] h-[80px] flex items-center justify-center mx-auto">
                                            <div class="w-full h-full bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTMgM2g4djhIM3YtOGptMiAydjRoNHYtNEg1em0xNCAwdi0yaC04djhoOHYtNGgtMnYtMmgyeXpNMyAxM2g4djhIM3YtOGptMiAydjRoNHYtNEg1em0xNiAySDE1djRINDUydi0yaDR2LTJoMnY0aDJ2LTR6bS0yLTJoLTR2MmgydjIuNWgzdi00LjVoLTF6IiBmaWxsPSJjdXJyZW50Q29sb3IiLz48L3N2Zz4=')] bg-contain bg-no-repeat bg-center"></div>
                                        </div>
                                        <div x-show="tag_barcode_type === '1D'" class="w-[80px] h-[48px] flex items-center justify-center overflow-hidden bg-[repeating-linear-gradient(90deg,#000,#000_2px,transparent_2px,transparent_4px,#000_4px,#000_6px,transparent_6px,transparent_8px)] mx-auto">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 border-t border-gray-200 p-5 flex justify-end gap-3 rounded-b-lg">
                <a href="{{ route('admin.pos-markets.index') }}" class="px-6 py-2 rounded-md font-medium text-gray-700 hover:bg-gray-100 transition-colors">Cancel</a>
                <button type="submit" class="bg-black text-white px-6 py-2 rounded-md hover:bg-gray-800 text-sm font-medium transition-colors">
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

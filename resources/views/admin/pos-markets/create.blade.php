@extends('layouts.admin')

@section('header', 'Create POS Store')

@section('content')
<div class="space-y-8 pb-24" x-data="{ type: 'store', name: '', slug: '', manuallyEditedSlug: false }">
    
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Create New Store / Market</h3>
            <p class="text-sm text-gray-500">Add a physical location to sync inventory and process offline orders.</p>
        </div>
        <a href="{{ route('admin.pos-markets.index') }}" class="text-sm text-gray-600 hover:text-gray-900 font-medium bg-gray-100 px-4 py-2 rounded-md hover:bg-gray-200 transition-colors">
            &larr; Back to Stores
        </a>
    </div>

    <form action="{{ route('admin.pos-markets.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="bg-white rounded-lg shadow">
                        <div class="p-6 space-y-8">

                {{-- 1. Basic Information --}}
                <div class="pb-6 border-b border-gray-200">
                    <h4 class="text-base font-medium text-gray-900 mb-4">1. Basic Information</h4>
                    <div class="grid grid-cols-1  gap-6">
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
                            <input type="text" name="name" x-model="name" @input="if(!manuallyEditedSlug) slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '')" placeholder="e.g. Delhi Pop-up!" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                        </div>
                        <div class="">
                            <label class="block text-sm font-medium text-gray-700 mb-1">System Slug</label>
                            <input type="text" name="slug" x-model="slug" @input="manuallyEditedSlug = true" placeholder="e.g. delhi-pop-up" required class="w-full bg-gray-50 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            <p class="text-xs text-gray-500 mt-1">This is used for URLs and system records. It will automatically generate from the name unless you change it.</p>
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
                                    <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
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
                            <input type="checkbox" name="show_in_store" value="1" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                            <span class="ml-3 text-sm font-medium text-gray-700">Visible on Public Storefront</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1  gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Store Image</label>
                            <input type="file" name="store_image" accept="image/*" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black text-sm px-3 py-2 bg-white">
                            <p class="text-xs text-gray-500 mt-1">Recommended: 800x600px</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Google Maps Link</label>
                            <input type="url" name="map_link" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-black focus:border-black text-sm px-3 py-2" placeholder="https://maps.google.com/...">
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
                            <input type="date" name="start_date" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" name="end_date" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Open Time</label>
                            <input type="time" name="open_time" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Close Time</label>
                            <input type="time" name="close_time" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                        </div>
                    </div>
                </div>

                {{-- 4. Business & Contact Details --}}
                <div class="pb-6 border-b border-gray-200">
                    <h4 class="text-base font-medium text-gray-900 mb-4">4. Business & Contact Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">GST Number</label>
                            <input type="text" name="gst_number" placeholder="e.g. 22AAAAA0000A1Z5" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black uppercase" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                            <input type="text" name="contact_phone" placeholder="e.g. +91 9876543210" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                            <input type="email" name="contact_email" placeholder="store@example.com" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                        </div>
                    </div>
                </div>

                {{-- 5. Location Details --}}
                <div class="pt-2">
                    <h4 class="text-base font-medium text-gray-900 mb-4">5. Location Details</h4>
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 1</label>
                                <input type="text" name="address" placeholder="123 Street Name" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 2</label>
                                <input type="text" name="address_line_2" placeholder="Suite, Unit, Floor, etc." class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                                <input type="text" name="pincode" placeholder="110001" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                <input type="text" name="city" placeholder="New Delhi" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">District</label>
                                <input type="text" name="district" placeholder="New Delhi" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                <input type="text" name="state" placeholder="Delhi" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                <input type="text" name="country" value="India" placeholder="India" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-black" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="bg-gray-50 border-t border-gray-200 p-5 flex justify-end rounded-b-lg">
                <button type="submit" class="bg-black text-white px-6 py-2 rounded-md hover:bg-gray-800 text-sm font-medium transition-colors">
                    Save Store
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

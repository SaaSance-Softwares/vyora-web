@extends('layouts.admin')

@section('header', 'Sign In & Sign Up Designer')

@section('content')
<div class="max-w-7xl mx-auto pb-32" x-data="authDesigner()">
    <form action="{{ route('admin.online-store.auth-settings.update') }}" method="POST" enctype="multipart/form-data" @submit.prevent="prepSubmit">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="auth_fields" x-ref="hiddenFields">
        <input type="hidden" name="auth_social" x-ref="hiddenSocial">
        <input type="hidden" name="auth_appearance" x-ref="hiddenAppearance">
        <input type="hidden" name="auth_header" x-ref="hiddenHeader">
        <input type="hidden" name="auth_footer" x-ref="hiddenFooter">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Settings Panel -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- Tabs Navigation -->
                <div class="flex items-center space-x-1 bg-white p-1 rounded-xl border border-gray-200 shadow-sm overflow-x-auto whitespace-nowrap">
                    <button type="button" @click="tab = 'fields'" :class="tab === 'fields' ? 'bg-black text-white shadow-sm' : 'text-gray-500 hover:text-black'" class="flex-1 py-2.5 px-4 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
                        Form Fields & Logic
                    </button>
                    <button type="button" @click="tab = 'appearance'" :class="tab === 'appearance' ? 'bg-black text-white shadow-sm' : 'text-gray-500 hover:text-black'" class="flex-1 py-2.5 px-4 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
                        Visual Appearance
                    </button>
                    <button type="button" @click="tab = 'social'" :class="tab === 'social' ? 'bg-black text-white shadow-sm' : 'text-gray-500 hover:text-black'" class="flex-1 py-2.5 px-4 rounded-lg text-xs font-bold uppercase tracking-wider transition-all">
                        Social Auth
                    </button>
                </div>

                <!-- Tab: Form Fields -->
                <div x-show="tab === 'fields'" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-900 leading-tight">Registration Fields & Authentication Logic</h3>
                        <p class="text-xs text-gray-500 mt-1 uppercase tracking-widest font-semibold italic">Define what data you collect and how users authenticate</p>
                    </div>
                    <div class="p-6 space-y-8">
                        
                        <!-- Name Field -->
                        <div class="p-5 border border-gray-100 rounded-2xl bg-gray-50/30">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <span class="font-bold text-sm text-gray-900">User Full Name</span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"  x-model="config.fields.name.visible" class="w-4 h-4 accent-black">
                                        <span class="text-[10px] uppercase font-black text-gray-500">Visible</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"  x-model="config.fields.name.required" class="w-4 h-4 accent-black">
                                        <span class="text-[10px] uppercase font-black text-gray-500">Required</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Email Field -->
                        <div class="p-5 border border-gray-100 rounded-2xl bg-gray-50/30">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <span class="font-bold text-sm text-gray-900">Email Address</span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"  x-model="config.fields.email.visible" class="w-4 h-4 accent-black">
                                        <span class="text-[10px] uppercase font-black text-gray-500">Visible</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"  x-model="config.fields.email.required" class="w-4 h-4 accent-black">
                                        <span class="text-[10px] uppercase font-black text-gray-500">Required</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="pl-11" x-show="config.fields.email.visible">
                                <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3">Email Authorization Type</label>
                                <div class="flex flex-wrap gap-3">
                                    <label class="flex items-center gap-2 px-3 py-2 border rounded-xl cursor-pointer hover:border-black transition-all" :class="config.fields.email.auth_type === 'data_entry' ? 'bg-black text-white border-black' : 'bg-white text-gray-600 border-gray-200'">
                                        <input type="radio"  value="data_entry" x-model="config.fields.email.auth_type" class="sr-only">
                                        <span class="text-xs font-bold">Just Data Entry</span>
                                    </label>
                                    <label class="flex items-center gap-2 px-3 py-2 border rounded-xl cursor-pointer hover:border-black transition-all" :class="config.fields.email.auth_type === 'verification' ? 'bg-black text-white border-black' : 'bg-white text-gray-600 border-gray-200'">
                                        <input type="radio"  value="verification" x-model="config.fields.email.auth_type" class="sr-only">
                                        <span class="text-xs font-bold">Email Verification</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Phone Field -->
                        <div class="p-5 border border-gray-100 rounded-2xl bg-gray-50/30">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <span class="font-bold text-sm text-gray-900">Phone Number</span>
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"  x-model="config.fields.phone.visible" class="w-4 h-4 accent-black">
                                        <span class="text-[10px] uppercase font-black text-gray-500">Visible</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"  x-model="config.fields.phone.required" class="w-4 h-4 accent-black">
                                        <span class="text-[10px] uppercase font-black text-gray-500">Required</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="pl-11" x-show="config.fields.phone.visible">
                                <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3">Phone Authorization Type</label>
                                <div class="flex flex-wrap gap-3">
                                    <label class="flex items-center gap-2 px-3 py-2 border rounded-xl cursor-pointer hover:border-black transition-all" :class="config.fields.phone.auth_type === 'data_entry' ? 'bg-black text-white border-black' : 'bg-white text-gray-600 border-gray-200'">
                                        <input type="radio"  value="data_entry" x-model="config.fields.phone.auth_type" class="sr-only">
                                        <span class="text-xs font-bold">Just Data Entry</span>
                                    </label>
                                    
                                    <label class="flex items-center gap-2 px-3 py-2 border rounded-xl transition-all relative group {{ $twilioEnabled ? 'cursor-pointer hover:border-black' : 'opacity-50 cursor-not-allowed bg-gray-50' }}" 
                                           :class="config.fields.phone.auth_type === 'sms_otp' ? 'bg-black text-white border-black' : '{{ $twilioEnabled ? "bg-white text-gray-600 border-gray-200" : "text-gray-400 border-gray-200" }}'">
                                        <input type="radio"  value="sms_otp" x-model="config.fields.phone.auth_type" class="sr-only" {{ $twilioEnabled ? '' : 'disabled' }}>
                                        <span class="text-xs font-bold italic uppercase">SMS OTP</span>
                                        @if(!$twilioEnabled)
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block w-max bg-black text-white text-[10px] px-2 py-1 rounded shadow-lg z-10">Requires Twilio Integration</div>
                                        @endif
                                    </label>
                                    
                                    <label class="flex items-center gap-2 px-3 py-2 border rounded-xl transition-all relative group {{ $whatsappEnabled ? 'cursor-pointer hover:border-black' : 'opacity-50 cursor-not-allowed bg-gray-50' }}" 
                                           :class="config.fields.phone.auth_type === 'whatsapp_otp' ? 'bg-black text-white border-black' : '{{ $whatsappEnabled ? "bg-white text-gray-600 border-gray-200" : "text-gray-400 border-gray-200" }}'">
                                        <input type="radio"  value="whatsapp_otp" x-model="config.fields.phone.auth_type" class="sr-only" {{ $whatsappEnabled ? '' : 'disabled' }}>
                                        <span class="text-xs font-bold italic uppercase">WhatsApp OTP</span>
                                        @if(!$whatsappEnabled)
                                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block w-max bg-black text-white text-[10px] px-2 py-1 rounded shadow-lg z-10">Requires WhatsApp Integration</div>
                                        @endif
                                    </label>
                                </div>
                                
                                <!-- OTP Template Configuration -->
                                <div class="mt-4 p-4 border border-gray-200 bg-gray-50 rounded-xl" x-show="config.fields.phone.auth_type === 'sms_otp'">
                                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">SMS OTP Template</label>
                                    <textarea  x-model="config.fields.phone.sms_template" rows="2" 
                                        class="w-full bg-white border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-black rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                        placeholder="Your OTP is {otp}"></textarea>
                                    <p class="mt-2 text-[10px] font-bold text-gray-400 uppercase">Use <span class="text-black bg-gray-200 px-1 py-0.5 rounded">{otp}</span> where the code should appear.</p>
                                </div>
                                
                                <div class="mt-4 p-4 border border-gray-200 bg-gray-50 rounded-xl" x-show="config.fields.phone.auth_type === 'whatsapp_otp'">
                                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">WhatsApp OTP Template Name</label>
                                    <input type="text"  x-model="config.fields.phone.whatsapp_template" 
                                        class="w-full bg-white border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-black rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                                        placeholder="e.g. user_verification_otp">
                                    <p class="mt-2 text-[10px] font-bold text-gray-400 uppercase leading-relaxed">
                                        WhatsApp strictly requires pre-approved templates. Enter the exact name of your template from the Meta Developer Dashboard.<br>
                                        Your template <span class="text-red-500">must contain exactly one variable {{1}}</span> which will be dynamically replaced by the OTP code.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Tab: Appearance -->
                <div x-show="tab === 'appearance'" class="space-y-6">
                    <!-- Global Logic -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <label class="block text-xs font-black uppercase tracking-[0.2em] text-gray-400 mb-4 ml-1 italic leading-tight">UX Delivery Mode</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative flex flex-col items-center p-4 border-2 rounded-2xl cursor-pointer transition-all" :class="config.appearance.ux_mode === 'page' ? 'border-black bg-black text-white' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                                <input type="radio"  value="page" x-model="config.appearance.ux_mode" class="sr-only">
                                <span class="font-bold text-sm">Separate Page</span>
                                <span class="text-[10px] uppercase font-bold tracking-widest mt-1 opacity-60">Classic URL Routes</span>
                            </label>
                            <label class="relative flex flex-col items-center p-4 border-2 rounded-2xl cursor-pointer transition-all" :class="config.appearance.ux_mode === 'modal' ? 'border-black bg-black text-white' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                                <input type="radio"  value="modal" x-model="config.appearance.ux_mode" class="sr-only">
                                <span class="font-bold text-sm">Modal Pop-up</span>
                                <span class="text-[10px] uppercase font-bold tracking-widest mt-1 opacity-60">Zero Page Reload</span>
                            </label>
                        </div>
                    </div>

                    <!-- Styling -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h4 class="font-bold text-gray-900 border-b pb-4 mb-6">Live Card Styling</h4>
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-[0.2em] text-gray-400 mb-2 ml-1">Card Rounding (px)</label>
                                <input type="range"  min="0" max="64" x-model="config.appearance.border_radius" class="w-full accent-black">
                                <div class="flex justify-between text-[10px] font-bold text-gray-400 mt-1 uppercase">
                                    <span>Sharp</span>
                                    <span x-text="config.appearance.border_radius + 'px'"></span>
                                    <span>Pill</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-[0.2em] text-gray-400 mb-2 ml-1">Border Color</label>
                                <div class="flex items-center gap-2">
                                    <input type="color"  x-model="config.appearance.border_color" class="h-10 w-10 border rounded cursor-pointer">
                                    <input type="text" x-model="config.appearance.border_color" class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm font-mono uppercase" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Blocks -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h4 class="font-bold text-gray-900 border-b pb-4 mb-6">Header & Footer Content</h4>
                        
                        <!-- Above Form Reordering -->
                        <div class="space-y-4 mb-10">
                            <label class="inline-block text-[10px] font-black uppercase tracking-[0.2em] bg-black text-white px-3 py-1 rounded-full mb-2">Above Form (Header Delivery)</label>
                            
                            <!-- Layout Selector UI -->
                            <div class="space-y-3 relative bg-gray-50 p-4 border border-gray-200 rounded-xl">
                                <label class="block text-xs font-bold text-gray-500 mb-1">Layout Order</label>
                                <select x-on:change="config.header.order = $event.target.value === 'image_first' ? ['image', 'text'] : ['text', 'image']" 
                                        class="w-full border p-2 rounded-lg text-sm focus:border-black outline-none bg-white">
                                    <option value="image_first" :selected="config.header.order[0] === 'image'">Image Above Text</option>
                                    <option value="text_first" :selected="config.header.order[0] === 'text'">Text Above Image</option>
                                </select>
                                
                                <!-- Hidden inputs to send to backend -->
                                <input type="hidden"  :value="config.header.order[0] || 'image'">
                                <input type="hidden"  :value="config.header.order[1] || 'text'">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 pt-6 border-t border-dashed">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 mb-1">Header Text</label>
                                        <input type="text"  x-model="config.header.text" class="w-full border p-2 rounded-lg text-sm focus:border-black outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 mb-1">Header Image</label>
                                        <input type="file" name="auth_header_image" x-on:change="previewFile($event, 'header')" class="text-xs file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-black file:text-white hover:file:bg-gray-800">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2">Image Width (px)</label>
                                    <input type="range"  min="40" max="400" x-model="config.header.image_width" class="w-full accent-black">
                                    <div class="flex justify-between text-[10px] font-bold text-gray-400 mt-1 uppercase">
                                        <span>Small</span>
                                        <span x-text="config.header.image_width + 'px'"></span>
                                        <span>Full</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Content -->
                        <div class="space-y-4 pt-8 border-t">
                            <label class="inline-block text-[10px] font-black uppercase tracking-[0.2em] bg-gray-200 text-gray-600 px-3 py-1 rounded-full mb-2">Below Form (Footer)</label>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Footer Text</label>
                                    <textarea  x-model="config.footer.text" class="w-full border p-2 rounded-lg text-sm focus:border-black outline-none" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab: Social Login -->
                <div x-show="tab === 'social'" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="font-bold text-gray-900 leading-tight">Social Integration</h3>
                        <p class="text-xs text-gray-500 mt-1 uppercase tracking-widest font-semibold italic">Boost conversion with one-tap login</p>
                    </div>
                    <div class="p-6 space-y-8">
                        @foreach(['google' => 'Google Account', 'facebook' => 'Facebook Connect'] as $key => $label)
                        <div class="space-y-4 p-4 border-2 rounded-2xl" :class="config.social.{{ $key }}.enabled ? 'border-gray-900' : 'border-gray-50'">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center font-black text-xs uppercase">{{ substr($key, 0, 1) }}</div>
                                    <span class="font-bold text-sm">{{ $label }}</span>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox"  x-model="config.social.{{ $key }}.enabled" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                                </label>
                            </div>
                            <div x-show="config.social.{{ $key }}.enabled" class="space-y-2 pt-2 border-t">
                                <label class="block text-[10px] font-black uppercase text-gray-400">Client ID / App Key</label>
                                <input type="text"  x-model="config.social.{{ $key }}.client_id" class="w-full border p-3 rounded-xl text-xs font-mono" placeholder="paste_key_here">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Preview Panel -->
            <div class="lg:col-span-5 sticky top-24">
                <div class="bg-white rounded-2xl shadow-2xl shadow-black/5 border border-gray-100 overflow-hidden">
                    <div class="p-4 border-b border-gray-50 flex items-center justify-between bg-gray-50/30">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 italic">Live Mockup</span>
                        <div class="flex gap-1.5">
                            <div class="w-2 h-2 rounded-full bg-red-400"></div>
                            <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                            <div class="w-2 h-2 rounded-full bg-green-400"></div>
                        </div>
                    </div>
                    
                    <!-- Preview Container -->
                    <div class="p-8 bg-[#FDFDFD] min-h-[500px] flex items-center justify-center">
                        <div class="w-full max-w-[340px] text-center">
                            
                            <!-- Mock Above Section -->
                            <div class="mb-6 space-y-4">
                                <!-- First Element -->
                                <div>
                                    <template x-if="config.header.order[0] === 'image'">
                                        <div class="relative mx-auto" :style="{ width: config.header.image_width + 'px' }">
                                            <template x-if="config.header.image">
                                                <img :src="config.header.image" class="w-full h-auto object-contain mx-auto mb-2">
                                            </template>
                                            <template x-if="!config.header.image">
                                                <div class="w-full aspect-square bg-gray-100 rounded-lg flex items-center justify-center text-[10px] font-bold text-gray-300 uppercase italic">Header Image</div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="config.header.order[0] === 'text'">
                                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 mt-2" 
                                            x-text="config.header.text"
                                            :style="{ fontFamily: '\'{{ $brand['heading_font'] }}\', sans-serif' }">
                                        </h2>
                                    </template>
                                </div>
                                
                                <!-- Second Element -->
                                <div>
                                    <template x-if="config.header.order[1] === 'image'">
                                        <div class="relative mx-auto" :style="{ width: config.header.image_width + 'px' }">
                                            <template x-if="config.header.image">
                                                <img :src="config.header.image" class="w-full h-auto object-contain mx-auto mb-2">
                                            </template>
                                            <template x-if="!config.header.image">
                                                <div class="w-full aspect-square bg-gray-100 rounded-lg flex items-center justify-center text-[10px] font-bold text-gray-300 uppercase italic">Header Image</div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="config.header.order[1] === 'text'">
                                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 mt-2" 
                                            x-text="config.header.text"
                                            :style="{ fontFamily: '\'{{ $brand['heading_font'] }}\', sans-serif' }">
                                        </h2>
                                    </template>
                                </div>
                            </div>

                            <!-- Mock Card -->
                            <div class="bg-white p-6 shadow-[0_10px_30px_rgba(0,0,0,0.03)] border-2 text-left"
                                 :style="{ borderRadius: config.appearance.border_radius + 'px', borderColor: config.appearance.border_color }">
                                
                                <div class="space-y-4">
                                    <!-- Dynamic Fields Rendering -->
                                    <div class="space-y-3">
                                        <template x-if="config.fields.name.visible">
                                            <div class="space-y-1">
                                                <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Full Name</label>
                                                <div class="h-10 bg-gray-50 rounded-lg border border-gray-100"></div>
                                            </div>
                                        </template>

                                        <template x-if="config.fields.email.visible">
                                            <div class="space-y-1">
                                                <div class="flex justify-between items-center">
                                                    <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400" x-text="config.fields.email.auth_type === 'verification' ? 'Email (Verify)' : 'Email Address'"></label>
                                                </div>
                                                <div class="h-10 bg-gray-50 rounded-lg border border-gray-100"></div>
                                            </div>
                                        </template>

                                        <template x-if="config.fields.phone.visible">
                                            <div class="space-y-1">
                                                <div class="flex justify-between items-center">
                                                    <label class="text-[10px] font-bold uppercase tracking-wider text-gray-400" 
                                                           x-text="config.fields.phone.auth_type === 'sms_otp' ? 'Phone Number (SMS OTP)' : 
                                                                  (config.fields.phone.auth_type === 'whatsapp_otp' ? 'Phone Number (WhatsApp OTP)' : 'Phone Number')">
                                                    </label>
                                                </div>
                                                <div class="h-10 bg-gray-50 rounded-lg border border-gray-100"></div>
                                            </div>
                                        </template>
                                    </div>

                                    <button type="button" class="w-full py-3 rounded-lg text-white text-xs font-bold uppercase tracking-widest shadow-lg shadow-black/10 transition-all hover:scale-[0.98]"
                                            :style="{ background: '{{ $brand['primary_color'] }}' }">
                                        Continue
                                    </button>

                                    <!-- Social Divider -->
                                    <template x-if="config.social.google.enabled || config.social.facebook.enabled">
                                        <div class="py-2 flex items-center gap-3">
                                            <div class="flex-1 h-px bg-gray-100"></div>
                                            <span class="text-[8px] font-black text-gray-300 uppercase italic">Social Auth</span>
                                            <div class="flex-1 h-px bg-gray-100"></div>
                                        </div>
                                    </template>

                                    <!-- Social Buttons Mock -->
                                    <div class="flex gap-2">
                                        <template x-if="config.social.google.enabled">
                                            <div class="flex-1 h-9 border rounded-lg bg-gray-50 flex items-center justify-center text-[10px] font-bold text-gray-400 uppercase">Google</div>
                                        </template>
                                        <template x-if="config.social.facebook.enabled">
                                            <div class="flex-1 h-9 border rounded-lg bg-gray-50 flex items-center justify-center text-[10px] font-bold text-gray-400 uppercase">Facebook</div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Mock Below Section -->
                            <div class="mt-8 space-y-3">
                                <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest italic leading-relaxed" x-text="config.footer.text"></p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Sticky Save -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-50 md:pl-64 flex justify-end">
            <button type="submit" class="bg-black text-white px-8 py-2 rounded-md font-bold text-sm hover:bg-gray-800 transition-colors shadow-lg">
                Save Designer Changes
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function authDesigner() {
        return {
            tab: localStorage.getItem('auth_settings_tab') || 'fields',
            config: {
                fields: @json($settings['auth_fields']),
                social: @json($settings['social_login'] ?? $settings['auth_social']),
                appearance: @json($settings['auth_appearance']),
                header: @json($settings['auth_header']),
                footer: @json($settings['auth_footer'])
            },
            prepSubmit(e) {
                this.$refs.hiddenFields.value = JSON.stringify(this.config.fields);
                this.$refs.hiddenSocial.value = JSON.stringify(this.config.social);
                this.$refs.hiddenAppearance.value = JSON.stringify(this.config.appearance);
                this.$refs.hiddenHeader.value = JSON.stringify(this.config.header);
                this.$refs.hiddenFooter.value = JSON.stringify(this.config.footer);
                e.target.submit();
            },
            init() {
                this.$watch('tab', value => localStorage.setItem('auth_settings_tab', value));
                
                // Sanitize header order to prevent duplication bugs
                if (Array.isArray(this.config.header.order)) {
                    this.config.header.order = [...new Set(this.config.header.order)].filter(x => x === 'image' || x === 'text');
                }
                if (!Array.isArray(this.config.header.order) || this.config.header.order.length === 0) {
                    this.config.header.order = ['image', 'text'];
                }
                
                // Normalize fields to ensure legacy data doesn't break new features
                const fieldKeys = ['name', 'email', 'phone'];
                fieldKeys.forEach(key => {
                    if (!this.config.fields[key]) this.config.fields[key] = {};
                    if (typeof this.config.fields[key] !== 'object') {
                        // Handle legacy boolean values
                        const val = !!this.config.fields[key];
                        this.config.fields[key] = { visible: val, required: false, auth_type: 'data_entry' };
                    }
                    if (this.config.fields[key].visible === undefined) this.config.fields[key].visible = (key !== 'phone');
                    if (this.config.fields[key].required === undefined) this.config.fields[key].required = (key === 'name');
                    if (this.config.fields[key].auth_type === undefined) this.config.fields[key].auth_type = 'data_entry';
                });

                // Watch for changes in auth methods to enforce field visibility
                this.$watch('config.fields.phone.auth_type', value => {
                    if (value && value !== 'data_entry') {
                        this.config.fields.phone.visible = true;
                        this.config.fields.phone.required = true;
                    }
                });

                this.$watch('config.fields.email.auth_type', value => {
                    if (value === 'verification') {
                        this.config.fields.email.visible = true;
                        this.config.fields.email.required = true;
                    }
                });
            },
            moveItem(type, index, direction) {
                const target = type === 'header' ? this.config.header.order : this.config.footer.order;
                const newIndex = index + direction;
                if (newIndex < 0 || newIndex >= target.length) return;
                
                const temp = target[index];
                target[index] = target[newIndex];
                target[newIndex] = temp;
                
                if (type === 'header') this.config.header.order = [...target];
                else this.config.footer.order = [...target];
            },
            previewFile(event, part) {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (part === 'header') this.config.header.image = e.target.result;
                    else this.config.footer.image = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }
    }
</script>
@endpush
@endsection

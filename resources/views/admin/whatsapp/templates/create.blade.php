@extends('admin.whatsapp.layout')

@section('whatsapp_content')
<div class="w-full" x-data="templateBuilder()">
    
    <div class="bg-white rounded-3xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Advanced Template Builder</h2>
                <p class="text-sm text-gray-500">Design a message template and submit to Meta for approval.</p>
            </div>
            <a href="{{ route('admin.whatsapp.templates.index') }}" class="text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">
                &larr; Back to Library
            </a>
        </div>

        @if(session('error'))
        <div class="px-6 py-4 bg-red-50 text-red-600 text-sm font-bold border-b border-red-100">
            {{ session('error') }}
        </div>
        @endif

        <form action="{{ route('admin.whatsapp.templates.store') }}" method="POST" id="templateForm">
            @csrf

            <div class="flex flex-col lg:flex-row gap-6 p-6">
                {{-- Main Form --}}
                <div class="flex-1 space-y-8">

            {{-- Basic Info --}}
            <div class="space-y-6">
                <div>
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Template Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="templateName" 
                        @input="templateName = templateName.toLowerCase().replace(/[^a-z0-9_]/g, '_').replace(/_+/g, '_')"
                        class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400"
                        placeholder="e.g. order_shipped_v2" required>
                    <p class="mt-2 text-[10px] text-gray-500">Automatically formatted to Meta standards (lowercase, underscores).</p>
                    @error('name')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Category <span class="text-red-500">*</span></label>
                        <select name="category" class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm font-medium text-gray-900" required>
                            <option value="MARKETING" {{ old('category') == 'MARKETING' ? 'selected' : '' }}>Marketing (Promotions, updates)</option>
                            <option value="UTILITY" {{ old('category') == 'UTILITY' ? 'selected' : '' }}>Utility (Order updates, accounts)</option>
                            <option value="AUTHENTICATION" {{ old('category') == 'AUTHENTICATION' ? 'selected' : '' }}>Authentication (OTPs)</option>
                        </select>
                        @error('category')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-2">Language <span class="text-red-500">*</span></label>
                        <select name="language" class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm font-medium text-gray-900" required>
                            <option value="en_US" {{ old('language') == 'en_US' ? 'selected' : '' }}>English (US)</option>
                            <option value="en_GB" {{ old('language') == 'en_GB' ? 'selected' : '' }}>English (UK)</option>
                            <option value="hi" {{ old('language') == 'hi' ? 'selected' : '' }}>Hindi</option>
                            <option value="es" {{ old('language') == 'es' ? 'selected' : '' }}>Spanish</option>
                        </select>
                        @error('language')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            <hr class="border-gray-100">

            {{-- Components --}}
            <div class="space-y-6">
                <h3 class="text-sm font-bold text-gray-900">Message Components</h3>

                {{-- Header --}}
                <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-500">Header (Optional)</label>
                        <select name="header_type" x-model="headerType" class="bg-white border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-lg px-3 py-1.5 text-xs font-medium text-gray-900">
                            <option value="NONE">None</option>
                            <option value="TEXT">Text</option>
                        </select>
                    </div>

                    <div x-show="headerType === 'TEXT'" class="space-y-4" style="display: none;">
                        <input type="text" name="header_text" x-model="headerText"
                            class="w-full bg-white border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm text-gray-900"
                            placeholder="e.g. Hello {customer_name}">
                        <p class="text-[10px] text-gray-500">Max 60 chars. Supports 1 variable (e.g. {customer_name}).</p>
                        
                        <div x-show="headerText.includes('{') && headerText.includes('}')" class="p-3 bg-blue-50/50 rounded-xl border border-blue-100" style="display: none;">
                            <label class="block text-[10px] font-bold text-blue-800 mb-1">Example for Header Variable</label>
                            <input type="text" name="header_example" class="w-full bg-white border-0 ring-1 ring-inset ring-blue-200 focus:ring-2 focus:ring-inset focus:ring-blue-500 rounded-lg px-3 py-2 text-xs text-gray-900" placeholder="e.g. Karan">
                        </div>
                    </div>
                </div>

                {{-- Body --}}
                <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-4">Body Text <span class="text-red-500">*</span></label>
                    <textarea name="body_text" x-model="bodyText" rows="5" 
                        class="w-full bg-white border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm text-gray-900"
                        placeholder="Hi {customer_name}, your order {order_number} has shipped!" required></textarea>
                    <p class="mt-2 text-[10px] text-gray-500">Max 1024 chars. Use {variable_name} syntax for dynamic data. E.g. {customer_name}</p>
                    
                    {{-- Dynamic Body Examples --}}
                    <div x-show="bodyVariables.length > 0" class="mt-4 p-4 bg-blue-50/50 rounded-xl border border-blue-100 space-y-3" style="display: none;">
                        <p class="text-[10px] font-bold text-blue-800 uppercase tracking-wider">Provide Examples for Variables</p>
                        <template x-for="varName in bodyVariables" :key="varName">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold text-blue-900 w-auto min-w-[80px]" x-text="'{'+varName+'}'"></span>
                                <input type="text" :name="'body_examples['+varName+']'" required
                                    class="flex-1 bg-white border-0 ring-1 ring-inset ring-blue-200 focus:ring-2 focus:ring-inset focus:ring-blue-500 rounded-lg px-3 py-2 text-xs text-gray-900" 
                                    :placeholder="'Example for ' + varName">
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200">
                    <label class="block text-xs font-black uppercase tracking-widest text-gray-500 mb-4">Footer (Optional)</label>
                    <input type="text" name="footer_text"
                        class="w-full bg-white border-0 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-[#25D366] rounded-xl px-4 py-3 text-sm text-gray-900"
                        placeholder="e.g. Thanks for choosing Vyora!">
                    <p class="mt-2 text-[10px] text-gray-500">Max 60 chars. Appears in small text at the bottom. No variables allowed.</p>
                </div>

                {{-- Buttons --}}
                <div class="p-5 bg-gray-50 rounded-2xl border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-500">Buttons (Optional)</label>
                        <button type="button" @click="addButton" x-show="buttons.length < 3" class="text-xs font-bold text-[#25D366] hover:text-[#1DA851]">+ Add Button</button>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(btn, index) in buttons" :key="index">
                            <div class="p-4 bg-white rounded-xl border border-gray-200 relative">
                                <button type="button" @click="removeButton(index)" class="absolute top-4 right-4 text-gray-400 hover:text-red-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 pr-8">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 mb-1">Button Type</label>
                                        <select x-model="btn.type" :name="'buttons['+index+'][type]'" class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900">
                                            <option value="QUICK_REPLY">Quick Reply</option>
                                            <option value="URL">Visit Website (URL)</option>
                                            <option value="PHONE_NUMBER">Call Phone Number</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 mb-1">Button Text</label>
                                        <input type="text" x-model="btn.text" :name="'buttons['+index+'][text]'" required
                                            class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900" placeholder="e.g. Track Order">
                                    </div>
                                </div>

                                <div x-show="btn.type === 'URL'" class="space-y-3" style="display: none;">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 mb-1">URL</label>
                                        <input type="text" x-model="btn.url" :name="'buttons['+index+'][url]'"
                                            class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900" placeholder="{{ url('/') }}/track/{tracking_number}">
                                        <p class="mt-1 text-[10px] text-gray-500">Supports 1 variable at the end: {tracking_number} or {tracking_url}</p>
                                    </div>
                                    <div x-show="btn.url && btn.url.includes('{') && btn.url.includes('}')" style="display: none;">
                                        <label class="block text-[10px] font-bold text-blue-800 mb-1">URL Example</label>
                                        <input type="text" :name="'buttons['+index+'][url_example]'"
                                            class="w-full bg-white border-0 ring-1 ring-inset ring-blue-200 focus:ring-2 focus:ring-inset focus:ring-blue-500 rounded-lg px-3 py-2 text-xs text-gray-900" placeholder="e.g. 123456">
                                    </div>
                                </div>

                                <div x-show="btn.type === 'PHONE_NUMBER'" style="display: none;">
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1">Phone Number (with Country Code)</label>
                                    <input type="text" x-model="btn.phone_number" :name="'buttons['+index+'][phone_number]'"
                                        class="w-full bg-gray-50 border-0 ring-1 ring-inset ring-gray-200 rounded-lg px-3 py-2 text-xs text-gray-900" placeholder="+1234567890">
                                </div>
                            </div>
                        </template>
                        <p x-show="buttons.length === 0" class="text-xs text-gray-400 italic">No buttons added.</p>
                    </div>
                </div>
                    </div>
                </div>
                
                {{-- Sidebar Cheat Sheet --}}
                <div class="w-full lg:w-80 space-y-6">
                    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5">
                        <h3 class="text-xs font-black uppercase tracking-widest text-blue-800 mb-3">Dynamic Variables</h3>
                        <p class="text-xs text-blue-900/80 mb-4">Use these variables in your template to inject real data automatically.</p>
                        
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-[10px] font-bold text-blue-700 uppercase tracking-wide mb-2">Order Events</h4>
                                <ul class="space-y-1">
                                    <li class="text-xs font-mono bg-white px-2 py-1 rounded border border-blue-100 text-blue-900">{customer_name}</li>
                                    <li class="text-xs font-mono bg-white px-2 py-1 rounded border border-blue-100 text-blue-900">{order_number}</li>
                                    <li class="text-xs font-mono bg-white px-2 py-1 rounded border border-blue-100 text-blue-900">{order_total}</li>
                                    <li class="text-xs font-mono bg-white px-2 py-1 rounded border border-blue-100 text-blue-900">{product_names}</li>
                                    <li class="text-xs font-mono bg-white px-2 py-1 rounded border border-blue-100 text-blue-900">{tracking_number}</li>
                                    <li class="text-xs font-mono bg-white px-2 py-1 rounded border border-blue-100 text-blue-900">{tracking_url}</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="text-[10px] font-bold text-blue-700 uppercase tracking-wide mb-2">Account Events</h4>
                                <ul class="space-y-1">
                                    <li class="text-xs font-mono bg-white px-2 py-1 rounded border border-blue-100 text-blue-900">{customer_email}</li>
                                    <li class="text-xs font-mono bg-white px-2 py-1 rounded border border-blue-100 text-blue-900">{customer_phone}</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-[10px] font-bold text-blue-700 uppercase tracking-wide mb-2">Authentication</h4>
                                <ul class="space-y-1">
                                    <li class="text-xs font-mono bg-white px-2 py-1 rounded border border-blue-100 text-blue-900">{otp}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-3 bg-gray-50/50">
                <a href="{{ route('admin.whatsapp.templates.index') }}" class="px-5 py-3 border border-gray-200 text-sm font-bold rounded-xl text-gray-600 hover:bg-gray-50 transition-all">
                    Cancel
                </a>
                <button type="submit" class="px-8 py-3 bg-[#25D366] hover:bg-[#1DA851] text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-[#25D366]/30">
                    Submit for Approval
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('templateBuilder', () => ({
        templateName: '{{ old("name", "") }}',
        headerType: 'NONE',
        headerText: '',
        bodyText: '',
        buttons: [],
        
        get bodyVariables() {
            // Find all instances of {variable}
            const regex = /\{([a-zA-Z0-9_]+)\}/g;
            const matches = [...this.bodyText.matchAll(regex)];
            // Extract the variable names, make unique
            const vars = [...new Set(matches.map(m => m[1]))];
            return vars;
        },

        addButton() {
            if (this.buttons.length < 3) {
                this.buttons.push({ type: 'QUICK_REPLY', text: '', url: '', phone_number: '' });
            }
        },

        removeButton(index) {
            this.buttons.splice(index, 1);
        }
    }));
});
</script>
@endsection

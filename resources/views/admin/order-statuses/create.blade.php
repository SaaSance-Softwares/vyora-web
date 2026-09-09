@extends('layouts.admin')

@section('header', 'Create Order Status')

@section('content')
<div class="w-full">
    <div class="mb-6">
        <a href="{{ route('admin.order-statuses.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-900 inline-flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Order Statuses
        </a>
    </div>

    <form action="{{ route('admin.order-statuses.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        @csrf
        
        <div class="p-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm" required>
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Color (Hex)</label>
                    <div class="flex gap-2">
                        <input type="color" id="colorPicker" value="{{ old('color', '#000000') }}" class="h-[42px] w-[42px] rounded-lg border border-gray-300 cursor-pointer p-1">
                        <input type="text" name="color" id="colorText" value="{{ old('color', '#000000') }}" class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 text-sm" required pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$">
                    </div>
                    @error('color') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm" min="0">
                <p class="mt-1 text-xs text-gray-400">Lower numbers appear first in the order status dropdown.</p>
                @error('sort_order') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <hr class="border-gray-100">

            <div>
                <h3 class="text-base font-bold text-gray-900 mb-1">Automated Notifications</h3>
                <p class="text-sm text-gray-500 mb-6">Select which templates should be sent automatically when an order is updated to this status.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- SMS -->
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 relative">
                        <div class="absolute top-4 right-4 text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Linked SMS Template</label>
                        <select name="sms_template_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white">
                            <option value="">-- No SMS Template --</option>
                            @foreach($smsTemplates as $t)
                                <option value="{{ $t->id }}" {{ old('sms_template_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                        @error('sms_template_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <!-- Email -->
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 relative">
                        <div class="absolute top-4 right-4 text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Linked Email Template</label>
                        <select name="email_template_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white">
                            <option value="">-- No Email Template --</option>
                            @foreach($emailTemplates as $t)
                                <option value="{{ $t->id }}" {{ old('email_template_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                        @error('email_template_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <!-- WhatsApp -->
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 relative">
                        <div class="absolute top-4 right-4 text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        </div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Linked WhatsApp Template</label>
                        <select name="whatsapp_template_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white">
                            <option value="">-- No WhatsApp Template --</option>
                            @foreach($whatsappTemplates as $t)
                                <option value="{{ $t->id }}" {{ old('whatsapp_template_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                        @error('whatsapp_template_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50/50 border-t border-gray-100 px-6 py-4 flex justify-end">
            <button type="submit" class="px-6 py-2 bg-black text-white text-sm font-bold rounded-lg hover:bg-gray-800 transition-colors shadow-sm">
                Create Status
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('colorPicker').addEventListener('input', function(e) {
        document.getElementById('colorText').value = e.target.value;
    });
    document.getElementById('colorText').addEventListener('input', function(e) {
        if(/^#[0-9A-F]{6}$/i.test(e.target.value)) {
            document.getElementById('colorPicker').value = e.target.value;
        }
    });
</script>
@endsection

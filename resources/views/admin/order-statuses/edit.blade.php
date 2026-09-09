@extends('layouts.admin')

@section('header', 'Edit Order Status')

@section('content')
<div class="w-full">
    <div class="mb-6">
        <a href="{{ route('admin.order-statuses.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-900 inline-flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Order Statuses
        </a>
    </div>

    <form action="{{ route('admin.order-statuses.update', $orderStatus) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        @csrf
        @method('PUT')
        
        <div class="p-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status Name</label>
                    <input type="text" name="name" value="{{ old('name', $orderStatus->name) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm" required {{ $orderStatus->is_system ? 'readonly' : '' }}>
                    @if($orderStatus->is_system)
                        <p class="text-xs text-gray-500 mt-2">This is a system status, its name cannot be changed.</p>
                    @endif
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Color -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Color Label (Hex)</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="colorPicker" value="{{ old('color', $orderStatus->color ?? '#3b82f6') }}" class="h-[42px] w-[42px] rounded-lg border border-gray-300 cursor-pointer p-1 bg-white">
                        <input type="text" name="color" id="colorInput" value="{{ old('color', $orderStatus->color) }}" placeholder="#000000" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm uppercase font-mono">
                    </div>
                    @error('color')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $orderStatus->sort_order) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm" required>
                <p class="text-xs text-gray-500 mt-2">Lower numbers appear first in the status dropdown.</p>
                @error('sort_order')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <hr class="border-gray-100">

            <h3 class="text-base font-bold text-gray-900">Automated Notifications</h3>
            <p class="text-sm text-gray-500 mb-4">Select templates to send automatically when an order is updated to this status.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- SMS Template -->
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 relative">
                    <div class="absolute top-4 right-4 text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">SMS Template</label>
                    <select name="sms_template_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white">
                        <option value="">-- None --</option>
                        @foreach($smsTemplates as $template)
                            <option value="{{ $template->id }}" {{ old('sms_template_id', $orderStatus->sms_template_id) == $template->id ? 'selected' : '' }}>{{ $template->name }}</option>
                        @endforeach
                    </select>
                    @error('sms_template_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Email Template -->
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 relative">
                    <div class="absolute top-4 right-4 text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email Template</label>
                    <select name="email_template_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white">
                        <option value="">-- None --</option>
                        @foreach($emailTemplates as $template)
                            <option value="{{ $template->id }}" {{ old('email_template_id', $orderStatus->email_template_id) == $template->id ? 'selected' : '' }}>{{ $template->name }}</option>
                        @endforeach
                    </select>
                    @error('email_template_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- WhatsApp Template -->
                <div class="bg-gray-50 rounded-xl p-5 border border-gray-100 relative">
                    <div class="absolute top-4 right-4 text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">WhatsApp Template</label>
                    <select name="whatsapp_template_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white">
                        <option value="">-- None --</option>
                        @foreach($whatsappTemplates as $template)
                            <option value="{{ $template->id }}" {{ old('whatsapp_template_id', $orderStatus->whatsapp_template_id) == $template->id ? 'selected' : '' }}>{{ $template->name }}</option>
                        @endforeach
                    </select>
                    @error('whatsapp_template_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

        </div>

        <div class="bg-gray-50/50 border-t border-gray-100 px-6 py-4 flex justify-end gap-3">
            <a href="{{ route('admin.order-statuses.index') }}" class="px-6 py-2.5 text-sm font-bold text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-black text-white text-sm font-bold rounded-lg hover:bg-gray-800 transition-colors shadow-sm">Update Status</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('colorPicker').addEventListener('input', function() {
        document.getElementById('colorInput').value = this.value.toUpperCase();
    });
    document.getElementById('colorInput').addEventListener('input', function() {
        if (/^#[0-9A-F]{6}$/i.test(this.value)) {
            document.getElementById('colorPicker').value = this.value;
        }
    });
</script>
@endsection

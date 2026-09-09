@extends('layouts.admin')

@section('header', 'Edit SMS Template')

@section('content')
<div class="w-full">
    <div class="mb-6">
        <a href="{{ route('admin.sms-templates.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-900 inline-flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Templates
        </a>
    </div>

    <form action="{{ route('admin.sms-templates.update', $smsTemplate) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        @csrf
        @method('PUT')
        
        <div class="p-8 space-y-8">
            <!-- Name -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Template Name</label>
                <input type="text" name="name" value="{{ old('name', $smsTemplate->name) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm" required>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Content -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">SMS Content</label>
                <textarea name="content" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm resize-y" required>{{ old('content', $smsTemplate->content) }}</textarea>
                @error('content')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                
                <div class="mt-4 bg-blue-50/50 border border-blue-100 rounded-xl p-4">
                    <h4 class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-2">Available Variables</h4>
                    <p class="text-xs text-blue-700 mb-3">You can use these variables in your content. They will be automatically replaced with real data when sending.</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-2 py-1 bg-white border border-blue-100 rounded-lg text-xs font-mono text-blue-800 cursor-help" title="Customer's full name">{customer_name}</span>
                        <span class="px-2 py-1 bg-white border border-blue-100 rounded-lg text-xs font-mono text-blue-800 cursor-help" title="The unique order ID">{order_number}</span>
                        <span class="px-2 py-1 bg-white border border-blue-100 rounded-lg text-xs font-mono text-blue-800 cursor-help" title="Total amount formatted with currency">{order_total}</span>
                        <span class="px-2 py-1 bg-white border border-blue-100 rounded-lg text-xs font-mono text-blue-800 cursor-help" title="Tracking URL if available">{tracking_link}</span>
                        <span class="px-2 py-1 bg-white border border-blue-100 rounded-lg text-xs font-mono text-blue-800 cursor-help" title="The current status name">{status_name}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-50/50 border-t border-gray-100 px-6 py-4 flex justify-end gap-3">
            <a href="{{ route('admin.sms-templates.index') }}" class="px-6 py-2.5 text-sm font-bold text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-black text-white text-sm font-bold rounded-lg hover:bg-gray-800 transition-colors shadow-sm">Save Changes</button>
        </div>
    </form>
</div>
@endsection

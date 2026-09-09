@extends('layouts.admin')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-toolbar.ql-snow {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
        border-color: #d1d5db;
        background-color: #f9fafb;
    }
    .ql-container.ql-snow {
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        border-color: #d1d5db;
        font-family: inherit;
        font-size: 0.875rem;
    }
    .ql-editor {
        min-height: 250px;
    }
</style>
@endpush

@section('header', 'Edit Email Template')

@section('content')
<div class="w-full">
    <div class="mb-6">
        <a href="{{ route('admin.email-templates.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-900 inline-flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Templates
        </a>
    </div>

    <form action="{{ route('admin.email-templates.update', $emailTemplate) }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        @csrf
        @method('PUT')
        
        <div class="p-8 space-y-6">
            <!-- Name -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Template Name</label>
                <input type="text" name="name" value="{{ old('name', $emailTemplate->name) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm" required>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Subject -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email Subject</label>
                <input type="text" name="subject" value="{{ old('subject', $emailTemplate->subject) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm" required>
                @error('subject')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Content -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-semibold text-gray-700">Email Body</label>
                </div>
                <p class="text-xs text-gray-500 mb-3">You can use variables like <code class="bg-gray-100 px-1 rounded text-gray-700">{customer_name}</code>, <code class="bg-gray-100 px-1 rounded text-gray-700">{order_number}</code>, <code class="bg-gray-100 px-1 rounded text-gray-700">{total_amount}</code> which will be replaced automatically.</p>
                <textarea name="body" id="body-textarea" class="hidden">{{ old('body', $emailTemplate->body) }}</textarea>
                <div id="editor-container" class="bg-white">{!! old('body', $emailTemplate->body) !!}</div>
                @error('body')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Status -->
            <div class="flex items-center">
                <input type="checkbox" name="status" id="status" value="1" class="w-5 h-5 rounded border-gray-300 text-black focus:ring-black" {{ old('status', $emailTemplate->status) ? 'checked' : '' }}>
                <label for="status" class="ml-3 text-sm font-bold text-gray-900">Active Template</label>
            </div>
        </div>

        <div class="bg-gray-50/50 border-t border-gray-100 px-6 py-4 flex justify-end gap-3">
            <a href="{{ route('admin.email-templates.index') }}" class="px-6 py-2.5 text-sm font-bold text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-black text-white text-sm font-bold rounded-lg hover:bg-gray-800 transition-colors shadow-sm">Update Template</button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            placeholder: 'Write your email template here...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });

        var form = document.querySelector('form');
        form.addEventListener('submit', function() {
            var bodyTextarea = document.querySelector('#body-textarea');
            // If the editor is empty, set empty string, else set the HTML
            bodyTextarea.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
        });
    });
</script>
@endpush
@endsection

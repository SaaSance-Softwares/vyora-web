@extends('layouts.admin')

@section('title', 'Create Legal Page')
@section('header', 'Create Legal Page')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .ql-editor {
        min-height: 400px;
        background-color: white;
        font-size: 14px;
    }
</style>
@endpush

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.legal-pages.index') }}" class="text-sm font-semibold text-gray-500 hover:text-black flex items-center gap-1 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Pages
    </a>
</div>

<form action="{{ route('admin.legal-pages.store') }}" method="POST" id="legalForm">
    @csrf
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- General Information -->
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Page Content</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title_input" value="{{ old('title') }}" required class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2.5 focus:border-black focus:bg-white transition-colors" placeholder="e.g. Terms of Service">
                    </div>
                    
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-bold text-gray-700">Content <span class="text-red-500">*</span></label>
                            <button type="button" id="toggle-html-btn" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1 rounded transition-colors border border-indigo-100 shadow-sm">&lt;/&gt; Source Code</button>
                        </div>
                        <input type="hidden" name="content" id="content_input">
                        <div id="editor-container" class="relative">
                            <div id="content-editor" class="border border-gray-200 rounded-lg overflow-hidden"></div>
                            <textarea id="html-editor" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-4 font-mono text-sm hidden focus:border-black focus:bg-white transition-colors" rows="20" style="min-height: 400px; display: none;">{{ old('content') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Settings -->
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Settings</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">URL Slug</label>
                        <input type="text" name="slug" id="slug_input" value="{{ old('slug') }}" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 focus:border-black focus:bg-white transition-colors" placeholder="Leave empty to auto-generate">
                    </div>
                    
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="is_published" value="0">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="is_published" value="1" class="sr-only peer" {{ old('is_published', true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                            </div>
                            <span class="text-sm font-bold text-gray-700">Published</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- SEO Settings -->
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4">SEO Details</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title') }}" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 focus:border-black focus:bg-white transition-colors">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Meta Description</label>
                        <textarea name="meta_description" rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 focus:border-black focus:bg-white transition-colors">{{ old('meta_description') }}</textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-black text-white font-bold py-3 px-4 rounded-xl hover:bg-gray-800 transition-colors shadow-sm text-sm uppercase tracking-wider">
                Create Page
            </button>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var quill = new Quill('#content-editor', {
            theme: 'snow',
            placeholder: 'Start writing your legal policy here...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'blockquote'],
                    ['clean']
                ]
            }
        });

        var isHtmlMode = false;
        var toggleBtn = document.getElementById('toggle-html-btn');
        var quillContainer = document.querySelector('#content-editor');
        var quillToolbar = document.querySelector('.ql-toolbar');
        var htmlEditor = document.getElementById('html-editor');
        
        // Initialize Quill with the safely escaped content from the textarea
        if (htmlEditor && htmlEditor.value) {
            quill.clipboard.dangerouslyPasteHTML(htmlEditor.value);
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                if (!isHtmlMode) {
                    // Switch to HTML mode
                    htmlEditor.value = quill.root.innerHTML;
                    quillContainer.style.display = 'none';
                    if (quillToolbar) quillToolbar.style.display = 'none';
                    htmlEditor.style.display = 'block';
                    toggleBtn.innerHTML = 'View Visual Editor';
                    isHtmlMode = true;
                } else {
                    // Switch back to Visual mode
                    var htmlContent = htmlEditor.value;
                    quill.clipboard.dangerouslyPasteHTML(htmlContent);
                    if (htmlContent === '') {
                        quill.root.innerHTML = '<p><br></p>';
                    }
                    htmlEditor.style.display = 'none';
                    quillContainer.style.display = 'block';
                    if (quillToolbar) quillToolbar.style.display = 'block';
                    toggleBtn.innerHTML = '&lt;/&gt; Source Code';
                    isHtmlMode = false;
                }
            });
        }

        document.getElementById('legalForm').onsubmit = function() {
            var html = isHtmlMode ? htmlEditor.value : quill.root.innerHTML;
            document.getElementById('content_input').value = html;
        };

        // Slug auto-generation logic
        const titleInput = document.getElementById('title_input');
        const slugInput = document.getElementById('slug_input');
        let slugManuallyEdited = false;

        slugInput.addEventListener('input', function() {
            slugManuallyEdited = true;
            // Replace spaces with hyphens and remove leading/trailing hyphens
            this.value = this.value.replace(/\s+/g, '-').toLowerCase();
            
            if(this.value === '') {
                slugManuallyEdited = false; // Reset if they clear it
            }
        });

        titleInput.addEventListener('input', function() {
            if (!slugManuallyEdited) {
                // Generate slug: lowercase, replace spaces and non-alphanumeric chars with hyphens
                let generatedSlug = this.value.toLowerCase()
                                        .replace(/[^a-z0-9\s-]/g, '') // Remove invalid chars
                                        .replace(/\s+/g, '-') // Replace spaces with hyphens
                                        .replace(/-+/g, '-') // Remove consecutive hyphens
                                        .replace(/^-+|-+$/g, ''); // Trim hyphens
                
                slugInput.value = generatedSlug;
            }
        });
    });
</script>
@endpush

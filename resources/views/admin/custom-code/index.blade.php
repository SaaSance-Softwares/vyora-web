@extends('layouts.admin')

@section('header', 'Custom Code')

@section('content')
<div class="w-full mx-auto pb-24 px-4 sm:px-6 lg:px-8" x-data="customCodeTabs()">
    <div class="mb-6 mt-6">
        <p class="text-sm text-gray-600">Inject custom HTML, CSS, or JavaScript snippets into your storefront layout.</p>
    </div>


    <form id="custom-code-form" action="{{ route('admin.online-store.custom-code.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-200">
            <!-- Tabs -->
            <div class="flex border-b border-gray-200 overflow-x-auto bg-gray-50">
                <button type="button" @click="setTab('header')" :class="tab === 'header' ? 'border-black text-black bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex-1 text-center transition-colors">
                    Header Code
                </button>
                <button type="button" @click="setTab('body')" :class="tab === 'body' ? 'border-black text-black bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex-1 text-center transition-colors">
                    Body (Top) Code
                </button>
                <button type="button" @click="setTab('footer')" :class="tab === 'footer' ? 'border-black text-black bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex-1 text-center transition-colors">
                    Footer Code
                </button>
            </div>

            <!-- Header Content -->
            <div x-show="tab === 'header'" class="p-0">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <p class="text-sm text-gray-600">Code will be injected right before the closing <code>&lt;/head&gt;</code> tag. Best for meta tags, CSS links, or essential tracking scripts.</p>
                </div>
                <textarea id="custom_code_header" name="custom_code_header" class="hidden">{{ $settings['custom_code_header'] ?? '' }}</textarea>
            </div>

            <!-- Body Content -->
            <div x-show="tab === 'body'" x-cloak class="p-0">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <p class="text-sm text-gray-600">Code will be injected immediately after the opening <code>&lt;body&gt;</code> tag. Often used for GTM noscript tags.</p>
                </div>
                <textarea id="custom_code_body" name="custom_code_body" class="hidden">{{ $settings['custom_code_body'] ?? '' }}</textarea>
            </div>

            <!-- Footer Content -->
            <div x-show="tab === 'footer'" x-cloak class="p-0">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <p class="text-sm text-gray-600">Code will be injected right before the closing <code>&lt;/body&gt;</code> tag. Best for chat widgets, analytics scripts, or deferred JS.</p>
                </div>
                <textarea id="custom_code_footer" name="custom_code_footer" class="hidden">{{ $settings['custom_code_footer'] ?? '' }}</textarea>
            </div>
        </div>

        <!-- Save Section -->
        <div class="bg-gray-50 border-t border-gray-200 p-5 flex justify-end rounded-b-lg mt-6">
            <button type="submit" class="bg-black text-white px-8 py-2.5 rounded-lg font-bold text-sm hover:bg-gray-800 transition-colors">
                Save Custom Code
            </button>
        </div>
    </form>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/dracula.min.css">
<style>
    .CodeMirror {
        height: 60vh;
        min-height: 400px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 14px;
        line-height: 1.6;
    }
    [x-cloak] { display: none !important; }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/htmlmixed/htmlmixed.min.js"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('customCodeTabs', () => ({
            tab: localStorage.getItem('customCodeTab') || 'header',
            editors: {},
            init() {
                // Initialize CodeMirror after Alpine has mounted
                setTimeout(() => {
                    const config = {
                        lineNumbers: true,
                        mode: "htmlmixed",
                        theme: "dracula",
                        indentUnit: 4,
                        lineWrapping: true,
                    };
                    
                    this.editors['header'] = CodeMirror.fromTextArea(document.getElementById('custom_code_header'), config);
                    this.editors['body'] = CodeMirror.fromTextArea(document.getElementById('custom_code_body'), config);
                    this.editors['footer'] = CodeMirror.fromTextArea(document.getElementById('custom_code_footer'), config);

                    // Ensure form submission saves the edited values
                    document.getElementById('custom-code-form').addEventListener('submit', () => {
                        this.editors['header'].save();
                        this.editors['body'].save();
                        this.editors['footer'].save();
                    });
                }, 100);

                // Refresh editors when tabs change to fix display issues
                this.$watch('tab', (value) => {
                    localStorage.setItem('customCodeTab', value);
                    setTimeout(() => {
                        if (this.editors[value]) {
                            this.editors[value].refresh();
                            this.editors[value].focus();
                        }
                    }, 50);
                });
            },
            setTab(t) {
                this.tab = t;
            }
        }));
    });
</script>
@endpush
@endsection

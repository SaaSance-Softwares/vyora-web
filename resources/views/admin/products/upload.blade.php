@extends('layouts.admin')

@section('header', 'Bulk Import')

@section('content')
<div class="max-w-5xl mx-auto pb-24 space-y-8">
    <!-- Header -->
    <div class="flex justify-between items-center bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Import Products</h1>
            <p class="text-sm text-gray-500">Mass import catalog items via CSV files.</p>
        </div>
        <div class="flex bg-gray-100 p-1 rounded-lg">
            <button onclick="switchTab('general')" id="btn-general" class="px-4 py-1.5 text-xs font-bold rounded cursor-pointer transition-colors bg-white shadow-sm text-black">General Importer</button>
            <button onclick="switchTab('qikink')" id="btn-qikink" class="px-4 py-1.5 text-xs font-bold rounded cursor-pointer transition-colors text-gray-500 hover:text-black">QikInk Feed</button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Upload Card -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 min-h-[400px]">
                
                {{-- Form: General --}}
                <div id="tab-general-content" class="space-y-8">
                    <div class="space-y-2">
                        <h3 class="font-bold text-gray-900">Standard CSV Importer</h3>
                        <p class="text-xs text-gray-500">Imports name, description, SEO, prices, and complex attributes.</p>
                    </div>

                    <form action="{{ route('admin.upload.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <input type="hidden" name="type" value="general">
                        
                        <div class="border-2 border-dashed border-gray-200 rounded-lg p-12 text-center hover:bg-gray-50 transition-colors cursor-pointer" onclick="document.getElementById('file-general').click()">
                            <input type="file" name="file" id="file-general" class="hidden" accept=".csv" onchange="fileSelected('general')">
                            <div id="msg-general" class="space-y-3">
                                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto text-gray-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                </div>
                                <p class="text-sm font-bold text-gray-600">Click to upload or drag & drop CSV</p>
                                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Master Pattern CSV Only</p>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-black text-white px-8 py-3 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors shadow-md">
                                Start Import
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Form: QikInk --}}
                <div id="tab-qikink-content" class="hidden space-y-8">
                    <div class="space-y-2">
                        <h3 class="font-bold text-gray-900">QikInk Variant Automator</h3>
                        <p class="text-xs text-gray-500">Processes QikInk raw product exports and groups them by item name.</p>
                    </div>

                    <form action="{{ route('admin.upload.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        <input type="hidden" name="type" value="qikink">
                        
                        <div class="border-2 border-dashed border-gray-200 rounded-lg p-12 text-center hover:bg-gray-50 transition-colors cursor-pointer" onclick="document.getElementById('file-qikink').click()">
                            <input type="file" name="file" id="file-qikink" class="hidden" accept=".csv" onchange="fileSelected('qikink')">
                            <div id="msg-qikink" class="space-y-3">
                                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto text-gray-400">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                </div>
                                <p class="text-sm font-bold text-gray-600">Click to upload raw QikInk CSV</p>
                                <p class="text-[10px] text-gray-400 uppercase font-black tracking-widest">Raw Feed Schema</p>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-black text-white px-8 py-3 rounded-lg text-sm font-bold hover:bg-gray-800 transition-colors shadow-md">
                                Process Feed
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-4">
                <h4 class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Sample Data</h4>
                <div class="space-y-2">
                    <a href="{{ route('admin.upload.sample-general') }}" class="flex items-center gap-3 p-3 bg-gray-50 rounded group hover:bg-black transition-colors">
                        <div class="text-xs group-hover:text-white">
                            <div class="font-bold">General Blueprint</div>
                            <div class="text-[10px] opacity-60">Master format CSV</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.upload.sample-qikink') }}" class="flex items-center gap-3 p-3 bg-gray-50 rounded group hover:bg-black transition-colors">
                        <div class="text-xs group-hover:text-white">
                            <div class="font-bold">QikInk Feed Schema</div>
                            <div class="text-[10px] opacity-60">Managed feed format</div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="p-6 bg-blue-50 border border-blue-100 rounded-lg italic">
                <p class="text-xs text-blue-700 leading-relaxed">
                    <strong>Note:</strong> Multiple rows with the same <strong>Item Name</strong> will be grouped into a single product with multiple variants automaticallly.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    function switchTab(t) {
        const url = new URL(window.location);
        url.searchParams.set('tab', t);
        window.history.pushState({}, '', url);

        document.getElementById('tab-general-content').classList.toggle('hidden', t !== 'general');
        document.getElementById('tab-qikink-content').classList.toggle('hidden', t !== 'qikink');

        const bg = document.getElementById('btn-general');
        const bq = document.getElementById('btn-qikink');

        if(t === 'general') {
            bg.className = "px-4 py-1.5 text-xs font-bold rounded cursor-pointer transition-colors bg-white shadow-sm text-black";
            bq.className = "px-4 py-1.5 text-xs font-bold rounded cursor-pointer transition-colors text-gray-500 hover:text-black";
        } else {
            bq.className = "px-4 py-1.5 text-xs font-bold rounded cursor-pointer transition-colors bg-white shadow-sm text-black";
            bg.className = "px-4 py-1.5 text-xs font-bold rounded cursor-pointer transition-colors text-gray-500 hover:text-black";
        }
    }

    function fileSelected(t) {
        const input = document.getElementById(`file-${t}`);
        const msg = document.getElementById(`msg-${t}`);
        if(input.files.length > 0) {
            msg.innerHTML = `<p class="text-sm font-bold text-green-600">Selected: ${input.files[0].name}</p>`;
        }
    }

    // Auto switch if param exists
    document.addEventListener('DOMContentLoaded', () => {
        const tab = new URLSearchParams(window.location.search).get('tab');
        if(tab) switchTab(tab);
    });
</script>
@endsection
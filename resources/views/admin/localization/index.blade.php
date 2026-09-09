@extends('layouts.admin')

@section('header', 'Localization & Postal Codes')

@section('content')
<div class="space-y-8 pb-32" x-data="localizationManager()">
    
    {{-- ── EXPLANATION ──────────────── --}}
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-widest mb-4">How Localization Works</h3>
        <p class="text-sm text-gray-600 leading-relaxed mb-4">
            Upload a master database of postal codes for a country. When users enter their postal code at checkout, the City and State will auto-fill instantly.
        </p>
        <div class="bg-blue-50 text-blue-800 p-4 rounded text-sm">
            <strong>CSV Format Required:</strong> Your CSV must have these columns: <code>postal_code, state</code>. You can optionally include <code>city</code> and/or <code>district</code>.
            <br>
            <a href="data:text/csv;charset=utf-8,postal_code%2Ccity%2Cdistrict%2Cstate%0A110001%2CNew%20Delhi%2CNew%20Delhi%2CDelhi" download="sample_pincodes.csv" class="text-blue-600 underline font-medium mt-2 inline-block">Download Sample CSV</a>
        </div>
    </div>

    {{-- ── ADD NEW COUNTRY ──────────────────────────────── --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Add New Country</h3>
        <form action="{{ route('admin.online-store.localization.country.store') }}" method="POST" class="flex gap-4 items-end">
            @csrf
            <div class="flex-1">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Country Name</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-1 focus:ring-black" placeholder="e.g. India">
            </div>
            <div class="flex-1">
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Country Code</label>
                <input type="text" name="code" required class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-1 focus:ring-black" placeholder="e.g. IN">
            </div>
            <div>
                <button type="submit" class="bg-black text-white px-6 py-2 rounded-md font-bold hover:bg-gray-800">Add Country</button>
            </div>
        </form>
    </div>

    {{-- ── EXISTING COUNTRIES ──────────────────────────────── --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-700 uppercase tracking-wider">
                    <th class="p-4">Country</th>
                    <th class="p-4">Postal Codes Uploaded</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($countries as $country)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="p-4">
                        <div class="font-bold text-gray-900">{{ $country->name }}</div>
                        <div class="text-xs text-gray-500 uppercase">{{ $country->code }}</div>
                    </td>
                    <td class="p-4 text-sm">
                        <span class="inline-block px-2 py-1 bg-green-100 text-green-800 rounded font-bold">{{ number_format($country->postal_codes_count) }}</span> rows
                    </td>
                    <td class="p-4 text-right space-x-2">
                        <!-- Upload Button triggers Alpine state -->
                        <button type="button" @click="startUpload({{ $country->id }}, '{{ $country->name }}')" class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded font-semibold text-sm hover:bg-blue-100 transition-colors">
                            Upload CSV
                        </button>
                        
                        <!-- Wipe Button -->
                        <form action="{{ route('admin.online-store.localization.country.truncate', $country->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Wipe all postal codes for this country? This cannot be undone.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 bg-orange-50 text-orange-600 rounded font-semibold text-sm hover:bg-orange-100 transition-colors">Wipe Data</button>
                        </form>

                        <!-- Delete Button -->
                        <form action="{{ route('admin.online-store.localization.country.destroy', $country->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this country and ALL its postal codes?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 rounded font-semibold text-sm hover:bg-red-100 transition-colors">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="p-8 text-center text-gray-500 italic">No countries added yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── UPLOAD MODAL (AlpineJS) ──────────────────────────────── --}}
    <template x-teleport="body">
        <div x-show="uploading" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 m-4" @click.away="if(!isProcessing) uploading = false">
            <h3 class="text-xl font-bold mb-2">Upload CSV for <span x-text="selectedCountryName"></span></h3>
            
            <div x-show="!isProcessing">
                <p class="text-sm text-gray-600 mb-4">Select your CSV file. The file will be processed in chunks directly from your browser to prevent server timeouts.</p>
                <input type="file" id="csvFile" accept=".csv" class="mb-4 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                
                <div class="flex justify-end gap-3 mt-6">
                    <button @click="uploading = false" class="px-4 py-2 text-gray-600 font-semibold">Cancel</button>
                    <button @click="processCsv()" class="px-4 py-2 bg-black text-white rounded font-bold hover:bg-gray-800">Start Import</button>
                </div>
            </div>

            <div x-show="isProcessing" class="py-4">
                <p class="text-sm font-bold text-gray-900 mb-2">Processing... <span x-text="progressText"></span></p>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" :style="`width: ${progressPercent}%`"></div>
                </div>
                <p class="text-xs text-red-500 font-bold uppercase mt-4">Do not close this tab until complete!</p>
            </div>

            <div x-show="uploadComplete" class="py-4 text-center">
                <div class="w-12 h-12 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h4 class="text-lg font-bold text-green-600 mb-1">Import Complete!</h4>
                <p class="text-sm text-gray-600 mb-4">Successfully imported <span x-text="totalRows"></span> rows.</p>
                <button @click="window.location.reload()" class="px-6 py-2 bg-black text-white rounded font-bold">Refresh Page</button>
            </div>
        </div>
    </div>
    </template>
</div>

{{-- Load PapaParse for fast browser CSV parsing --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('localizationManager', () => ({
        uploading: false,
        isProcessing: false,
        uploadComplete: false,
        selectedCountryId: null,
        selectedCountryName: '',
        progressPercent: 0,
        progressText: '0 / 0',
        totalRows: 0,

        startUpload(id, name) {
            this.selectedCountryId = id;
            this.selectedCountryName = name;
            this.uploading = true;
            this.isProcessing = false;
            this.uploadComplete = false;
            this.progressPercent = 0;
            if(document.getElementById('csvFile')) document.getElementById('csvFile').value = '';
        },

        async processCsv() {
            const fileInput = document.getElementById('csvFile');
            if (!fileInput.files.length) {
                alert('Please select a file first.');
                return;
            }

            this.isProcessing = true;
            
            Papa.parse(fileInput.files[0], {
                header: true,
                skipEmptyLines: true,
                complete: async (results) => {
                    const data = results.data;
                    this.totalRows = data.length;
                    const chunkSize = 1000;
                    let processed = 0;

                    for (let i = 0; i < data.length; i += chunkSize) {
                        const chunk = data.slice(i, i + chunkSize);
                        
                        // Map headers to DB columns (in case CSV has slight header variations)
                        const cleanChunk = chunk.map(row => {
                            // Find keys case-insensitively
                            const getVal = (possibleKeys) => {
                                for(let key in row) {
                                    if(possibleKeys.includes(key.toLowerCase().trim())) return row[key].trim();
                                }
                                return '';
                            };
                            return {
                                postal_code: getVal(['postal_code', 'pincode', 'pin_code', 'zip', 'zipcode', 'zip_code', 'pincode']),
                                city: getVal(['city', 'town', 'taluk', 'officename']),
                                district: getVal(['district', 'districtname']),
                                state: getVal(['state', 'province', 'statename'])
                            };
                        }).filter(row => row.postal_code && row.state); // Drop invalid rows

                        if(cleanChunk.length > 0) {
                            try {
                                let uploadUrl = "{{ route('admin.online-store.localization.country.upload-chunk', ['country' => 'COUNTRY_ID_PLACEHOLDER']) }}";
                                uploadUrl = uploadUrl.replace('COUNTRY_ID_PLACEHOLDER', this.selectedCountryId);
                                
                                const response = await fetch(uploadUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ chunk: cleanChunk })
                                });

                                if (!response.ok) {
                                    throw new Error('Server responded with status: ' + response.status);
                                }
                            } catch (error) {
                                console.error('Chunk upload failed', error);
                                alert('Error uploading chunk. Please check console.');
                                this.isProcessing = false;
                                return;
                            }
                        }

                        processed += chunk.length;
                        this.progressText = `${processed} / ${this.totalRows}`;
                        this.progressPercent = Math.min(100, Math.round((processed / this.totalRows) * 100));
                    }

                    this.isProcessing = false;
                    this.uploadComplete = true;
                },
                error: (error) => {
                    alert('Error reading CSV: ' + error.message);
                    this.isProcessing = false;
                }
            });
        }
    }));
});
</script>
@endsection

@extends('layouts.admin')

@section('header', 'New Size Chart')

@section('content')
<div class="max-w-6xl mx-auto pb-24">
    <form action="{{ route('admin.size-charts.store') }}" method="POST" id="size-chart-form" class="space-y-8">
        @csrf
        
        <!-- Header -->
        <div class="flex justify-between items-center bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div>
                <h1 class="text-xl font-bold">Create Size Chart</h1>
                <p class="text-sm text-gray-500">Define measurement columns and size values.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.size-charts.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-bold">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-black text-white rounded-lg text-sm font-bold hover:bg-gray-800 shadow-md">Create Chart</button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: Table Builder -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center border-b pb-4 mb-6">
                        <h3 class="font-bold">Chart Builder</h3>
                        <div class="flex gap-2">
                            <button type="button" onclick="addColumn()" class="text-xs bg-gray-100 px-3 py-1.5 rounded font-bold hover:bg-gray-200">+ Add Column</button>
                            <button type="button" onclick="addRow()" class="text-xs bg-black text-white px-3 py-1.5 rounded font-bold hover:bg-gray-800">+ Add Size</button>
                        </div>
                    </div>

                    <!-- Columns Configuration -->
                    <div class="mb-8 p-4 bg-gray-50 rounded border border-gray-200">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Measurement Labels</label>
                        <div id="column-list" class="flex flex-wrap gap-2">
                            <div class="flex items-center gap-1 bg-white border border-gray-300 rounded px-2 py-1">
                                <input type="text" value="Chest" class="col-input text-xs font-bold border-0 p-0 focus:ring-0 w-20" placeholder="Label">
                                <button type="button" onclick="this.parentElement.remove(); updateUI();" class="text-red-500 font-bold px-1">&times;</button>
                            </div>
                        </div>
                    </div>

                    <!-- Values Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse" id="values-table">
                            <thead>
                                <tr class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase border-b border-gray-200">
                                    <th class="px-4 py-3">Size Code</th>
                                    <th class="px-4 py-3">Display Name</th>
                                    <!-- Dynamic Cols here -->
                                    <th class="actions-head px-4 py-3 text-right">Remove</th>
                                </tr>
                            </thead>
                            <tbody id="rows-list" class="divide-y divide-gray-100">
                                <tr class="size-row">
                                    <td class="px-4 py-3">
                                        <select class="sc-code w-full text-xs font-bold border-gray-300 rounded p-1.5" onchange="syncName(this)">
                                            <option value="">Select</option>
                                            @foreach($sizes as $size)
                                                <option value="{{ $size->code }}" data-name="{{ $size->name }}">{{ $size->code }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="text" class="sc-name w-full text-xs border-gray-300 rounded p-1.5 bg-gray-50" readonly placeholder="Name">
                                    </td>
                                    <!-- Dynamic Inputs -->
                                    <td class="px-4 py-3 text-right">
                                        <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Settings -->
            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
                    <h3 class="font-bold border-b pb-4 uppercase text-[10px] tracking-widest text-gray-400">Basic Info</h3>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Chart Name</label>
                        <input type="text" name="name" required class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-bold" placeholder="e.g. Mens Cotton Tee">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Unit</label>
                        <select name="unit" class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-bold">
                            <option value="inches">Inches</option>
                            <option value="cm">CM</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
                        <select name="is_active" class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-bold">
                            <option value="1">Active</option>
                            <option value="0">Draft / Hidden</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Public Notes</label>
                        <textarea name="description" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="Helpful info for customers..."></textarea>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 space-y-3">
                    <h4 class="text-xs font-bold text-blue-800 uppercase">Usage Tip</h4>
                    <p class="text-xs text-blue-700 leading-relaxed">Add all core measurement columns first (e.g. Chest, Length, Shoulder), then add your sizes. The table will automatically expand to match.</p>
                </div>
            </div>
        </div>

        <input type="hidden" name="table_data" id="final-json">
    </form>
</div>

<script>
    const sizes = @json($sizes);
    
    function updateUI() {
        const cols = Array.from(document.querySelectorAll('.col-input')).map(i => i.value.trim()).filter(v => v);
        const thead = document.querySelector('#values-table thead tr');
        thead.querySelectorAll('.dyn-th').forEach(th => th.remove());
        const actH = thead.querySelector('.actions-head');
        cols.forEach(c => {
            const th = document.createElement('th'); th.className = 'dyn-th px-4 py-3 text-center'; th.textContent = c;
            thead.insertBefore(th, actH);
        });

        document.querySelectorAll('.size-row').forEach(row => {
            row.querySelectorAll('.dyn-td').forEach(td => td.remove());
            const lastTd = row.lastElementChild;
            cols.forEach(() => {
                const td = document.createElement('td'); td.className = 'dyn-td px-4 py-3';
                td.innerHTML = `<input type="number" step="0.1" class="sc-val w-full text-xs text-center border-gray-300 rounded p-1.5" placeholder="0">`;
                row.insertBefore(td, lastTd);
            });
        });
    }

    function addColumn() {
        const wrap = document.getElementById('column-list');
        const div = document.createElement('div');
        div.className = 'flex items-center gap-1 bg-white border border-gray-300 rounded px-2 py-1';
        div.innerHTML = `<input type="text" class="col-input text-xs font-bold border-0 p-0 focus:ring-0 w-20" oninput="updateUI()"><button type="button" onclick="this.parentElement.remove(); updateUI();" class="text-red-500 font-bold px-1">&times;</button>`;
        wrap.appendChild(div);
    }

    function addRow() {
        const list = document.getElementById('rows-list');
        const tr = document.createElement('tr'); tr.className = 'size-row';
        tr.innerHTML = `
            <td class="px-4 py-3"><select class="sc-code w-full text-xs font-bold border-gray-300 rounded p-1.5" onchange="syncName(this)"><option value="">Select</option>${sizes.map(s => `<option value="${s.code}" data-name="${s.name}">${s.code}</option>`).join('')}</select></td>
            <td class="px-4 py-3"><input type="text" class="sc-name w-full text-xs border-gray-300 rounded p-1.5 bg-gray-50" readonly></td>
            <td class="px-4 py-3 text-right"><button type="button" onclick="this.closest('tr').remove()" class="text-red-500 font-bold">&times;</button></td>
        `;
        list.appendChild(tr);
        updateUI();
    }

    function syncName(s) {
        const row = s.closest('tr');
        row.querySelector('.sc-name').value = s.options[s.selectedIndex].dataset.name || '';
    }

    document.getElementById('size-chart-form').onsubmit = (e) => {
        const headers = Array.from(document.querySelectorAll('.col-input')).map(i => i.value.trim()).filter(v => v);
        const rows = [];
        document.querySelectorAll('.size-row').forEach(tr => {
            const code = tr.querySelector('.sc-code').value;
            if(!code) return;
            const vals = Array.from(tr.querySelectorAll('.sc-val')).map(i => parseFloat(i.value) || 0);
            const m = {}; headers.forEach((h, i) => m[h] = vals[i]);
            rows.push({size_code: code, size_name: tr.querySelector('.sc-name').value, measurements: m});
        });
        document.getElementById('final-json').value = JSON.stringify({headers, rows});
    };

    // Init Logic
    document.addEventListener('DOMContentLoaded', updateUI);
</script>
@endsection
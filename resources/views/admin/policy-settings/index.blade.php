@extends('layouts.admin')

@section('header', 'Policy Settings')

@section('content')
<div class="max-w-5xl mx-auto pb-24">
    <form id="policy-form" action="{{ route('admin.online-store.policy-settings.update') }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Core Charges -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="font-bold text-gray-900 border-b pb-4 mb-6 text-lg">Shipping & Logistics Charges</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">COD Charges</label>
                    <input type="text" name="cod_charges" value="{{ $settings['cod_charges'] }}" placeholder="e.g. ₹50" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Prepaid Charges</label>
                    <input type="text" name="prepaid_charges" value="{{ $settings['prepaid_charges'] }}" placeholder="e.g. Free" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Delivery Timeline</label>
                    <input type="text" name="delivery_timeline" value="{{ $settings['delivery_timeline'] }}" placeholder="3-5 Days" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        <!-- Main Policies -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col">
                <h3 class="font-bold text-gray-900 border-b pb-4 mb-4">Returns Policy</h3>
                <div id="return-policy-editor" class="flex-1 min-h-[250px] border border-gray-300 rounded mb-4">
                    {!! $settings['return_policy'] !!}
                </div>
                <textarea name="return_policy" class="hidden">{{ $settings['return_policy'] }}</textarea>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col">
                <h3 class="font-bold text-gray-900 border-b pb-4 mb-4">Exchange Policy</h3>
                <div id="exchange-policy-editor" class="flex-1 min-h-[250px] border border-gray-300 rounded mb-4">
                    {!! $settings['exchange_policy'] !!}
                </div>
                <textarea name="exchange_policy" class="hidden">{{ $settings['exchange_policy'] }}</textarea>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex flex-col">
                <h3 class="font-bold text-gray-900 border-b pb-4 mb-4">Refund Method</h3>
                <div id="refund-method-editor" class="flex-1 min-h-[250px] border border-gray-300 rounded mb-4">
                    {!! $settings['refund_method'] !!}
                </div>
                <textarea name="refund_method" class="hidden">{{ $settings['refund_method'] }}</textarea>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex justify-between items-center border-b pb-4 mb-4">
                    <h3 class="font-bold text-gray-900">Custom Sections</h3>
                    <button type="button" id="add-section-btn" class="text-xs font-bold bg-gray-100 px-3 py-1 rounded hover:bg-gray-200">+ Add Block</button>
                </div>
                <div id="extra-sections-container" class="space-y-6"></div>
                <input type="hidden" name="extra_sections" id="extra-sections-input" value="{{ $settings['extra_sections'] ?? '[]' }}">
            </div>
        </div>

        <!-- Sticky Save -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-50 md:pl-64 flex justify-end">
            <button type="submit" class="bg-black text-white px-8 py-2 rounded font-bold text-sm hover:bg-gray-800 transition-colors shadow-lg">
                Save All Policies
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var toolbar = [['bold', 'italic'], [{'list': 'ordered'}, {'list': 'bullet'}], ['clean']];
        var qReturn = new Quill('#return-policy-editor', { theme: 'snow', modules: { toolbar: toolbar } });
        var qExchange = new Quill('#exchange-policy-editor', { theme: 'snow', modules: { toolbar: toolbar } });
        var qRefund = new Quill('#refund-method-editor', { theme: 'snow', modules: { toolbar: toolbar } });

        var rIn = document.querySelector('textarea[name="return_policy"]');
        var eIn = document.querySelector('textarea[name="exchange_policy"]');
        var fIn = document.querySelector('textarea[name="refund_method"]');

        qReturn.on('text-change', () => rIn.value = qReturn.root.innerHTML);
        qExchange.on('text-change', () => eIn.value = qExchange.root.innerHTML);
        qRefund.on('text-change', () => fIn.value = qRefund.root.innerHTML);

        var container = document.getElementById('extra-sections-container');
        var jsonIn = document.getElementById('extra-sections-input');
        var idx = 0; var qMap = {};

        function addBlock(h, c) {
            var i = idx++;
            var d = document.createElement('div');
            d.className = 'p-4 bg-gray-50 rounded border relative';
            d.innerHTML = `
                <button type="button" class="absolute top-2 right-2 text-red-500 font-bold text-xs" onclick="this.parentElement.remove(); delete qMap[${i}]">X</button>
                <input type="text" placeholder="Heading" class="b-heading w-full border rounded p-2 text-xs font-bold mb-2" value="${h||''}">
                <div id="ed-${i}" class="bg-white border rounded min-h-[100px]">${c||''}</div>
            `;
            container.appendChild(d);
            qMap[i] = new Quill(`#ed-${i}`, { theme: 'snow', modules: { toolbar: toolbar } });
        }

        JSON.parse(jsonIn.value || '[]').forEach(s => addBlock(s.heading, s.content));
        document.getElementById('add-section-btn').onclick = () => addBlock('', '');
        document.getElementById('policy-form').onsubmit = () => {
            var res = [];
            container.querySelectorAll('.p-4').forEach((row, i) => {
                var h = row.querySelector('.b-heading').value;
                var c = qMap[Object.keys(qMap)[i]] ? qMap[Object.keys(qMap)[i]].root.innerHTML : '';
                if(h || c) res.push({heading:h, content:c});
            });
            jsonIn.value = JSON.stringify(res);
        };
    });
</script>
@endpush
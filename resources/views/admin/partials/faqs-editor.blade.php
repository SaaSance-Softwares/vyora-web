<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-medium text-gray-900">AEO Conversational FAQs</h3>
            <p class="text-sm text-gray-500 mt-1">Add question and answer pairs to boost AI search matching and Google SGE.</p>
        </div>
        <button type="button" id="add-faq-btn" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-200 transition-colors">
            + Add Question
        </button>
    </div>

    <div id="faqs-container" class="space-y-4">
        @php
            $faqs = isset($model) && $model->relationLoaded('faqs') ? $model->faqs : (isset($model) ? $model->faqs()->orderBy('sort_order')->get() : collect([]));
            $oldFaqs = old('faqs', $faqs->toArray());
        @endphp

        @foreach($oldFaqs as $index => $faq)
            <div class="faq-item border border-gray-200 rounded-lg p-4 bg-gray-50 relative">
                <button type="button" class="remove-faq-btn absolute top-4 right-4 text-gray-400 hover:text-red-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
                <div class="grid grid-cols-1 gap-4 mr-8">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Question</label>
                        <input type="text" name="faqs[{{ $index }}][question]" value="{{ $faq['question'] ?? '' }}" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black" placeholder="e.g. What is the material?">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Answer</label>
                        <textarea name="faqs[{{ $index }}][answer]" rows="2" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black" placeholder="e.g. It is made of 100% premium cotton.">{{ $faq['answer'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<template id="faq-template">
    <div class="faq-item border border-gray-200 rounded-lg p-4 bg-gray-50 relative animate-fade-in-up">
        <button type="button" class="remove-faq-btn absolute top-4 right-4 text-gray-400 hover:text-red-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        </button>
        <div class="grid grid-cols-1 gap-4 mr-8">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Question</label>
                <input type="text" name="faqs[__INDEX__][question]" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black" placeholder="e.g. What is the material?">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Answer</label>
                <textarea name="faqs[__INDEX__][answer]" rows="2" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black" placeholder="e.g. It is made of 100% premium cotton."></textarea>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('faqs-container');
        const addBtn = document.getElementById('add-faq-btn');
        const template = document.getElementById('faq-template');
        let faqIndex = document.querySelectorAll('.faq-item').length;

        if(addBtn && container && template) {
            addBtn.addEventListener('click', function() {
                const html = template.innerHTML.replace(/__INDEX__/g, faqIndex);
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                const newFaq = tempDiv.firstElementChild;
                container.appendChild(newFaq);
                faqIndex++;
            });

            container.addEventListener('click', function(e) {
                if(e.target.closest('.remove-faq-btn')) {
                    e.target.closest('.faq-item').remove();
                }
            });
        }
    });
</script>
@endpush

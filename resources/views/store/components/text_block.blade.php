@if(!empty($data['content']))
    <section class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="prose prose-lg mx-auto text-gray-500">
                {!! nl2br(e($data['content'])) !!}
            </div>
        </div>
    </section>
@endif
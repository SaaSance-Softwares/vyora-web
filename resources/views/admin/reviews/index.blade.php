@extends('layouts.admin')

@section('header', 'Product Reviews')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Product Reviews</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4">
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-4">Product</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Rating</th>
                        <th class="px-6 py-4">Review</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($reviews as $review)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($review->product->preview_image)
                                        <img src="{{ asset('storage/' . $review->product->preview_image) }}" class="w-10 h-10 rounded object-cover">
                                    @else
                                        <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $review->product->name }}</div>
                                        <a href="{{ url('/p/' . $review->product->slug) }}" target="_blank" class="text-xs text-blue-600 hover:underline">View Product</a>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium">{{ $review->user->name }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center text-yellow-400">
                                    @for($i = 0; $i < $review->rating; $i++)
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                    @endfor
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-gray-700 italic max-w-xs truncate" title="{{ $review->comment }}">"{{ $review->comment ?? 'No comment' }}"</p>
                                @if($review->images->count() > 0)
                                    <div class="flex gap-2 mt-2">
                                        @foreach($review->images as $image)
                                            <a href="{{ asset($image->image_path) }}" target="_blank">
                                                <img src="{{ asset($image->image_path) }}" class="w-8 h-8 rounded object-cover border">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                                @if($review->admin_reply)
                                    <div class="mt-2 bg-gray-50 border-l-2 border-gray-300 p-2 text-xs">
                                        <span class="font-bold">You:</span> {{ $review->admin_reply }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs">{{ $review->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button onclick="openReplyModal({{ $review->id }}, `{{ htmlspecialchars($review->admin_reply) }}`)" class="px-3 py-1 bg-black text-white rounded text-[10px] font-bold uppercase hover:bg-gray-800">
                                        {{ $review->admin_reply ? 'Edit Reply' : 'Reply' }}
                                    </button>
                                    <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Delete this review completely?');">
                                        @csrf @method('DELETE')
                                        <button class="px-3 py-1 bg-red-50 text-red-600 rounded text-[10px] font-bold uppercase hover:bg-red-100">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">No reviews yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reviews->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Reply Modal -->
<div id="replyModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-bold mb-4">Reply to Review</h3>
        <form id="replyForm" method="POST" action="">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Your Reply</label>
                <textarea name="admin_reply" id="admin_reply_input" rows="4" class="w-full border border-gray-300 rounded-lg py-2 px-3 text-sm focus:ring-black focus:border-black" placeholder="Type your response here..."></textarea>
                <p class="text-xs text-gray-500 mt-1">This reply will be visible to all customers on the product page.</p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeReplyModal()" class="px-4 py-2 text-sm font-semibold text-gray-600 hover:text-black">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-black text-white text-sm font-semibold rounded-lg hover:bg-gray-800">Save Reply</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openReplyModal(reviewId, currentReply) {
        document.getElementById('replyModal').classList.remove('hidden');
        document.getElementById('replyForm').action = `/admin/reviews/${reviewId}/reply`;
        document.getElementById('admin_reply_input').value = currentReply || '';
    }

    function closeReplyModal() {
        document.getElementById('replyModal').classList.add('hidden');
    }
</script>
@endpush

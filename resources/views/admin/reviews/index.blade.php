@extends('layouts.admin')

@section('header', 'Product Reviews')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Product Reviews</h1>
    </div>



    <!-- Bulk Action Form & UI -->
    <form action="{{ route('admin.reviews.bulk') }}" method="POST" id="bulk-action-form" class="mb-4" style="display: none;">
        @csrf
        <input type="hidden" name="action" id="bulk-action-input" value="approve">
        <div id="bulk-action-inputs"></div>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-4 bg-gray-50 flex items-center justify-between">
                <span class="text-sm text-gray-600 font-bold"><span id="selected-count">0</span> reviews selected</span>
                <div class="flex gap-2">
                    <button type="button" onclick="executeBulkAction('approve')" class="px-4 py-2 bg-green-100 text-green-800 text-xs font-bold rounded-lg hover:bg-green-200 transition-colors">Approve</button>
                    <button type="button" onclick="executeBulkAction('reject')" class="px-4 py-2 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-lg hover:bg-yellow-200 transition-colors">Reject</button>
                    <button type="button" onclick="executeBulkAction('delete')" class="px-4 py-2 bg-red-100 text-red-800 text-xs font-bold rounded-lg hover:bg-red-200 transition-colors">Delete</button>
                </div>
            </div>
        </div>
    </form>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                    <tr>
                        <th class="px-6 py-4 w-12"><input type="checkbox" id="select-all" class="rounded border-gray-300 text-black focus:ring-black"></th>
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
                                <input type="checkbox" value="{{ $review->id }}" class="review-checkbox rounded border-gray-300 text-black focus:ring-black">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($review->product->image_url)
                                        <img src="{{ $review->product->image_url }}" class="w-10 h-10 rounded object-cover">
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
                                <button onclick="openReplyModal({{ $review->id }}, `{{ htmlspecialchars($review->admin_reply) }}`, `{{ htmlspecialchars($review->user->name ?? 'Guest') }}`, {{ $review->rating }}, `{{ htmlspecialchars($review->comment) }}`)" class="text-left group w-full">
                                    <p class="text-gray-700 italic max-w-xs truncate group-hover:text-black group-hover:underline">"{{ $review->comment ?? 'No comment' }}"</p>
                                    <span class="text-[10px] text-gray-400 font-semibold uppercase">Read full</span>
                                </button>
                                @if($review->images->count() > 0)
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        @foreach($review->images as $image)
                                            <div class="relative group inline-block">
                                                <a href="{{ asset($image->image_path) }}" target="_blank" class="block">
                                                    @php
                                                        $ext = strtolower(pathinfo($image->image_path, PATHINFO_EXTENSION));
                                                        $isHeic = in_array($ext, ['heic', 'heif']);
                                                    @endphp
                                                    @if($isHeic)
                                                        <div class="w-10 h-10 rounded bg-gray-100 flex items-center justify-center text-[9px] font-bold text-gray-500 border border-gray-200">
                                                            HEIC
                                                        </div>
                                                    @else
                                                        <img src="{{ asset($image->image_path) }}" class="w-10 h-10 rounded object-cover border" onerror="this.onerror=null; this.outerHTML='<div class=\'w-10 h-10 rounded bg-gray-100 flex items-center justify-center text-[9px] font-bold text-gray-500 border border-gray-200\'>IMG</div>';">
                                                    @endif
                                                </a>
                                                <form action="{{ route('admin.reviews.images.destroy', $image->id) }}" method="POST" class="absolute -top-1.5 -right-1.5 opacity-0 group-hover:opacity-100 transition-opacity z-10" onsubmit="return confirm('Delete this image?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="bg-red-500 text-white rounded-full p-0.5 shadow hover:bg-red-600">
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </form>
                                            </div>
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
                                    <form action="{{ route('admin.reviews.bulk') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="selected_ids[]" value="{{ $review->id }}">
                                        @if($review->is_approved)
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="px-3 py-1 bg-yellow-50 text-yellow-700 rounded text-[10px] font-bold uppercase hover:bg-yellow-100">Hide</button>
                                        @else
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="px-3 py-1 bg-green-50 text-green-700 rounded text-[10px] font-bold uppercase hover:bg-green-100">Approve</button>
                                        @endif
                                    </form>
                                    <button onclick="openReplyModal({{ $review->id }}, `{{ htmlspecialchars($review->admin_reply) }}`, `{{ htmlspecialchars($review->user->name ?? 'Guest') }}`, {{ $review->rating }}, `{{ htmlspecialchars($review->comment) }}`)" class="px-3 py-1 bg-black text-white rounded text-[10px] font-bold uppercase hover:bg-gray-800">
                                        View/Reply
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
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">No reviews yet.</td>
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
<div id="replyModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg font-bold">Review Details</h3>
            <button onclick="closeReplyModal()" class="text-gray-400 hover:text-black">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex justify-between items-center mb-2">
                <span id="modalCustomerName" class="font-bold text-sm text-gray-900">Customer</span>
                <div class="flex text-yellow-400" id="modalStars">
                    <!-- Stars injected via JS -->
                </div>
            </div>
            <p id="modalComment" class="text-sm text-gray-700 italic whitespace-pre-wrap">"Comment"</p>
        </div>

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
    function openReplyModal(reviewId, currentReply, customerName, rating, comment) {
        document.getElementById('replyModal').classList.remove('hidden');
        document.getElementById('replyForm').action = `{{ url(config('app.admin_path', 'admin').'/reviews') }}/${reviewId}/reply`;
        document.getElementById('admin_reply_input').value = currentReply || '';
        
        document.getElementById('modalCustomerName').textContent = customerName;
        document.getElementById('modalComment').textContent = comment ? '"' + comment + '"' : 'No text provided.';
        
        // Generate stars
        let starsHtml = '';
        for(let i=1; i<=5; i++) {
            if (i <= rating) {
                starsHtml += '<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
            } else if (i - 0.5 <= rating) {
                starsHtml += '<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M22 9.24l-7.19-.62L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.63-7.03L22 9.24zM12 15.4V6.1l1.71 4.04 4.38.38-3.32 2.88 1 4.28L12 15.4z"/></svg>';
            } else {
                starsHtml += '<svg class="w-4 h-4 text-gray-300 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
            }
        }
        document.getElementById('modalStars').innerHTML = starsHtml;
    }

    function closeReplyModal() {
        document.getElementById('replyModal').classList.add('hidden');
    }

    function updateBulkActionBar() {
        const checkboxes = document.querySelectorAll('.review-checkbox:checked');
        const bulkActionForm = document.getElementById('bulk-action-form');
        const selectedCount = document.getElementById('selected-count');
        
        selectedCount.textContent = checkboxes.length;
        
        if (checkboxes.length > 0) {
            bulkActionForm.style.display = 'block';
        } else {
            bulkActionForm.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.review-checkbox');

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActionBar();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateBulkActionBar();
                if (!this.checked) {
                    selectAll.checked = false;
                } else if (document.querySelectorAll('.review-checkbox:checked').length === checkboxes.length) {
                    selectAll.checked = true;
                }
            });
        });
    });

    function executeBulkAction(action) {
        let msg = '';
        if (action === 'reject') {
            msg = 'Are you sure you want to reject the selected reviews? They will be hidden from the storefront.';
        } else if (action === 'delete') {
            msg = 'Are you sure you want to permanently delete the selected reviews? This cannot be undone.';
        }
        
        if (msg === '' || confirm(msg)) {
            // Populate hidden inputs
            const container = document.getElementById('bulk-action-inputs');
            container.innerHTML = ''; // clear previous
            
            document.querySelectorAll('.review-checkbox:checked').forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_ids[]';
                input.value = cb.value;
                container.appendChild(input);
            });
            
            document.getElementById('bulk-action-input').value = action;
            document.getElementById('bulk-action-form').submit();
        }
    }
</script>
@endpush

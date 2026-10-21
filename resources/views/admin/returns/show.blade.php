@extends('layouts.admin')

@section('title', 'Review Request')
@section('header', 'Review Request')

@section('content')
<div class="space-y-6">

    {{-- Breadcrumb & Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('admin.returns.index') }}" class="hover:text-black">Returns Management</a>
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="font-medium text-gray-900">Request #{{ $returnRequest->id }}</span>
        </div>
    </div>



    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left Column: Details & Items --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- General Info Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-start">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Request Details</h2>
                        <p class="text-sm text-gray-500 mt-1">Order <a href="{{ route('admin.orders.show', $returnRequest->order_id) }}" class="text-indigo-600 hover:underline font-semibold">{{ $returnRequest->order->order_number }}</a></p>
                    </div>
                    <div>
                        @php
                            $sColors = [
                                'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'approved' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                'received' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            ];
                            $c = $sColors[$returnRequest->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider border {{ $c }}">
                            {{ $returnRequest->type }} - {{ $returnRequest->status }}
                        </span>
                    </div>
                </div>
                
                <div class="p-6 grid grid-cols-2 gap-6 text-sm">
                    <div>
                        <p class="text-gray-500 font-medium mb-1">Customer</p>
                        <p class="font-semibold text-gray-900">{{ $returnRequest->user->name ?? 'N/A' }}</p>
                        <p class="text-gray-600">{{ $returnRequest->user->email ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 font-medium mb-1">Requested On</p>
                        <p class="font-semibold text-gray-900">{{ $returnRequest->created_at->format('M d, Y \a\t h:i A') }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-gray-500 font-medium mb-1">Customer Reason</p>
                        <div class="bg-gray-50 rounded-lg p-4 text-gray-700 italic border border-gray-100">
                            {{ $returnRequest->reason ?: 'No reason provided.' }}
                            @if($returnRequest->comments)
                                <br><br><strong>Comments:</strong> {{ $returnRequest->comments }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900">Items to {{ ucfirst($returnRequest->type) }}</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @php $totalProductValue = 0; @endphp
                    @foreach($returnRequest->items as $item)
                        @php 
                            $orderItem = $item->orderItem;
                            $productValue = ($orderItem->price ?? 0) * $item->quantity;
                            $totalProductValue += $productValue;
                        @endphp
                        <div class="p-6 flex gap-4">
                            <div class="w-16 h-20 bg-gray-100 rounded-lg overflow-hidden shrink-0">
                                @if($orderItem && $orderItem->image_url)
                                    <img src="{{ $orderItem->image_url }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900">{{ $orderItem->product_name ?? 'Unknown Product' }}</h3>
                                @if($orderItem && $orderItem->variant_name)
                                    <p class="text-sm text-gray-500 mt-0.5">{{ $orderItem->variant_name }}</p>
                                @endif
                                
                                @if($orderItem && $orderItem->sku)
                                    @php $attrs = $orderItem->sku->attributeValues ?? collect(); @endphp
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @if($orderItem->sku->color)
                                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[10px] font-semibold">Color: {{ $orderItem->sku->color->name }}</span>
                                        @endif
                                        @if($orderItem->sku->size)
                                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[10px] font-semibold">Size: {{ $orderItem->sku->size->name }}</span>
                                        @endif
                                        @if($attrs->count())
                                            @foreach($attrs as $av)
                                                @if($av->attribute)
                                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[10px] font-semibold">{{ $av->attribute->name }}: {{ $av->value }}</span>
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-1 font-mono">SKU: {{ $orderItem->sku->code }}</p>
                                @endif

                                <div class="mt-1 flex items-center gap-2">
                                    @if($returnRequest->order->status === 'delivered' || str_contains($returnRequest->order->status, 'returned'))
                                        <span class="text-[10px] text-green-700 font-semibold bg-green-50 px-2 py-0.5 rounded border border-green-100">
                                            Delivered on {{ $returnRequest->order->delivered_at ? $returnRequest->order->delivered_at->format('M d, Y') : 'Unknown Date' }}
                                        </span>
                                    @elseif($returnRequest->order->status === 'shipped')
                                        <span class="text-[10px] text-indigo-700 font-semibold bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">
                                            Shipped on {{ $returnRequest->order->shipped_at ? $returnRequest->order->shipped_at->format('M d, Y') : 'Unknown Date' }}
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-gray-100 text-gray-800">
                                        Qty: {{ $item->quantity }}
                                    </span>
                                    <span class="font-bold text-gray-900">₹{{ number_format($productValue, 2) }}</span>
                                </div>
                                @if($returnRequest->type === 'exchange' && $item->exchangeVariant)
                                    <div class="mt-3 p-3 bg-blue-50 border border-blue-100 rounded-lg">
                                        <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wider mb-1">Exchange Requested For:</p>
                                        <p class="text-sm font-bold text-gray-900">{{ $item->exchangeVariant->product->name ?? 'Unknown Product' }}</p>
                                        <div class="flex gap-2 mt-1">
                                            @if($item->exchangeVariant->color)
                                                <span class="text-[10px] font-semibold bg-white px-2 py-0.5 rounded border border-gray-200">Color: {{ $item->exchangeVariant->color->name }}</span>
                                            @endif
                                            @if($item->exchangeVariant->size)
                                                <span class="text-[10px] font-semibold bg-white px-2 py-0.5 rounded border border-gray-200">Size: {{ $item->exchangeVariant->size->name }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-2 pt-2 border-t border-blue-100/50 flex justify-between items-center">
                                            <p class="text-[10px] font-mono text-gray-500">SKU: {{ $item->exchangeVariant->code }}</p>
                                            @if($item->exchangeVariant->stock > 0)
                                                <span class="text-[10px] font-bold text-emerald-600">✓ In Stock ({{ $item->exchangeVariant->stock }})</span>
                                            @else
                                                <span class="text-[10px] font-bold text-red-600">✗ Out of Stock</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
        </div>

        {{-- Right Column: Actions & Financials --}}
        <div class="space-y-6">
            
            {{-- Financial Summary --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Financial Summary</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Total Product Value</span>
                        <span class="font-medium">₹{{ number_format($totalProductValue, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-red-600">
                        <span>Return/Exchange Fee</span>
                        <span class="font-medium">
                            @if($returnRequest->type === 'exchange')
                                ₹{{ number_format($returnRequest->fee_deducted, 2) }}
                            @else
                                -₹{{ number_format($returnRequest->fee_deducted, 2) }}
                            @endif
                        </span>
                    </div>
                    @if($returnRequest->type === 'exchange' && $returnRequest->fee_deducted > 0)
                        <div class="mt-2 p-3 bg-amber-50 rounded-lg border border-amber-100 space-y-1">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-amber-800 uppercase tracking-wider">Fee Collection</span>
                                @if($returnRequest->payment_status === 'paid')
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-[10px] font-bold">PAID</span>
                                @else
                                    <span class="px-2 py-0.5 bg-yellow-200 text-yellow-800 rounded text-[10px] font-bold">PENDING</span>
                                @endif
                            </div>
                            @if($returnRequest->payment_id)
                                <div class="text-[10px] text-amber-700/80 font-mono">
                                    Txn: {{ $returnRequest->payment_id }}
                                </div>
                            @endif
                        </div>
                    @endif
                    <div class="pt-3 border-t border-gray-100 flex justify-between font-bold text-gray-900 text-base">
                        <span>Suggested Refund</span>
                        @php $suggestedRefund = max(0, $totalProductValue - $returnRequest->fee_deducted); @endphp
                        <span class="text-emerald-600">₹{{ number_format($suggestedRefund, 2) }}</span>
                    </div>
                    @if($returnRequest->status === 'completed')
                        <div class="mt-4 p-3 bg-emerald-50 rounded-lg border border-emerald-100 text-center">
                            <p class="text-xs text-emerald-800 font-medium uppercase tracking-wider mb-1">Actually Refunded</p>
                            <p class="text-xl font-bold text-emerald-700">₹{{ number_format($returnRequest->refund_amount, 2) }}</p>
                            @if($returnRequest->completed_at)
                                <p class="text-[10px] text-emerald-600 mt-1 font-semibold">on {{ $returnRequest->completed_at->format('M d, Y \a\t h:i A') }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Timeline --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Request Timeline</h2>
                <div class="relative border-l border-gray-200 ml-3 space-y-4 pb-2">
                    <div class="relative pl-5">
                        <div class="absolute w-2 h-2 bg-gray-400 rounded-full -left-[4.5px] top-1.5 ring-4 ring-white"></div>
                        <p class="text-xs font-bold text-gray-900">Requested</p>
                        <p class="text-[10px] text-gray-500">{{ $returnRequest->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    @if($returnRequest->approved_at)
                    <div class="relative pl-5">
                        <div class="absolute w-2 h-2 bg-blue-500 rounded-full -left-[4.5px] top-1.5 ring-4 ring-white"></div>
                        <p class="text-xs font-bold text-gray-900">Approved</p>
                        <p class="text-[10px] text-gray-500">{{ $returnRequest->approved_at->format('M d, Y h:i A') }}</p>
                    </div>
                    @endif
                    @if($returnRequest->received_at)
                    <div class="relative pl-5">
                        <div class="absolute w-2 h-2 bg-indigo-500 rounded-full -left-[4.5px] top-1.5 ring-4 ring-white"></div>
                        <p class="text-xs font-bold text-gray-900">Received at Warehouse</p>
                        <p class="text-[10px] text-gray-500">{{ $returnRequest->received_at->format('M d, Y h:i A') }}</p>
                    </div>
                    @endif
                    @if($returnRequest->rejected_at)
                    <div class="relative pl-5">
                        <div class="absolute w-2 h-2 bg-red-500 rounded-full -left-[4.5px] top-1.5 ring-4 ring-white"></div>
                        <p class="text-xs font-bold text-gray-900">Rejected</p>
                        <p class="text-[10px] text-gray-500">{{ $returnRequest->rejected_at->format('M d, Y h:i A') }}</p>
                    </div>
                    @endif
                    @if($returnRequest->completed_at)
                    <div class="relative pl-5">
                        <div class="absolute w-2 h-2 bg-emerald-500 rounded-full -left-[4.5px] top-1.5 ring-4 ring-white"></div>
                        <p class="text-xs font-bold text-gray-900">Completed (Refunded)</p>
                        <p class="text-[10px] text-gray-500">{{ $returnRequest->completed_at->format('M d, Y h:i A') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Process Actions --}}
            @if($returnRequest->status !== 'completed')
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Update Status</h2>
                
                <form action="{{ route('admin.returns.updateStatus', $returnRequest->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                        <select name="status" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 bg-white" id="statusSelect" onchange="toggleRefundInput()">
                            <option value="pending" {{ $returnRequest->status == 'pending' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="approved" {{ $returnRequest->status == 'approved' ? 'selected' : '' }}>Approved (Awaiting Pickup)</option>
                            <option value="received" {{ $returnRequest->status == 'received' ? 'selected' : '' }}>Received at Warehouse</option>
                            <option value="completed" {{ $returnRequest->status == 'completed' ? 'selected' : '' }}>Completed (Issue Refund)</option>
                            <option value="rejected" {{ $returnRequest->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div id="refundInputDiv" class="{{ $returnRequest->status == 'completed' ? 'block' : 'hidden' }} space-y-2 mt-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Refund Amount to Issue (₹)</label>
                        <p class="text-[10px] text-gray-500 mb-2">Entering this will officially record the refund and deduct it from the Order's balance.</p>
                        <input type="number" step="0.01" name="refund_amount" value="{{ $suggestedRefund }}" 
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
                    </div>

                    <button type="submit" class="w-full flex justify-center py-2.5 px-4 rounded-xl shadow-sm text-sm font-bold text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition-colors">
                        Update Status
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    function toggleRefundInput() {
        const status = document.getElementById('statusSelect').value;
        const refundDiv = document.getElementById('refundInputDiv');
        if (status === 'completed') {
            refundDiv.classList.remove('hidden');
        } else {
            refundDiv.classList.add('hidden');
        }
    }
    // Run on load
    document.addEventListener('DOMContentLoaded', toggleRefundInput);
</script>
@endsection

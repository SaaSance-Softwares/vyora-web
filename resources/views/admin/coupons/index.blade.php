@extends('layouts.admin')

@section('header', 'Coupons & Promotions')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Discount Campaigns</h1>
        <a href="{{ route('admin.online-store.coupons.create') }}" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 text-sm font-bold flex items-center gap-2">
            <span>+</span> Create Coupon
        </a>
    </div>

    <!-- Coupon List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($coupons->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-4">Coupon Code</th>
                            <th class="px-6 py-4">Discount</th>
                            <th class="px-6 py-4 text-center">Usage</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm font-medium">
                        @foreach($coupons as $coupon)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $coupon->code }}</div>
                                    <div class="text-[10px] text-gray-400 font-bold uppercase">{{ $coupon->name ?: 'Standard' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($coupon->type === 'percentage')
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold">{{ $coupon->discount_amount }}% OFF</span>
                                    @elseif($coupon->type === 'fixed')
                                        <span class="px-2 py-0.5 bg-green-50 text-green-700 rounded text-[10px] font-bold">₹{{ $coupon->discount_amount }} OFF</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-gray-50 text-gray-700 rounded text-[10px] font-bold">{{ ucfirst($coupon->type) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-gray-900">{{ $coupon->times_used }} <span class="text-gray-400 text-xs">/ {{ $coupon->usage_limit ?: '∞' }}</span></div>
                                </td>
                                <td class="px-6 py-4 uppercase text-[10px] font-bold">
                                    @if(!$coupon->is_active)
                                        <span class="text-gray-400">Inactive</span>
                                    @elseif($coupon->expires_at && $coupon->expires_at->isPast())
                                        <span class="text-red-500">Expired</span>
                                    @else
                                        <span class="text-green-600">Active</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-3 text-xs font-bold">
                                        <a href="{{ route('admin.online-store.coupons.edit', $coupon->id) }}" class="text-blue-600 hover:underline">Edit</a>
                                        <form action="{{ route('admin.online-store.coupons.destroy', $coupon->id) }}" method="POST" onsubmit="return confirm('Delete coupon?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:underline">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($coupons->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $coupons->links() }}
                </div>
            @endif
        @else
            <div class="p-12 text-center text-gray-400 italic">
                No active coupons found.
            </div>
        @endif
    </div>
</div>
@endsection

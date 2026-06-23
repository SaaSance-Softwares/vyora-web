@extends('layouts.admin')
@section('header', 'Abandoned Carts')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Abandoned Carts</h1>
            <p class="text-sm text-gray-500 mt-1">View and manage carts that users left behind without completing checkout.</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50/50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-gray-900">Customer</th>
                        <th class="px-6 py-4 font-semibold text-gray-900">Items</th>
                        <th class="px-6 py-4 font-semibold text-gray-900">Total Value</th>
                        <th class="px-6 py-4 font-semibold text-gray-900">Last Active</th>
                        <th class="px-6 py-4 font-semibold text-gray-900">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($carts as $cart)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">
                                    {{ $cart->user ? $cart->user->name : 'Guest' }}
                                </div>
                                <div class="text-gray-500 text-xs mt-0.5">
                                    {{ $cart->user ? $cart->user->email : $cart->guest_email }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex -space-x-2 overflow-hidden">
                                    @foreach($cart->items->take(3) as $item)
                                        @if($item->image)
                                            <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white object-cover" src="{{ $item->image }}" alt="">
                                        @else
                                            <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-gray-100 flex items-center justify-center text-[10px] text-gray-500">
                                                No img
                                            </div>
                                        @endif
                                    @endforeach
                                    @if($cart->items->count() > 3)
                                        <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-600">
                                            +{{ $cart->items->count() - 3 }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                ₹{{ number_format($cart->items->sum(function($item) { return $item->price * $item->quantity; }), 2) }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $cart->updated_at->diffForHumans() }}
                            </td>
                            <td class="px-6 py-4">
                                @if($cart->abandoned_email_sent_at)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20">
                                        Email Sent
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                        Pending Email
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <h3 class="text-sm font-medium text-gray-900">No abandoned carts</h3>
                                    <p class="mt-1 text-sm text-gray-500">When users leave items in their cart without checking out, they will appear here.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($carts->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $carts->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

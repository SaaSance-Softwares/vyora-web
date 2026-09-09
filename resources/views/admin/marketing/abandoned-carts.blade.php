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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 flex gap-3">
            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <h3 class="text-sm font-medium text-blue-800">2-Hour Waiting Period</h3>
                <p class="text-xs text-blue-600 mt-1">
                    To prevent spamming active shoppers, carts will only appear on this page after <strong>2 full hours of inactivity</strong>.
                </p>
            </div>
        </div>

        <div class="bg-green-50/50 border border-green-100 rounded-xl p-4 flex gap-3">
            <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-green-800">WhatsApp Recovery setup</h3>
                <p class="text-xs text-green-600 mt-1">
                    To auto-send WhatsApp reminders, create a template in your Meta Manager named exactly <code class="bg-green-100 px-1 py-0.5 rounded font-bold">abandoned_cart</code> and sync it.
                </p>
            </div>
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
                        <th class="px-6 py-4 font-semibold text-gray-900">Added to Cart</th>
                        <th class="px-6 py-4 font-semibold text-gray-900">Abandoned Time</th>
                        <th class="px-6 py-4 font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-4 font-semibold text-gray-900">Action</th>
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
                                @if($cart->user && $cart->user->phone)
                                <div class="text-gray-400 text-[10px] mt-0.5">
                                    {{ $cart->user->phone }}
                                </div>
                                @endif
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
                                @php
                                    $firstAdded = $cart->items->min('created_at');
                                @endphp
                                {{ $firstAdded ? $firstAdded->format('h:i A (d M)') : $cart->updated_at->format('h:i A (d M)') }}
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                @if($cart->status === 'abandoned' || $cart->abandoned_email_sent_at)
                                    {{ $cart->abandoned_email_sent_at ? $cart->abandoned_email_sent_at->format('h:i A (d M)') : $cart->updated_at->addHours(2)->format('h:i A (d M)') }}
                                @else
                                    <span class="text-xs text-gray-400">Not abandoned yet</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1.5 items-start">
                                    @if($cart->abandoned_email_sent_at)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-medium bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20">
                                            Email Sent
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-medium bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20">
                                            Email Pending
                                        </span>
                                    @endif

                                    @if(isset($cart->whatsapp_status))
                                        @if($cart->whatsapp_status === 'sent')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-medium bg-[#25D366]/10 text-[#128C7E] ring-1 ring-inset ring-[#25D366]/20">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                                                WA Sent
                                            </span>
                                        @elseif($cart->whatsapp_status === 'failed')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-medium bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20">
                                                WA Failed
                                            </span>
                                        @elseif($cart->whatsapp_status === 'no_phone')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-medium bg-gray-50 text-gray-500 ring-1 ring-inset ring-gray-500/20">
                                                No Phone
                                            </span>
                                        @elseif($cart->whatsapp_status === 'not_configured')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-medium bg-gray-50 text-gray-500 ring-1 ring-inset ring-gray-500/20">
                                                WA Not Setup
                                            </span>
                                        @elseif($cart->whatsapp_status === 'pending')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-medium bg-gray-50 text-gray-600 ring-1 ring-inset ring-gray-500/20">
                                                WA Pending
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.abandoned-carts.show', $cart->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                                    More Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
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

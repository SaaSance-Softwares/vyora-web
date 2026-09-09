@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.abandoned-carts.index') }}" class="hover:text-gray-900 transition-colors">Abandoned Carts</a>
                <span>/</span>
                <span class="text-gray-900 font-medium">Cart #{{ $cart->id }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                Abandoned Cart
                @if($cart->status === 'abandoned' || $cart->abandoned_email_sent_at)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Abandoned
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Active / Pending
                    </span>
                @endif
            </h1>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        <!-- Main Content (Left) -->
        <div class="xl:col-span-2 space-y-6">
            
            <!-- Items Table -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                    <h2 class="text-base font-semibold text-gray-900">Cart Contents ({{ $cart->items->count() }} items)</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-white border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-gray-500 uppercase tracking-wider text-xs">Product</th>
                                <th class="px-6 py-3 font-semibold text-gray-500 uppercase tracking-wider text-xs">Added At</th>
                                <th class="px-6 py-3 font-semibold text-gray-500 uppercase tracking-wider text-xs text-right">Price</th>
                                <th class="px-6 py-3 font-semibold text-gray-500 uppercase tracking-wider text-xs text-center">Qty</th>
                                <th class="px-6 py-3 font-semibold text-gray-500 uppercase tracking-wider text-xs text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($cart->items as $item)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            @if($item->image)
                                                <img src="{{ $item->image }}" alt="" class="w-12 h-12 rounded-lg object-cover border border-gray-200 bg-white">
                                            @else
                                                <div class="w-12 h-12 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $item->sku && $item->sku->product ? $item->sku->product->name : 'Unknown Product' }}</div>
                                                <div class="text-xs text-gray-500 mt-0.5">SKU: {{ $item->sku ? $item->sku->code : 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-gray-900">{{ $item->created_at ? $item->created_at->format('d M, Y') : 'Unknown' }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->created_at ? $item->created_at->format('h:i A') : '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-gray-900">
                                        ₹{{ number_format($item->price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center font-medium text-gray-900">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold text-gray-900">
                                        ₹{{ number_format($item->quantity * $item->price, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        No items found in this cart.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($cart->items->count() > 0)
                        <tfoot class="bg-gray-50/50">
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-right font-medium text-gray-500">
                                    Total Cart Value
                                </td>
                                <td class="px-6 py-4 text-right text-lg font-bold text-gray-900">
                                    ₹{{ number_format($cart->items->sum(function($i) { return $i->price * $i->quantity; }), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Recovery Actions -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                    <h2 class="text-base font-semibold text-gray-900">Recovery Delivery Status</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Email Status -->
                        <div class="flex items-start gap-4 p-4 rounded-xl border {{ $cart->abandoned_email_sent_at ? 'border-green-200 bg-green-50/50' : 'border-gray-200 bg-gray-50/50' }}">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $cart->abandoned_email_sent_at ? 'bg-green-100 text-green-600' : 'bg-gray-200 text-gray-500' }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold {{ $cart->abandoned_email_sent_at ? 'text-green-900' : 'text-gray-900' }}">Email Recovery</h3>
                                <p class="text-sm mt-1 {{ $cart->abandoned_email_sent_at ? 'text-green-700' : 'text-gray-500' }}">
                                    {{ $cart->abandoned_email_sent_at ? 'Sent successfully on '.$cart->abandoned_email_sent_at->format('d M, h:i A') : 'Has not been dispatched yet.' }}
                                </p>
                            </div>
                        </div>

                        <!-- WhatsApp Status -->
                        <div class="flex items-start gap-4 p-4 rounded-xl border 
                            @if($cart->whatsapp_status === 'sent') border-green-200 bg-green-50/50
                            @elseif($cart->whatsapp_status === 'failed') border-red-200 bg-red-50/50
                            @else border-gray-200 bg-gray-50/50 @endif
                        ">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 
                                @if($cart->whatsapp_status === 'sent') bg-green-100 text-[#25D366]
                                @elseif($cart->whatsapp_status === 'failed') bg-red-100 text-red-600
                                @else bg-gray-200 text-gray-500 @endif
                            ">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold 
                                    @if($cart->whatsapp_status === 'sent') text-green-900
                                    @elseif($cart->whatsapp_status === 'failed') text-red-900
                                    @else text-gray-900 @endif
                                ">WhatsApp Recovery</h3>
                                <p class="text-sm mt-1 
                                    @if($cart->whatsapp_status === 'sent') text-green-700
                                    @elseif($cart->whatsapp_status === 'failed') text-red-700
                                    @else text-gray-500 @endif
                                ">
                                    {{ $cart->whatsapp_status === 'sent' ? 'Message delivered successfully.' : ($cart->whatsapp_status === 'failed' ? 'Failed to dispatch Meta API.' : ($cart->whatsapp_status === 'no_phone' ? 'No phone number provided.' : 'Has not been dispatched yet.')) }}
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <!-- Sidebar (Right) -->
        <div class="space-y-6">
            
            <!-- Customer Card -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                    <h2 class="text-base font-semibold text-gray-900">Customer</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-900 text-white rounded-full flex items-center justify-center font-bold text-lg">
                            {{ strtoupper(substr($cart->user ? $cart->user->name : ($cart->guest_email ?: 'G'), 0, 1)) }}
                        </div>
                        <div>
                            <div class="font-bold text-gray-900 text-lg">{{ $cart->user ? $cart->user->name : 'Guest Customer' }}</div>
                            <div class="text-sm text-gray-500">{{ $cart->user ? 'Registered Account' : 'No Account' }}</div>
                        </div>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-100 space-y-3">
                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Email Address</div>
                            <div class="text-sm text-gray-900 font-medium">
                                {{ $cart->user ? $cart->user->email : ($cart->guest_email ?: 'Not provided') }}
                            </div>
                        </div>
                        
                        @if($cart->user && $cart->user->phone)
                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Phone Number</div>
                            <div class="text-sm text-gray-900 font-medium">
                                {{ $cart->user->phone }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timeline Card -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
                    <h2 class="text-base font-semibold text-gray-900">Session Timeline</h2>
                </div>
                <div class="p-5">
                    <ul class="relative border-l-2 border-gray-100 ml-4 space-y-6">
                        <!-- Created -->
                        <li class="relative">
                            <div class="absolute -left-[1.4rem] top-0 w-10 h-10 rounded-full bg-white border-4 border-white flex items-center justify-center">
                                <div class="w-full h-full bg-blue-50 text-blue-600 rounded-full flex items-center justify-center ring-1 ring-blue-100">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                </div>
                            </div>
                            <div class="pl-8 pt-0.5">
                                <div class="text-sm font-bold text-gray-900">Cart Initialized</div>
                                <div class="text-xs text-gray-500 mt-0.5">First interaction started</div>
                                <div class="inline-flex items-center gap-1.5 mt-2 px-2 py-1 rounded bg-gray-50 border border-gray-100 text-xs font-medium text-gray-600">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    {{ $cart->created_at->format('d M, Y \a\t h:i A') }}
                                </div>
                            </div>
                        </li>
                        <!-- Updated -->
                        <li class="relative">
                            <div class="absolute -left-[1.4rem] top-0 w-10 h-10 rounded-full bg-white border-4 border-white flex items-center justify-center">
                                <div class="w-full h-full bg-gray-50 text-gray-500 rounded-full flex items-center justify-center ring-1 ring-gray-200">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                </div>
                            </div>
                            <div class="pl-8 pt-0.5">
                                <div class="text-sm font-bold text-gray-900">Last Cart Activity</div>
                                <div class="text-xs text-gray-500 mt-0.5">Latest changes by the customer</div>
                                <div class="inline-flex items-center gap-1.5 mt-2 px-2 py-1 rounded bg-gray-50 border border-gray-100 text-xs font-medium text-gray-600">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    {{ $cart->updated_at->format('d M, Y \a\t h:i A') }}
                                </div>
                            </div>
                        </li>
                        <!-- Abandoned -->
                        <li class="relative">
                            @if($cart->status === 'abandoned' || $cart->abandoned_email_sent_at)
                                <div class="absolute -left-[1.4rem] top-0 w-10 h-10 rounded-full bg-white border-4 border-white flex items-center justify-center">
                                    <div class="w-full h-full bg-red-50 text-red-600 rounded-full flex items-center justify-center ring-1 ring-red-200">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                </div>
                                <div class="pl-8 pt-0.5">
                                    <div class="text-sm font-bold text-red-700">Marked as Abandoned</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Cart left inactive for 2 hours</div>
                                    <div class="inline-flex items-center gap-1.5 mt-2 px-2 py-1 rounded bg-red-50 border border-red-100 text-xs font-medium text-red-700">
                                        <svg class="w-3.5 h-3.5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        {{ $cart->updated_at->addHours(2)->format('d M, Y \a\t h:i A') }}
                                    </div>
                                </div>
                            @else
                                <div class="absolute -left-[1.4rem] top-0 w-10 h-10 rounded-full bg-white border-4 border-white flex items-center justify-center">
                                    <div class="w-full h-full bg-gray-50 text-gray-300 rounded-full flex items-center justify-center border-2 border-dashed border-gray-200">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </div>
                                </div>
                                <div class="pl-8 pt-0.5">
                                    <div class="text-sm font-bold text-gray-400">Abandonment Schedule</div>
                                    <div class="text-xs text-gray-400 mt-0.5">Will mark as abandoned at</div>
                                    <div class="inline-flex items-center gap-1.5 mt-2 px-2 py-1 rounded bg-gray-50 border border-gray-100 text-xs font-medium text-gray-500">
                                        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        {{ $cart->updated_at->addHours(2)->format('d M, Y \a\t h:i A') }}
                                    </div>
                                </div>
                            @endif
                        </li>
                        
                        <!-- Messages Sent -->
                        <li class="relative">
                            @if($cart->abandoned_email_sent_at)
                                <div class="absolute -left-[1.4rem] top-0 w-10 h-10 rounded-full bg-white border-4 border-white flex items-center justify-center">
                                    <div class="w-full h-full bg-green-50 text-green-600 rounded-full flex items-center justify-center ring-1 ring-green-200">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    </div>
                                </div>
                                <div class="pl-8 pt-0.5">
                                    <div class="text-sm font-bold text-green-700">Recovery Messages Sent</div>
                                    <div class="text-xs text-gray-500 mt-0.5">Email and WhatsApp dispatched</div>
                                    <div class="inline-flex items-center gap-1.5 mt-2 px-2 py-1 rounded bg-green-50 border border-green-100 text-xs font-medium text-green-700">
                                        <svg class="w-3.5 h-3.5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        {{ $cart->abandoned_email_sent_at->format('d M, Y \a\t h:i A') }}
                                    </div>
                                </div>
                            @else
                                <div class="absolute -left-[1.4rem] top-0 w-10 h-10 rounded-full bg-white border-4 border-white flex items-center justify-center">
                                    <div class="w-full h-full bg-gray-50 text-gray-300 rounded-full flex items-center justify-center border-2 border-dashed border-gray-200">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    </div>
                                </div>
                                <div class="pl-8 pt-0.5">
                                    <div class="text-sm font-bold text-gray-400">Recovery Schedule</div>
                                    <div class="text-xs text-gray-400 mt-0.5">Pending automated dispatch</div>
                                </div>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

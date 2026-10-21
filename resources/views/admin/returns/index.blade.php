@extends('layouts.admin')

@section('title', 'Returns & Exchanges')
@section('header', 'Returns & Exchanges')

@section('content')
<div class="space-y-6">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Returns Management (RMA)</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage, track, and process customer returns and exchanges</p>
        </div>
        <div class="flex items-center gap-3 text-sm text-gray-500">
            <span class="bg-gray-100 px-3 py-1.5 rounded-full font-medium">Total: {{ $stats['all'] ?? 0 }} requests</span>
            <a href="{{ route('admin.returns.create') }}" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-black transition-colors shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Request
            </a>
        </div>
    </div>

    {{-- ===== STATS CARDS ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        @php
            $statConfig = [
                'all'        => ['label' => 'All',        'color' => 'bg-gray-900 text-white',          'ring' => ''],
                'pending'    => ['label' => 'Pending',    'color' => 'bg-amber-50 text-amber-700',      'ring' => 'ring-1 ring-amber-200'],
                'approved'   => ['label' => 'Approved',   'color' => 'bg-blue-50 text-blue-700',        'ring' => 'ring-1 ring-blue-200'],
                'received'   => ['label' => 'Received',   'color' => 'bg-indigo-50 text-indigo-700',    'ring' => 'ring-1 ring-indigo-200'],
                'completed'  => ['label' => 'Completed',  'color' => 'bg-emerald-50 text-emerald-700',  'ring' => 'ring-1 ring-emerald-200'],
            ];
            $currentStatus = request('status', 'all');
        @endphp

        @foreach($statConfig as $key => $config)
            <a href="{{ route('admin.returns.index', ['status' => $key]) }}"
               class="p-4 rounded-xl transition-all duration-200 relative overflow-hidden group border 
                      {{ $currentStatus === $key ? $config['color'].' border-transparent shadow-md transform scale-[1.02]' : 'bg-white border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-gray-700' }}">
                <div class="flex items-center justify-between relative z-10">
                    <span class="text-sm font-semibold">{{ $config['label'] }}</span>
                    <span class="text-lg font-bold">{{ $stats[$key] ?? 0 }}</span>
                </div>
            </a>
        @endforeach
    </div>

    {{-- ===== SEARCH & FILTER BAR ===== --}}
    <form method="GET" action="{{ route('admin.returns.index') }}" class="mb-6 bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
        <div class="flex flex-col lg:flex-row gap-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            
            {{-- Search --}}
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search by Order Number or ID..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent">
            </div>

            {{-- Submit --}}
            <button type="submit" class="px-5 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-medium hover:bg-black transition-colors shadow-sm">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('admin.returns.index', request()->except('search')) }}" class="px-5 py-2.5 bg-gray-50 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-100 transition-colors">Clear</a>
            @endif
        </div>
    </form>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-white text-gray-500 uppercase tracking-wider text-xs border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Request Details</th>
                        <th class="px-6 py-4 font-semibold">Customer</th>
                        <th class="px-6 py-4 font-semibold">Type</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Fees / Refund</th>
                        <th class="px-6 py-4 font-semibold">Date</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($returns as $req)
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-900">{{ $req->order->order_number ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-500 font-mono mt-0.5" title="{{ $req->uuid }}">
                                        {{ Str::limit($req->uuid, 8, '') }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-900">{{ $req->user->name ?? 'Guest' }}</span>
                                    <span class="text-xs text-gray-500 mt-0.5">{{ $req->user->email ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($req->type === 'return')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-amber-100 text-amber-800 uppercase tracking-wider border border-amber-200">
                                        Return
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-blue-100 text-blue-800 uppercase tracking-wider border border-blue-200">
                                        Exchange
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $sColors = [
                                        'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'approved' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                        'received' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                        'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    ];
                                    $c = $sColors[$req->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold {{ $c }} uppercase tracking-wider border">
                                    {{ $req->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col text-xs">
                                    <span class="text-red-600">Fee: ₹{{ number_format($req->fee_deducted, 2) }}</span>
                                    <span class="text-emerald-600 font-bold">Refunded: ₹{{ number_format($req->refund_amount, 2) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $req->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.returns.show', $req->id) }}" 
                                   class="inline-flex items-center justify-center bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 hover:text-black hover:border-black px-3 py-1.5 rounded-lg text-sm font-medium transition-all shadow-sm">
                                    Review
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-medium text-gray-900">No Return Requests Found</h3>
                                <p class="text-sm text-gray-500 mt-1">Try adjusting your filters or search term.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
            <div class="p-4 border-t border-gray-200">
                {{ $returns->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

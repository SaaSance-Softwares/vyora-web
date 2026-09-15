@extends('layouts.admin')

@section('header', 'POS Markets / Stores')

@section('content')
<div class="space-y-6 pb-24">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Manage Stores</h3>
            <p class="text-sm text-gray-500">View and manage your POS locations and markets.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('frontend.stores') }}" target="_blank" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50 text-sm font-medium transition-colors shadow-sm" title="Click to view the public Store Locator page">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                Public URL: /stores
            </a>
            <a href="{{ route('admin.pos-markets.create') }}" class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 text-sm font-medium transition-colors shadow-sm">
                + Create Store
            </a>
        </div>
    </div>

    {{-- ── EXISTING LOCATIONS LIST ─────────────────────── --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Store Name</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($locations as $loc)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $loc->name }}</div>
                        @if($loc->city)
                            <div class="text-xs text-gray-500 mt-1">{{ $loc->city }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($loc->type === 'temporary')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                Temporary Stall
                            </span>
                            @if($loc->start_date && $loc->end_date)
                                <div class="text-[10px] text-gray-500 mt-1">
                                    {{ \Carbon\Carbon::parse($loc->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($loc->end_date)->format('M d, Y') }}
                                </div>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Permanent Store
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($loc->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-2"><a href="{{ route('admin.pos-markets.edit', $loc->slug) }}" class="text-sm text-gray-600 hover:text-gray-900 font-medium bg-gray-100 px-3 py-1.5 rounded-md hover:bg-gray-200 transition-colors">Edit Store</a><a href="{{ route('admin.pos-markets.show', ['slug' => $loc->slug, 'tab' => 'add']) }}" class="text-sm text-blue-600 hover:text-blue-900 font-medium bg-blue-50 px-3 py-1.5 rounded-md hover:bg-blue-100 transition-colors">Add Product</a><a href="{{ route('admin.pos-markets.show', ['slug' => $loc->slug, 'tab' => 'manage']) }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium bg-indigo-50 px-3 py-1.5 rounded-md hover:bg-indigo-100 transition-colors">Manage Product</a></div>
                    </td>
                </tr>
                @endforeach
                
                @if(count($locations) === 0)
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                        No stores created yet. Add one above to get started!
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection

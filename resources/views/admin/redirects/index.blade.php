@extends('layouts.admin')
@section('header', '301 Redirects')

@section('content')
<div class="space-y-6" x-data="{ showImport: false }">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">301 Redirects</h1>
        <div class="flex gap-2">
            <button @click="showImport = !showImport" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-lg hover:bg-gray-200 text-sm font-medium flex items-center gap-2 border border-gray-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                Import CSV
            </button>
            <a href="{{ route('admin.settings.redirects.create') }}" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 text-sm font-medium flex items-center gap-2">
                <span>+</span> Add Redirect
            </a>
        </div>
    </div>

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-start gap-3 transition-all duration-500">
            <svg class="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <!-- Import CSV Form (Hidden by default) -->
    <div x-show="showImport" style="display: none;" class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
        <h2 class="text-lg font-semibold mb-4 text-gray-900">Bulk Import via CSV</h2>
        <div class="mb-4 text-sm text-gray-600 bg-blue-50 p-3 rounded border border-blue-100">
            <strong>Tip:</strong> Your CSV should have columns named <code>old_url</code> and <code>new_url</code>. If you are uploading directly from a service like Google Search Console without a <code>new_url</code> column, the old URLs will still be saved so you can map them later!
        </div>
        <form action="{{ route('admin.settings.redirects.import') }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-4">
            @csrf
            <div class="flex-1">
                <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-1">Select CSV File</label>
                <input type="file" name="csv_file" id="csv_file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 border border-gray-200 rounded-lg" accept=".csv" required>
            </div>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">Upload & Process</button>
            <button type="button" @click="showImport = false" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">Cancel</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Old URL</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Redirect To</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($redirects as $redirect)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <code class="px-2 py-1 bg-red-50 text-red-700 rounded text-xs">{{ $redirect->old_url }}</code>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($redirect->new_url)
                            <code class="px-2 py-1 bg-green-50 text-green-700 rounded text-xs">{{ $redirect->new_url }}</code>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                Missing Target
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($redirect->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active (301)
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Disabled
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.settings.redirects.edit', $redirect) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                        <form action="{{ route('admin.settings.redirects.destroy', $redirect) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this redirect?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-10 text-center text-gray-500 text-sm">
                        No redirects found. Import a CSV or create one manually.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($redirects->hasPages())
        <div class="px-6 py-3 bg-white border-t border-gray-200">
            {{ $redirects->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

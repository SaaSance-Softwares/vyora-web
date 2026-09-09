@extends('layouts.admin')
@section('header', 'Edit Redirect')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.settings.redirects.index') }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Edit 301 Redirect</h1>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden w-full">
        <form action="{{ route('admin.settings.redirects.update', $redirect) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label for="old_url" class="block text-sm font-semibold text-gray-900 mb-1">Old URL <span class="text-red-500">*</span></label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <input type="text" name="old_url" id="old_url" class="block w-full rounded-md border-gray-300 focus:border-black focus:ring-black sm:text-sm p-3 border @error('old_url') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" value="{{ old('old_url', $redirect->old_url) }}" required>
                </div>
                <p class="mt-2 text-sm text-gray-500">The legacy path that users or search engines are visiting.</p>
                @error('old_url')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="new_url" class="block text-sm font-semibold text-gray-900 mb-1">Redirect To (New URL)</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <input type="text" name="new_url" id="new_url" class="block w-full rounded-md border-gray-300 focus:border-black focus:ring-black sm:text-sm p-3 border @error('new_url') border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 @enderror" value="{{ old('new_url', $redirect->new_url) }}">
                </div>
                <p class="mt-2 text-sm text-gray-500">The new destination where they should be sent. Can be a relative path or full URL.</p>
                @error('new_url')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-4 border-t border-gray-100">
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input id="is_active" name="is_active" type="checkbox" value="1" {{ $redirect->is_active ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-black focus:ring-black">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Enable this redirect immediately (Active)
                    </label>
                </div>
            </div>

            <div class="pt-6 flex gap-3">
                <button type="submit" class="px-6 py-2.5 bg-black text-white rounded-lg hover:bg-gray-800 text-sm font-medium transition-colors">
                    Update Redirect
                </button>
                <a href="{{ route('admin.settings.redirects.index') }}" class="px-6 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

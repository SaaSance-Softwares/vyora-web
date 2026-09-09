@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Delivery Timeline</h1>
            <p class="text-sm text-gray-500 mt-1">Update your delivery timeline details.</p>
        </div>
        <a href="{{ route('admin.online-store.delivery-timelines.index') }}" class="text-sm font-medium text-gray-600 hover:text-black">
            &larr; Back to Timelines
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 max-w-2xl">
        <form action="{{ route('admin.online-store.delivery-timelines.update', $deliveryTimeline) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Min Days *</label>
                    <input type="number" name="min_days" value="{{ old('min_days', $deliveryTimeline->min_days) }}" required min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-black focus:border-black outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Max Days *</label>
                    <input type="number" name="max_days" value="{{ old('max_days', $deliveryTimeline->max_days) }}" required min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-black focus:border-black outline-none transition-all">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Internal Note (Optional)</label>
                <textarea name="internal_note" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-black focus:border-black outline-none transition-all">{{ old('internal_note', $deliveryTimeline->internal_note) }}</textarea>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Show to User (Optional)</label>
                <textarea name="show_to_user" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-black focus:border-black outline-none transition-all">{{ old('show_to_user', $deliveryTimeline->show_to_user) }}</textarea>
            </div>
            
            <div class="pt-4 border-t border-gray-100 flex justify-end space-x-3">
                <a href="{{ route('admin.online-store.delivery-timelines.index') }}" class="px-4 py-2.5 text-sm font-bold text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-lg uppercase tracking-wider transition-colors">
                    Cancel
                </a>
                <button type="submit" class="bg-black text-white rounded-lg px-6 py-2.5 text-sm font-bold uppercase tracking-wider hover:bg-gray-800 transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

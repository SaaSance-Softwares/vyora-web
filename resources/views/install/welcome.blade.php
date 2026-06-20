@extends('layouts.installer')

@section('subtitle', 'System Requirements Check')

@section('content')
<div class="space-y-6">
    <div class="text-center mb-8">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Congratulations on your new platform! 🎉</h3>
        <p class="text-sm text-gray-500 font-medium">You are about to install <strong class="text-black">Vyora</strong>, the premium, high-performance e-commerce engine. Let's make sure your server is ready to handle the power.</p>
    </div>

    <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100 shadow-inner">
        <ul class="space-y-4">
            @foreach($requirements as $req => $met)
            <li class="flex items-center justify-between text-sm">
                <span class="text-gray-700 font-bold tracking-tight">{{ $req }}</span>
                @if($met)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-green-50 border border-green-200 text-green-700 text-[10px] uppercase font-black tracking-widest shadow-sm">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        Passed
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-red-50 border border-red-200 text-red-700 text-[10px] uppercase font-black tracking-widest shadow-sm">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Failed
                    </span>
                @endif
            </li>
            @endforeach
        </ul>
    </div>

    <div class="pt-2">
        @if($allMet)
            <a href="{{ route('install.database') }}" class="w-full flex items-center justify-center gap-2 py-4 px-4 border border-transparent rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] text-xs uppercase tracking-[0.2em] font-black text-white bg-black hover:bg-gray-800 hover:shadow-[0_8px_30px_rgb(0,0,0,0.2)] transition-all active:scale-[0.98]">
                Proceed to Database <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        @else
            <button disabled class="w-full flex items-center justify-center gap-2 py-4 px-4 border border-transparent rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] text-xs uppercase tracking-[0.2em] font-black text-white bg-gray-300 cursor-not-allowed">
                Please Fix Requirements
            </button>
        @endif
    </div>
</div>
@endsection

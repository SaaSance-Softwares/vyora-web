@extends('layouts.admin')
@section('header', 'Update manager')

@section('content')
<!-- Full Width Container -->
<div class="w-full px-6 lg:px-12 pb-16">



    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl shadow-sm flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <h4 class="font-bold">Error</h4>
                <p class="text-sm">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        {{-- ── COLUMN 1: SYSTEM UPDATES ───────────────────────────── --}}
        <div class="xl:col-span-2 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Available System Updates</h2>
                <a href="{{ route('admin.settings.update.index') }}" class="inline-flex items-center justify-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900 bg-white border border-gray-200 px-5 py-2 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Check for Updates
                </a>
            </div>
            
            <div class="relative overflow-hidden bg-white rounded-3xl shadow-sm border {{ isset($backUpdateAvailable) && $backUpdateAvailable ? 'border-amber-300 shadow-amber-100/50' : 'border-emerald-200 shadow-emerald-100/50' }} flex flex-col group transition-all duration-300 hover:shadow-md">
                @if(isset($backUpdateAvailable) && $backUpdateAvailable)
                    <div class="absolute top-0 right-0 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
                @else
                    <div class="absolute top-0 right-0 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
                @endif
                
                <div class="p-6 md:p-8 bg-gradient-to-b from-gray-50/80 to-white border-b border-gray-100">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center shadow-inner shadow-white/10">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 leading-tight">Vyora Platform</h3>
                                <p class="text-xs text-gray-500 font-medium mt-1">SaaSance-Softwares/vyora-web</p>
                            </div>
                        </div>
                        <div class="px-4 py-1.5 bg-gray-100 border border-gray-200/60 rounded-full text-xs font-bold text-gray-600 shadow-sm">v{{ $backendCurrent }}</div>
                    </div>
                    <p class="text-sm text-gray-500 mt-5 leading-relaxed w-full">Manage Over-The-Air (OTA) updates for the Vyora platform. Keep your system secure and up-to-date with the latest open-source releases.</p>
                </div>
                
                <div class="p-6 md:p-8 flex-1 flex flex-col relative z-10">
                    @php
                        $backLatest = $backendRelease ? $backendRelease['version'] : null;
                        $isPrerelease = $backendRelease ? ($backendRelease['is_prerelease'] ?? false) : false;
                        $backUpdateAvailable = $backLatest && version_compare($backLatest, $backendCurrent, '>');
                    @endphp

                    @if($backUpdateAvailable)
                        <div class="flex-1 mb-6">
                            <h3 class="text-sm font-bold text-gray-900 mb-3">Release Notes (v{{ $backLatest }})</h3>
                            <div class="bg-gray-50/80 p-5 rounded-2xl border border-gray-100 max-h-72 overflow-y-auto custom-scrollbar text-sm text-gray-600 leading-relaxed [&>h2]:text-lg [&>h2]:font-bold [&>h2]:text-gray-900 [&>h2]:mb-2 [&>h3]:text-base [&>h3]:font-semibold [&>h3]:text-gray-800 [&>h3]:mt-4 [&>h3]:mb-2 [&>p]:mb-3 [&>ul]:list-disc [&>ul]:pl-5 [&>ul]:mb-3 [&>ul>li]:mb-1 [&>ul>li>ul]:mt-1 [&>ul>li>ul]:mb-0 [&>ul>li>ul]:list-circle [&>hr]:my-4 [&>hr]:border-gray-200">{!! $backendRelease['notes'] !!}</div>
                        </div>

                        <div class="pt-6 mt-auto border-t border-gray-100">
                            @if($isPrerelease)
                                <div class="flex items-center gap-3 mb-3 bg-red-50 border border-red-200/60 p-4 rounded-2xl">
                                    <span class="flex h-3 w-3 relative">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-3 w-3 bg-red-600"></span>
                                    </span>
                                    <h3 class="text-sm font-bold text-red-800">Pre-release Beta v{{ $backLatest }} Available</h3>
                                </div>
                                <div class="bg-red-100/50 text-red-800 text-xs p-3 rounded-xl mb-6 font-medium border border-red-200/50">
                                    <strong class="font-bold uppercase tracking-wider text-[10px]">Warning:</strong> This release is in Beta and might not be stable. It may contain bugs. If you are running a live store, please wait until a stable release.
                                </div>
                            @else
                                <div class="flex items-center gap-3 mb-6 bg-slate-50 border border-slate-200/60 p-4 rounded-2xl">
                                    <span class="flex h-3 w-3 relative">
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-slate-400 opacity-75"></span>
                                      <span class="relative inline-flex rounded-full h-3 w-3 bg-slate-600"></span>
                                    </span>
                                    <h3 class="text-sm font-bold text-slate-800">Stable Update v{{ $backLatest }} Available</h3>
                                </div>
                            @endif

                            @if($backendRelease['download_url'])
                                <form action="{{ route('admin.settings.update.process') }}" method="POST" x-data="{ isUpdating: false }" @submit.prevent="if(confirm('{{ $isPrerelease ? 'WARNING: This is a Beta release. Proceed with caution?' : 'Update Platform? This will run database migrations.' }}')) { isUpdating = true; $el.submit(); }">
                                    @csrf
                                    <input type="hidden" name="type" value="backend">
                                    <input type="hidden" name="download_url" value="{{ $backendRelease['download_url'] }}">
                                    <button type="submit" :disabled="isUpdating" class="w-full sm:w-auto text-white px-8 py-3.5 rounded-2xl font-bold transition-all active:scale-[0.98] flex items-center justify-center gap-2 disabled:opacity-75 disabled:cursor-not-allowed disabled:active:scale-100 {{ $isPrerelease ? 'bg-red-600 hover:bg-red-700 shadow-lg shadow-red-600/20 hover:shadow-red-600/30' : 'bg-slate-900 hover:bg-slate-800 shadow-lg shadow-slate-900/20 hover:shadow-slate-900/30' }}">
                                        <span x-show="!isUpdating" class="flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            {{ $isPrerelease ? 'Install Beta Pre-release' : 'Install Platform Update' }}
                                        </span>
                                        <span x-show="isUpdating" style="display: none;" class="flex items-center gap-2">
                                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Updating System... Please wait
                                        </span>
                                    </button>
                                    
                                    <div x-show="isUpdating" x-transition.opacity.duration.300ms style="display: none;" class="mt-4 p-4 bg-orange-50 border border-orange-100 rounded-xl text-orange-800 text-sm font-medium flex gap-3 items-start">
                                        <svg class="w-5 h-5 text-orange-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        <div>
                                            <strong class="font-bold">Important:</strong> Please do not refresh or navigate away from this page while the update is processing. This process may take a few moments.
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class="p-4 bg-red-50 text-red-700 rounded-2xl text-sm border border-red-100 font-medium">
                                    No valid zip asset was attached to this release on GitHub.
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="flex-1 flex flex-col items-center justify-center text-center py-10">
                            <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mb-5 border border-emerald-100">
                                <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 mb-2">Up to date</h4>
                            <p class="text-sm text-gray-500 max-w-xs">The platform is running the latest stable release.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── COLUMN 2: MAINTENANCE & SUPPORT ───────────────────────────── --}}
        <div class="xl:col-span-1 space-y-8 xl:mt-14">
            
            {{-- ── MAINTENANCE MODE CARD ───────────────────────────── --}}
            <div class="relative overflow-hidden bg-white rounded-3xl shadow-sm border {{ $maintenanceMode ? 'border-amber-300 shadow-amber-100/50' : 'border-emerald-200 shadow-emerald-100/50' }} p-6 md:p-8 flex flex-col group transition-all duration-300 hover:shadow-md">
                @if($maintenanceMode)
                    <div class="absolute top-0 right-0 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
                @else
                    <div class="absolute top-0 right-0 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none"></div>
                @endif

                <div class="relative z-10 flex-1">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl {{ $maintenanceMode ? 'bg-gradient-to-br from-amber-400 to-amber-600 text-white shadow-amber-500/30' : 'bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-emerald-500/30' }} flex items-center justify-center shadow-lg">
                                @if($maintenanceMode)
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                @else
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                @endif
                            </div>
                            <div>
                                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Maintenance Mode</h2>
                                <div class="flex items-center gap-2 mt-1.5 text-sm font-medium">
                                    System Status: 
                                    @if($maintenanceMode)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-amber-100 border border-amber-200 text-amber-800 text-xs uppercase tracking-wider animate-pulse">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span> Under Maintenance
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-emerald-100 border border-emerald-200 text-emerald-800 text-xs uppercase tracking-wider">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Live & Active
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-[15px] text-gray-600 leading-relaxed mb-6">
                        Before installing updates or performing database migrations, it is highly recommended to place your store into Maintenance Mode. This prevents customers from placing orders or encountering broken pages while system files are being securely overwritten.
                    </p>
                    
                    @if($maintenanceMode)
                        <div class="bg-amber-50/80 border border-amber-200/60 text-amber-800 text-sm p-4 rounded-2xl mb-6 shadow-sm">
                            <strong class="font-bold">Bypass Access:</strong> You can view the storefront while in maintenance mode by appending <code class="bg-amber-200/50 px-2 py-0.5 rounded text-amber-900 font-mono text-xs">?vyora-update</code> to the URL.
                        </div>
                    @endif
                </div>

                <form action="{{ route('admin.settings.update.maintenance') }}" method="POST" class="relative z-10 mt-auto">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 border border-transparent text-sm font-bold rounded-2xl text-white {{ $maintenanceMode ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20' : 'bg-amber-500 hover:bg-amber-600 shadow-amber-500/20' }} transition-all shadow-lg active:scale-[0.98]">
                        <svg class="w-5 h-5 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        {{ $maintenanceMode ? 'Disable Maintenance Mode (Go Live)' : 'Enable Maintenance Mode' }}
                    </button>
                </form>
            </div>

            {{-- ── SUPPORT VYORA CARD ───────────────────────────── --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-indigo-900 via-slate-900 to-black rounded-3xl shadow-xl border border-indigo-500/30 p-6 md:p-8 flex flex-col justify-between group">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wNSkiLz48L3N2Zz4=')] opacity-50"></div>
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/20 rounded-full blur-3xl -mr-10 -mt-10 pointer-events-none"></div>
                
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-indigo-300 mb-6 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    </div>
                    <h2 class="text-xl font-extrabold text-white mb-3 tracking-tight">Support Vyora</h2>
                    <p class="text-sm text-indigo-100/80 leading-relaxed mb-2 font-medium">
                        Vyora is an open-source ecosystem. If this platform is accelerating your business, please consider supporting its continuous development and security updates.
                    </p>
                </div>
                
                <div class="relative z-10 mt-6 pt-6 border-t border-indigo-500/20">
                    <div class="razorpay-embed-btn flex justify-center" data-url="https://pages.razorpay.com/pl_SxxDFjvFGowTTD/view" data-text="Support Vyora Project" data-color="#528FF0" data-size="large">
                      <script>
                        (function(){
                          var d=document; var x=!d.getElementById('razorpay-embed-btn-js')
                          if(x){ var s=d.createElement('script'); s.defer=!0;s.id='razorpay-embed-btn-js';
                          s.src='https://cdn.razorpay.com/static/embed_btn/bundle.js';d.body.appendChild(s);} else{var rzp=window['__rzp__'];
                          rzp && rzp.init && rzp.init()}})();
                      </script>
                    </div>
                    <div class="mt-4 flex items-center justify-center space-x-1.5 text-[10px] text-indigo-200/60 uppercase tracking-widest font-bold">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 21a11.955 11.955 0 01-9.618-7.016m19.236 0a11.955 11.955 0 00-19.236 0M12 11V7a4 4 0 00-8 0v4h8z"></path></svg>
                        <span>Secured by Razorpay</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

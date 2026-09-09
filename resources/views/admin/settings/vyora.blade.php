@extends('layouts.admin')

@section('header', 'Project Vyora')

@section('content')
    <!-- Full-Width Container (Removing max-w constraints for luxury feel) -->
    <div class="w-full pb-16">
        
        {{-- ── LUXURY HERO SECTION ─────────────────────────────────── --}}
        <div class="relative overflow-hidden bg-slate-950 border border-slate-800 shadow-2xl mb-12 mx-4 lg:mx-12 mt-6 rounded-3xl">
            <!-- Luxury Background Effects -->
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(192,159,104,0.15),transparent_50%)] pointer-events-none"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,rgba(99,102,241,0.1),transparent_50%)] pointer-events-none"></div>
            <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-amber-500/30 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-slate-700 to-transparent"></div>

            <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-8 p-8 lg:p-10">
                <!-- Text Content -->
                <div class="space-y-4 text-center lg:text-left flex-1">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black tracking-[0.2em] bg-amber-500/10 text-amber-500 border border-amber-500/20 uppercase">
                        Premium Open Source
                    </span>
                    <h1 class="text-3xl lg:text-4xl font-black tracking-tight text-white drop-shadow-lg">
                        Project Vyora
                    </h1>
                    <p class="text-sm lg:text-base text-slate-300 max-w-2xl leading-relaxed font-light mx-auto lg:mx-0">
                        A modern, next-generation e-commerce ecosystem. Crafted for lightning-fast speeds, premium brand aesthetics, and exceptional developer happiness.
                    </p>
                    
                    <!-- Version Badges -->
                    <div class="flex flex-wrap justify-center lg:justify-start gap-4 pt-4">
                        <div class="flex items-center px-4 py-2 rounded-xl bg-slate-900/80 border border-slate-800 backdrop-blur-sm shadow-inner">
                            <div class="text-left">
                                <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-0.5">Current Version</span>
                                <span class="text-sm font-black text-white tracking-wider">v{{ $currentVersion ?? '1.0.0' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center px-4 py-2 rounded-xl bg-slate-900/80 border border-slate-800 backdrop-blur-sm shadow-inner">
                            <div class="text-left">
                                <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-0.5">Latest Release</span>
                                <span class="text-sm font-black text-white tracking-wider">v{{ $latestVersion ?? '1.0.0' }}</span>
                            </div>
                        </div>

                        @if(version_compare($currentVersion ?? '1.0.0', $latestVersion ?? '1.0.0', '<'))
                            <div class="flex items-center px-4 py-2 rounded-xl bg-amber-500/10 border border-amber-500/30 backdrop-blur-sm">
                                <span class="text-sm font-bold text-amber-500 flex items-center gap-2">
                                    <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Update Available
                                </span>
                            </div>
                        @else
                            <div class="flex items-center px-4 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/30 backdrop-blur-sm">
                                <span class="text-sm font-bold text-emerald-500 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    System Up to Date
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Abstract Visual -->
                <div class="hidden lg:flex flex-shrink-0 relative group">
                    <div class="absolute -inset-4 bg-gradient-to-tr from-amber-500/20 to-indigo-500/20 rounded-full blur-2xl opacity-70 group-hover:opacity-100 transition duration-700"></div>
                    <div class="relative bg-slate-900/50 p-6 rounded-full border border-slate-800 backdrop-blur-md shadow-2xl">
                        <svg class="w-16 h-16 text-slate-300 drop-shadow-[0_0_15px_rgba(255,255,255,0.1)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>



        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 px-6 lg:px-12">
            {{-- ── COMMUNITY CARD (SUPPORT VYORA) ─────────────────── --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 flex flex-col justify-between shadow-sm hover:shadow-xl transition-all duration-300 relative group overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-indigo-500 scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-500"></div>
                <div class="space-y-5">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 mb-4 shadow-inner border border-indigo-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Support Vyora</h3>
                    <div class="space-y-3 text-slate-600 text-sm">
                        <p class="leading-relaxed">
                            Vyora is <strong>open-source and completely free to use</strong> under the MIT license.
                        </p>
                        <p class="leading-relaxed">
                            If you are using Vyora for your business and find it valuable, please consider supporting its continuous development and open-source roadmap.
                        </p>
                        <p class="text-sm italic text-slate-500 border-l-2 border-indigo-200 pl-4 mt-4">
                            Your contribution helps us improve, secure, and maintain the project for everyone.
                        </p>
                    </div>
                </div>

                <div class="mt-10 pt-8 border-t border-slate-100">
                    <div class="razorpay-embed-btn flex justify-start" data-url="https://pages.razorpay.com/pl_SxxDFjvFGowTTD/view" data-text="Support Vyora Project" data-color="#0f172a" data-size="large">
                      <script>
                        (function(){
                          var d=document; var x=!d.getElementById('razorpay-embed-btn-js')
                          if(x){ var s=d.createElement('script'); s.defer=!0;s.id='razorpay-embed-btn-js';
                          s.src='https://cdn.razorpay.com/static/embed_btn/bundle.js';d.body.appendChild(s);} else{var rzp=window['__rzp__'];
                          rzp && rzp.init && rzp.init()}})();
                      </script>
                    </div>
                </div>
            </div>

            {{-- ── CONTACT & RESOURCES ───────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 flex flex-col justify-between shadow-sm hover:shadow-xl transition-all duration-300 relative group overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-emerald-500 scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-500"></div>
                <div class="space-y-5">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 mb-4 shadow-inner border border-emerald-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Developer Helpdesk</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Need assistance, custom modules, integration help, or have security reports? Reach out directly to the official support channel.
                    </p>

                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 flex items-center justify-between group/email hover:border-slate-300 transition-colors">
                        <div>
                            <span class="block text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Direct Support</span>
                            <a href="mailto:vyora.support@saasance.com" class="text-base font-bold text-slate-800 hover:text-emerald-600 transition-colors">
                                vyora.support@saasance.com
                            </a>
                        </div>
                        <button type="button" onclick="copySupportEmail()" class="p-3 text-slate-400 hover:text-slate-900 hover:bg-white rounded-xl shadow-sm border border-transparent hover:border-slate-200 transition-all focus:outline-none" title="Copy to clipboard">
                            <svg id="copy-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            <span id="copied-toast" class="hidden text-xs font-bold text-emerald-600">COPIED</span>
                        </button>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-100">
                    <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-3">Official Resources</h4>
                    <div class="grid grid-cols-3 gap-3">
                        <a href="https://saasance.com/project/vyora/docs" target="_blank" rel="noopener noreferrer" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 hover:border-slate-900 hover:text-slate-900 transition-all group/link text-xs font-bold text-slate-600 text-center">
                            <svg class="w-5 h-5 mb-1.5 text-slate-400 group-hover/link:text-slate-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            Docs
                        </a>
                        <a href="https://github.com/SaaSance-Softwares/vyora-web/issues" target="_blank" rel="noopener noreferrer" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 hover:border-slate-900 hover:text-slate-900 transition-all group/link text-xs font-bold text-slate-600 text-center">
                            <svg class="w-5 h-5 mb-1.5 text-slate-400 group-hover/link:text-slate-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            Community
                        </a>
                        <a href="https://chat.arattai.in/groups/o43545f313436393030373631383232343939363533315f313238343033342d47437c3031303032313133343035323137383339323533363732353230" target="_blank" rel="noopener noreferrer" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 hover:border-slate-900 hover:text-slate-900 transition-all group/link text-xs font-bold text-slate-600 text-center">
                            <img src="https://www.arattai.in/sites/oweb/images/productlogos/arattai.svg" alt="Arattai" class="w-20 h-auto mb-1.5 grayscale opacity-60 group-hover/link:grayscale-0 group-hover/link:opacity-100 transition-all">
                            Arattai Group
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── PWA INSTALLATION SHORTCUT (MOVED TO BOTTOM) ─────────────────────────────────── --}}
        <div class="px-6 lg:px-12 mt-8" id="pwa-install-container">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm hover:shadow-xl transition-all duration-300 relative group overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-1 bg-amber-500 scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-500"></div>
                <div class="space-y-1 relative z-10">
                    <h3 class="text-xl font-black text-slate-900 flex items-center gap-3 tracking-tight">
                        <img src="{{ asset('favicon.ico') }}" alt="App Icon" class="w-6 h-6 rounded-md shadow-sm border border-slate-200" onerror="this.src='{{ asset('favicon.png') }}'; this.onerror=function(){ this.style.display='none'; }">
                        Install Admin Desktop App
                    </h3>
                    <p class="text-slate-600 text-sm max-w-2xl leading-relaxed pt-1">
                        Install the Vyora Admin Panel as a dedicated application on your Desktop, Tablet, or iPad for a faster, app-like experience. 
                    </p>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600 pt-2 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Please note: The Admin Panel is optimized for large screens and is not designed for mobile phones.
                    </p>
                </div>
                <button id="pwa-install-btn" class="relative z-10 px-8 py-3.5 bg-slate-900 text-white rounded-xl font-black uppercase tracking-widest text-[10px] hover:bg-slate-800 shadow-lg shadow-slate-900/20 hover:shadow-slate-900/30 transition-all whitespace-nowrap active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download Shortcut
                </button>
            </div>
        </div>
    </div>

    {{-- Custom PWA Fallback Modal --}}
    <div id="pwa-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('pwa-modal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-100">
                <div class="bg-white px-6 pt-6 pb-8">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left flex-1">
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3 mb-2" id="modal-title">
                                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Choose Installation Method
                            </h3>
                            <p class="text-sm text-slate-500 mb-6">Select how you would like to install the Vyora Admin Panel on your device.</p>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                                
                                {{-- Option 1: iOS/Safari --}}
                                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 flex flex-col">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8zm1-13h-2v5h2V7zm0 7h-2v2h2v-2z"></path></svg>
                                        </div>
                                        <h4 class="font-bold text-slate-900 leading-tight">iOS & Safari<br><span class="text-[10px] font-normal text-slate-500 uppercase tracking-wider">Mobile & Tablet</span></h4>
                                    </div>
                                    <ol class="text-sm text-slate-600 space-y-2.5 list-decimal list-inside pl-1 flex-1">
                                        <li>Tap the <strong>Share</strong> icon at the bottom/top of your screen.</li>
                                        <li>Scroll down and tap <strong>Add to Home Screen</strong>.</li>
                                        <li>Tap <strong>Add</strong> in the top right corner.</li>
                                    </ol>
                                </div>

                                {{-- Option 2: Chrome/Edge --}}
                                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 flex flex-col relative overflow-hidden">
                                    <div class="flex items-center gap-3 mb-4 relative z-10">
                                        <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                        </div>
                                        <h4 class="font-bold text-slate-900 leading-tight">Chrome & Edge<br><span class="text-[10px] font-normal text-slate-500 uppercase tracking-wider">Desktop & Android</span></h4>
                                    </div>
                                    <ol class="text-sm text-slate-600 space-y-2.5 list-decimal list-inside pl-1 flex-1 relative z-10">
                                        <li>Look for the <strong>Install icon</strong> (monitor) in your address bar.</li>
                                        <li>If hidden, open the browser menu (⋮).</li>
                                        <li>Chrome: <strong>Save and share</strong> &gt; <strong>Install page as app</strong>.</li>
                                        <li>Edge: <strong>Apps</strong> &gt; <strong>Install this site as an app</strong>.</li>
                                    </ol>
                                    <button type="button" id="native-install-btn" class="relative z-10 mt-4 w-full py-2.5 bg-emerald-600 text-white rounded-xl font-bold text-xs hover:bg-emerald-700 transition-colors shadow-sm active:scale-95 flex items-center justify-center gap-2 hidden">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        Auto-Install Now
                                    </button>
                                </div>
                                

                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-slate-100">
                    <button type="button" class="w-full inline-flex justify-center rounded-xl shadow-sm px-8 py-2.5 bg-white border border-slate-300 text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-slate-900 focus:outline-none sm:ml-3 sm:w-auto active:scale-95 transition-all" onclick="document.getElementById('pwa-modal').classList.add('hidden')">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Copy Email Logic
        function copySupportEmail() {
            navigator.clipboard.writeText('vyora.support@saasance.com').then(() => {
                const copyIcon = document.getElementById('copy-icon');
                const toast = document.getElementById('copied-toast');

                copyIcon.classList.add('hidden');
                toast.classList.remove('hidden');

                setTimeout(() => {
                    copyIcon.classList.remove('hidden');
                    toast.classList.add('hidden');
                }, 2000);
            });
        }



        // PWA Install Logic
        let deferredPrompt;
        const installContainer = document.getElementById('pwa-install-container');
        const installBtn = document.getElementById('pwa-install-btn');
        const nativeInstallBtn = document.getElementById('native-install-btn');

        // Listen for the beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            installContainer.style.display = 'block';
            if (nativeInstallBtn) {
                nativeInstallBtn.classList.remove('hidden');
            }
        });

        // Handle the main install button click
        if(installBtn) {
            installBtn.addEventListener('click', () => {
                // ALWAYS open the modal now
                document.getElementById('pwa-modal').classList.remove('hidden');
            });
        }
        
        // Handle the native install button inside the modal
        if (nativeInstallBtn) {
            nativeInstallBtn.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    if (outcome === 'accepted') {
                        installContainer.style.display = 'none';
                        document.getElementById('pwa-modal').classList.add('hidden');
                    }
                    deferredPrompt = null;
                    nativeInstallBtn.classList.add('hidden');
                }
            });
        }

        // Hide the prompt if successfully installed
        window.addEventListener('appinstalled', (evt) => {
            installContainer.style.display = 'none';
            console.log('App was installed');
        });
    </script>
@endsection
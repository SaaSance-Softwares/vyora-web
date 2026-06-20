@extends('layouts.admin')

@section('header', 'Project Vyora')

@section('content')
    <div class="max-w-4xl mx-auto pb-16">
        {{-- ── HERO SECTION ─────────────────────────────────── --}}
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-tr from-slate-950 via-slate-900 to-indigo-950 text-white shadow-xl mb-8 p-8 md:p-12">
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_30%_30%,rgba(99,102,241,0.15),transparent)] pointer-events-none">
            </div>
            <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start justify-between gap-8">
                <div class="space-y-4 text-center md:text-left">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase tracking-wider">
                        Open Source Core
                    </span>
                    <h1
                        class="text-3xl md:text-5xl font-extrabold tracking-tight bg-gradient-to-r from-white via-indigo-100 to-indigo-300 bg-clip-text text-transparent">
                        Project Vyora
                    </h1>
                    <p class="text-sm md:text-base text-slate-350 max-w-lg leading-relaxed text-slate-300">
                        A modern, next-generation open-source e-commerce ecosystem built for lightning-fast speeds, premium
                        brand aesthetics, and developer happiness.
                    </p>
                    <div class="flex flex-wrap justify-center md:justify-start gap-3 pt-2">
                        <span
                            class="inline-flex items-center text-xs text-slate-400 bg-slate-900/60 px-3 py-1.5 rounded-lg border border-slate-800">
                            <svg class="w-4 h-4 mr-2 text-indigo-450" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12" />
                            </svg>
                            MIT Licensed
                        </span>
                        <span
                            class="inline-flex items-center text-xs text-slate-400 bg-slate-900/60 px-3 py-1.5 rounded-lg border border-slate-800">
                            <svg class="w-4 h-4 mr-2 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                            </svg>
                            v1.0.0 Core
                        </span>
                    </div>
                </div>
                <div class="flex-shrink-0 bg-slate-900/40 p-4 rounded-2xl border border-slate-850 shadow-inner">
                    <svg class="w-20 h-20 text-indigo-500 animate-pulse" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- ── COMMUNITY CARD (SUPPORT VYORA) ─────────────────── --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 p-6 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-650">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Support Vyora</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Vyora is <strong>open-source and free to use</strong> under the MIT license.
                    </p>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        If you are using Vyora for your business, shop, or projects and find it valuable, please consider
                        supporting its continuous development and open-source roadmap.
                    </p>
                    <p class="text-sm text-gray-500 italic">
                        Your contribution helps us improve, secure, and maintain the project for everyone.
                    </p>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100">
                    <a href="https://pages.razorpay.com/support-vyora" target="_blank" rel="noopener noreferrer"
                        class="w-full inline-flex items-center justify-center px-6 py-3.5 border border-transparent text-sm font-semibold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-md hover:shadow-indigo-200/50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                            </path>
                        </svg>
                        <span>Support Vyora</span>
                    </a>
                    <div class="mt-4 flex items-center justify-center space-x-2 text-[11px] text-gray-400">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 21a11.955 11.955 0 01-9.618-7.016m19.236 0a11.955 11.955 0 00-19.236 0M12 11V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                        <span>Payment Secured by Razorpay</span>
                    </div>
                </div>
            </div>

            {{-- ── CONTACT & RESOURCES ───────────────────────────── --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 p-6 flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-650">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Developer Helpdesk</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Need assistance, custom modules, integration help, or have security reports? Reach out directly to
                        the official support channel.
                    </p>

                    <div class="bg-slate-50 border border-slate-200/60 rounded-xl p-4 flex items-center justify-between">
                        <div>
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Support
                                Email</span>
                            <a href="mailto:vyora.support@saasance.com"
                                class="text-sm font-semibold text-gray-800 hover:text-indigo-600 transition-colors">
                                vyora.support@saasance.com
                            </a>
                        </div>
                        <button type="button" onclick="copySupportEmail()"
                            class="p-2 text-slate-500 hover:text-black hover:bg-slate-200 rounded-lg transition-colors"
                            title="Copy to clipboard">
                            <svg id="copy-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3">
                                </path>
                            </svg>
                            <span id="copied-toast" class="hidden text-xs text-indigo-650 font-medium">Copied!</span>
                        </button>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-100 space-y-3">
                    <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Community Resources</h4>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <a href="https://github.com" target="_blank" rel="noopener noreferrer"
                            class="flex items-center text-gray-600 hover:text-black">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                            Documentation
                        </a>
                        <a href="https://github.com" target="_blank" rel="noopener noreferrer"
                            class="flex items-center text-gray-650 hover:text-black text-gray-600">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                </path>
                            </svg>
                            Community Chat
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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
    </script>
@endsection
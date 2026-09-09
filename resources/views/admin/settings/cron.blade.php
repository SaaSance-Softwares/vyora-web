@extends('layouts.admin')
@section('header', 'Cron Jobs Setup')

@section('content')
<div class="max-w-4xl space-y-6">

    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-white border border-gray-200 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
            <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900">Cron Jobs Setup</h1>
            <p class="text-sm text-gray-500 font-medium mt-1">Configure scheduled background tasks like Abandoned Cart recovery, order syncing, and more.</p>
        </div>
    </div>

    {{-- Important Notice --}}
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-4">
        <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        <div>
            <h3 class="text-sm font-bold text-amber-800">Important: Choose the right method for your server</h3>
            <p class="text-sm text-amber-700 mt-1">The standard Laravel <code class="bg-amber-100 px-1 rounded text-xs">schedule:run</code> command requires <code class="bg-amber-100 px-1 rounded text-xs">proc_open</code>, which is <strong>disabled by default on most shared hosting servers</strong> (Hostinger, GoDaddy, etc.).</p>
            <p class="text-sm text-amber-700 mt-2">
                <strong>Hostinger users:</strong> You can enable <code class="bg-amber-100 px-1 rounded text-xs">proc_open</code> by going to
                <strong>Websites → Dashboard → PHP Configuration → PHP Options</strong>
                and removing <code class="bg-amber-100 px-1 rounded text-xs">proc_open</code> from the <code class="bg-amber-100 px-1 rounded text-xs">disable_functions</code> list. Once enabled, use Method 1 (single cron job). If you cannot enable it, use Method 2.
            </p>
            <p class="text-sm text-amber-700 mt-2">
                <strong>Also note:</strong> Always use <code class="bg-amber-100 px-1 rounded text-xs">/usr/bin/php</code> in your cron command — not <code class="bg-amber-100 px-1 rounded text-xs">/usr/bin/php82</code> or any versioned path, as these may not exist on your server.
            </p>
        </div>
    </div>

    {{-- Required Cron Jobs --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80">
            <h2 class="text-base font-bold text-gray-900">Required Cron Jobs</h2>
            <p class="text-sm text-gray-500 mt-0.5">Vyora requires these 3 background tasks to be running on your server.</p>
        </div>
        <div class="divide-y divide-gray-100">
            <div class="px-6 py-4 flex items-start gap-4">
                <div class="w-8 h-8 rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-4 mb-1">
                        <div class="font-semibold text-gray-900 text-sm">Abandoned Cart Recovery</div>
                        <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full whitespace-nowrap">Every 15 minutes</span>
                    </div>
                    <p class="text-xs text-gray-500 mb-2">Automatically sends WhatsApp and email reminders to users who left items in their cart for over 2 hours.</p>
                    <code class="block text-xs bg-gray-900 text-green-400 rounded-lg px-3 py-2 font-mono break-all select-all">{{ $projectPath }}/artisan cart:abandoned-emails</code>
                </div>
            </div>

            <div class="px-6 py-4 flex items-start gap-4">
                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" /></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-4 mb-1">
                        <div class="font-semibold text-gray-900 text-sm">Qikink — Push New Orders</div>
                        <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full whitespace-nowrap">Every minute</span>
                    </div>
                    <p class="text-xs text-gray-500 mb-2">Pushes new paid orders to Qikink for fulfillment. Only needed if you use Qikink for print-on-demand.</p>
                    <code class="block text-xs bg-gray-900 text-green-400 rounded-lg px-3 py-2 font-mono break-all select-all">{{ $projectPath }}/artisan qikink:push-orders</code>
                </div>
            </div>

            <div class="px-6 py-4 flex items-start gap-4">
                <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-4 mb-1">
                        <div class="font-semibold text-gray-900 text-sm">Qikink — Sync Order Status</div>
                        <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full whitespace-nowrap">Every hour</span>
                    </div>
                    <p class="text-xs text-gray-500 mb-2">Pulls updated tracking and fulfillment status from Qikink back into your orders. Only needed if you use Qikink.</p>
                    <code class="block text-xs bg-gray-900 text-green-400 rounded-lg px-3 py-2 font-mono break-all select-all">{{ $projectPath }}/artisan qikink:sync-orders</code>
                </div>
            </div>
        </div>
    </div>

    {{-- Method 1: VPS --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80 flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-900 text-white text-xs font-bold shrink-0">1</span>
            <div>
                <h2 class="text-base font-bold text-gray-900">Method 1 — VPS / Dedicated Server <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full ml-1">Recommended</span></h2>
                <p class="text-xs text-gray-500 mt-0.5">Use this if you are on DigitalOcean, AWS, Linode, or any VPS where you have full SSH access.</p>
            </div>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-sm text-gray-600">Add a <strong>single</strong> cron entry. Laravel's scheduler will handle running all three commands at the correct intervals automatically.</p>
            <div>
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Add to crontab (run <code class="bg-gray-100 px-1 rounded">crontab -e</code>)</div>
                <code class="block text-xs bg-gray-900 text-green-400 rounded-lg px-4 py-3 font-mono break-all select-all">* * * * * cd {{ $projectPath }} && php artisan schedule:run >> /dev/null 2>&1</code>
            </div>
        </div>
    </div>

    {{-- Method 2: Shared Hosting --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/80 flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-900 text-white text-xs font-bold shrink-0">2</span>
            <div>
                <h2 class="text-base font-bold text-gray-900">Method 2 — Shared Hosting <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full ml-1">Hostinger / cPanel / GoDaddy</span></h2>
                <p class="text-xs text-gray-500 mt-0.5">Use this if <code class="bg-gray-100 px-1 rounded">schedule:run</code> fails. Add each command as a separate cron job.</p>
            </div>
        </div>
        <div class="p-6 space-y-5">
            <p class="text-sm text-gray-600">Go to your hosting panel → <strong>Cron Jobs</strong> and add the following <strong>3 separate entries</strong>. Replace <code class="bg-gray-100 px-1 rounded text-xs">/usr/bin/php</code> with the path your host uses (check with your host if unsure).</p>

            <ol class="space-y-4">
                <li class="flex items-start gap-3">
                    <span class="text-xs font-bold bg-gray-100 text-gray-600 px-2 py-1 rounded mt-0.5 shrink-0">Every 15 min</span>
                    <code class="block text-xs bg-gray-900 text-green-400 rounded-lg px-3 py-2 font-mono break-all select-all flex-1">/usr/bin/php {{ $projectPath }}/artisan cart:abandoned-emails</code>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-xs font-bold bg-gray-100 text-gray-600 px-2 py-1 rounded mt-0.5 shrink-0">Every minute</span>
                    <code class="block text-xs bg-gray-900 text-green-400 rounded-lg px-3 py-2 font-mono break-all select-all flex-1">/usr/bin/php {{ $projectPath }}/artisan qikink:push-orders</code>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-xs font-bold bg-gray-100 text-gray-600 px-2 py-1 rounded mt-0.5 shrink-0">Every hour</span>
                    <code class="block text-xs bg-gray-900 text-green-400 rounded-lg px-3 py-2 font-mono break-all select-all flex-1">/usr/bin/php {{ $projectPath }}/artisan qikink:sync-orders</code>
                </li>
            </ol>
        </div>
    </div>

</div>
@endsection

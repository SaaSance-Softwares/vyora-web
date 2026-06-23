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
            <p class="text-sm text-gray-500 font-medium mt-1">Configure scheduled tasks for background processes like Abandoned Cart Emails.</p>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">How to Setup the CRON Job</h2>
        <p class="text-sm text-gray-600 mb-6">
            To ensure background tasks run automatically, you must add a single Cron entry to your server. 
            This entry will run every minute and allow Laravel to execute scheduled tasks (like sending abandoned cart emails) at the correct times.
        </p>

        <div class="space-y-4">
            <h3 class="text-sm font-bold text-gray-900">1. For Linux Server (cPanel / SSH)</h3>
            <p class="text-sm text-gray-500">Copy the following command and add it to your server's Crontab to run every minute (<code class="text-xs bg-gray-100 px-1 rounded">* * * * *</code>):</p>
            
            <div class="bg-gray-900 rounded-xl p-4 flex items-center justify-between group">
                <code class="text-sm text-green-400 font-mono break-all select-all">
                    * * * * * cd {{ $projectPath }} && php artisan schedule:run >> /dev/null 2>&1
                </code>
            </div>

            <div class="mt-6">
                <h3 class="text-sm font-bold text-gray-900">2. Using cPanel Interface</h3>
                <ol class="list-decimal list-inside text-sm text-gray-500 space-y-2 mt-2">
                    <li>Log into your cPanel account.</li>
                    <li>Scroll down to the <strong>Advanced</strong> section and click on <strong>Cron Jobs</strong>.</li>
                    <li>Under <strong>Common Settings</strong>, select <strong>Once Per Minute (* * * * *)</strong>.</li>
                    <li>In the <strong>Command</strong> field, paste the command shown above (starting from <code>cd ...</code>).</li>
                    <li>Click <strong>Add New Cron Job</strong>.</li>
                </ol>
            </div>
        </div>
    </div>

</div>
@endsection

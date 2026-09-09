@extends('layouts.admin')

@section('title', 'System Logs')
@section('header', 'System Logs')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            
            <div class="mb-6 flex justify-between items-end">
                <form method="GET" action="{{ route('admin.settings.logs') }}" class="flex space-x-4">
                    <div>
                        <label for="file" class="block text-sm font-medium text-gray-700">Log File</label>
                        <select id="file" name="file" onchange="this.form.submit()" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md border">
                            @foreach($files as $file)
                                <option value="{{ $file }}" {{ $selectedFile == $file ? 'selected' : '' }}>{{ $file }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="level" class="block text-sm font-medium text-gray-700">Log Level</label>
                        <select id="level" name="level" onchange="this.form.submit()" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md border">
                            <option value="ALL">All Levels</option>
                            @foreach($levels as $lvl)
                                <option value="{{ $lvl }}" {{ request('level') == $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>

                @if($selectedFile)
                <form method="POST" action="{{ route('admin.settings.logs.clear') }}" onsubmit="return confirm('Are you sure you want to clear the {{ $selectedFile }} log file?');">
                    @csrf
                    <input type="hidden" name="file" value="{{ $selectedFile }}">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Clear {{ $selectedFile }}
                    </button>
                </form>
                @endif
            </div>

            <div class="flex flex-col">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Environment</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($paginatedLogs as $log)
                                        <tr x-data="{ open: false }" class="hover:bg-gray-50 cursor-pointer" @click="open = !open">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 align-top">
                                                {{ $log['date'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap align-top">
                                                @php
                                                    $color = match($log['level']) {
                                                        'ERROR', 'CRITICAL', 'EMERGENCY' => 'bg-red-100 text-red-800',
                                                        'WARNING' => 'bg-yellow-100 text-yellow-800',
                                                        'INFO', 'NOTICE' => 'bg-blue-100 text-blue-800',
                                                        'DEBUG' => 'bg-gray-100 text-gray-800',
                                                        default => 'bg-gray-100 text-gray-800',
                                                    };
                                                @endphp
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                                    {{ $log['level'] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 align-top">
                                                {{ $log['env'] }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900 w-full">
                                                <div class="font-mono text-xs break-all line-clamp-2" x-show="!open">
                                                    {{ $log['summary'] }}
                                                </div>
                                                <div x-show="open" x-cloak class="mt-2">
                                                    <div class="font-mono text-xs break-all font-bold text-red-600 mb-2">
                                                        {{ $log['summary'] }}
                                                    </div>
                                                    @if(!empty($log['stack']))
                                                        <pre class="bg-gray-800 text-gray-100 p-4 rounded text-xs overflow-x-auto whitespace-pre-wrap">{{ $log['stack'] }}</pre>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                No logs found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                {{ $paginatedLogs->links() }}
            </div>

        </div>
    </div>
</div>
@endsection

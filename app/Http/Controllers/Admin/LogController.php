<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logPath = storage_path('logs');
        $files = File::glob($logPath . '/*.log');
        $files = array_map(function($file) {
            return basename($file);
        }, $files);
        rsort($files); // newest first usually for daily logs

        $selectedFile = $request->input('file', 'laravel.log');
        if (!in_array($selectedFile, $files)) {
            $selectedFile = count($files) > 0 ? $files[0] : null;
        }

        $logs = [];
        $levels = [];

        if ($selectedFile) {
            $filePath = $logPath . '/' . $selectedFile;
            
            // To prevent memory issues with massive logs, we read the last part if it's too big
            if (File::size($filePath) > 10 * 1024 * 1024) {
                // Larger than 10MB, read the last 5MB using native PHP
                $fp = fopen($filePath, 'r');
                fseek($fp, -5 * 1024 * 1024, SEEK_END);
                $content = fread($fp, 5 * 1024 * 1024);
                fclose($fp);
            } else {
                $content = File::get($filePath);
            }

            if ($content) {
                $pattern = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (.*?)\.([A-Z]+): (.*?)(?=\n\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|\z)/ms';
                preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $level = $match[3];
                    if (!in_array($level, $levels)) {
                        $levels[] = $level;
                    }

                    if ($request->filled('level') && $request->level !== 'ALL' && $request->level !== $level) {
                        continue;
                    }

                    $messageParts = explode("\n", trim($match[4]), 2);
                    $summary = $messageParts[0];
                    $stack = isset($messageParts[1]) ? $messageParts[1] : '';

                    $logs[] = [
                        'date' => $match[1],
                        'env' => $match[2],
                        'level' => $level,
                        'summary' => $summary,
                        'stack' => $stack,
                    ];
                }
            }
        }

        // Reverse to show newest first
        $logs = array_reverse($logs);

        // Paginate
        $page = $request->input('page', 1);
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $paginatedLogs = new LengthAwarePaginator(
            array_slice($logs, $offset, $perPage),
            count($logs),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.settings.logs', compact('paginatedLogs', 'files', 'selectedFile', 'levels'));
    }

    public function clear(Request $request)
    {
        $logPath = storage_path('logs');
        $selectedFile = $request->input('file', 'laravel.log');
        $filePath = $logPath . '/' . basename($selectedFile);

        if (File::exists($filePath)) {
            File::put($filePath, '');
            return redirect()->back()->with('success', "Log file {$selectedFile} has been cleared.");
        }

        return redirect()->back()->with('error', 'Log file not found.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RedirectUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RedirectUrlController extends Controller
{
    public function index()
    {
        $redirects = RedirectUrl::orderBy('id', 'desc')->paginate(20);
        return view('admin.redirects.index', compact('redirects'));
    }

    public function create()
    {
        return view('admin.redirects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'old_url' => 'required|string|unique:redirect_urls,old_url',
            'new_url' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        RedirectUrl::create($request->all());

        return redirect()->route('admin.settings.redirects.index')->with('success', 'Redirect created successfully.');
    }

    public function edit(RedirectUrl $redirect)
    {
        return view('admin.redirects.edit', compact('redirect'));
    }

    public function update(Request $request, RedirectUrl $redirect)
    {
        $request->validate([
            'old_url' => 'required|string|unique:redirect_urls,old_url,' . $redirect->id,
            'new_url' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $redirect->update($request->all());

        return redirect()->route('admin.settings.redirects.index')->with('success', 'Redirect updated successfully.');
    }

    public function destroy(RedirectUrl $redirect)
    {
        $redirect->delete();
        return redirect()->route('admin.settings.redirects.index')->with('success', 'Redirect deleted successfully.');
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('csv_file');
        $csvData = file_get_contents($file);
        $rows = array_map('str_getcsv', explode("\n", $csvData));
        $header = array_shift($rows);

        // Find the index of the old URL column. It might be named "URL", "old_url", "Old Path" etc.
        $oldUrlIndex = -1;
        $newUrlIndex = -1;

        if (is_array($header)) {
            foreach ($header as $index => $colName) {
                $colName = strtolower(trim($colName));
                if (in_array($colName, ['url', 'old url', 'old_url', 'old path', 'top queries'])) {
                    $oldUrlIndex = $index;
                }
                if (in_array($colName, ['new url', 'new_url', 'new path', 'redirect', 'target'])) {
                    $newUrlIndex = $index;
                }
            }
        }

        if ($oldUrlIndex === -1) {
            // Assume first column is old_url
            $oldUrlIndex = 0;
        }

        $imported = 0;
        $updated = 0;

        foreach ($rows as $row) {
            if (empty($row) || !isset($row[$oldUrlIndex]) || empty(trim($row[$oldUrlIndex]))) continue;

            $oldUrl = trim($row[$oldUrlIndex]);
            
            // Clean up the URL (remove domain if it's there)
            if (str_starts_with($oldUrl, 'http')) {
                $oldUrl = parse_url($oldUrl, PHP_URL_PATH) . (parse_url($oldUrl, PHP_URL_QUERY) ? '?' . parse_url($oldUrl, PHP_URL_QUERY) : '');
            }

            // Remove leading slash for consistency unless it's just '/'
            if ($oldUrl !== '/' && str_starts_with($oldUrl, '/')) {
                $oldUrl = substr($oldUrl, 1);
            }

            $newUrl = ($newUrlIndex !== -1 && isset($row[$newUrlIndex])) ? trim($row[$newUrlIndex]) : null;

            $redirect = RedirectUrl::where('old_url', $oldUrl)->first();
            if ($redirect) {
                // If it exists, update it ONLY if the new url is provided
                if ($newUrl) {
                    $redirect->update(['new_url' => $newUrl]);
                    $updated++;
                }
            } else {
                RedirectUrl::create([
                    'old_url' => $oldUrl,
                    'new_url' => $newUrl,
                    'is_active' => true
                ]);
                $imported++;
            }
        }

        return redirect()->route('admin.settings.redirects.index')->with('success', "Import complete! Imported $imported new redirects. Updated $updated existing redirects.");
    }
}

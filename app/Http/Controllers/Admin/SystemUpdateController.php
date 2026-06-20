<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SystemUpdateController extends Controller
{
    private $backendRepo = 'WitReach/vyora-api';
    private $githubToken = null;

    private function getLatestRelease($repo)
    {
        try {
            $headers = ['Accept' => 'application/vnd.github.v3+json'];
            if ($this->githubToken) {
                $headers['Authorization'] = 'token ' . $this->githubToken;
            }

            $response = Http::withHeaders($headers)
                ->get("https://api.github.com/repos/{$repo}/releases/latest");

            if ($response->successful()) {
                $release = $response->json();
                $version = str_replace('v', '', $release['tag_name'] ?? '1.0.0');
                $notes = $release['body'] ?? 'No release notes provided.';
                
                $downloadUrl = null;
                if (!empty($release['assets'])) {
                    $downloadUrl = $release['assets'][0]['browser_download_url'];
                } else {
                    $downloadUrl = $release['zipball_url'] ?? null;
                }

                return [
                    'version' => $version,
                    'notes' => $notes,
                    'download_url' => $downloadUrl
                ];
            }
        } catch (\Exception $e) {
            Log::error("GitHub Update Check Failed for {$repo}: " . $e->getMessage());
        }
        
        return null;
    }

    public function index()
    {
        $backendCurrent = config('app.version', '1.0.0');

        $backendRelease = $this->getLatestRelease($this->backendRepo);
        $maintenanceMode = file_exists(storage_path('framework/down'));

        return view('admin.system.update', compact(
            'backendCurrent', 
            'backendRelease',
            'maintenanceMode'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'download_url' => 'required|url',
            'type' => 'required|in:backend'
        ]);

        try {
            $type = $request->type;
            $downloadUrl = $request->download_url;
            $tempZipPath = storage_path('app/temp_update_' . $type . '.zip');

            // 1. Download the Zip File
            $headers = [];
            if ($this->githubToken) {
                $headers['Authorization'] = 'token ' . $this->githubToken;
                $headers['Accept'] = 'application/octet-stream';
            }

            $response = Http::withHeaders($headers)
                ->withOptions(['sink' => $tempZipPath])
                ->get($downloadUrl);

            if (!$response->successful()) {
                throw new \Exception('Failed to download the update package from GitHub.');
            }

            // 2. Extract the Zip File
            $zip = new \ZipArchive;
            if ($zip->open($tempZipPath) === TRUE) {
                
                $tempExtractDir = storage_path('app/temp_extract_' . time());
                $zip->extractTo($tempExtractDir);
                $zip->close();

                // Find the actual contents (Handle GitHub's wrapper folder)
                $directories = File::directories($tempExtractDir);
                $sourceDir = $tempExtractDir;
                
                if (count($directories) === 1 && count(File::files($tempExtractDir)) === 0) {
                    // It's a GitHub zipball with a wrapper folder
                    $sourceDir = $directories[0];
                }

                // Move contents of $sourceDir to base_path()
                File::copyDirectory($sourceDir, base_path());

                // Cleanup
                File::deleteDirectory($tempExtractDir);
                    
                // Run database migrations and clear cache via Artisan
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                \Illuminate\Support\Facades\Artisan::call('optimize:clear');
                $message = 'System updated successfully. Cache cleared and migrations run!';

                File::delete($tempZipPath);
                return redirect()->back()->with('success', $message);
            } else {
                throw new \Exception('Could not extract the update package.');
            }

        } catch (\Exception $e) {
            Log::error('Update Application Failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function toggleMaintenance(Request $request)
    {
        try {
            if (file_exists(storage_path('framework/down'))) {
                \Illuminate\Support\Facades\Artisan::call('up');
                return redirect()->back()->with('success', 'Maintenance mode disabled. The application is now live.');
            } else {
                \Illuminate\Support\Facades\Artisan::call('down', [
                    '--secret' => 'vyora-update',
                ]);
                return redirect()->back()->with('success', 'Maintenance mode enabled. Only users with the bypass token can access the site.');
            }
        } catch (\Exception $e) {
            Log::error('Maintenance Toggle Failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to toggle maintenance mode: ' . $e->getMessage());
        }
    }
}

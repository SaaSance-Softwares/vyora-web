<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class InstallerController extends Controller
{
    public function welcome()
    {
        // Check PHP requirements
        $requirements = [
            'PHP Version >= 8.2' => version_compare(phpversion(), '8.2.0', '>='),
            'BCMath' => extension_loaded('bcmath'),
            'Ctype' => extension_loaded('ctype'),
            'JSON' => extension_loaded('json'),
            'Mbstring' => extension_loaded('mbstring'),
            'OpenSSL' => extension_loaded('openssl'),
            'PDO' => extension_loaded('pdo'),
            'Tokenizer' => extension_loaded('tokenizer'),
            'XML' => extension_loaded('xml'),
            'Symlink Function' => function_exists('symlink'),
        ];

        $allMet = ! in_array(false, $requirements);

        return view('install.welcome', compact('requirements', 'allMet'));
    }

    public function database()
    {
        return view('install.database');
    }

    public function processDatabase(Request $request)
    {
        $request->validate([
            'db_host' => 'required',
            'db_port' => 'required',
            'db_database' => 'required',
            'db_username' => 'required',
        ]);

        // Try connection
        try {
            $pdo = new \PDO(
                "mysql:host={$request->db_host};port={$request->db_port};dbname={$request->db_database}",
                $request->db_username,
                $request->db_password
            );
        } catch (\Exception $e) {
            return back()->withErrors(['connection' => 'Could not connect to database: '.$e->getMessage()])->withInput();
        }

        // Dynamically update config for this request so migrations use the new DB
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $request->db_host,
            'database.connections.mysql.port' => $request->db_port,
            'database.connections.mysql.database' => $request->db_database,
            'database.connections.mysql.username' => $request->db_username,
            'database.connections.mysql.password' => $request->db_password,
        ]);
        DB::purge('mysql');

        // Run Migrations FIRST before modifying .env to avoid php artisan serve restarting and killing the process
        Artisan::call('migrate:fresh --force');

        // Write to .env LAST
        $this->updateEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $request->db_host,
            'DB_PORT' => $request->db_port,
            'DB_DATABASE' => $request->db_database,
            'DB_USERNAME' => $request->db_username,
            'DB_PASSWORD' => $request->db_password,
        ]);

        return redirect()->route('install.admin');
    }

    public function admin()
    {
        return view('install.admin');
    }

    public function processAdmin(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:50',
            'admin_path' => 'required|string|min:3|max:20|alpha_dash',
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
            'role' => 'administrator',
        ]);

        // Write Admin path and App Name to env
        $this->updateEnv([
            'APP_NAME' => $request->store_name,
            'ADMIN_PATH' => $request->admin_path,
        ]);
        Artisan::call('config:clear');

        // Create storage symlink
        try {
            $storageLink = public_path('storage');
            if (is_link($storageLink)) {
                @unlink($storageLink);
            } elseif (file_exists($storageLink)) {
                if (is_dir($storageLink)) {
                    @rmdir($storageLink);
                } else {
                    @unlink($storageLink);
                }
            }
            Artisan::call('storage:link');
        } catch (\Exception $e) {
            // Ignore if it fails
        }

        // Mark installed
        File::put(storage_path('installed'), 'installed');

        return redirect('/'.$request->admin_path.'/login')->with('success', 'Installation Complete! Please login.');
    }

    private function updateEnv($data)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $env = file_get_contents($path);
            foreach ($data as $key => $value) {
                // Replace either uncommented or commented key (e.g. # DB_HOST=)
                if (preg_match("/^#?\s*{$key}=/m", $env)) {
                    $env = preg_replace("/^#?\s*{$key}=.*/m", "{$key}=\"{$value}\"", $env);
                } else {
                    // Append if not exists
                    $env .= "\n{$key}=\"{$value}\"";
                }
            }
            file_put_contents($path, $env);
        }
    }
}

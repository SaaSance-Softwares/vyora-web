<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If 'storage/installed' file exists, we are installed.
        // If not, redirect to /install/welcome

        $isInstalled = File::exists(storage_path('installed'));
        $isInstallRoute = $request->is('install*');

        if (! $isInstalled && ! $isInstallRoute) {
            return redirect()->route('install.welcome');
        }

        if ($isInstalled && $isInstallRoute) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}

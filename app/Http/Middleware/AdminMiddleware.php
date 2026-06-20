<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Define which roles are considered "Admin"
        $adminRoles = ['administrator', 'editor', 'manager', 'customer_service'];

        if (!auth()->check() || !in_array(auth()->user()->role, $adminRoles)) {
            // If they are logged in but not an admin, log them out
            if (auth()->check()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->withErrors([
                'email' => 'You do not have administrative privileges to access this area.',
            ]);
        }

        return $next($request);
    }
}

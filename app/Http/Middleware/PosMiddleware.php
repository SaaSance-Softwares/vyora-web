<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            // Unauthenticated — send to ADMIN login, not customer login
            return redirect()->route('admin.login')->withErrors([
                'email' => 'Please log in with your admin or POS account to access the POS system.',
            ]);
        }

        $user = Auth::user();
        $posRoles = ['administrator', 'pos', 'pos_cashier'];

        if (in_array($user->role, $posRoles)) {
            return $next($request);
        }

        // Logged in but wrong role (e.g. customer) — log them out and redirect
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->withErrors([
            'email' => 'Access Denied: Your account does not have permission to access the POS system. Please log in with an admin or POS account.',
        ]);
    }
}

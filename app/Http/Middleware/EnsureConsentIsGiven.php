<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureConsentIsGiven
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && !auth()->user()->has_consented_to_terms) {
            $exemptRoutes = [
                'frontend.consent',
                'frontend.consent.submit',
                'logout',
                'admin.logout',
            ];

            if (!$request->is('api/*') && !$request->routeIs($exemptRoutes) && !$request->is('logout')) {
                return redirect()->route('frontend.consent');
            }
        }

        return $next($request);
    }
}

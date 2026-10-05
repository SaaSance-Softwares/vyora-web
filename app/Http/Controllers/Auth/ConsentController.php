<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConsentController extends Controller
{
    public function show(Request $request)
    {
        if (!auth()->check() || auth()->user()->has_consented_to_terms) {
            return redirect('/');
        }
        
        $userRole = auth()->user()->role;
        if ($userRole && $userRole !== 'customer' && $userRole !== 'user') {
            return redirect('/');
        }
        
        return Inertia::render('Auth/Consent');
    }

    public function store(Request $request)
    {
        if (!auth()->check()) {
            return redirect('/');
        }

        $request->validate([
            'terms' => 'required|accepted',
            'marketing' => 'nullable|boolean',
        ], [
            'terms.accepted' => 'You must agree to the Terms of Service and Privacy Policy to continue.',
        ]);

        $user = auth()->user();
        $user->has_consented_to_terms = true;
        $user->has_consented_to_marketing = $request->boolean('marketing');
        $user->consent_timestamp = now();
        $user->consent_ip_address = $request->ip();
        $user->save();

        return redirect()->intended('/');
    }
}

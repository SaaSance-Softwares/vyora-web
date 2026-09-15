<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->strictValidate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:128'],
        ]);

        if (Auth::attempt($credentials, true)) {
            $user = Auth::user();
            $adminRoles = ['administrator', 'editor', 'manager', 'customer_service', 'pos', 'pos_cashier'];

            if (! in_array($user->role, $adminRoles)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                Log::channel('single')->warning('Admin Login Access Denied (Not Admin): ' . $user->email . ' from IP: ' . $request->ip());
                return back()->withErrors([
                    'email' => 'Access Denied: You do not have administrative privileges.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            Log::channel('single')->info('Admin Login Success: ' . $user->email . ' from IP: ' . $request->ip());

            // POS-only roles go to the POS dashboard, not the admin panel
            if ($user->role === 'pos' || $user->role === 'pos_cashier') {
                return redirect()->intended(route('pos.dashboard'));
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        Log::channel('single')->warning('Admin Login Failed: ' . $request->email . ' from IP: ' . $request->ip());
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

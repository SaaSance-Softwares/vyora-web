<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Mail;

class SocialLoginController extends Controller
{
    private $supportedProviders = ['google', 'facebook', 'apple', 'github', 'snapchat'];

    public function redirect($provider)
    {
        if (!in_array($provider, $this->supportedProviders)) {
            abort(404);
        }

        $this->configureProvider($provider);

        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback($provider)
    {
        if (!in_array($provider, $this->supportedProviders)) {
            abort(404);
        }

        $this->configureProvider($provider);

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Social Login Error ({$provider}): " . $e->getMessage());
            return redirect('/login')->withErrors(['email' => 'Social Login Failed. Please try again or use another method.']);
        }

        $user = User::where('email', $socialUser->getEmail())->first();

        // If user doesn't exist, OR user exists but hasn't consented yet
        if (!$user || !$user->has_consented_to_terms) {
            $pending = [
                'id' => $user ? $user->id : null,
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'email' => $socialUser->getEmail(),
                'provider' => $provider,
            ];
            
            $payload = Crypt::encryptString(json_encode($pending));
            return redirect()->route('social.consent.show', ['payload' => $payload]);
        }

        Auth::login($user);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->view('auth.social-callback', [
            'token' => $token,
            'user' => $user
        ]);
    }

    public function showConsent(\Illuminate\Http\Request $request)
    {
        if (!$request->has('payload')) {
            return redirect('/login');
        }
        
        try {
            $pending = json_decode(Crypt::decryptString($request->payload), true);
        } catch (\Exception $e) {
            return redirect('/login');
        }

        return view('auth.social-consent', ['payload' => $request->payload]);
    }

    public function processConsent(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'payload' => 'required|string',
            'has_consented_to_terms' => 'required|accepted',
            'has_consented_to_marketing' => 'nullable|boolean',
        ]);

        try {
            $pending = json_decode(Crypt::decryptString($request->payload), true);
        } catch (\Exception $e) {
            return redirect('/login');
        }
        
        if ($pending['id']) {
            $user = User::find($pending['id']);
            $user->update([
                'has_consented_to_terms' => 1,
                'has_consented_to_marketing' => $request->has('has_consented_to_marketing') ? 1 : 0,
                'consent_timestamp' => now(),
                'consent_ip_address' => $request->ip(),
            ]);
        } else {
            $user = User::create([
                'name' => $pending['name'],
                'email' => $pending['email'],
                'password' => Hash::make(Str::random(24)),
                'email_verified_at' => now(),
                'has_consented_to_terms' => 1,
                'has_consented_to_marketing' => $request->has('has_consented_to_marketing') ? 1 : 0,
                'consent_timestamp' => now(),
                'consent_ip_address' => $request->ip(),
                'registration_ip' => $request->ip(),
            ]);

            try {
                Mail::to($user->email)->send(new \App\Mail\WelcomeEmail($user));
            } catch (\Exception $e) {
                \Log::error('Failed to send Welcome Email for Social Login: '.$e->getMessage());
            }
        }

        Auth::login($user);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->view('auth.social-callback', [
            'token' => $token,
            'user' => $user
        ]);
    }

    private function configureProvider($provider)
    {
        $group = "integration.{$provider}-login";
        
        $enabled = ThemeSetting::where('group', $group)->where('key', 'enabled')->value('value');
        if ($enabled !== '1') {
            abort(403, ucfirst($provider) . ' login is not enabled on this store.');
        }

        $clientId = ThemeSetting::where('group', $group)->where('key', 'client_id')->value('value');
        $clientSecret = ThemeSetting::where('group', $group)->where('key', 'client_secret')->value('value');

        if (!$clientId || !$clientSecret) {
            abort(500, ucfirst($provider) . ' credentials are not configured properly.');
        }

        $config = [
            'client_id' => trim(Crypt::decryptString($clientId)),
            'client_secret' => trim(Crypt::decryptString($clientSecret)),
            'redirect' => url("/auth/{$provider}/callback"),
        ];

        if ($provider === 'apple') {
            $teamId = ThemeSetting::where('group', $group)->where('key', 'team_id')->value('value');
            $keyId = ThemeSetting::where('group', $group)->where('key', 'key_id')->value('value');
            $privateKey = ThemeSetting::where('group', $group)->where('key', 'private_key')->value('value');

            $config['team_id'] = $teamId ? Crypt::decryptString($teamId) : '';
            $config['key_id'] = $keyId ? Crypt::decryptString($keyId) : '';
            $config['private_key'] = $privateKey ? Crypt::decryptString($privateKey) : '';
        }

        config(["services.{$provider}" => $config]);
    }
}

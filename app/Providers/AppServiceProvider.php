<?php

namespace App\Providers;

use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.admin', function ($view) {
            $latestVersion = Cache::remember('vyora_latest_version', 43200, function () {
                try {
                    $response = Http::timeout(5)->withHeaders(['Accept' => 'application/vnd.github.v3+json'])
                        ->get('https://api.github.com/repos/SaaSance-Softwares/vyora-web/releases/latest');
                    if ($response->successful()) {
                        $release = $response->json();

                        return str_replace('v', '', $release['tag_name'] ?? '1.0.0');
                    }
                } catch (\Exception $e) {
                }

                return config('app.version', '1.0.0');
            });

            $currentVersion = config('app.version', '1.0.0');
            $view->with('globalUpdateAvailable', version_compare($latestVersion, $currentVersion, '>'));
        });

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('apple', \SocialiteProviders\Apple\Provider::class);
            $event->extendSocialite('snapchat', \SocialiteProviders\Snapchat\Provider::class);
        });

        try {
            if (Schema::hasTable('theme_settings')) {
                $timezone = ThemeSetting::where('group', 'general')->where('key', 'time_zone')->value('value');
                if ($timezone) {
                    config(['app.timezone' => $timezone]);
                    date_default_timezone_set($timezone);
                }

                $smtpEnabled = ThemeSetting::where('group', 'integration.smtp')->where('key', 'enabled')->value('value');
                if ($smtpEnabled === '1') {
                    $smtpSettings = ThemeSetting::where('group', 'integration.smtp')->pluck('value', 'key');
                    try {
                        config([
                            'mail.default' => 'smtp',
                            'mail.mailers.smtp.host' => Crypt::decryptString($smtpSettings['smtp_host'] ?? ''),
                            'mail.mailers.smtp.port' => Crypt::decryptString($smtpSettings['smtp_port'] ?? ''),
                            'mail.mailers.smtp.username' => Crypt::decryptString($smtpSettings['smtp_username'] ?? ''),
                            'mail.mailers.smtp.password' => Crypt::decryptString($smtpSettings['smtp_password'] ?? ''),
                            'mail.mailers.smtp.encryption' => Crypt::decryptString($smtpSettings['smtp_encryption'] ?? '') ?: null,
                            'mail.from.address' => Crypt::decryptString($smtpSettings['smtp_from_address'] ?? ''),
                            'mail.from.name' => Crypt::decryptString($smtpSettings['smtp_from_name'] ?? ''),
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to decrypt SMTP settings: ' . $e->getMessage());
                    }
                }

                $enabled = ThemeSetting::where('group', 'integration.algolia')->where('key', 'enabled')->value('value');
                if ($enabled === '1') {
                    $appId = ThemeSetting::where('group', 'integration.algolia')->where('key', 'app_id')->value('value');
                    $apiKey = ThemeSetting::where('group', 'integration.algolia')->where('key', 'admin_api_key')->value('value');

                    if ($appId && $apiKey) {
                        try {
                            $appId = Crypt::decryptString($appId);
                            $apiKey = Crypt::decryptString($apiKey);

                            config([
                                'scout.driver' => 'algolia',
                                'scout.algolia.id' => $appId,
                                'scout.algolia.secret' => $apiKey,
                            ]);
                        } catch (\Exception $e) {
                            config(['scout.driver' => 'database']);
                        }
                    } else {
                        config(['scout.driver' => 'database']);
                    }
                } else {
                    config(['scout.driver' => 'database']);
                }
            } else {
                config(['scout.driver' => 'database']);
            }
        } catch (\Exception $e) {
            config(['scout.driver' => 'database']);
        }

        RateLimiter::for('public_api', function (Request $request) {
            return Limit::perMinutes(
                config('rate_limiting.public.decay_minutes', 1),
                config('rate_limiting.public.max_attempts', 60)
            )->by($request->ip());
        });

        RateLimiter::for('authenticated_api', function (Request $request) {
            return Limit::perMinutes(
                config('rate_limiting.authenticated.decay_minutes', 1),
                config('rate_limiting.authenticated.max_attempts', 120)
            )->by($request->user()?->id ?: $request->ip());
        });
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
                        ->get('https://api.github.com/repos/WitReach/vyora-api/releases/latest');
                    if ($response->successful()) {
                        $release = $response->json();
                        return str_replace('v', '', $release['tag_name'] ?? '1.0.0');
                    }
                } catch (\Exception $e) {}
                return config('app.version', '1.0.0');
            });

            $currentVersion = config('app.version', '1.0.0');
            $view->with('globalUpdateAvailable', version_compare($latestVersion, $currentVersion, '>'));
        });

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('theme_settings')) {
                $enabled = \App\Models\ThemeSetting::where('group', 'integration.algolia')->where('key', 'enabled')->value('value');
                if ($enabled === '1') {
                    $appId = \App\Models\ThemeSetting::where('group', 'integration.algolia')->where('key', 'app_id')->value('value');
                    $apiKey = \App\Models\ThemeSetting::where('group', 'integration.algolia')->where('key', 'admin_api_key')->value('value');
                    
                    if ($appId && $apiKey) {
                        try {
                            $appId = \Illuminate\Support\Facades\Crypt::decryptString($appId);
                            $apiKey = \Illuminate\Support\Facades\Crypt::decryptString($apiKey);
                            
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
    }
}

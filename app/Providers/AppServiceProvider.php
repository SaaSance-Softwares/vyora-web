<?php

namespace App\Providers;

use App\Models\ThemeSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
                        ->get('https://api.github.com/repos/WitReach/vyora-api/releases/latest');
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

        try {
            if (Schema::hasTable('theme_settings')) {
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
    }
}

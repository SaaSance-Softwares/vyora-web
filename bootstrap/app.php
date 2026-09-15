<?php

if (! file_exists(__DIR__.'/../.env') && file_exists(__DIR__.'/../.env.example')) {
    copy(__DIR__.'/../.env.example', __DIR__.'/../.env');
    $key = 'base64:'.base64_encode(random_bytes(32));
    file_put_contents(__DIR__.'/../.env', preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$key, file_get_contents(__DIR__.'/../.env')));
}

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckInstalled;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            $posUrl = 'pos';
            try {
                $setting = \Illuminate\Support\Facades\DB::table('theme_settings')
                    ->where('group', 'pos_settings')
                    ->where('key', 'pos_url')
                    ->first();
                if ($setting && !empty($setting->value)) {
                    $posUrl = ltrim($setting->value, '/');
                }
            } catch (\Exception $e) {
                // DB not ready or missing table
            }

            \Illuminate\Support\Facades\Route::middleware(['web'])
                ->prefix($posUrl)
                ->name('pos.')
                ->group(base_path('routes/pos.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'api/mobile/*',
            'api/login',
            'api/login/*',
            'api/register',
            'api/register/*',
            'api/tracking/*',
            '*/api/orders',
            '*/api/orders/*'
        ]);

        $middleware->web(append: [
            CheckInstalled::class,
            HandleInertiaRequests::class,
        ]);

        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);

        $adminPath = env('ADMIN_PATH', 'occ');
        $middleware->preventRequestsDuringMaintenance(
            except: [
                $adminPath,
                $adminPath.'/*',
                'api/maintenance-status',
                'api/settings',
            ]
        );

        $middleware->alias([
            'admin_access' => AdminMiddleware::class,
            'pos_access' => \App\Http\Middleware\PosMiddleware::class,
            'throttle.auth.backoff' => \App\Http\Middleware\ThrottleAuthWithBackoff::class,
            'throttle.otp.backoff' => \App\Http\Middleware\ThrottleOtpWithBackoff::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            $adminPath = config('app.admin_path', 'occ');
            // Check for admin paths
            if ($request->is($adminPath) || $request->is($adminPath.'/*')) {
                return route('admin.login');
            }
            // Check for POS paths — must use admin login, NOT customer login
            $posUrl = 'pos';
            try {
                $setting = \Illuminate\Support\Facades\DB::table('theme_settings')
                    ->where('group', 'pos_settings')->where('key', 'pos_url')->first();
                if ($setting && !empty($setting->value)) {
                    $posUrl = ltrim($setting->value, '/');
                }
            } catch (\Exception $e) {}
            if ($request->is($posUrl) || $request->is($posUrl.'/*')) {
                return route('admin.login');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

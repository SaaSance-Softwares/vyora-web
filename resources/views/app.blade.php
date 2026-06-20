<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $gaEnabled = false;
            $gaId = '';
            $pixelEnabled = false;
            $pixelId = '';
            $gscEnabled = false;
            $gscVerificationCode = '';
            $storeName = config('app.name', 'Vyora');
            $faviconUrl = asset('favicon.ico');

            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('theme_settings')) {
                    $dbStoreName = \App\Models\ThemeSetting::where('key', 'store_name')->value('value');
                    if ($dbStoreName) {
                        $storeName = $dbStoreName;
                    }
                    
                    $dbFavicon = \App\Models\ThemeSetting::where('group', 'logos')->where('key', 'favicon')->value('value');
                    if ($dbFavicon) {
                        $faviconUrl = asset($dbFavicon);
                    }

                    $gaEnabled = \App\Models\ThemeSetting::where('group', 'integration.google-analytics')->where('key', 'enabled')->value('value') === '1';
                    if ($gaEnabled) {
                        $gaId = \Illuminate\Support\Facades\Crypt::decryptString(\App\Models\ThemeSetting::where('group', 'integration.google-analytics')->where('key', 'measurement_id')->value('value'));
                    }

                    $pixelEnabled = \App\Models\ThemeSetting::where('group', 'integration.meta-pixel')->where('key', 'enabled')->value('value') === '1';
                    if ($pixelEnabled) {
                        $pixelId = \Illuminate\Support\Facades\Crypt::decryptString(\App\Models\ThemeSetting::where('group', 'integration.meta-pixel')->where('key', 'pixel_id')->value('value'));
                    }

                    $gscEnabled = \App\Models\ThemeSetting::where('group', 'integration.google-search-console')->where('key', 'enabled')->value('value') === '1';
                    if ($gscEnabled) {
                        $gscVerificationCode = \App\Models\ThemeSetting::where('group', 'integration.google-search-console')->where('key', 'site_verification_code')->value('value');
                        if ($gscVerificationCode) {
                            $gscVerificationCode = \Illuminate\Support\Facades\Crypt::decryptString($gscVerificationCode);
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore errors during migration or missing DB
            }
        @endphp

        <title inertia>{{ $storeName }}</title>
        <link rel="icon" href="{{ $faviconUrl }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        @if($gscEnabled && $gscVerificationCode)
            <meta name="google-site-verification" content="{{ $gscVerificationCode }}" />
        @endif

        @if($gaEnabled && $gaId)
            <!-- Google tag (gtag.js) -->
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
            <script>
              window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag('js', new Date());
              gtag('config', '{{ $gaId }}');
            </script>
        @endif

        @if($pixelEnabled && $pixelId)
            <!-- Meta Pixel Code -->
            <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $pixelId }}');
            fbq('track', 'PageView');
            </script>
            <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"
            /></noscript>
            <!-- End Meta Pixel Code -->
        @endif

        <!-- Scripts -->
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>

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
            $snapchatPixelEnabled = false;
            $snapchatPixelId = '';
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

                    $snapchatPixelEnabled = \App\Models\ThemeSetting::where('group', 'integration.snapchat-pixel')->where('key', 'enabled')->value('value') === '1';
                    if ($snapchatPixelEnabled) {
                        $snapchatPixelId = \Illuminate\Support\Facades\Crypt::decryptString(\App\Models\ThemeSetting::where('group', 'integration.snapchat-pixel')->where('key', 'pixel_id')->value('value'));
                    }

                    $gscEnabled = \App\Models\ThemeSetting::where('group', 'integration.google-search-console')->where('key', 'enabled')->value('value') === '1';
                    if ($gscEnabled) {
                        $gscVerificationCode = \App\Models\ThemeSetting::where('group', 'integration.google-search-console')->where('key', 'site_verification_code')->value('value');
                        if ($gscVerificationCode) {
                            $gscVerificationCode = \Illuminate\Support\Facades\Crypt::decryptString($gscVerificationCode);
                        }
                    }

                    $customCodeHeader = \App\Models\ThemeSetting::where('key', 'custom_code_header')->value('value');
                    $customCodeBody = \App\Models\ThemeSetting::where('key', 'custom_code_body')->value('value');
                    $customCodeFooter = \App\Models\ThemeSetting::where('key', 'custom_code_footer')->value('value');

                    // AEO Organization Schema variables
                    $orgEmail = \App\Models\ThemeSetting::where('key', 'support_email')->value('value');
                    $orgPhone = \App\Models\ThemeSetting::where('key', 'support_phone')->value('value');
                    $socialInsta = \App\Models\ThemeSetting::where('key', 'social_instagram')->value('value');
                    $socialFb = \App\Models\ThemeSetting::where('key', 'social_facebook')->value('value');
                    $socialYt = \App\Models\ThemeSetting::where('key', 'social_youtube')->value('value');
                    
                    $orgSameAs = array_filter([$socialInsta, $socialFb, $socialYt]);
                    
                    $globalOrgSchema = [
                        "@context" => "https://schema.org",
                        "@type" => "Organization",
                        "name" => $storeName,
                        "url" => url('/'),
                        "logo" => $faviconUrl
                    ];

                    $websiteSchema = [
                        "@context" => "https://schema.org",
                        "@type" => "WebSite",
                        "name" => $storeName,
                        "url" => url('/')
                    ];
                    if ($orgEmail) $globalOrgSchema['email'] = $orgEmail;
                    if ($orgPhone) $globalOrgSchema['telephone'] = $orgPhone;
                    if (!empty($orgSameAs)) $globalOrgSchema['sameAs'] = array_values($orgSameAs);
                }
            } catch (\Exception $e) {
                // Ignore errors during migration or missing DB
            }
        @endphp

        <title inertia>{{ $storeName }}</title>
        
        <link rel="canonical" href="{{ url()->current() }}" />
        
        <meta name="description" content="{{ $og_description ?? 'Shop the latest streetwear and fashion at ' . $storeName }}" />
        
        <meta property="og:title" content="{{ $og_title ?? $storeName }}" />
        <meta property="og:description" content="{{ $og_description ?? 'Shop the latest streetwear and fashion at ' . $storeName }}" />
        <meta name="twitter:description" content="{{ $og_description ?? 'Shop the latest streetwear and fashion at ' . $storeName }}" />
        
        @if(isset($og_image) && $og_image)
            <meta property="og:image" content="{{ $og_image }}" />
            <meta name="twitter:image" content="{{ $og_image }}" />
            <meta name="twitter:card" content="summary_large_image" />
        @else
            <meta property="og:image" content="{{ $faviconUrl }}" />
            <meta name="twitter:image" content="{{ $faviconUrl }}" />
            <meta name="twitter:card" content="summary" />
        @endif
        
        <meta property="og:url" content="{{ $og_url ?? url()->current() }}" />
        
                <!-- Google Search Console optimized static favicons (generated automatically on upload) -->
        <link rel="icon" type="image/png" sizes="192x192" href="/favicon.png">
        <link rel="shortcut icon" href="/favicon.ico">
        <!-- Fallback for dynamic hashing if needed -->
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

        @if($snapchatPixelEnabled && $snapchatPixelId)
            <!-- Snap Pixel Code -->
            <script type='text/javascript'>
            (function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function()
            {a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};
            a.queue=[];var s='script';r=t.createElement(s);r.async=!0;
            r.src=n;var u=t.getElementsByTagName(s)[0];
            u.parentNode.insertBefore(r,u);})(window,document,
            'https://sc-static.net/scevent.min.js');
            snaptr('init', '{{ $snapchatPixelId }}');
            snaptr('track', 'PAGE_VIEW');
            </script>
            <!-- End Snap Pixel Code -->
        @endif

        <!-- Scripts -->
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
        @inertiaHead

        @if(isset($globalOrgSchema))
            <script type="application/ld+json">
                {!! json_encode($globalOrgSchema) !!}
            </script>
            <script type="application/ld+json">
                {!! json_encode($websiteSchema) !!}
            </script>
        @endif
        
        @if(isset($json_ld) && $json_ld)
            <script type="application/ld+json">
                {!! is_array($json_ld) ? json_encode($json_ld) : $json_ld !!}
            </script>
        @endif

        {!! $customCodeHeader ?? '' !!}
    </head>
    <body class="font-sans antialiased">
        {!! $customCodeBody ?? '' !!}
        
        @if(isset($bot_html) && $bot_html)
            <noscript>
                {!! $bot_html !!}
            </noscript>
            <div id="seo-bot-content" style="display: none !important; opacity: 0; position: absolute; left: -9999px;">
                {!! $bot_html !!}
            </div>
        @endif

        @inertia
        
        {!! $customCodeFooter ?? '' !!}
    </body>
</html>

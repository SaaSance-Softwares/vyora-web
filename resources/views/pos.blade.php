<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title inertia>{{ config('app.name', 'Vyora') }} POS</title>
        
        <!-- Favicon and PWA -->
        <link rel="icon" type="image/png" sizes="192x192" href="/vyora-asset/pos/vyora-pos-192.png">
        <link rel="shortcut icon" href="/vyora-asset/pos/vyora-pos.png">
        <link rel="manifest" href="{{ route('pos.manifest') }}">
        <meta name="theme-color" content="#000000">
        <link rel="apple-touch-icon" href="/vyora-asset/pos/vyora-pos-192.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

        <!-- Scripts -->
        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased bg-gray-50">
        @inertia
        
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').catch(error => {
                        console.log('ServiceWorker registration failed: ', error);
                    });
                });
            }
        </script>
    </body>
</html>

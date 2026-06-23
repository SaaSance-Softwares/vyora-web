<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] ?? 'Redirecting...' }}</title>
    
    <!-- SEO Canonical Tag (Tells search engines the true URL of this content to avoid duplicate content penalties) -->
    <link rel="canonical" href="{{ $canonicalUrl }}" />
    
    @if($meta)
        <meta name="description" content="{{ $meta['description'] }}">
        <meta property="og:title" content="{{ $meta['title'] }}">
        <meta property="og:description" content="{{ $meta['description'] }}">
        @if($meta['image'])
            <meta property="og:image" content="{{ $meta['image'] }}">
        @endif
        <meta name="twitter:card" content="summary_large_image">
    @endif

    <!-- Automatic Redirect -->
    <meta http-equiv="refresh" content="0;url={{ $redirectUrl }}">
    
    <script>
        // Fallback JavaScript redirect
        window.location.replace("{!! addslashes($redirectUrl) !!}");
    </script>
</head>
<body>
    <p>Redirecting you to the product... <a href="{{ $redirectUrl }}">Click here if you are not redirected automatically</a>.</p>
</body>
</html>

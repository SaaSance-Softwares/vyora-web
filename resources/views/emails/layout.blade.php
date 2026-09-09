<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 0; }
        .wrapper { width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; margin-top: 40px; margin-bottom: 40px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { text-align: center; padding: 30px 20px; background-color: #ffffff; border-bottom: 1px solid #f3f4f6; }
        .header img { max-height: 40px; max-width: 200px; }
        .header h1 { margin: 0; font-size: 24px; color: #111827; font-weight: 700; letter-spacing: -0.5px; }
        .content { padding: 40px 30px; color: #374151; font-size: 15px; line-height: 1.6; }
        .footer { padding: 30px; text-align: center; background-color: #f9fafb; border-top: 1px solid #f3f4f6; color: #6b7280; font-size: 13px; }
        .btn { display: inline-block; background-color: #111827; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; margin-top: 20px; text-align: center; }
        .item-row { display: table; width: 100%; margin-bottom: 15px; border-bottom: 1px solid #f3f4f6; padding-bottom: 15px; }
        .item-image { display: table-cell; width: 60px; vertical-align: top; }
        .item-image img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; background-color: #f9fafb; }
        .item-details { display: table-cell; padding-left: 15px; vertical-align: top; }
        .item-price { display: table-cell; width: 80px; text-align: right; vertical-align: top; font-weight: 600; }
        p { margin-top: 0; margin-bottom: 16px; }
        h2 { font-size: 20px; color: #111827; margin-top: 0; margin-bottom: 20px; font-weight: 600; }
        .text-sm { font-size: 13px; color: #6b7280; }
        .text-center { text-align: center; }
        .mt-4 { margin-top: 16px; }
        .mb-0 { margin-bottom: 0; }
    </style>
</head>
<body>
    @php
        $storeName = \App\Models\ThemeSetting::where('key', 'store_name')->first()?->value ?? config('app.name', 'Store');
        $logo = \App\Models\ThemeSetting::where('group', 'logos')->where('key', 'main_logo')->value('value');
        $logoUrl = $logo ? asset($logo) : null;
    @endphp

    <div style="background-color: #f9fafb; padding: 20px;">
        <div class="wrapper">
            <div class="header">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $storeName }}">
                @else
                    <h1>{{ $storeName }}</h1>
                @endif
            </div>
            
            <div class="content">
                @yield('content')
            </div>

            <div class="footer">
                <p>&copy; {{ date('Y') }} {{ $storeName }}. All rights reserved.</p>
                <p class="mb-0">This is an automated email, please do not reply.</p>
            </div>
        </div>
    </div>
</body>
</html>

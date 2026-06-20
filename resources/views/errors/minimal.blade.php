<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | {{ config('app.name', 'Vyora') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', system-ui, -apple-system, sans-serif; 
            background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            background-color: #111827;
            color: white;
            background-attachment: fixed;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255,255,255,0.05) inset;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col items-center justify-center p-6">
    
    <div class="mb-10 text-center">
        <!-- Dynamic System App Name -->
        <h1 class="text-3xl font-black tracking-[0.2em] uppercase text-white/80 drop-shadow-md">
            {{ config('app.name', 'Vyora') }}
        </h1>
    </div>

    <div class="glass-card max-w-lg w-full rounded-[2.5rem] p-10 sm:p-14 text-center space-y-6 relative overflow-hidden">
        <!-- Decorative abstract lighting blobs -->
        <div class="absolute -top-32 -right-32 w-64 h-64 bg-white/10 rounded-full blur-[80px]"></div>
        <div class="absolute -bottom-32 -left-32 w-64 h-64 bg-blue-500/10 rounded-full blur-[80px]"></div>
        
        <div class="relative z-10">
            <!-- Dynamic Error Code -->
            <h1 class="text-8xl font-black tracking-tighter mb-2 text-transparent bg-clip-text bg-gradient-to-br from-white to-white/40 drop-shadow-sm">
                @yield('code')
            </h1>
            
            <div class="h-1.5 w-16 bg-white/20 mx-auto rounded-full my-8"></div>

            <!-- Dynamic Error Message -->
            <h2 class="text-2xl font-bold tracking-tight text-gray-100">
                @yield('message')
            </h2>
            
            <p class="mt-4 text-sm text-gray-400 font-medium max-w-sm mx-auto leading-relaxed">
                @hasSection('description')
                    @yield('description')
                @else
                    We're sorry, but something went wrong or the page you are looking for could not be found. Our systems have logged the issue.
                @endif
            </p>
            
            @unless(View::hasSection('hide_button'))
            <div class="mt-12">
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2.5 px-8 py-4 bg-white text-black text-xs font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-100 hover:scale-[1.02] hover:-translate-y-1 transition-all duration-300 shadow-[0_10px_40px_-10px_rgba(255,255,255,0.3)] active:scale-[0.98]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to Store
                </a>
            </div>
            @endunless
        </div>
    </div>
</body>
</html>

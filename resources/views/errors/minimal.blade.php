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
            background-color: #f9fafb;
            color: #111827;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0px); }
        }

        .animate-float {
            animation: float 4s ease-in-out infinite;
        }
        
        .animate-fade-in {
            animation: fadeIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col items-center justify-center p-6 bg-gray-50">
    
    <div class="w-full max-w-lg mx-auto text-center animate-fade-in">
        <!-- Minimal Logo / App Name -->
        <div class="mb-10">
            <h1 class="text-xl font-bold tracking-[0.2em] uppercase text-gray-900">
                {{ config('app.name', 'Vyora') }}
            </h1>
        </div>

        <div class="bg-white rounded-3xl p-10 sm:p-14 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 relative overflow-hidden">
            
            <!-- Dynamic Error Code with float animation -->
            <div class="animate-float mb-6">
                <h1 class="text-7xl sm:text-8xl font-black tracking-tighter text-gray-900 drop-shadow-sm">
                    @yield('code')
                </h1>
            </div>
            
            <div class="h-1 w-12 bg-gray-200 mx-auto rounded-full my-8"></div>

            <!-- Dynamic Error Message -->
            <div class="animate-fade-in delay-100 opacity-0">
                <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                    @yield('message')
                </h2>
                
                <p class="mt-4 text-sm text-gray-500 font-medium max-w-sm mx-auto leading-relaxed">
                    @hasSection('description')
                        @yield('description')
                    @else
                        We're sorry, but something went wrong or the page you are looking for could not be found. Our systems have logged the issue.
                    @endif
                </p>
            </div>
            
            @unless(View::hasSection('hide_button'))
            <div class="mt-10 animate-fade-in delay-200 opacity-0">
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-gray-900 text-white text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-black hover:-translate-y-0.5 transition-all duration-300 shadow-md shadow-gray-900/10 active:translate-y-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to Store
                </a>
            </div>
            @endunless
        </div>
        
        <div class="mt-12 text-xs font-medium text-gray-400 uppercase tracking-widest animate-fade-in delay-300 opacity-0">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>

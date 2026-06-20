<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ config('app.name') }}</title>
    <meta name="description" content="@yield('meta_description')">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $theme['typography']['heading_font'] ?? 'Inter') }}:wght@400;600;700&family={{ str_replace(' ', '+', $theme['typography']['body_font'] ?? 'Inter') }}:wght@400;500&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '{{ $theme['colors']['primary_color'] ?? '#000000' }}',
                        secondary: '{{ $theme['colors']['secondary_color'] ?? '#ffffff' }}',
                        accent: '{{ $theme['colors']['accent_color'] ?? '#3b82f6' }}',
                    },
                    fontFamily: {
                        heading: ['{{ explode(",", $theme['typography']['heading_font'] ?? 'Inter')[0] }}', 'sans-serif'],
                        body: ['{{ explode(",", $theme['typography']['body_font'] ?? 'Inter')[0] }}', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: '{{ explode(",", $theme['typography']['body_font'] ?? 'Inter')[0] }}', sans-serif;
            color:
                {{ $theme['colors']['text_color'] ?? '#1f2937' }}
            ;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: '{{ explode(",", $theme['typography']['heading_font'] ?? 'Inter')[0] }}', sans-serif;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-secondary">
    {{-- Header --}}
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                {{-- Logo --}}
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('store.home') }}">
                        @if(isset($theme['logos']['main_logo']))
                            <img class="h-8 w-auto" src="{{ Storage::url($theme['logos']['main_logo']) }}"
                                alt="{{ config('app.name') }}">
                        @else
                            <span
                                class="text-2xl font-bold font-heading text-primary">{{ config('app.name', 'Store') }}</span>
                        @endif
                    </a>
                </div>

                {{-- Navigation (Placeholder) --}}
                <nav class="hidden md:flex space-x-8">
                    <a href="{{ route('store.home') }}"
                        class="text-gray-900 hover:text-primary px-3 py-2 rounded-md text-sm font-medium">Home</a>
                    <a href="#"
                        class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Shop</a>
                    <a href="#"
                        class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">About</a>
                    <a href="#"
                        class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Contact</a>
                </nav>

                {{-- Actions (Search, Cart, User) --}}
                <div class="flex items-center space-x-4">
                    <button class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Search</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                    <button class="text-gray-400 hover:text-gray-500 relative">
                        <span class="sr-only">Cart</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span
                            class="absolute -top-1 -right-1 bg-accent text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">0</span>
                    </button>
                    {{-- User Login (Placeholder) --}}
                    <a href="{{ route('login') }}" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Account</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-800 text-white">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                {{-- Footer Brand --}}
                <div class="col-span-1 md:col-span-1">
                    @if(isset($theme['logos']['footer_logo']))
                        <img class="h-8 w-auto mb-4" src="{{ Storage::url($theme['logos']['footer_logo']) }}"
                            alt="{{ config('app.name') }}">
                    @else
                        <span class="text-xl font-bold font-heading mb-4 block">{{ config('app.name') }}</span>
                    @endif
                    <p class="text-gray-400 text-sm">Your one-stop shop for everything amazing.</p>
                </div>

                {{-- Links --}}
                <div>
                    <h3 class="text-sm font-semibold tracking-wider uppercase mb-4">Shop</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm">New Arrivals</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm">Best Sellers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm">Sale</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold tracking-wider uppercase mb-4">Company</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm">Contact</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm">Privacy Policy</a></li>
                    </ul>
                </div>

                {{-- Newsletter --}}
                <div>
                    <h3 class="text-sm font-semibold tracking-wider uppercase mb-4">Stay Connected</h3>
                    <form class="flex">
                        <input type="email"
                            class="w-full px-3 py-2 text-gray-900 placeholder-gray-500 rounded-l-md focus:outline-none focus:ring-1 focus:ring-primary"
                            placeholder="Enter your email">
                        <button type="submit"
                            class="bg-primary px-4 py-2 rounded-r-md hover:bg-opacity-90 transition">Subscribe</button>
                    </form>
                </div>
            </div>
            <div class="mt-8 border-t border-gray-700 pt-8 text-center">
                <p class="text-gray-400 text-sm">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>

</html>
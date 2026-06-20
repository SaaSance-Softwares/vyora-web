<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Dope Style Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#111827', // Gray 900
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-sm px-6">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <div class="p-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Welcome Back</h2>
                    <p class="text-sm text-gray-500 mt-2">Sign in to your admin account</p>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-lg mb-6 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="email">
                            Email Address
                        </label>
                        <input
                            class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200"
                            id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="admin@example.com">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-medium text-gray-700" for="password">
                                Password
                            </label>

                        </div>
                        <input
                            class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-200 text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200"
                            id="password" type="password" name="password" required placeholder="••••••••">
                    </div>

                    <div class="pt-2">
                        <button
                            class="w-full bg-primary text-white font-semibold py-3 px-4 rounded-lg hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary shadow-lg hover:shadow-xl transition-all duration-200"
                            type="submit">
                            Sign In
                        </button>
                    </div>
                </form>
            </div>

        </div>
        <div class="text-center mt-6">
            <a href="#" class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                Forgot your password?
            </a>
        </div>
    </div>
</body>

</html>
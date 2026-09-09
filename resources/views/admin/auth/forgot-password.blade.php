<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - Dope Style Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: sans-serif; }</style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-sm px-6">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Reset Password</h2>
                <p class="text-sm text-gray-500 mt-2">Enter your email to receive a reset link</p>
            </div>

            @if (session('status'))
                <div class="bg-green-50 border border-green-100 text-green-600 px-4 py-3 rounded-lg mb-6 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-lg mb-6 text-sm">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.password.email') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5" for="email">Email Address</label>
                    <input class="w-full px-4 py-3 rounded-lg bg-gray-50 border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-black"
                        id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                <button type="submit" class="w-full bg-black text-white font-semibold py-3 px-4 rounded-lg shadow-lg hover:shadow-xl transition-all">
                    Send Reset Link
                </button>
            </form>
        </div>
        <div class="text-center mt-6">
            <a href="{{ route('admin.login') }}" class="text-xs text-gray-400 hover:text-gray-600">Back to Login</a>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete your Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Almost there!</h1>
            <p class="text-gray-500 text-sm">Please review our terms to complete your setup.</p>
        </div>

        <form action="{{ route('social.consent.process') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="payload" value="{{ $payload }}">

            <div class="space-y-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                <!-- Terms Consent -->
                <label class="flex items-start gap-3 cursor-pointer group">
                    <div class="flex items-center h-5 mt-0.5">
                        <input type="checkbox" name="has_consented_to_terms" value="1" required
                            class="w-5 h-5 rounded border-gray-300 text-black focus:ring-black transition-colors"
                            {{ old('has_consented_to_terms') ? 'checked' : '' }}>
                    </div>
                    <div class="text-sm">
                        <span class="font-semibold text-gray-900">I agree to the Terms of Service & Privacy Policy</span>
                        <p class="text-gray-500 mt-1 text-xs">Required to create your account and process orders.</p>
                        @error('has_consented_to_terms')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </label>

                <hr class="border-gray-200">

                <!-- Marketing Consent -->
                <label class="flex items-start gap-3 cursor-pointer group">
                    <div class="flex items-center h-5 mt-0.5">
                        <input type="checkbox" name="has_consented_to_marketing" value="1"
                            class="w-5 h-5 rounded border-gray-300 text-black focus:ring-black transition-colors"
                            {{ old('has_consented_to_marketing') ? 'checked' : '' }}>
                    </div>
                    <div class="text-sm">
                        <span class="font-medium text-gray-900">I want to receive offers & updates</span>
                        <p class="text-gray-500 mt-1 text-xs">Optional. We'll send you exclusive discounts.</p>
                    </div>
                </label>
            </div>

            <button type="submit" 
                class="w-full bg-black hover:bg-gray-900 text-white font-semibold py-3.5 px-4 rounded-xl transition-all shadow-md hover:shadow-lg focus:ring-2 focus:ring-black focus:ring-offset-2">
                Create Account
            </button>
        </form>

    </div>

</body>
</html>

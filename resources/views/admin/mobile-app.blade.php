@extends('layouts.admin')

@section('header', 'Mobile App Connection')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col md:flex-row">
        <!-- Info Section -->
        <div class="p-10 md:w-1/2 flex flex-col justify-center bg-gradient-to-br from-indigo-50 to-white">
            <h2 class="text-3xl font-black text-gray-900 tracking-tight mb-4">Connect Vyora Mobile</h2>
            <p class="text-gray-500 mb-6 font-medium leading-relaxed">
                Scan the QR code to securely link your Vyora Store with the mobile app. You can manage orders, view analytics, and respond to customers on the go.
            </p>
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">1</div>
                    <p class="text-sm font-semibold text-gray-700">Download the Vyora App</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">2</div>
                    <p class="text-sm font-semibold text-gray-700">Log in with SaaSance</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">3</div>
                    <p class="text-sm font-semibold text-gray-700">Scan this QR Code</p>
                </div>
            </div>
        </div>

        <!-- QR Code Section -->
        <div class="p-10 md:w-1/2 flex flex-col items-center justify-center bg-white border-l border-gray-100 relative">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-indigo-50 to-transparent rounded-bl-full pointer-events-none"></div>
            
            <div class="p-6 bg-white rounded-2xl shadow-xl shadow-indigo-500/10 border border-gray-100 relative z-10 mb-4" id="qrcode-container">
                <!-- QR Code gets rendered here -->
            </div>
            
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-4">One-time Secure Token</p>
            <p class="text-[10px] text-gray-400 mt-1">This QR code is valid for this session only.</p>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const qrData = JSON.stringify({
        store_url: "{{ url('/') }}",
        token: "{{ $token }}"
    });

    new QRCode(document.getElementById("qrcode-container"), {
        text: qrData,
        width: 250,
        height: 250,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
});
</script>
@endsection

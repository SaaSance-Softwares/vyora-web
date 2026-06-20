@extends('layouts.installer')

@section('subtitle', 'Database Configuration')

@section('content')
<form id="dbForm" action="{{ route('install.processDatabase') }}" method="POST" class="space-y-6">
    @csrf
    
    @if($errors->any())
        <div class="p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3">
            <div class="w-1.5 h-1.5 rounded-full bg-red-500 mt-2 shrink-0"></div>
            <ul class="text-sm text-red-800 font-medium space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-5">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-black text-gray-900 uppercase tracking-widest mb-2 pl-1">Host</label>
                <input type="text" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" required class="block w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-black focus:border-transparent transition-all outline-none">
                <p class="mt-1.5 ml-1 text-[11px] text-gray-500 font-medium">Usually <span class="font-bold text-gray-700">127.0.0.1</span> or <span class="font-bold text-gray-700">localhost</span></p>
            </div>
            
            <div>
                <label class="block text-xs font-black text-gray-900 uppercase tracking-widest mb-2 pl-1">Port</label>
                <input type="text" name="db_port" value="{{ old('db_port', '3306') }}" required class="block w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-black focus:border-transparent transition-all outline-none">
            </div>
        </div>

        <div>
            <label class="block text-xs font-black text-gray-900 uppercase tracking-widest mb-2 pl-1">Database Name</label>
            <input type="text" name="db_database" value="{{ old('db_database', '') }}" required placeholder="e.g. u123456789_store" class="block w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-black focus:border-transparent transition-all outline-none">
            <p class="mt-1.5 ml-1 text-[11px] text-gray-500 font-medium">On Hostinger, it starts with an alphanumeric prefix like <span class="font-bold text-gray-700">u123456789_</span></p>
        </div>

        <div>
            <label class="block text-xs font-black text-gray-900 uppercase tracking-widest mb-2 pl-1">Username</label>
            <input type="text" name="db_username" value="{{ old('db_username', '') }}" required placeholder="e.g. u123456789_user" class="block w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-black focus:border-transparent transition-all outline-none">
            <p class="mt-1.5 ml-1 text-[11px] text-gray-500 font-medium">Make sure to add the user to the database with all privileges.</p>
        </div>

        <div>
            <label class="block text-xs font-black text-gray-900 uppercase tracking-widest mb-2 pl-1">Password</label>
            <input type="password" name="db_password" placeholder="Leave empty if none" class="block w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-black focus:border-transparent transition-all outline-none">
        </div>
    </div>

    <div class="pt-4">
        <button id="submitBtn" type="submit" class="w-full flex items-center justify-center gap-2 py-4 px-4 border border-transparent rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] text-xs uppercase tracking-[0.2em] font-black text-white bg-black hover:bg-gray-800 hover:shadow-[0_8px_30px_rgb(0,0,0,0.2)] transition-all active:scale-[0.98]">
            <span id="btnText">Connect & Run Migrations</span> <svg id="btnIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
    </div>
</form>

<script>
    const form = document.getElementById('dbForm');
    const btn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnIcon = document.getElementById('btnIcon');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        btn.disabled = true;
        btn.classList.add('opacity-70', 'cursor-not-allowed');
        btnText.innerText = 'Configuring Database...';
        btnIcon.style.display = 'none';

        const formData = new FormData(form);
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.redirected) {
                window.location.href = response.url;
            } else if (!response.ok) {
                // If validation failed, reload to show errors
                window.location.reload();
            }
        } catch (error) {
            // When .env is modified, artisan serve restarts and drops the connection!
            btnText.innerText = 'Server restarting...';
            setTimeout(() => {
                window.location.href = "{{ route('install.admin') }}";
            }, 3000);
        }
    });
</script>
@endsection

@extends('layouts.installer')

@section('subtitle', 'Administrator Setup')

@section('content')
<form id="adminForm" action="{{ route('install.processAdmin') }}" method="POST" class="space-y-6">
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
        <div>
            <label class="block text-xs font-black text-gray-900 uppercase tracking-widest mb-2 pl-1">Store Name</label>
            <input type="text" name="store_name" value="{{ old('store_name', 'Vyora Store') }}" required placeholder="Vyora Store" class="block w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-black focus:border-transparent transition-all outline-none">
        </div>

        <div>
            <label class="block text-xs font-black text-gray-900 uppercase tracking-widest mb-2 pl-1">Secure Admin Path</label>
            <div class="relative flex items-center">
                <span class="absolute pl-4 text-sm font-medium text-gray-400 pointer-events-none">{{ request()->getHttpHost() }}/</span>
                <input type="text" name="admin_path" value="{{ old('admin_path', 'occ') }}" required placeholder="occ" style="padding-left: {{ strlen(request()->getHttpHost()) * 8 + 40 }}px;" class="block w-full pr-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-black focus:border-transparent transition-all outline-none">
            </div>
            <p class="mt-2 text-[10px] text-gray-400 uppercase tracking-widest font-bold pl-1">This will be your secret backend URL (e.g. occ, admin, portal)</p>
        </div>

        <div class="h-px bg-gray-100 my-2"></div>

        <div>
            <label class="block text-xs font-black text-gray-900 uppercase tracking-widest mb-2 pl-1">Full Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="John Doe" class="block w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-black focus:border-transparent transition-all outline-none">
        </div>
        
        <div>
            <label class="block text-xs font-black text-gray-900 uppercase tracking-widest mb-2 pl-1">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" required placeholder="admin@vyora.com" class="block w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-black focus:border-transparent transition-all outline-none">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-black text-gray-900 uppercase tracking-widest mb-2 pl-1">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="block w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-black focus:border-transparent transition-all outline-none">
            </div>

            <div>
                <label class="block text-xs font-black text-gray-900 uppercase tracking-widest mb-2 pl-1">Confirm</label>
                <input type="password" name="password_confirmation" required placeholder="••••••••" class="block w-full px-4 py-3.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-black focus:border-transparent transition-all outline-none">
            </div>
        </div>
    </div>

    <div class="pt-4">
        <button id="submitBtn" type="submit" class="w-full flex items-center justify-center gap-2 py-4 px-4 border border-transparent rounded-xl shadow-[0_8px_30px_rgb(0,0,0,0.12)] text-xs uppercase tracking-[0.2em] font-black text-white bg-black hover:bg-gray-800 hover:shadow-[0_8px_30px_rgb(0,0,0,0.2)] transition-all active:scale-[0.98]">
            <span id="btnText">Complete Installation</span> <svg id="btnIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </button>
    </div>
</form>

<script>
    const form = document.getElementById('adminForm');
    const btn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnIcon = document.getElementById('btnIcon');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        btn.disabled = true;
        btn.classList.add('opacity-70', 'cursor-not-allowed');
        btnText.innerText = 'Finalizing...';
        btnIcon.style.display = 'none';

        const formData = new FormData(form);
        const adminPath = formData.get('admin_path');
        
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
                window.location.reload();
            }
        } catch (error) {
            btnText.innerText = 'Server restarting...';
            setTimeout(() => {
                window.location.href = `/${adminPath}/login`;
            }, 3000);
        }
    });
</script>
@endsection

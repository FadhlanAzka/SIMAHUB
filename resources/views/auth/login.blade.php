@extends('layouts.auth')

@section('title', 'Masuk ke SIMAHUB')

@section('content')
<div class="sm:mx-auto sm:w-full sm:max-w-md">
    
    <!-- Modern Brand Identity & Sleek Monogram Logo -->
    <div class="text-center">
        <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-700 via-blue-600 to-indigo-600 items-center justify-center text-white shadow-xl shadow-blue-500/25 mb-4 ring-4 ring-blue-100/60 transform hover:scale-105 transition-transform duration-300">
            <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
        </div>
        <h1 class="text-2xl font-black tracking-tight text-slate-900">SIMAHUB</h1>
        <p class="text-xs text-slate-500 mt-1 font-semibold tracking-wide uppercase">Sistem Informasi Absensi MagangHub</p>
    </div>

    <!-- Login Card -->
    <div class="mt-7 bg-white py-8 px-6 sm:px-10 shadow-xl shadow-slate-200/60 rounded-3xl border border-slate-200/80 backdrop-blur-sm">
        
        <!-- Flash Alert -->
        @if(session('error'))
            <div class="mb-5 rounded-xl bg-rose-50 border border-rose-200/80 p-3.5 text-rose-800 text-xs flex items-center gap-2.5">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->has('login'))
            <div class="mb-5 rounded-xl bg-rose-50 border border-rose-200/80 p-3.5 text-rose-800 text-xs flex items-center gap-2.5">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                <span class="font-medium">{{ $errors->first('login') }}</span>
            </div>
        @endif

        <form action="{{ route('login.process') }}" method="POST" id="loginForm" class="space-y-5">
            @csrf

            <!-- Login Input (Email / NIM) -->
            <div>
                <label for="login" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Username
                </label>
                <div class="relative rounded-xl shadow-xs">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </div>
                    <input type="text" 
                           id="login" 
                           name="login" 
                           value="{{ old('login') }}" 
                           required 
                           autofocus
                           placeholder="Masukkan Username Anda"
                           class="block w-full pl-10 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm placeholder-slate-400 text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
            </div>

            <!-- Password Input -->
            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Kata Sandi
                </label>
                <div class="relative rounded-xl shadow-xs">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </div>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           placeholder="Masukkan kata sandi Anda"
                           class="block w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm placeholder-slate-400 text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <button type="button" 
                            id="togglePassword" 
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none focus:text-blue-600 rounded-xl transition-colors cursor-pointer">
                        <i data-lucide="eye" id="eyeIcon" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Remember Option -->
            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-600 select-none">
                    <input type="checkbox" 
                           name="remember" 
                           class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                    <span class="font-medium">Ingat saya di perangkat ini</span>
                </label>
            </div>

            <!-- Submit Button (Changed to 'Masuk') -->
            <div>
                <button type="submit" 
                        id="btnLogin"
                        class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl shadow-lg shadow-blue-500/25 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all cursor-pointer">
                    <span id="btnText">Masuk</span>
                    <i data-lucide="arrow-right" id="btnIcon" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
    // Toggle Password Visibility
    const toggleBtn = document.getElementById('togglePassword');
    const pwdInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    toggleBtn.addEventListener('click', function () {
        const isPassword = pwdInput.type === 'password';
        pwdInput.type = isPassword ? 'text' : 'password';
        eyeIcon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');
        lucide.createIcons();
    });

    // Login Form Submit Animation
    const form = document.getElementById('loginForm');
    const btnLogin = document.getElementById('btnLogin');
    const btnText = document.getElementById('btnText');
    const btnIcon = document.getElementById('btnIcon');

    form.addEventListener('submit', function () {
        btnLogin.disabled = true;
        btnLogin.classList.add('opacity-90', 'cursor-wait');
        btnText.textContent = 'Memverifikasi...';
        btnIcon.setAttribute('data-lucide', 'loader-2');
        btnIcon.classList.add('animate-spin');
        lucide.createIcons();
    });

    // Success Popup if redirected back or initialized
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            timer: 2000,
            showConfirmButton: false,
            customClass: {
                popup: 'rounded-2xl shadow-2xl border border-slate-100',
                title: 'text-lg font-bold text-slate-900',
                htmlContainer: 'text-xs text-slate-600'
            }
        });
    @endif
</script>
@endpush
@endsection

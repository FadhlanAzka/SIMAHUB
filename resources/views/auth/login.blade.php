@extends('layouts.auth')

@section('title', 'Masuk ke SIMAHUB')

@section('content')
<div class="sm:mx-auto sm:w-full sm:max-w-md">
    
    <!-- Header Logo & Identity -->
    <div class="text-center">
        <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-700 to-blue-500 items-center justify-center text-white shadow-lg shadow-blue-500/25 mb-4">
            <i data-lucide="calendar-check" class="w-7 h-7"></i>
        </div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">SIMAHUB</h1>
        <p class="text-sm text-slate-500 mt-1 font-medium">Sistem Informasi Absensi MagangHub</p>
    </div>

    <!-- Login Card -->
    <div class="mt-8 bg-white py-8 px-6 sm:px-10 shadow-xl shadow-slate-200/50 rounded-2xl border border-slate-200/80">
        
        <!-- Flash Alert -->
        @if(session('error'))
            <div class="mb-5 rounded-xl bg-rose-50 border border-rose-200 p-3.5 text-rose-800 text-xs flex items-center gap-2.5">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 p-3.5 text-emerald-800 text-xs flex items-center gap-2.5">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->has('login'))
            <div class="mb-5 rounded-xl bg-rose-50 border border-rose-200 p-3.5 text-rose-800 text-xs flex items-center gap-2.5">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                <span>{{ $errors->first('login') }}</span>
            </div>
        @endif

        <form action="{{ route('login.process') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Login Input (Email / NIM) -->
            <div>
                <label for="login" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                    Email atau NIM
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
                           placeholder="Masukkan Email atau NIM Anda"
                           class="block w-full pl-10 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm placeholder-slate-400 text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
            </div>

            <!-- Password Input -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
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
                            tabindex="-1"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
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
                    <span>Ingat saya di perangkat ini</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" 
                        class="w-full flex justify-center items-center gap-2 py-2.5 px-4 border border-transparent rounded-xl shadow-md shadow-blue-500/20 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all cursor-pointer">
                    <span>Masuk ke Sistem</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Clean Footer -->
    <div class="mt-8 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} SIMAHUB. Hak Cipta Dilindungi.
    </div>

</div>

@push('scripts')
<script>
    const toggleBtn = document.getElementById('togglePassword');
    const pwdInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    toggleBtn.addEventListener('click', function () {
        const isPassword = pwdInput.type === 'password';
        pwdInput.type = isPassword ? 'text' : 'password';
        eyeIcon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');
        lucide.createIcons();
    });
</script>
@endpush
@endsection

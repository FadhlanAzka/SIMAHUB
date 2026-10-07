<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIMAHUB') - Absensi MagangHub</title>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- SweetAlert2 for modern popups -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50/70 flex flex-col min-h-screen">

    <!-- Top Navigation Bar -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 gap-3">
                
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-2.5 sm:space-x-3 min-w-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 sm:space-x-3 group min-w-0">
                        <!-- Manual Logo Image -->
                        <img src="{{ asset('storage/logos/Logo_NavBar.png') }}" alt="Logo Instansi" class="h-10 sm:h-12 object-contain drop-shadow-sm group-hover:scale-105 transition-transform shrink-0">
                    </a>
                </div>

                <!-- Navigation Links (Desktop: Laptop & PC) -->
                <nav class="hidden lg:flex items-center space-x-1">
                    @if(!auth()->user()->isIntern())
                        <a href="{{ route('dashboard') }}" 
                           class="px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('admin.intern.index') }}" 
                           class="px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ request()->routeIs('admin.intern.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            <span>Data Peserta Magang</span>
                        </a>
                        <a href="{{ route('admin.attendance.index') }}" 
                           class="px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ request()->routeIs('admin.attendance.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                            <span>Rekap Absensi</span>
                        </a>
                        @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.settings.index') }}" 
                           class="px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            <span>Lokasi & Jam</span>
                        </a>
                        @endif
                        <a href="{{ route('admin.profile.edit') }}" 
                           class="px-3 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ request()->routeIs('admin.profile.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span>Profil Saya</span>
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                            <span>Dashboard Presensi</span>
                        </a>
                        <a href="{{ route('intern.history') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ request()->routeIs('intern.history') ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="history" class="w-4 h-4"></i>
                            <span>Riwayat Absensi</span>
                        </a>
                        <a href="{{ route('intern.profile.edit') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ request()->routeIs('intern.profile.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span>Profil Saya</span>
                        </a>
                    @endif
                </nav>

                <!-- User Profile & Logout -->
                <div class="flex items-center space-x-3 shrink-0">
                    <div class="hidden sm:block text-right">
                        <div class="text-xs font-bold text-slate-900 leading-tight max-w-[140px] md:max-w-[180px] truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-blue-600 font-semibold max-w-[140px] md:max-w-[180px] truncate">
                            {{ auth()->user()->role === 'admin' ? 'Administrator' : (auth()->user()->role === 'mentor' ? 'Mentor' : (auth()->user()->position ?: 'Peserta Magang')) }}
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Apakah Anda yakin ingin keluar?')"
                                title="Keluar" 
                                class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 transition-all cursor-pointer">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mobile & Tablet Responsive Navigation Bar -->
        <div class="lg:hidden border-t border-slate-200/90 bg-white/95 backdrop-blur-md px-2 sm:px-4 py-1.5 shadow-xs">
            <div class="flex items-center justify-between sm:justify-center gap-1 sm:gap-2 max-w-2xl mx-auto min-w-0">
                @if(!auth()->user()->isIntern())
                    <a href="{{ route('dashboard') }}" 
                       class="flex-1 min-w-[56px] sm:flex-initial sm:min-w-0 py-1.5 px-1 sm:px-3 sm:py-2 rounded-xl flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1.5 transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white font-bold shadow-xs shadow-blue-500/20' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
                        <span class="text-[10px] sm:text-xs leading-tight text-center">Dashboard</span>
                    </a>
                    <a href="{{ route('admin.intern.index') }}" 
                       class="flex-1 min-w-[56px] sm:flex-initial sm:min-w-0 py-1.5 px-1 sm:px-3 sm:py-2 rounded-xl flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1.5 transition-all {{ request()->routeIs('admin.intern.*') ? 'bg-blue-600 text-white font-bold shadow-xs shadow-blue-500/20' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                        <i data-lucide="users" class="w-4 h-4 shrink-0"></i>
                        <span class="text-[10px] sm:text-xs leading-tight text-center">Peserta<span class="hidden sm:inline"> Magang</span></span>
                    </a>
                    <a href="{{ route('admin.attendance.index') }}" 
                       class="flex-1 min-w-[56px] sm:flex-initial sm:min-w-0 py-1.5 px-1 sm:px-3 sm:py-2 rounded-xl flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1.5 transition-all {{ request()->routeIs('admin.attendance.*') ? 'bg-blue-600 text-white font-bold shadow-xs shadow-blue-500/20' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                        <i data-lucide="clipboard-list" class="w-4 h-4 shrink-0"></i>
                        <span class="text-[10px] sm:text-xs leading-tight text-center">Rekap<span class="hidden sm:inline"> Absensi</span></span>
                    </a>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.settings.index') }}" 
                       class="flex-1 min-w-[56px] sm:flex-initial sm:min-w-0 py-1.5 px-1 sm:px-3 sm:py-2 rounded-xl flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1.5 transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600 text-white font-bold shadow-xs shadow-blue-500/20' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                        <i data-lucide="map-pin" class="w-4 h-4 shrink-0"></i>
                        <span class="text-[10px] sm:text-xs leading-tight text-center">Lokasi<span class="hidden sm:inline"> & Jam</span></span>
                    </a>
                    @endif
                    <a href="{{ route('admin.profile.edit') }}" 
                       class="flex-1 min-w-[56px] sm:flex-initial sm:min-w-0 py-1.5 px-1 sm:px-3 sm:py-2 rounded-xl flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1.5 transition-all {{ request()->routeIs('admin.profile.*') ? 'bg-blue-600 text-white font-bold shadow-xs shadow-blue-500/20' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                        <i data-lucide="user" class="w-4 h-4 shrink-0"></i>
                        <span class="text-[10px] sm:text-xs leading-tight text-center">Profil<span class="hidden sm:inline"> Saya</span></span>
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" 
                       class="flex-1 min-w-[70px] sm:flex-initial sm:min-w-0 py-1.5 px-1 sm:px-3 sm:py-2 rounded-xl flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1.5 transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white font-bold shadow-xs shadow-blue-500/20' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                        <i data-lucide="clock" class="w-4 h-4 shrink-0"></i>
                        <span class="text-[10px] sm:text-xs leading-tight text-center">Dashboard</span>
                    </a>
                    <a href="{{ route('intern.history') }}" 
                       class="flex-1 min-w-[70px] sm:flex-initial sm:min-w-0 py-1.5 px-1 sm:px-3 sm:py-2 rounded-xl flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1.5 transition-all {{ request()->routeIs('intern.history') ? 'bg-blue-600 text-white font-bold shadow-xs shadow-blue-500/20' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                        <i data-lucide="history" class="w-4 h-4 shrink-0"></i>
                        <span class="text-[10px] sm:text-xs leading-tight text-center">Riwayat</span>
                    </a>
                    <a href="{{ route('intern.profile.edit') }}" 
                       class="flex-1 min-w-[70px] sm:flex-initial sm:min-w-0 py-1.5 px-1 sm:px-3 sm:py-2 rounded-xl flex flex-col sm:flex-row items-center justify-center gap-0.5 sm:gap-1.5 transition-all {{ request()->routeIs('intern.profile.*') ? 'bg-blue-600 text-white font-bold shadow-xs shadow-blue-500/20' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 font-medium' }}">
                        <i data-lucide="user" class="w-4 h-4 shrink-0"></i>
                        <span class="text-[10px] sm:text-xs leading-tight text-center">Profil</span>
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Flash Notification Messages -->
            @if(session('success'))
                <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200/80 p-4 text-emerald-800 flex items-start gap-3 shadow-xs">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0"></i>
                    <div>
                        <p class="font-semibold text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-200/80 p-4 text-rose-800 flex items-start gap-3 shadow-xs">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 mt-0.5 shrink-0"></i>
                    <div>
                        <p class="font-semibold text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-2xl bg-amber-50 border border-amber-200/80 p-4 text-amber-900 shadow-xs">
                    <div class="flex items-center gap-2 mb-2 font-bold text-sm">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i>
                        Periksa kembali input Anda:
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1 text-amber-800">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Clean Minimal Footer (No Copyright Text) -->
    <footer class="bg-white/80 border-t border-slate-200/80 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-slate-500">
            <div class="font-medium text-slate-600">
                Sistem Informasi Absensi MagangHub
            </div>
            <div class="flex items-center gap-3 text-slate-500 text-[11px]">
                <span>Jam Kerja: Senin - Kamis (07:30 - 16:00 WIB) | Jumat (07:30 - 16:30 WIB)</span>
            </div>
        </div>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>

    <!-- SweetAlert2 Login Success Pop-up Animation -->
    @if(session('login_success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil!',
                    text: "{{ session('login_success') }}",
                    timer: 2400,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    backdrop: `rgba(15, 23, 42, 0.45)`,
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl border border-slate-100 p-6',
                        title: 'text-lg font-black text-slate-900',
                        htmlContainer: 'text-xs font-semibold text-slate-600 mt-1',
                        timerProgressBar: 'bg-blue-600'
                    }
                });
            });
        </script>
    @endif

    @if(auth()->check() && auth()->user()->isIntern())
        @include('intern.partials.notification_script')
    @endif

    @stack('scripts')
</body>
</html>

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
            <div class="flex justify-between items-center h-16">
                
                <!-- Logo & Brand (Clean, no calendar checklist icon) -->
                <div class="flex items-center space-x-3">
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('mahasiswa.dashboard') }}" class="flex items-center space-x-3 group">
                        <!-- Custom Modern SVG Monogram Logo -->
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-700 via-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20 group-hover:scale-105 transition-transform">
                            <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-xl font-extrabold tracking-tight text-slate-900 flex items-center gap-2">
                                SIMAHUB
                                <span class="text-[10px] uppercase tracking-wider font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200/60">
                                    {{ auth()->user()->role === 'admin' ? 'Admin' : 'Magang' }}
                                </span>
                            </span>
                            <p class="text-[11px] text-slate-500 font-medium -mt-1">Sistem Absensi MagangHub</p>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden md:flex items-center space-x-1">
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.mahasiswa.index') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ request()->routeIs('admin.mahasiswa.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            Data Mahasiswa
                        </a>
                        <a href="{{ route('admin.attendance.index') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ request()->routeIs('admin.attendance.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                            Rekap Absensi
                        </a>
                        <a href="{{ route('admin.settings.index') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            Lokasi & Jam
                        </a>
                    @else
                        <a href="{{ route('mahasiswa.dashboard') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                            Dashboard Presensi
                        </a>
                        <a href="{{ route('mahasiswa.history') }}" 
                           class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ request()->routeIs('mahasiswa.history') ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="history" class="w-4 h-4"></i>
                            Riwayat Absensi
                        </a>
                    @endif
                </nav>

                <!-- User Profile & Logout -->
                <div class="flex items-center space-x-4">
                    <div class="hidden sm:block text-right">
                        <div class="text-xs font-bold text-slate-900 leading-tight">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-slate-500 font-medium">
                            {{ auth()->user()->isAdmin() ? 'Administrator' : (auth()->user()->nim ?: auth()->user()->institution) }}
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

        <!-- Mobile Navigation Strip -->
        <div class="md:hidden border-t border-slate-200 bg-slate-50/90 px-4 py-2 flex items-center justify-around text-xs font-semibold">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'text-blue-600 font-bold' : 'text-slate-600' }} py-1 flex items-center gap-1">
                    <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i> Dashboard
                </a>
                <a href="{{ route('admin.mahasiswa.index') }}" class="{{ request()->routeIs('admin.mahasiswa.*') ? 'text-blue-600 font-bold' : 'text-slate-600' }} py-1 flex items-center gap-1">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i> Mahasiswa
                </a>
                <a href="{{ route('admin.attendance.index') }}" class="{{ request()->routeIs('admin.attendance.*') ? 'text-blue-600 font-bold' : 'text-slate-600' }} py-1 flex items-center gap-1">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i> Rekap
                </a>
                <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'text-blue-600 font-bold' : 'text-slate-600' }} py-1 flex items-center gap-1">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Lokasi
                </a>
            @else
                <a href="{{ route('mahasiswa.dashboard') }}" class="{{ request()->routeIs('mahasiswa.dashboard') ? 'text-blue-600 font-bold' : 'text-slate-600' }} py-1 flex items-center gap-1">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i> Dashboard
                </a>
                <a href="{{ route('mahasiswa.history') }}" class="{{ request()->routeIs('mahasiswa.history') ? 'text-blue-600 font-bold' : 'text-slate-600' }} py-1 flex items-center gap-1">
                    <i data-lucide="history" class="w-3.5 h-3.5"></i> Riwayat
                </a>
            @endif
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
                <span>Jam Kerja: 07:30 - 16:00 WIB</span>
                <span>&bull;</span>
                <span>WFO (Senin - Kamis) | WFH (Jumat)</span>
            </div>
        </div>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
    @stack('scripts')
</body>
</html>

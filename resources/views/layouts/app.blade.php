<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIMAHUB') - Absensi MagangHub</title>
    
    <!-- Google Fonts: Inter -->
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
                            950: '#172554',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50 flex flex-col min-h-screen">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('mahasiswa.dashboard') }}" class="flex items-center space-x-2.5">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-700 to-blue-500 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                            <i data-lucide="calendar-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="text-xl font-bold tracking-tight text-slate-900 flex items-center gap-1.5">
                                SIMAHUB
                                <span class="text-[10px] uppercase tracking-wider font-semibold px-2 py-0.5 rounded-md bg-blue-100 text-blue-700">
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
                           class="px-3.5 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            Dasbor
                        </a>
                        <a href="{{ route('admin.mahasiswa.index') }}" 
                           class="px-3.5 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2 {{ request()->routeIs('admin.mahasiswa.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            Data Mahasiswa
                        </a>
                        <a href="{{ route('admin.attendance.index') }}" 
                           class="px-3.5 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2 {{ request()->routeIs('admin.attendance.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                            Rekap Absensi
                        </a>
                        <a href="{{ route('admin.settings.index') }}" 
                           class="px-3.5 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2 {{ request()->routeIs('admin.settings.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            Lokasi & Jam
                        </a>
                    @else
                        <a href="{{ route('mahasiswa.dashboard') }}" 
                           class="px-3.5 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2 {{ request()->routeIs('mahasiswa.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                            Presensi Hari Ini
                        </a>
                        <a href="{{ route('mahasiswa.history') }}" 
                           class="px-3.5 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2 {{ request()->routeIs('mahasiswa.history') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i data-lucide="history" class="w-4 h-4"></i>
                            Riwayat Absensi
                        </a>
                    @endif
                </nav>

                <!-- User Profile & Logout -->
                <div class="flex items-center space-x-4">
                    <div class="hidden sm:block text-right">
                        <div class="text-sm font-bold text-slate-800 leading-tight">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-slate-500 font-medium">
                            {{ auth()->user()->isAdmin() ? 'Administrator Instansi' : (auth()->user()->nim ?: auth()->user()->institution) }}
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')"
                                title="Keluar" 
                                class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Strip -->
        <div class="md:hidden border-t border-slate-200 bg-slate-50 px-4 py-2 flex items-center justify-around text-xs font-semibold">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'text-blue-600 font-bold' : 'text-slate-600' }} py-1 flex items-center gap-1">
                    <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i> Dasbor
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
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i> Presensi
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
                <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 flex items-start gap-3 shadow-xs">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0"></i>
                    <div>
                        <p class="font-semibold text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 rounded-xl bg-rose-50 border border-rose-200 p-4 text-rose-800 flex items-start gap-3 shadow-xs">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 mt-0.5 shrink-0"></i>
                    <div>
                        <p class="font-semibold text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-xl bg-amber-50 border border-amber-200 p-4 text-amber-900 shadow-xs">
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

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-slate-500">
            <div>
                &copy; {{ date('Y') }} <span class="font-bold text-slate-700">SIMAHUB</span> &mdash; Sistem Informasi Absensi MagangHub.
            </div>
            <div class="flex items-center gap-4">
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

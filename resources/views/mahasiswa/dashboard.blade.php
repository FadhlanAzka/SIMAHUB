@extends('layouts.app')

@section('title', 'Dasbor Presensi Mahasiswa')

@section('content')
<div class="space-y-6">

    <!-- Hero Card with Realtime Clock & Status -->
    <div class="bg-gradient-to-r from-blue-700 via-blue-600 to-blue-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-blue-900/10 relative overflow-hidden">
        
        <!-- Subtle Background Pattern -->
        <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-white/5 blur-2xl pointer-events-none"></div>
        <div class="absolute right-24 -bottom-16 w-64 h-64 rounded-full bg-blue-400/10 blur-xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-blue-100 text-xs font-semibold backdrop-blur-xs mb-3">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Portal Mahasiswa MagangHub</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Halo, {{ $user->name }}
                </h1>
                <p class="text-blue-100/90 text-sm mt-1 max-w-xl">
                    {{ $user->institution ?: 'Mahasiswa Magang' }} &bull; NIM: {{ $user->nim ?: '-' }}
                </p>
            </div>

            <!-- Realtime Clock Widget -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/20 text-center min-w-[220px]">
                <div class="text-xs uppercase tracking-wider text-blue-200 font-semibold" id="currentDayDate">
                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </div>
                <div class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white mt-1 tabular-nums font-mono" id="liveClock">
                    {{ \Carbon\Carbon::now()->format('H:i:s') }}
                </div>
                <div class="text-[11px] text-blue-200 mt-1 font-medium">
                    Waktu Indonesia Barat (WIB)
                </div>
            </div>
        </div>

        <!-- Rule & Work Mode Banner -->
        <div class="mt-6 pt-6 border-t border-white/15 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div class="flex items-center gap-2.5 bg-black/10 rounded-xl p-3">
                <div class="p-1.5 rounded-lg bg-white/10 text-blue-200">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-blue-200 font-medium block">Jam Masuk:</span>
                    <span class="font-bold text-white text-sm">Maks. {{ substr($office->work_start_time, 0, 5) }} WIB</span>
                    <span class="text-[10px] text-blue-200/80 block">(Lewat = Terlambat)</span>
                </div>
            </div>

            <div class="flex items-center gap-2.5 bg-black/10 rounded-xl p-3">
                <div class="p-1.5 rounded-lg bg-white/10 text-blue-200">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-blue-200 font-medium block">Jam Pulang:</span>
                    <span class="font-bold text-white text-sm">Min. {{ substr($office->work_end_time, 0, 5) }} WIB</span>
                    <span class="text-[10px] text-blue-200/80 block">(Bisa absen mulai 16:00)</span>
                </div>
            </div>

            <div class="flex items-center gap-2.5 bg-black/10 rounded-xl p-3">
                <div class="p-1.5 rounded-lg bg-white/10 text-blue-200">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-blue-200 font-medium block">Mode Hari Ini:</span>
                    @if($isWeekend)
                        <span class="font-bold text-amber-300 text-sm">Libur Akhir Pekan</span>
                    @elseif($isFriday)
                        <span class="font-bold text-purple-200 text-sm">WFH (Bebas dari Rumah)</span>
                    @else
                        <span class="font-bold text-emerald-300 text-sm">WFO (Radius {{ $office->radius_meters }}m)</span>
                    @endif
                    <span class="text-[10px] text-blue-200/80 block">Senin-Kamis WFO, Jumat WFH</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Action & GPS Geofencing Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Attendance Action Card (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between pb-5 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        <i data-lucide="check-square" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Presensi Hari Ini</h2>
                        <p class="text-xs text-slate-500">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>

                @if($todayAttendance)
                    <div class="flex items-center gap-2">
                        {!! $todayAttendance->status_badge !!}
                        {!! $todayAttendance->type_badge !!}
                    </div>
                @else
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                        Belum Absen
                    </span>
                @endif
            </div>

            <!-- GPS Live Detection Status -->
            <div class="my-5 p-4 rounded-xl border transition-all text-xs" id="gpsStatusBox">
                <div class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-blue-100 text-blue-700 shrink-0" id="gpsIconWrap">
                        <i data-lucide="navigation" class="w-4 h-4 animate-spin" id="gpsIcon"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800" id="gpsStatusTitle">Mendeteksi Lokasi GPS Anda...</span>
                            <span class="text-[11px] font-semibold text-slate-500" id="gpsDistanceText">Menghitung jarak...</span>
                        </div>
                        <p class="text-slate-500 mt-1" id="gpsStatusDesc">
                            Pastikan Anda mengizinkan akses lokasi (GPS) pada peramban/browser untuk melakukan absensi.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Grid -->
            @if($isWeekend)
                <div class="rounded-xl bg-amber-50 border border-amber-200 p-5 text-center my-4">
                    <i data-lucide="coffee" class="w-8 h-8 text-amber-600 mx-auto mb-2"></i>
                    <h3 class="text-sm font-bold text-amber-900">Akhir Pekan &bull; Hari Libur</h3>
                    <p class="text-xs text-amber-700 mt-1">
                        Presensi hanya dilakukan pada hari kerja (Senin s/d Jumat). Selamat beristirahat!
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                    
                    <!-- ABSEN MASUK BOX -->
                    <div class="p-5 rounded-2xl border {{ ($todayAttendance && $todayAttendance->check_in_time) ? 'bg-emerald-50/50 border-emerald-200' : 'bg-slate-50 border-slate-200' }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Absen Masuk</span>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-blue-100 text-blue-700">Maks 07:30</span>
                            </div>

                            @if($todayAttendance && $todayAttendance->check_in_time)
                                <div class="text-center py-3">
                                    <div class="text-2xl font-extrabold text-emerald-700 font-mono">
                                        {{ substr($todayAttendance->check_in_time, 0, 5) }} <span class="text-xs font-sans">WIB</span>
                                    </div>
                                    <p class="text-xs font-semibold text-emerald-800 mt-1">
                                        Status: {{ $todayAttendance->status === 'tepat_waktu' ? 'Tepat Waktu' : 'Terlambat' }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        {{ $todayAttendance->type }} &bull; {{ $todayAttendance->check_in_distance ? $todayAttendance->check_in_distance . 'm dari kantor' : 'GPS Tercatat' }}
                                    </p>
                                </div>
                            @else
                                <div class="text-slate-600 text-xs mb-4">
                                    Klik tombol di bawah ini untuk mencatat kehadiran masuk hari ini.
                                </div>
                            @endif
                        </div>

                        @if(!$todayAttendance || !$todayAttendance->check_in_time)
                            <form action="{{ route('mahasiswa.attendance.checkin') }}" method="POST" id="formCheckIn">
                                @csrf
                                <input type="hidden" name="latitude" class="inputLat">
                                <input type="hidden" name="longitude" class="inputLong">
                                
                                <button type="submit" 
                                        id="btnCheckIn" 
                                        disabled
                                        class="w-full py-3 px-4 rounded-xl text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed shadow-md shadow-blue-500/15 transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="log-in" class="w-4 h-4"></i>
                                    <span>Absen Masuk Sekarang</span>
                                </button>
                            </form>
                        @else
                            <div class="py-2.5 px-3 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-semibold text-center flex items-center justify-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-600"></i>
                                Sudah Absen Masuk
                            </div>
                        @endif
                    </div>

                    <!-- ABSEN PULANG BOX -->
                    <div class="p-5 rounded-2xl border {{ ($todayAttendance && $todayAttendance->check_out_time) ? 'bg-blue-50/50 border-blue-200' : 'bg-slate-50 border-slate-200' }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Absen Pulang</span>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-200 text-slate-700">Mulai 16:00</span>
                            </div>

                            @if($todayAttendance && $todayAttendance->check_out_time)
                                <div class="text-center py-3">
                                    <div class="text-2xl font-extrabold text-blue-700 font-mono">
                                        {{ substr($todayAttendance->check_out_time, 0, 5) }} <span class="text-xs font-sans">WIB</span>
                                    </div>
                                    <p class="text-xs font-semibold text-blue-800 mt-1">
                                        Total Kerja: {{ $todayAttendance->duration }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Selesai Bertugas</p>
                                </div>
                            @elseif(!$todayAttendance || !$todayAttendance->check_in_time)
                                <div class="text-slate-500 text-xs mb-4">
                                    Silakan lakukan absen masuk terlebih dahulu sebelum dapat absen pulang.
                                </div>
                            @else
                                <div class="text-slate-600 text-xs mb-4">
                                    Dapat dilakukan mulai pukul 16:00 WIB setelah menyelesaikan tugas hari ini.
                                </div>
                            @endif
                        </div>

                        @if($todayAttendance && $todayAttendance->check_in_time && !$todayAttendance->check_out_time)
                            <form action="{{ route('mahasiswa.attendance.checkout') }}" method="POST" id="formCheckOut">
                                @csrf
                                <input type="hidden" name="latitude" class="inputLat">
                                <input type="hidden" name="longitude" class="inputLong">

                                <button type="submit" 
                                        id="btnCheckOut"
                                        class="w-full py-3 px-4 rounded-xl text-xs sm:text-sm font-bold text-white bg-slate-800 hover:bg-slate-900 disabled:bg-slate-300 disabled:cursor-not-allowed shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    <span>Absen Pulang</span>
                                </button>
                            </form>
                        @elseif($todayAttendance && $todayAttendance->check_out_time)
                            <div class="py-2.5 px-3 rounded-xl bg-blue-100 text-blue-800 text-xs font-semibold text-center flex items-center justify-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-blue-600"></i>
                                Sudah Absen Pulang
                            </div>
                        @else
                            <button disabled class="w-full py-3 px-4 rounded-xl text-xs font-bold text-slate-400 bg-slate-200 cursor-not-allowed flex items-center justify-center gap-2">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                                <span>Menunggu Absen Masuk</span>
                            </button>
                        @endif
                    </div>

                </div>
            @endif

        </div>

        <!-- Monthly Summary Widget (1 Col) -->
        <div class="space-y-4">
            
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center justify-between">
                    <span>Statistik Bulan Ini</span>
                    <span class="text-xs font-semibold text-blue-600">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
                </h3>

                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="p-3.5 rounded-xl bg-blue-50 border border-blue-100">
                        <div class="text-2xl font-extrabold text-blue-700">{{ $stats['total_hadir'] }}</div>
                        <div class="text-[11px] font-semibold text-blue-800 mt-0.5">Total Hadir</div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-100">
                        <div class="text-2xl font-extrabold text-emerald-700">{{ $stats['tepat_waktu'] }}</div>
                        <div class="text-[11px] font-semibold text-emerald-800 mt-0.5">Tepat Waktu</div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-100">
                        <div class="text-2xl font-extrabold text-rose-700">{{ $stats['terlambat'] }}</div>
                        <div class="text-[11px] font-semibold text-rose-800 mt-0.5">Terlambat</div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-purple-50 border border-purple-100">
                        <div class="text-2xl font-extrabold text-purple-700">{{ $stats['wfh'] }}</div>
                        <div class="text-[11px] font-semibold text-purple-800 mt-0.5">WFH (Jumat)</div>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 text-center">
                    <a href="{{ route('mahasiswa.history') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1">
                        <span>Lihat Riwayat Lengkap</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>

            <!-- Information Card -->
            <div class="bg-slate-100/80 rounded-2xl p-5 border border-slate-200 text-xs text-slate-600 space-y-2">
                <div class="font-bold text-slate-800 flex items-center gap-1.5 text-xs">
                    <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                    Informasi Instansi
                </div>
                <p><strong>Kantor:</strong> {{ $office->office_name }}</p>
                <p><strong>Radius Absen WFO:</strong> {{ $office->radius_meters }} meter</p>
                <p><strong>Ketentuan Jumat:</strong> WFH (Diizinkan dari kediaman masing-masing).</p>
            </div>

        </div>

    </div>

    <!-- Recent 5 Attendances Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Riwayat Kehadiran Terakhir</h3>
            <a href="{{ route('mahasiswa.history') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
                Semua Riwayat &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3">Tanggal & Hari</th>
                        <th class="px-6 py-3">Tipe</th>
                        <th class="px-6 py-3">Masuk</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Pulang</th>
                        <th class="px-6 py-3">Durasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentAttendances as $att)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-3.5 font-medium text-slate-900">
                                {{ \Carbon\Carbon::parse($att->date)->translatedFormat('l, d M Y') }}
                            </td>
                            <td class="px-6 py-3.5">
                                {!! $att->type_badge !!}
                            </td>
                            <td class="px-6 py-3.5 font-mono text-slate-800">
                                {{ $att->check_in_time ? substr($att->check_in_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-3.5">
                                {!! $att->status_badge !!}
                            </td>
                            <td class="px-6 py-3.5 font-mono text-slate-800">
                                {{ $att->check_out_time ? substr($att->check_out_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-3.5 text-slate-600">
                                {{ $att->duration }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                Belum ada riwayat absensi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
    // Live Digital Clock (Client-Side synced)
    function updateLiveClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const clockEl = document.getElementById('liveClock');
        if (clockEl) {
            clockEl.textContent = `${hours}:${minutes}:${seconds}`;
        }
    }
    setInterval(updateLiveClock, 1000);

    // Office Configuration from Server
    const OFFICE_LAT = {{ (float) $office->latitude }};
    const OFFICE_LON = {{ (float) $office->longitude }};
    const OFFICE_RADIUS = {{ (int) $office->radius_meters }};
    const IS_FRIDAY = {{ $isFriday ? 'true' : 'false' }};
    const IS_WEEKEND = {{ $isWeekend ? 'true' : 'false' }};
    const WORK_END_TIME = "{{ substr($office->work_end_time, 0, 5) }}";

    // Haversine Formula (Calculates meters between two coords)
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // radius of Earth in meters
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return Math.round(R * c);
    }

    // Geolocation Handling
    const gpsBox = document.getElementById('gpsStatusBox');
    const gpsIcon = document.getElementById('gpsIcon');
    const gpsIconWrap = document.getElementById('gpsIconWrap');
    const gpsTitle = document.getElementById('gpsStatusTitle');
    const gpsDesc = document.getElementById('gpsStatusDesc');
    const gpsDist = document.getElementById('gpsDistanceText');
    const btnCheckIn = document.getElementById('btnCheckIn');
    const btnCheckOut = document.getElementById('btnCheckOut');
    const latInputs = document.querySelectorAll('.inputLat');
    const lonInputs = document.querySelectorAll('.inputLong');

    function applyCoordinates(lat, lon) {
        latInputs.forEach(i => i.value = lat);
        lonInputs.forEach(i => i.value = lon);

        const dist = calculateDistance(lat, lon, OFFICE_LAT, OFFICE_LON);
        gpsDist.textContent = `~${dist} meter dari kantor`;

        if (IS_FRIDAY) {
            // Jumat WFH: Bypass radius
            gpsBox.className = "my-5 p-4 rounded-xl border bg-purple-50 border-purple-200 text-xs";
            gpsIconWrap.className = "p-2 rounded-lg bg-purple-100 text-purple-700 shrink-0";
            gpsIcon.setAttribute('data-lucide', 'home');
            gpsTitle.textContent = "Mode WFH Aktif (Hari Jumat)";
            gpsDesc.textContent = `Lokasi GPS terdeteksi (${dist}m dari kantor). Hari Jumat Anda diperbolehkan absen dari kediaman/rumah.`;
            if (btnCheckIn) btnCheckIn.disabled = false;
        } else if (!IS_WEEKEND) {
            // Senin - Kamis WFO: Check radius
            if (dist <= OFFICE_RADIUS) {
                gpsBox.className = "my-5 p-4 rounded-xl border bg-emerald-50 border-emerald-200 text-xs";
                gpsIconWrap.className = "p-2 rounded-lg bg-emerald-100 text-emerald-700 shrink-0";
                gpsIcon.setAttribute('data-lucide', 'check-circle-2');
                gpsTitle.textContent = "Dalam Lingkungan Instansi (Valid WFO)";
                gpsDesc.textContent = `Anda terdeteksi berada di radius ${dist}m dari kantor (Batas toleransi: ${OFFICE_RADIUS}m). Anda dapat melakukan absensi.`;
                if (btnCheckIn) btnCheckIn.disabled = false;
            } else {
                gpsBox.className = "my-5 p-4 rounded-xl border bg-rose-50 border-rose-200 text-xs";
                gpsIconWrap.className = "p-2 rounded-lg bg-rose-100 text-rose-700 shrink-0";
                gpsIcon.setAttribute('data-lucide', 'map-pin-off');
                gpsTitle.textContent = "Di Luar Area Kantor Instansi";
                gpsDesc.textContent = `Jarak Anda (${dist}m) melebihi batas radius kantor (${OFFICE_RADIUS}m). Pada hari Senin-Kamis, absensi wajib dilakukan di lingkungan kantor.`;
                if (btnCheckIn) btnCheckIn.disabled = true;
            }
        }
        lucide.createIcons();
    }

    function initGeolocation() {
        if (!navigator.geolocation) {
            gpsTitle.textContent = "Peramban Tidak Mendukung Geolocation";
            gpsDesc.textContent = "Gunakan browser modern seperti Google Chrome atau Microsoft Edge untuk melakukan absensi.";
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                applyCoordinates(pos.coords.latitude, pos.coords.longitude);
            },
            (err) => {
                gpsBox.className = "my-5 p-4 rounded-xl border bg-amber-50 border-amber-200 text-xs";
                gpsIconWrap.className = "p-2 rounded-lg bg-amber-100 text-amber-700 shrink-0";
                gpsIcon.setAttribute('data-lucide', 'alert-triangle');
                gpsTitle.textContent = "Akses Lokasi GPS Ditolak / Tidak Aktif";
                gpsDesc.textContent = "Harap izinkan akses lokasi (GPS) pada peramban Anda agar sistem dapat memvalidasi posisi absensi Anda.";
                lucide.createIcons();
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    document.addEventListener('DOMContentLoaded', () => {
        initGeolocation();
    });
</script>
@endpush
@endsection

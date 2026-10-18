@extends('layouts.app')

@section('title', 'Dashboard Presensi Peserta Magang')

@section('content')
<div class="space-y-6">

    <!-- Hero Card with Realtime Clock & Status -->
    <div class="bg-gradient-to-tr from-blue-800 via-blue-700 to-indigo-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-blue-900/15 relative overflow-hidden">
        
        <!-- Ambient Background Glows -->
        <div class="absolute -right-10 -top-10 w-72 h-72 rounded-full bg-blue-400/20 blur-3xl pointer-events-none"></div>
        <div class="absolute right-32 -bottom-16 w-60 h-60 rounded-full bg-indigo-500/20 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-blue-100 text-xs font-bold backdrop-blur-md border border-white/10 max-w-full">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                        <span class="truncate">Posisi: {{ $user->position ?: 'Peserta Magang' }}</span>
                    </div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 text-blue-100 text-xs font-bold backdrop-blur-md border border-white/10">
                        <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                        <span>Mentor: {{ $user->mentor ? $user->mentor->name : 'Belum ditentukan' }}</span>
                    </div>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">
                    Halo, {{ $user->name }}
                </h1>

            </div>

            <!-- Realtime Digital Clock Widget -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/20 text-center min-w-[220px] shadow-inner">
                <div class="text-[11px] uppercase tracking-widest text-blue-200 font-bold" id="currentDayDate">
                    {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                </div>
                <div class="text-3xl sm:text-4xl font-black tracking-tight text-white mt-1 tabular-nums font-mono drop-shadow-xs" id="liveClock">
                    {{ \Carbon\Carbon::now()->format('H:i:s') }}
                </div>
                <div class="text-[10px] uppercase tracking-wider text-blue-200 mt-1 font-semibold">
                    Waktu Indonesia Barat (WIB)
                </div>
            </div>
        </div>

        <!-- Rule & Work Mode Banner -->
        <div class="mt-6 pt-6 border-t border-white/15 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div class="flex items-center gap-3 bg-black/15 backdrop-blur-xs rounded-2xl p-3.5 border border-white/10">
                <div class="p-2 rounded-xl bg-white/10 text-blue-200 shrink-0">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-blue-200/90 text-[11px] font-medium block">Batas Masuk (Hari {{ $todayDayName }}):</span>
                    <span class="font-extrabold text-white text-sm">Maks. {{ substr($todayStartLimit, 0, 5) }} WIB</span>
                    <span class="text-[10px] text-blue-200/75 block">(Lewat = Terlambat)</span>
                </div>
            </div>

            <div class="flex items-center gap-3 bg-black/15 backdrop-blur-xs rounded-2xl p-3.5 border border-white/10">
                <div class="p-2 rounded-xl bg-white/10 text-blue-200 shrink-0">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-blue-200/90 text-[11px] font-medium block">Jam Pulang (Hari {{ $todayDayName }}):</span>
                    <span class="font-extrabold text-white text-sm">Min. {{ substr($todayEndLimit, 0, 5) }} WIB</span>
                    <span class="text-[10px] text-blue-200/75 block">(Dibuka mulai {{ substr($todayEndLimit, 0, 5) }})</span>
                </div>
            </div>

            <div class="flex items-center gap-3 bg-black/15 backdrop-blur-xs rounded-2xl p-3.5 border border-white/10">
                <div class="p-2 rounded-xl bg-white/10 text-blue-200 shrink-0">
                    <i data-lucide="navigation-2" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-blue-200/90 text-[11px] font-medium block">Ketentuan Hari {{ $todayDayName }}:</span>
                    @if($isWeekend)
                        <span class="font-black text-amber-300 text-sm">Libur Akhir Pekan</span>
                    @elseif($isWfhDay)
                        <span class="font-black text-purple-200 text-sm">WFH (Bebas dari Rumah)</span>
                    @else
                        <span class="font-black text-emerald-300 text-sm">WFO (Radius {{ $office->radius_meters }}m)</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Attendance Reminder Alert Banner -->
    @php
        $nowTimeStr = \Carbon\Carbon::now()->format('H:i');
        $hasCheckedIn = $todayAttendance && $todayAttendance->check_in_time;
        $hasCheckedOut = $todayAttendance && $todayAttendance->check_out_time;
    @endphp

    <div id="attendanceReminderBanner" class="transition-all duration-300">
        @if($isWeekend)
            <div class="rounded-2xl bg-amber-50 border border-amber-200/80 p-4 sm:p-5 flex items-start sm:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <i data-lucide="coffee" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-amber-900">Libur Akhir Pekan</h3>
                        <p class="text-xs text-amber-700 mt-0.5">Hari ini libur magang. Sistem presensi dan pengingat akan aktif kembali pada hari kerja berikutnya pukul 07:30 WIB.</p>
                    </div>
                </div>
            </div>
        @elseif(!$hasCheckedIn && $nowTimeStr < substr($todayStartLimit, 0, 5))
            <div class="rounded-2xl bg-blue-50/90 border border-blue-200/90 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-start sm:items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                        <i data-lucide="bell-ring" class="w-5 h-5 animate-bounce"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xs sm:text-sm font-bold text-blue-950">Pengingat Presensi Masuk (Hari {{ $todayDayName }})</h3>
                            <span class="px-2 py-0.5 rounded-full bg-blue-200 text-blue-800 text-[10px] font-black uppercase tracking-wide">Penting</span>
                        </div>
                        <p class="text-xs text-blue-800/90 mt-0.5 leading-relaxed">
                            Batas waktu presensi masuk adalah pukul <strong class="font-black text-blue-950 font-mono">{{ substr($todayStartLimit, 0, 5) }} WIB</strong> (Ketentuan: <strong>{{ $workType }}</strong>). Segera lakukan absensi masuk agar tercatat <strong>Tepat Waktu</strong>.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="#formCheckIn" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>Absen Masuk</span>
                    </a>
                </div>
            </div>
        @elseif(!$hasCheckedIn && $nowTimeStr >= substr($todayStartLimit, 0, 5))
            <div class="rounded-2xl bg-rose-50/90 border border-rose-200/90 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-start sm:items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-500/20">
                        <i data-lucide="alert-triangle" class="w-5 h-5 animate-pulse"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xs sm:text-sm font-bold text-rose-950">Peringatan: Belum Melakukan Presensi Masuk</h3>
                            <span class="px-2 py-0.5 rounded-full bg-rose-200 text-rose-900 text-[10px] font-black uppercase tracking-wide">Terlambat</span>
                        </div>
                        <p class="text-xs text-rose-800 mt-0.5 leading-relaxed">
                            Batas waktu masuk pukul <strong class="font-black text-rose-950 font-mono">{{ substr($todayStartLimit, 0, 5) }} WIB</strong> telah lewat. Segera lakukan presensi masuk sekarang agar durasi keterlambatan Anda tidak terus bertambah!
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="#formCheckIn" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>Absen Sekarang</span>
                    </a>
                </div>
            </div>
        @elseif($hasCheckedIn && !$hasCheckedOut && $nowTimeStr < substr($todayEndLimit, 0, 5))
            <div class="rounded-2xl bg-indigo-50/70 border border-indigo-200/80 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-start sm:items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-indigo-500/20">
                        <i data-lucide="briefcase" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xs sm:text-sm font-bold text-indigo-950">Jam Kerja Hari {{ $todayDayName }} Sedang Berlangsung</h3>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">Masuk: {{ substr($todayAttendance->check_in_time, 0, 5) }} WIB</span>
                        </div>
                        <p class="text-xs text-indigo-800 mt-0.5 leading-relaxed">
                            Presensi masuk terekam dengan baik. Sesuai jam operasional hari <strong>{{ $todayDayName }}</strong>, presensi pulang akan dibuka mulai pukul <strong class="font-black text-indigo-950 font-mono">{{ substr($todayEndLimit, 0, 5) }} WIB</strong>.
                        </p>
                    </div>
                </div>
                <div class="text-xs font-bold text-indigo-700 font-mono bg-white/80 px-3 py-1.5 rounded-xl border border-indigo-200/80 shrink-0">
                    Pulang: {{ substr($todayEndLimit, 0, 5) }} WIB
                </div>
            </div>
        @elseif($hasCheckedIn && !$hasCheckedOut && $nowTimeStr >= substr($todayEndLimit, 0, 5))
            <div class="rounded-2xl bg-purple-50/90 border border-purple-200/90 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
                <div class="flex items-start sm:items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-purple-700 text-white flex items-center justify-center shrink-0 shadow-md shadow-purple-600/20">
                        <i data-lucide="clock" class="w-5 h-5 animate-pulse"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xs sm:text-sm font-bold text-purple-950">Waktunya Presensi Pulang (Hari {{ $todayDayName }})</h3>
                            <span class="px-2 py-0.5 rounded-full bg-purple-200 text-purple-900 text-[10px] font-black uppercase tracking-wide">Jam Pulang Tiba</span>
                        </div>
                        <p class="text-xs text-purple-800 mt-0.5 leading-relaxed">
                            Jam kerja hari ini telah selesai (pukul <strong class="font-black text-purple-950 font-mono">{{ substr($todayEndLimit, 0, 5) }} WIB</strong>). Jangan lupa lakukan <strong>Absen Pulang</strong> sekarang sebelum meninggalkan instansi atau menutup aktivitas magang Anda!
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="#formCheckOut" class="px-4 py-2 rounded-xl bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>Absen Pulang Sekarang</span>
                    </a>
                </div>
            </div>
        @else
            <div class="rounded-2xl bg-emerald-50/90 border border-emerald-200/80 p-4 sm:p-5 flex items-center justify-between gap-4 shadow-xs">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/20">
                        <i data-lucide="check-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-emerald-950">Presensi Hari Ini Lengkap</h3>
                        <p class="text-xs text-emerald-800 mt-0.5">
                            Anda telah menyelesaikan absen masuk ({{ substr($todayAttendance->check_in_time, 0, 5) }} WIB) dan pulang ({{ substr($todayAttendance->check_out_time, 0, 5) }} WIB) dengan durasi kerja <strong>{{ $todayAttendance->duration }}</strong>. Terima kasih atas dedikasi Anda hari ini!
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Attendance Action & GPS Geofencing Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Attendance Action Card (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between pb-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
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
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                        Belum Presensi
                    </span>
                @endif
            </div>

            <!-- Modern GPS Live Detection Status -->
            <div class="my-5 p-4 rounded-2xl border transition-all text-xs" id="gpsStatusBox">
                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-blue-100 text-blue-700 shrink-0" id="gpsIconWrap">
                        <i data-lucide="navigation" class="w-4 h-4 animate-spin" id="gpsIcon"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                            <span class="font-bold text-slate-900 text-sm" id="gpsStatusTitle">Mendeteksi Posisi GPS...</span>
                            <span class="text-xs font-bold text-slate-600 font-mono" id="gpsDistanceText">Menghitung jarak...</span>
                        </div>
                        <p class="text-slate-500 mt-1" id="gpsStatusDesc">
                            Pastikan Anda mengizinkan akses lokasi (GPS) pada peramban peranti Anda untuk presensi.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Grid -->
            @if($isWeekend)
                <div class="rounded-2xl bg-amber-50 border border-amber-200/80 p-6 text-center my-4">
                    <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-2.5">
                        <i data-lucide="coffee" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-amber-900">Hari Libur Akhir Pekan</h3>
                    <p class="text-xs text-amber-700 mt-1 max-w-md mx-auto">
                        Presensi magang hanya berlaku pada hari kerja (Senin s/d Jumat). Selamat menikmati akhir pekan Anda!
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                    
                    <!-- ABSEN MASUK BOX -->
                    <div class="p-6 rounded-2xl border {{ ($todayAttendance && $todayAttendance->check_in_time) ? 'bg-emerald-50/40 border-emerald-200' : 'bg-slate-50/80 border-slate-200' }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Absen Masuk</span>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">Maks {{ substr($todayStartLimit, 0, 5) }}</span>
                            </div>

                            @if($todayAttendance && $todayAttendance->check_in_time)
                                <div class="text-center py-4">
                                    <div class="text-3xl font-black text-emerald-600 font-mono">
                                        {{ substr($todayAttendance->check_in_time, 0, 5) }} <span class="text-xs font-sans font-bold">WIB</span>
                                    </div>
                                    <p class="text-xs font-bold text-emerald-800 mt-1.5">
                                        Status: {{ $todayAttendance->status === 'tepat_waktu' ? 'Tepat Waktu' : 'Terlambat' }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        {{ $todayAttendance->type }} &bull; {{ $todayAttendance->check_in_distance ? $todayAttendance->check_in_distance . 'm dari kantor' : 'GPS Terekam' }}
                                    </p>
                                </div>
                            @else
                                <div class="text-slate-600 text-xs mb-4 leading-relaxed">
                                    Pastikan berada di {{ $isWfhDay ? 'kediaman (WFH Bebas Lokasi)' : 'area kantor instansi (Radius ' . $office->radius_meters . 'm)' }}, lalu tekan tombol di bawah ini.
                                </div>
                            @endif
                        </div>

                        @if(!$todayAttendance || !$todayAttendance->check_in_time)
                            <form action="{{ route('intern.attendance.checkin') }}" method="POST" id="formCheckIn">
                                @csrf
                                <input type="hidden" name="latitude" class="inputLat">
                                <input type="hidden" name="longitude" class="inputLong">
                                
                                <button type="submit" 
                                        id="btnCheckIn" 
                                        disabled
                                        class="w-full py-3.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-[0.99] disabled:bg-slate-300 disabled:cursor-not-allowed shadow-md shadow-blue-500/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="log-in" class="w-4 h-4"></i>
                                    <span>Absen Masuk Sekarang</span>
                                </button>
                            </form>
                        @else
                            <div class="py-3 px-3 rounded-xl bg-emerald-100/80 text-emerald-800 text-xs font-bold text-center flex items-center justify-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-600"></i>
                                Presensi Masuk Selesai
                            </div>
                        @endif
                    </div>

                    <!-- ABSEN PULANG BOX -->
                    <div class="p-6 rounded-2xl border {{ ($todayAttendance && $todayAttendance->check_out_time) ? 'bg-blue-50/40 border-blue-200' : 'bg-slate-50/80 border-slate-200' }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Absen Pulang</span>
                                <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-700">Mulai {{ substr($todayEndLimit, 0, 5) }}</span>
                            </div>

                            @if($todayAttendance && $todayAttendance->check_out_time)
                                <div class="text-center py-4">
                                    <div class="text-3xl font-black text-blue-600 font-mono">
                                        {{ substr($todayAttendance->check_out_time, 0, 5) }} <span class="text-xs font-sans font-bold">WIB</span>
                                    </div>
                                    <p class="text-xs font-bold text-blue-800 mt-1.5">
                                        Durasi Kerja: {{ $todayAttendance->duration }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">Tugas Hari Ini Selesai</p>
                                </div>
                            @elseif(!$todayAttendance || !$todayAttendance->check_in_time)
                                <div class="text-slate-500 text-xs mb-4 leading-relaxed">
                                    Lakukan absen masuk terlebih dahulu pada pagi hari sebelum Anda dapat absen pulang.
                                </div>
                            @else
                                <div class="text-slate-600 text-xs mb-4 leading-relaxed">
                                    Dapat dilakukan mulai pukul {{ substr($todayEndLimit, 0, 5) }} WIB setelah menyelesaikan jam kerja Anda hari ini.
                                </div>
                            @endif
                        </div>

                        @if($todayAttendance && $todayAttendance->check_in_time && !$todayAttendance->check_out_time)
                            <form action="{{ route('intern.attendance.checkout') }}" method="POST" id="formCheckOut">
                                @csrf
                                <input type="hidden" name="latitude" class="inputLat">
                                <input type="hidden" name="longitude" class="inputLong">

                                <button type="submit" 
                                        id="btnCheckOut"
                                        class="w-full py-3.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-white bg-slate-900 hover:bg-black active:scale-[0.99] disabled:bg-slate-300 disabled:cursor-not-allowed shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    <span>Absen Pulang</span>
                                </button>
                            </form>
                        @elseif($todayAttendance && $todayAttendance->check_out_time)
                            <div class="py-3 px-3 rounded-xl bg-blue-100/80 text-blue-800 text-xs font-bold text-center flex items-center justify-center gap-1.5">
                                <i data-lucide="check" class="w-4 h-4 text-blue-600"></i>
                                Presensi Pulang Selesai
                            </div>
                        @else
                            <button disabled class="w-full py-3.5 px-4 rounded-xl text-xs font-bold text-slate-400 bg-slate-200 cursor-not-allowed flex items-center justify-center gap-2">
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
            
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
                <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center justify-between">
                    <span>Statistik Bulan Ini</span>
                    <span class="text-xs font-bold text-blue-600">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
                </h3>

                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="p-3.5 rounded-2xl bg-blue-50/80 border border-blue-100">
                        <div class="text-2xl font-black text-blue-600">{{ $stats['total_hadir'] }}</div>
                        <div class="text-[11px] font-bold text-blue-900 mt-0.5">Total Hadir</div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-emerald-50/80 border border-emerald-100">
                        <div class="text-2xl font-black text-emerald-600">{{ $stats['tepat_waktu'] }}</div>
                        <div class="text-[11px] font-bold text-emerald-900 mt-0.5">Tepat Waktu</div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-rose-50/80 border border-rose-100">
                        <div class="text-2xl font-black text-rose-600">{{ $stats['terlambat'] }}</div>
                        <div class="text-[11px] font-bold text-rose-900 mt-0.5">Terlambat</div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-purple-50/80 border border-purple-100">
                        <div class="text-2xl font-black text-purple-600">{{ $stats['wfh'] }}</div>
                        <div class="text-[11px] font-bold text-purple-900 mt-0.5">WFH (Bebas Lokasi)</div>
                    </div>
                    <div class="col-span-2 p-3 rounded-2xl bg-amber-50/80 border border-amber-200/80 flex items-center justify-between px-4 text-left">
                        <div class="flex items-center gap-2">
                            <i data-lucide="timer" class="w-4 h-4 text-amber-600"></i>
                            <span class="text-xs font-bold text-amber-900">Akumulasi Terlambat</span>
                        </div>
                        <span class="text-xs font-black text-amber-700 font-mono">{{ $stats['accumulated_late_text'] }}</span>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100 text-center">
                    <a href="{{ route('intern.history') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1">
                        <span>Lihat Riwayat Lengkap</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>

            <!-- Information Card -->
            <div class="bg-slate-100/70 rounded-3xl p-5 border border-slate-200/80 text-xs text-slate-600 space-y-2">
                <div class="font-bold text-slate-900 flex items-center gap-2 text-xs">
                    <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                    Informasi Instansi
                </div>
                <p><strong>Instansi:</strong> {{ $office->office_name }}</p>
                <p><strong>Radius WFO:</strong> {{ $office->radius_meters }} meter</p>
                <p><strong>Hari WFH:</strong> {{ $office->wfh_days_text }}</p>
            </div>

            <!-- Weekly Work & WFH Schedule Card -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                <div class="font-bold text-slate-900 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <i data-lucide="calendar-clock" class="w-4 h-4 text-blue-600"></i>
                        <span>Jadwal Kerja & WFH Mingguan</span>
                    </div>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @foreach($weeklySchedule as $ws)
                        <div class="py-2.5 flex items-center justify-between {{ $ws['is_today'] ? 'bg-blue-50/70 -mx-2 px-2.5 rounded-xl font-bold text-blue-900' : 'text-slate-600' }}">
                            <div class="flex items-center gap-2">
                                <span>{{ $ws['day_name'] }}</span>
                                @if($ws['is_today'])
                                    <span class="text-[9px] px-1.5 py-0.5 rounded-full bg-blue-600 text-white font-black tracking-wider">HARI INI</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="font-mono text-[11px]">{{ $ws['start'] }} - {{ $ws['end'] }}</span>
                                @if($ws['is_wfh'])
                                    <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-800 text-[10px] font-bold">WFH</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-medium">WFO</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Attendance Notification Control Card -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs space-y-3">
                <div class="font-bold text-slate-900 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <i data-lucide="bell" class="w-4 h-4 text-blue-600"></i>
                        <span>Pengingat Presensi Otomatis</span>
                    </div>
                    <span id="notifStatusBadge" class="text-[10px] px-2.5 py-0.5 rounded-full font-bold bg-slate-100 text-slate-600">
                        Mengecek...
                    </span>
                </div>
                <p class="text-slate-500 text-[11px] leading-relaxed">
                    Sistem akan otomatis mengirimkan notifikasi peramban dan nada pengingat sesuai jadwal operasional instansi:
                </p>
                <div class="grid grid-cols-2 gap-2 text-[11px] bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                    <div class="flex flex-col">
                        <span class="text-slate-400 text-[10px] uppercase font-bold">Batas Masuk</span>
                        <span class="font-bold text-slate-800 font-mono">{{ substr($todayStartLimit, 0, 5) }} WIB</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-slate-400 text-[10px] uppercase font-bold">Jam Pulang</span>
                        <span class="font-bold text-slate-800 font-mono">{{ substr($todayEndLimit, 0, 5) }} WIB</span>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 pt-1">
                    <button type="button" id="btnEnableNotif" class="flex-1 py-2 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="bell-ring" class="w-3.5 h-3.5"></i>
                        <span id="btnEnableNotifText">Aktifkan Pengingat</span>
                    </button>
                    <button type="button" id="btnTestNotif" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer" title="Coba bunyi dan notifikasi">
                        <i data-lucide="volume-2" class="w-3.5 h-3.5"></i>
                        <span>Uji Coba</span>
                    </button>
                </div>
            </div>

        </div>

    </div>

    <!-- Recent 5 Attendances Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">Riwayat Kehadiran Terakhir</h3>
            <a href="{{ route('intern.history') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                Semua Riwayat &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100 text-[11px]">
                    <tr>
                        <th class="px-6 py-3">Tanggal & Hari</th>
                        <th class="px-6 py-3">Tipe</th>
                        <th class="px-6 py-3">Masuk</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Pulang</th>
                        <th class="px-6 py-3 relative group" style="cursor: help;">
                            <div class="flex items-center gap-1.5">
                                Durasi
                                <i data-lucide="info" class="w-3.5 h-3.5 text-slate-400"></i>
                            </div>
                            <!-- Custom Tooltip (Bottom) -->
                            <div class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-max max-w-[200px] whitespace-normal px-2.5 py-1.5 bg-slate-800 text-white text-[10px] leading-relaxed font-medium rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 shadow-lg pointer-events-none text-center">
                                Total jam sudah dikurangi 1 jam waktu istirahat
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 border-4 border-transparent border-b-slate-800"></div>
                            </div>
                        </th>
                        <th class="px-6 py-3 whitespace-nowrap">Total Durasi Terlambat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentAttendances as $att)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-3.5 font-bold text-slate-900">
                                {{ \Carbon\Carbon::parse($att->date)->translatedFormat('l, d M Y') }}
                            </td>
                            <td class="px-6 py-3.5">
                                {!! $att->type_badge !!}
                            </td>
                            <td class="px-6 py-3.5 font-mono text-slate-800 font-medium">
                                {{ $att->check_in_time ? substr($att->check_in_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-3.5">
                                {!! $att->status_badge !!}
                            </td>
                            <td class="px-6 py-3.5 font-mono text-slate-800 font-medium">
                                {{ $att->check_out_time ? substr($att->check_out_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-3.5 text-slate-600 font-medium">
                                {{ $att->duration }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <span class="font-mono font-bold text-slate-800">{{ $att->user_total_late_text ?? '0 Menit' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">
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
    // Live Digital Clock
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
    const IS_WFH_DAY = {{ $isWfhDay ? 'true' : 'false' }};
    const IS_WEEKEND = {{ $isWeekend ? 'true' : 'false' }};
    const WORK_END_TIME = "{{ substr($office->work_end_time, 0, 5) }}";

    // Haversine Formula (Calculates distance in meters)
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return Math.round(R * c);
    }

    // Elements
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

        if (!IS_WEEKEND) {
            // WFO Mode
            if (dist <= OFFICE_RADIUS) {
                gpsBox.className = "my-5 p-4 rounded-2xl border bg-emerald-50/80 border-emerald-200 text-xs";
                gpsIconWrap.className = "p-2.5 rounded-xl bg-emerald-100 text-emerald-700 shrink-0";
                gpsIcon.setAttribute('data-lucide', 'check-circle-2');
                gpsTitle.textContent = "Dalam Lingkungan Instansi (Valid WFO)";
                gpsDesc.textContent = `Anda terdeteksi berada di radius ${dist}m dari kantor (Batas: ${OFFICE_RADIUS}m). Anda dapat melakukan presensi masuk.`;
                if (btnCheckIn) btnCheckIn.disabled = false;
            } else {
                gpsBox.className = "my-5 p-4 rounded-2xl border bg-rose-50/80 border-rose-200 text-xs";
                gpsIconWrap.className = "p-2.5 rounded-xl bg-rose-100 text-rose-700 shrink-0";
                gpsIcon.setAttribute('data-lucide', 'map-pin-off');
                gpsTitle.textContent = "Di Luar Area Kantor Instansi";
                gpsDesc.textContent = `Jarak Anda (${dist}m) melebihi batas radius kantor (${OFFICE_RADIUS}m). Pada hari kerja WFO, presensi wajib di lingkungan kantor.`;
                if (btnCheckIn) btnCheckIn.disabled = true;
            }
        }
        lucide.createIcons();
    }

    function initGeolocation() {
        if (IS_WFH_DAY) {
            gpsBox.className = "my-5 p-4 rounded-2xl border bg-purple-50/80 border-purple-200 text-xs";
            gpsIconWrap.className = "p-2.5 rounded-xl bg-purple-100 text-purple-700 shrink-0";
            gpsIcon.setAttribute('data-lucide', 'home');
            gpsTitle.textContent = "Mode WFH Aktif";
            gpsDesc.textContent = `Hari ini adalah hari WFH. Anda diperbolehkan presensi dari rumah tanpa verifikasi lokasi GPS.`;
            gpsDist.textContent = "-";
            if (btnCheckIn) btnCheckIn.disabled = false;
            lucide.createIcons();
            return; // Skip GPS finding entirely!
        }
        if (!navigator.geolocation) {
            gpsTitle.textContent = "Peramban Tidak Mendukung Geolocation";
            gpsDesc.textContent = "Gunakan browser modern seperti Google Chrome atau Microsoft Edge.";
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                applyCoordinates(pos.coords.latitude, pos.coords.longitude);
            },
            (err) => {
                gpsBox.className = "my-5 p-4 rounded-2xl border bg-amber-50/80 border-amber-200 text-xs";
                gpsIconWrap.className = "p-2.5 rounded-xl bg-amber-100 text-amber-700 shrink-0";
                gpsIcon.setAttribute('data-lucide', 'alert-triangle');
                gpsTitle.textContent = "Akses GPS Perlu Diizinkan";
                gpsDesc.textContent = "Klik 'Allow / Izinkan' pada notifikasi akses lokasi browser Anda.";
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

@extends('layouts.app')

@section('title', 'Dashboard Administrator')

@section('content')
<div class="space-y-6">

    <!-- Top Greeting & Quick Actions Bar -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-800 text-xs font-bold border border-blue-100 mb-2">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-blue-600"></i>
                Panel Administrator Instansi
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                Dashboard Monitoring Presensi
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }} &bull; Jam Kerja: {{ substr($office->work_start_time, 0, 5) }} - {{ substr($office->work_end_time, 0, 5) }} WIB
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.intern.create') }}" class="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all cursor-pointer">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Tambah Peserta Magang</span>
            </a>
            <a href="{{ route('admin.export.csv', ['month' => date('Y-m')]) }}" class="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-500/20 transition-all cursor-pointer">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Download Spreadsheet</span>
            </a>
            <a href="{{ route('admin.export.print', ['month' => date('Y-m')]) }}" target="_blank" class="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold shadow-md shadow-slate-800/10 transition-all cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Laporan</span>
            </a>
        </div>
    </div>

    <!-- Attendance Metrics Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Peserta Magang</span>
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $totalintern }}</div>
            <span class="text-[11px] text-slate-500 font-medium">Terdaftar aktif</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Hadir Hari Ini</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-emerald-600">{{ $hadirCount }}</div>
            <span class="text-[11px] text-slate-500 font-medium">Dari {{ $totalintern }} peserta magang</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tepat Waktu</span>
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-blue-600">{{ $tepatWaktuCount }}</div>
            <span class="text-[11px] text-emerald-600 font-bold">&le; {{ substr($office->work_start_time, 0, 5) }} WIB</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Terlambat</span>
                <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-rose-600">{{ $terlambatCount }}</div>
            <span class="text-[11px] text-rose-600 font-bold">&gt; {{ substr($office->work_start_time, 0, 5) }} WIB</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition-shadow col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Belum Absen</span>
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="user-x" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-amber-600">{{ $belumHadirCount }}</div>
            <span class="text-[11px] text-amber-600 font-medium">Belum check-in</span>
        </div>

    </div>

    <!-- Today's Attendance Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Kehadiran Hari Ini ({{ \Carbon\Carbon::now()->translatedFormat('d F Y') }})</h3>
                <p class="text-xs text-slate-500">
                    Mode Presensi: <strong class="text-slate-800">{{ $isWeekend ? 'Libur Akhir Pekan' : ($isWfhDay ? 'WFH (Bebas Lokasi)' : 'WFO Lingkungan Kantor') }}</strong>
                </p>
            </div>
            <a href="{{ route('admin.attendance.index', ['date' => date('Y-m-d')]) }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1">
                <span>Buka Rekapitulasi Lengkap</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100 text-[11px]">
                    <tr>
                        <th class="px-6 py-3.5">Nama Peserta Magang</th>
                        <th class="px-6 py-3.5">Tipe</th>
                        <th class="px-6 py-3.5">Jam Masuk</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Jam Pulang</th>
                        <th class="px-6 py-3.5">Jarak Masuk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($todayAttendances as $row)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900">
                                {{ $row->user ? $row->user->name : '-' }}
                                @if($row->user && $row->user->position)
                                    <span class="text-[10px] text-blue-600 font-semibold block">{{ $row->user->position }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {!! $row->type_badge !!}
                            </td>
                            <td class="px-6 py-4 font-mono font-medium text-slate-800">
                                {{ $row->check_in_time ? substr($row->check_in_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                {!! $row->status_badge !!}
                            </td>
                            <td class="px-6 py-4 font-mono font-medium text-slate-800">
                                {{ $row->check_out_time ? substr($row->check_out_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                @if($row->check_in_distance !== null)
                                    <span class="font-mono text-[11px]">{{ $row->check_in_distance }} meter</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                Belum ada peserta magang yang melakukan absensi hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

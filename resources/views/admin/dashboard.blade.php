@extends('layouts.app')

@section('title', 'Dasbor Administrator')

@section('content')
<div class="space-y-6">

    <!-- Top Greeting & Quick Actions Bar -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 text-xs font-semibold mb-1.5">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                Panel Administrator Instansi
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Dasbor Pengawasan Presensi
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }} &bull; Jam Operasional: {{ substr($office->work_start_time, 0, 5) }} - {{ substr($office->work_end_time, 0, 5) }} WIB
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.mahasiswa.create') }}" class="inline-flex items-center gap-2 py-2 px-3.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition-all cursor-pointer">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Tambah Mahasiswa</span>
            </a>
            <a href="{{ route('admin.export.csv', ['month' => date('Y-m')]) }}" class="inline-flex items-center gap-2 py-2 px-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-all cursor-pointer">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Download Excel/CSV</span>
            </a>
            <a href="{{ route('admin.export.print', ['month' => date('Y-m')]) }}" target="_blank" class="inline-flex items-center gap-2 py-2 px-3.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold shadow-xs transition-all cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Laporan</span>
            </a>
        </div>
    </div>

    <!-- Attendance Metrics Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold">Total Mahasiswa</span>
                <i data-lucide="users" class="w-4 h-4 text-blue-600"></i>
            </div>
            <div class="text-2xl font-extrabold text-slate-900">{{ $totalMahasiswa }}</div>
            <span class="text-[11px] text-slate-500 font-medium">Terdaftar di sistem</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold">Hadir Hari Ini</span>
                <i data-lucide="user-check" class="w-4 h-4 text-emerald-600"></i>
            </div>
            <div class="text-2xl font-extrabold text-emerald-700">{{ $hadirCount }}</div>
            <span class="text-[11px] text-slate-500 font-medium">Dari {{ $totalMahasiswa }} mahasiswa</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold">Tepat Waktu</span>
                <i data-lucide="clock" class="w-4 h-4 text-blue-600"></i>
            </div>
            <div class="text-2xl font-extrabold text-blue-700">{{ $tepatWaktuCount }}</div>
            <span class="text-[11px] text-emerald-600 font-medium">&le; {{ substr($office->work_start_time, 0, 5) }} WIB</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold">Terlambat</span>
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
            </div>
            <div class="text-2xl font-extrabold text-rose-700">{{ $terlambatCount }}</div>
            <span class="text-[11px] text-rose-600 font-medium">&gt; {{ substr($office->work_start_time, 0, 5) }} WIB</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold">Belum Absen</span>
                <i data-lucide="user-x" class="w-4 h-4 text-amber-600"></i>
            </div>
            <div class="text-2xl font-extrabold text-amber-700">{{ $belumHadirCount }}</div>
            <span class="text-[11px] text-amber-600 font-medium">Belum check-in</span>
        </div>

    </div>

    <!-- Today's Attendance Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Kehadiran Hari Ini ({{ \Carbon\Carbon::now()->translatedFormat('d F Y') }})</h3>
                <p class="text-xs text-slate-500">
                    Mode Kehadiran: <strong>{{ $isWeekend ? 'Libur Akhir Pekan' : ($isFriday ? 'WFH (Jumat)' : 'WFO Lingkungan Kantor') }}</strong>
                </p>
            </div>
            <a href="{{ route('admin.attendance.index', ['date' => date('Y-m-d')]) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
                Lihat di Rekapitulasi &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">NIM</th>
                        <th class="px-6 py-3.5">Nama Mahasiswa</th>
                        <th class="px-6 py-3.5">Asal Kampus</th>
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
                            <td class="px-6 py-3.5 font-mono text-slate-700 font-medium">
                                {{ $row->user ? $row->user->nim : '-' }}
                            </td>
                            <td class="px-6 py-3.5 font-bold text-slate-900">
                                {{ $row->user ? $row->user->name : '-' }}
                            </td>
                            <td class="px-6 py-3.5 text-slate-600">
                                {{ $row->user ? $row->user->institution : '-' }}
                            </td>
                            <td class="px-6 py-3.5">
                                {!! $row->type_badge !!}
                            </td>
                            <td class="px-6 py-3.5 font-mono font-medium text-slate-800">
                                {{ $row->check_in_time ? substr($row->check_in_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-3.5">
                                {!! $row->status_badge !!}
                            </td>
                            <td class="px-6 py-3.5 font-mono font-medium text-slate-800">
                                {{ $row->check_out_time ? substr($row->check_out_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-3.5 text-slate-500">
                                @if($row->check_in_distance !== null)
                                    <span class="font-mono text-[11px]">{{ $row->check_in_distance }} meter</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                Belum ada mahasiswa yang melakukan absensi hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

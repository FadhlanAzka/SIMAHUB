@extends('layouts.app')

@section('title', 'Riwayat Presensi intern')

@section('content')
<div class="space-y-6">

    <!-- Header & Filter Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Riwayat Presensi intern</h1>
            <p class="text-xs text-slate-500 mt-0.5">Catatan seluruh kehadiran Anda selama pelaksanaan program magang</p>
        </div>

        <!-- Month Filter Form -->
        <form action="{{ route('intern.history') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
            <div class="relative flex-1 sm:flex-initial">
                <input type="month" 
                       name="month" 
                       value="{{ $selectedMonth }}" 
                       class="text-xs font-bold py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800">
            </div>
            <button type="submit" class="py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md shadow-blue-500/20 cursor-pointer">
                Filter
            </button>
        </form>
    </div>

    <!-- Monthly Statistics Widget -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Hadir</span>
            <div class="text-2xl font-black text-blue-600 mt-1">{{ $stats['total_hadir'] }} <span class="text-xs font-normal text-slate-500">Hari</span></div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Tepat Waktu</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['tepat_waktu'] }} <span class="text-xs font-normal text-slate-500">Hari</span></div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Terlambat</span>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ $stats['terlambat'] }} <span class="text-xs font-normal text-slate-500">Hari</span></div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">WFH (Bebas Lokasi)</span>
            <div class="text-2xl font-black text-purple-600 mt-1">{{ $stats['wfh'] }} <span class="text-xs font-normal text-slate-500">Hari</span></div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">
                Data Kehadiran Periode {{ \Carbon\Carbon::parse($selectedMonth . '-01')->translatedFormat('F Y') }}
            </h3>
            <span class="text-xs text-slate-500 font-medium">{{ $attendances->count() }} catatan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100 text-[11px]">
                    <tr>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Hari</th>
                        <th class="px-6 py-3.5">Tipe</th>
                        <th class="px-6 py-3.5">Absen Masuk</th>
                        <th class="px-6 py-3.5">Status Masuk</th>
                        <th class="px-6 py-3.5">Absen Pulang</th>
                        <th class="px-6 py-3.5 relative group" style="cursor: help;">
                            <div class="flex items-center gap-1.5">
                                Durasi Kerja
                                <i data-lucide="info" class="w-3.5 h-3.5 text-slate-400"></i>
                            </div>
                            <!-- Custom Tooltip (Bottom) -->
                            <div class="absolute top-full left-1/2 -translate-x-1/2 mt-2 w-max max-w-[200px] whitespace-normal px-2.5 py-1.5 bg-slate-800 text-white text-[10px] leading-relaxed font-medium rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 shadow-lg pointer-events-none text-center">
                                Total jam sudah dikurangi 1 jam waktu istirahat
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 border-4 border-transparent border-b-slate-800"></div>
                            </div>
                        </th>
                        <th class="px-6 py-3.5">Lokasi GPS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $row)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ $row->date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-700 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($row->date)->translatedFormat('l') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {!! $row->type_badge !!}
                            </td>
                            <td class="px-6 py-4 font-mono font-medium text-slate-800 whitespace-nowrap">
                                {{ $row->check_in_time ? substr($row->check_in_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {!! $row->status_badge !!}
                            </td>
                            <td class="px-6 py-4 font-mono font-medium text-slate-800 whitespace-nowrap">
                                {{ $row->check_out_time ? substr($row->check_out_time, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 whitespace-nowrap font-medium">
                                {{ $row->duration }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                                @if($row->check_in_distance !== null)
                                    <span class="inline-flex items-center gap-1 font-mono text-[11px]">
                                        <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                        {{ $row->check_in_distance }}m
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                Tidak ada data presensi pada bulan yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

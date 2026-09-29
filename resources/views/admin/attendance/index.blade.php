@extends('layouts.app')

@section('title', 'Rekapitulasi Absensi Magang')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Tools -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Rekapitulasi Data Absensi</h1>
            <p class="text-xs text-slate-500 mt-0.5">Monitoring riwayat dan transaksi kehadiran seluruh intern magang</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <!-- Download Spreadsheet -->
            <a href="{{ route('admin.export.csv', request()->query()) }}" class="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-500/20 transition-all cursor-pointer">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Download Spreadsheet</span>
            </a>

            <!-- Print PDF -->
            <a href="{{ route('admin.export.print', request()->query()) }}" target="_blank" class="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold shadow-md shadow-slate-800/10 transition-all cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak Laporan</span>
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('admin.attendance.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3.5 text-xs">
            
            <!-- Month Filter -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Periode Bulan</label>
                <input type="month" 
                       name="month" 
                       value="{{ $selectedMonth }}" 
                       class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800 font-medium">
            </div>

            <!-- Specific Date Filter -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Atau Tanggal Spesifik</label>
                <input type="date" 
                       name="date" 
                       value="{{ $selectedDate }}" 
                       class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800 font-medium">
            </div>

            <!-- intern Filter -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih intern</label>
                <select name="user_id" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800 font-medium">
                    <option value="">-- Semua intern --</option>
                    @foreach($allintern as $m)
                        <option value="{{ $m->id }}" {{ $selectedUserId == $m->id ? 'selected' : '' }}>
                            {{ $m->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Status Kehadiran</label>
                <select name="status" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white text-slate-800 font-medium">
                    <option value="">-- Semua Status --</option>
                    <option value="tepat_waktu" {{ $selectedStatus == 'tepat_waktu' ? 'selected' : '' }}>Tepat Waktu (&le; 07:30)</option>
                    <option value="terlambat" {{ $selectedStatus == 'terlambat' ? 'selected' : '' }}>Terlambat (&gt; 07:30)</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition-all shadow-md shadow-blue-500/20 cursor-pointer flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                <a href="{{ route('admin.attendance.index') }}" class="p-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 transition-colors" title="Reset Filter">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>

        </form>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">
                Data Presensi ({{ $attendances->total() }} catatan ditemukan)
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100 text-[11px]">
                    <tr>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Nama intern</th>
                        <th class="px-6 py-3.5">Tipe</th>
                        <th class="px-6 py-3.5">Masuk</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Pulang</th>
                        <th class="px-6 py-3.5 relative group" style="cursor: help;">
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
                        <th class="px-6 py-3.5">Jarak Masuk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $row)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ $row->date->format('d/m/Y') }}
                                <span class="text-[10px] text-slate-400 font-normal block">
                                    {{ \Carbon\Carbon::parse($row->date)->translatedFormat('l') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ $row->user ? $row->user->name : '-' }}
                                @if($row->user && $row->user->position)
                                    <span class="text-[10px] text-blue-600 font-semibold block">{{ $row->user->position }}</span>
                                @endif
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
                                    <span class="font-mono text-[11px]">{{ $row->check_in_distance }} m</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                Tidak ada data absensi yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
